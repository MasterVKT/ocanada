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
            ->findAll();
        $postes = $this->employeModel->select('poste')
            ->distinct()
            ->findAll();

        return $this->renderView('admin/employees/index', [
            'title'        => 'Gestion des employés',
            'employes'     => $employes,
            'filters'      => $filters,
            'departements' => array_column($departements, 'departement'),
            'postes'       => array_column($postes, 'poste'),
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
     * Traitement de la création (étape 1: infos de base)
     */
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'nom'            => 'required|alpha_space|min_length[2]|max_length[50]',
            'prenom'         => 'required|alpha_space|min_length[2]|max_length[50]',
            'email'          => 'required|valid_email|is_unique[employes.email]',
            'telephone'      => 'permit_empty|regex_match[/^[0-9+\-\s()]+$/]',
            'date_naissance' => 'required|valid_date',
            'date_embauche'  => 'required|valid_date',
            'poste'          => 'required|min_length[2]|max_length[100]',
            'departement'    => 'required|min_length[2]|max_length[50]',
            'salaire_base'   => 'required|numeric|greater_than[0]',
            'adresse'        => 'permit_empty|max_length[255]',
            'ville'          => 'permit_empty|max_length[100]',
            'code_postal'    => 'permit_empty|max_length[10]',
            'pays'           => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nom'            => $this->request->getPost('nom'),
            'prenom'         => $this->request->getPost('prenom'),
            'email'          => $this->request->getPost('email'),
            'telephone'      => $this->request->getPost('telephone'),
            'date_naissance' => $this->request->getPost('date_naissance'),
            'date_embauche'  => $this->request->getPost('date_embauche'),
            'poste'          => $this->request->getPost('poste'),
            'departement'    => $this->request->getPost('departement'),
            'salaire_base'   => (float) $this->request->getPost('salaire_base'),
            'statut'         => 'actif',
            'adresse'        => $this->request->getPost('adresse'),
            'ville'          => $this->request->getPost('ville'),
            'code_postal'    => $this->request->getPost('code_postal'),
            'pays'           => $this->request->getPost('pays') ?: 'Cameroun',
        ];

        $employeId = $this->employeModel->insert($data);

        if (!$employeId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'employé.');
        }

        // Créer l'utilisateur associé
        $userData = [
            'email'         => $data['email'],
            'mot_de_passe'  => password_hash('TempPass123', PASSWORD_BCRYPT, ['cost' => 12]),
            'role'          => 'employe',
            'statut'        => 'actif',
            'employe_id'    => $employeId,
        ];

        $userId = $this->userModel->insert($userData);

        if (!$userId) {
            // Supprimer l'employé si échec création user
            $this->employeModel->delete($employeId);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du compte utilisateur.');
        }

        // Initialiser le solde de congé
        $this->soldeModel->initForEmployee($employeId);

        // Journaliser
        $this->auditLog('CREATION_EMPLOYE', [
            'employe_id' => $employeId,
            'user_id'    => $userId,
            'nom'        => $data['nom'] . ' ' . $data['prenom'],
        ]);

        return redirect()->to('/admin/employees')
            ->with('success', 'Employé créé avec succès. Mot de passe temporaire : TempPass123');
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
            'nom'            => 'required|alpha_space|min_length[2]|max_length[50]',
            'prenom'         => 'required|alpha_space|min_length[2]|max_length[50]',
            'email'          => "required|valid_email|is_unique[employes.email,id,{$id}]",
            'telephone'      => 'permit_empty|regex_match[/^[0-9+\-\s()]+$/]',
            'date_naissance' => 'required|valid_date',
            'date_embauche'  => 'required|valid_date',
            'poste'          => 'required|min_length[2]|max_length[100]',
            'departement'    => 'required|min_length[2]|max_length[50]',
            'salaire_base'   => 'required|numeric|greater_than[0]',
            'statut'         => 'required|in_list[actif,inactif]',
            'adresse'        => 'permit_empty|max_length[255]',
            'ville'          => 'permit_empty|max_length[100]',
            'code_postal'    => 'permit_empty|max_length[10]',
            'pays'           => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nom'            => $this->request->getPost('nom'),
            'prenom'         => $this->request->getPost('prenom'),
            'email'          => $this->request->getPost('email'),
            'telephone'      => $this->request->getPost('telephone'),
            'date_naissance' => $this->request->getPost('date_naissance'),
            'date_embauche'  => $this->request->getPost('date_embauche'),
            'poste'          => $this->request->getPost('poste'),
            'departement'    => $this->request->getPost('departement'),
            'salaire_base'   => (float) $this->request->getPost('salaire_base'),
            'statut'         => $this->request->getPost('statut'),
            'adresse'        => $this->request->getPost('adresse'),
            'ville'          => $this->request->getPost('ville'),
            'code_postal'    => $this->request->getPost('code_postal'),
            'pays'           => $this->request->getPost('pays') ?: 'Cameroun',
        ];

        $oldData = $employe;

        if ($this->employeModel->update($id, $data)) {
            // Mettre à jour l'email dans utilisateurs si changé
            if ($oldData['email'] !== $data['email']) {
                $this->userModel->where('employe_id', $id)
                    ->set(['email' => $data['email']])
                    ->update();
            }

            // Journaliser
            $this->auditLog('MODIFICATION_EMPLOYE', [
                'employe_id' => $id,
                'champs_modifies' => array_diff_assoc($data, $oldData),
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
}