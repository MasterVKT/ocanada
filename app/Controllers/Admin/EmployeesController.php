<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EmployeModel;
use App\Models\SoldeCongeModel;
use App\Models\UtilisateurModel;

/**
 * Contrôleur de gestion des employés (Admin)
 */
class EmployeesController extends BaseController
{
    protected EmployeModel $employeModel;
    protected UtilisateurModel $userModel;
    protected SoldeCongeModel $soldeModel;
    /**
     * @var string[]|null
     */
    private ?array $employeeColumnsCache = null;

    public function __construct()
    {
        $this->employeModel = model(EmployeModel::class);
        $this->userModel    = model(UtilisateurModel::class);
        $this->soldeModel   = model(SoldeCongeModel::class);
    }

    /**
     * Liste des employés
     */
    public function index(): string
    {
        $filters = [
            'search'      => $this->request->getGet('search'),
            'departement' => $this->request->getGet('departement'),
            'statut'      => $this->request->getGet('statut'),
            'poste'       => $this->request->getGet('poste'),
        ];

        $employes = $this->employeModel->search($filters);

        // Pagination
        $perPage = 10;
        $page = (int) ($this->request->getGet('page') ?? 1);
        $offset = ($page - 1) * $perPage;
        $total = count($employes);
        $employes = array_slice($employes, $offset, $perPage);

        // Données pour les filtres
        $departements = $this->employeModel->select('departement')
            ->distinct()
            ->where('departement IS NOT NULL')
            ->findAll();
        $postes = $this->employeModel->select('poste')
            ->distinct()
            ->where('poste IS NOT NULL')
            ->findAll();

        return $this->renderView('admin/employees/index', [
            'title'        => 'Gestion des employés',
            'employes'     => $employes,
            'filters'      => $filters,
            'departements' => array_filter(array_column($departements, 'departement')),
            'postes'       => array_filter(array_column($postes, 'poste')),
            'pager'        => [
                'currentPage' => $page,
                'perPage'     => $perPage,
                'total'       => $total,
                'lastPage'    => ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Formulaire de création d'employé
     */
    public function create(): string
    {
        return $this->renderView('admin/employees/create', [
            'title' => 'Créer un employé',
        ]);
    }

    /**
     * Traitement de la création d'employé
     */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'nom'            => 'required|alpha_space|min_length[2]|max_length[100]',
            'prenom'         => 'required|alpha_space|min_length[2]|max_length[100]',
            'email'          => 'required|valid_email|max_length[255]',
            'telephone_1'    => 'permit_empty|max_length[20]',
            'telephone_2'    => 'permit_empty|max_length[20]',
            'date_naissance' => 'permit_empty|valid_date',
            'date_embauche'  => 'required|valid_date',
            'poste'          => 'required|max_length[150]',
            'departement'    => 'required|max_length[100]',
            'type_contrat'   => 'permit_empty|in_list[CDI,CDD,stage,consultant]',
            'date_fin_contrat' => 'permit_empty|valid_date',
            'salaire_journalier' => 'required|decimal|greater_than_equal_to[0]',
            'heure_debut_travail' => 'permit_empty|regex_match[/^([01]\d|2[0-3]):[0-5]\d$/]',
            'heure_fin_travail' => 'permit_empty|regex_match[/^([01]\d|2[0-3]):[0-5]\d$/]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = trim((string) $this->request->getPost('email'));
        if ($this->hasEmployeeColumn('email') && $this->employeModel->where('email', $email)->first() !== null) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cet email est déjà utilisé par un employé.');
        }

        if ($this->userModel->where('email', $email)->first() !== null) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cet email est déjà utilisé par un compte utilisateur.');
        }

        // Mapper uniquement les colonnes réellement présentes en base.
        $employeData = $this->buildEmployeePayloadForPersistence($email);

        $this->db->transBegin();

        try {
            $employeId = $this->employeModel->insert($employeData);

            if (!$employeId) {
                throw new \RuntimeException('Erreur lors de la création de l\'employé.');
            }

            $userData = [
                'email'         => $email,
                'mot_de_passe'  => password_hash('TempPass123', PASSWORD_BCRYPT, ['cost' => 12]),
                'role'          => 'employe',
                'statut'        => 'actif',
                'employe_id'    => $employeId,
                'date_creation' => date('Y-m-d H:i:s'),
            ];

            $userId = $this->userModel->insert($userData);

            if (!$userId) {
                throw new \RuntimeException('Erreur lors de la création du compte utilisateur.');
            }

            // Mettre à jour employe avec utilisateur_id
            if ($this->hasEmployeeColumn('utilisateur_id') && !$this->employeModel->update((int) $employeId, ['utilisateur_id' => $userId])) {
                throw new \RuntimeException('Erreur lors de la liaison employé/utilisateur.');
            }

            // Initialiser le solde de congé
            if (!$this->soldeModel->initForEmployee((int) $employeId)) {
                throw new \RuntimeException('Erreur lors de l\'initialisation du solde de congé.');
            }

            // Journaliser
            $this->auditLog('CREATION_EMPLOYE', [
                'employe_id' => $employeId,
                'nom'        => $employeData['nom'] . ' ' . $employeData['prenom'],
            ]);

            $this->db->transCommit();

            return redirect()->to('/admin/employees')
                ->with('success', 'Employé créé avec succès.');
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Erreur création employé: {message}', ['message' => $e->getMessage()]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Impossible de créer l\'employé pour le moment. Vérifiez les données saisies puis réessayez.');
        }
    }

    /**
     * Affichage d'un employé
     */
    public function show(int $id): string
    {
        $employe = $this->employeModel->find($id);

        if (!$employe) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $solde = $this->soldeModel->getCurrentSolde($id);
        $anciennete = $this->employeModel->getAnciennete($id);

        return $this->renderView('admin/employees/show', [
            'title'      => 'Détails employé',
            'employe'    => $employe,
            'solde'      => $solde,
            'anciennete' => $anciennete,
        ]);
    }

    /**
     * Formulaire d'édition
     */
    public function edit(int $id): string
    {
        $employe = $this->employeModel->find($id);

        if (!$employe) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->renderView('admin/employees/edit', [
            'title'   => 'Modifier employé',
            'employe' => $employe,
        ]);
    }

    /**
     * Mise à jour d'un employé
     */
    public function update(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $employe = $this->employeModel->find($id);

        if (!$employe) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $rules = [
            'nom'            => 'required|alpha_space|min_length[2]|max_length[100]',
            'prenom'         => 'required|alpha_space|min_length[2]|max_length[100]',
            'email'          => 'required|valid_email|max_length[255]',
            'telephone_1'    => 'permit_empty|max_length[20]',
            'telephone_2'    => 'permit_empty|max_length[20]',
            'date_naissance' => 'permit_empty|valid_date',
            'date_embauche'  => 'required|valid_date',
            'poste'          => 'required|max_length[150]',
            'departement'    => 'required|max_length[100]',
            'type_contrat'   => 'permit_empty|in_list[CDI,CDD,stage,consultant]',
            'date_fin_contrat' => 'permit_empty|valid_date',
            'salaire_journalier' => 'required|decimal|greater_than_equal_to[0]',
            'heure_debut_travail' => 'permit_empty|regex_match[/^([01]\d|2[0-3]):[0-5]\d$/]',
            'heure_fin_travail' => 'permit_empty|regex_match[/^([01]\d|2[0-3]):[0-5]\d$/]',
            'statut'         => 'in_list[actif,inactif]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = trim((string) $this->request->getPost('email'));
        $existingUser = $this->userModel->where('employe_id', $id)->first();
        if ($existingUser !== null && (int) ($existingUser['id'] ?? 0) > 0) {
            $otherUser = $this->userModel->where('email', $email)->where('id !=', (int) $existingUser['id'])->first();
            if ($otherUser !== null) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Cet email est déjà utilisé par un autre compte utilisateur.');
            }
        } elseif ($this->userModel->where('email', $email)->first() !== null) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cet email est déjà utilisé par un compte utilisateur.');
        }

        if ($this->hasEmployeeColumn('email')) {
            $query = $this->employeModel->where('email', $email)->where('id !=', $id)->first();
            if ($query !== null) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Cet email est déjà utilisé par un employé.');
            }
        }

        $employeData = $this->buildEmployeePayloadForPersistence($email, true);
        $employeData['statut'] = $this->request->getPost('statut') ?: 'actif';

        if ($this->employeModel->update($id, $employeData)) {
            // Mettre à jour l'email utilisateur pour garder la cohérence d'authentification.
            $this->userModel->where('employe_id', $id)
                ->set(['email' => $email])
                ->update();

            // Journaliser
            $this->auditLog('MODIFICATION_EMPLOYE', [
                'employe_id' => $id,
            ]);

            return redirect()->to("/admin/employees/{$id}")
                ->with('success', 'Employé modifié avec succès.');
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'Erreur lors de la modification.');
    }

    /**
     * Désactivation d'un employé
     */
    public function deactivate(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $employe = $this->employeModel->find($id);

        if (!$employe) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if ($this->employeModel->update($id, ['statut' => 'inactif'])) {
            // Désactiver aussi le compte utilisateur
            $this->userModel->where('employe_id', $id)
                ->set(['statut' => 'inactif'])
                ->update();

            $this->auditLog('DESACTIVATION_EMPLOYE', [
                'employe_id' => $id,
                'nom'        => $employe['nom'] . ' ' . $employe['prenom'],
            ]);

            return redirect()->to('/admin/employees')
                ->with('success', 'Employé désactivé avec succès.');
        }

        return redirect()->back()
            ->with('error', 'Erreur lors de la désactivation.');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEmployeePayloadForPersistence(string $email, bool $forUpdate = false): array
    {
        $payload = [
            'nom' => $this->request->getPost('nom'),
            'prenom' => $this->request->getPost('prenom'),
            'date_naissance' => $this->request->getPost('date_naissance') ?: null,
            'genre' => $this->request->getPost('genre') ?: null,
            'nationalite' => $this->request->getPost('nationalite') ?: null,
            'numero_cni' => $this->request->getPost('numero_cni') ?: null,
            'telephone' => $this->request->getPost('telephone_1') ?: null,
            'telephone_1' => $this->request->getPost('telephone_1') ?: null,
            'telephone_2' => $this->request->getPost('telephone_2') ?: null,
            'email' => $email,
            'date_embauche' => $this->request->getPost('date_embauche'),
            'poste' => $this->request->getPost('poste'),
            'departement' => $this->request->getPost('departement'),
            'type_contrat' => $this->request->getPost('type_contrat') ?: null,
            'date_fin_contrat' => $this->request->getPost('date_fin_contrat') ?: null,
            'heure_debut_travail' => $this->request->getPost('heure_debut_travail') ?: null,
            'heure_fin_travail' => $this->request->getPost('heure_fin_travail') ?: null,
            'adresse' => $this->request->getPost('adresse') ?: null,
            'ville' => $this->request->getPost('ville') ?: null,
            'code_postal' => $this->request->getPost('code_postal') ?: null,
            'pays' => $this->request->getPost('pays') ?: 'Cameroun',
        ];

        $dailySalary = (float) $this->request->getPost('salaire_journalier');
        $payload['salaire_journalier'] = $dailySalary;
        // Keep legacy monthly salary column synchronized when present.
        $payload['salaire_base'] = round($dailySalary * 22, 2);

        if (!$forUpdate) {
            $payload['statut'] = 'actif';
        }

        return $this->filterByExistingEmployeeColumns($payload);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function filterByExistingEmployeeColumns(array $payload): array
    {
        $columns = $this->getEmployeeColumns();

        return array_filter(
            $payload,
            static fn(string $key): bool => in_array($key, $columns, true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @return string[]
     */
    private function getEmployeeColumns(): array
    {
        if ($this->employeeColumnsCache !== null) {
            return $this->employeeColumnsCache;
        }

        try {
            $this->employeeColumnsCache = $this->db->getFieldNames('employes');
        } catch (\Throwable) {
            $this->employeeColumnsCache = [];
        }

        return $this->employeeColumnsCache;
    }

    private function hasEmployeeColumn(string $column): bool
    {
        return in_array($column, $this->getEmployeeColumns(), true);
    }
}
