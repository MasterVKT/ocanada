<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle pour la gestion des employés
 */
class EmployeModel extends Model
{
    protected $table            = 'employes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'utilisateur_id',
        'matricule',
        'nom',
        'prenom',
        'email',
        'telephone',
        'telephone_1',
        'telephone_2',
        'date_naissance',
        'genre',
        'nationalite',
        'numero_cni',
        'date_embauche',
        'poste',
        'departement',
        'type_contrat',
        'date_fin_contrat',
        'salaire_journalier',
        'salaire_base',
        'heure_debut_travail',
        'heure_fin_travail',
        'statut',
        'date_desactivation',
        'pin_kiosque',
        'photo',
        'adresse',
        'ville',
        'code_postal',
        'pays',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'salaire_journalier' => '?float',
        'salaire_base' => '?float',
    ];
    protected array $castHandlers = [];

    // Dates - Use manual timestamps
    protected $useTimestamps = false;
    protected $createdField  = 'date_creation';
    protected $updatedField  = 'date_modification';

    // Validation - rules for employee creation
    protected $validationRules = [
        'nom'           => 'required|alpha_space|min_length[2]|max_length[100]',
        'prenom'        => 'required|alpha_space|min_length[2]|max_length[100]',
        'email'         => 'permit_empty|valid_email|max_length[255]',
        'telephone'     => 'permit_empty|max_length[20]',
        'date_naissance' => 'permit_empty|valid_date',
        'date_embauche' => 'required|valid_date',
        'poste'         => 'permit_empty|max_length[150]',
        'departement'   => 'permit_empty|max_length[100]',
        'statut'        => 'in_list[actif,inactif]',
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateMatricule'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * @var string[]|null
     */
    private ?array $tableFieldsCache = null;

    protected function initialize(): void
    {
        parent::initialize();

        // Keep only columns that actually exist in the current DB schema.
        $existing = $this->getTableFields();
        if ($existing !== []) {
            $this->allowedFields = array_values(array_filter(
                $this->allowedFields,
                static fn(string $field): bool => in_array($field, $existing, true)
            ));
        }
    }

    /**
     * Génère un matricule unique
     */
    protected function generateMatricule(array $data): array
    {
        if (empty($data['data']['matricule'])) {
            $year = date('Y');
            $prefix = sprintf('EMP-%s-', $year);

            // Build the sequence directly from table rows to avoid model state side effects.
            $rows = $this->db->table($this->table)
                ->select('matricule')
                ->like('matricule', $prefix, 'after')
                ->get()
                ->getResultArray();

            $maxSequence = 0;
            foreach ($rows as $row) {
                $matricule = (string) ($row['matricule'] ?? '');
                if (!str_starts_with($matricule, $prefix)) {
                    continue;
                }

                $sequence = (int) substr($matricule, strlen($prefix));
                if ($sequence > $maxSequence) {
                    $maxSequence = $sequence;
                }
            }

            $sequence = $maxSequence + 1;
            $candidate = sprintf('%s%04d', $prefix, $sequence);

            while ($this->matriculeExists($candidate)) {
                $sequence++;
                $candidate = sprintf('%s%04d', $prefix, $sequence);
            }

            $data['data']['matricule'] = $candidate;
        }

        return $data;
    }

    private function matriculeExists(string $matricule): bool
    {
        return $this->db->table($this->table)
            ->where('matricule', $matricule)
            ->countAllResults() > 0;
    }

    /**
     * Recherche d'employés avec filtres
     */
    public function search(array $filters = []): array
    {
        $builder = $this->builder();
        $hasEmail = $this->hasColumn('email');
        $hasTelephone = $this->hasColumn('telephone');
        $hasTelephone1 = $this->hasColumn('telephone_1');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart();
            $builder->like('nom', $search)
                ->orLike('prenom', $search)
                ->orLike('matricule', $search);

            if ($hasEmail) {
                $builder->orLike('email', $search);
            }

            if ($hasTelephone) {
                $builder->orLike('telephone', $search);
            }

            if ($hasTelephone1) {
                $builder->orLike('telephone_1', $search);
            }

            $builder->groupEnd();
        }

        if (!empty($filters['departement'])) {
            $builder->where('departement', $filters['departement']);
        }

        if (!empty($filters['statut'])) {
            $builder->where('statut', $filters['statut']);
        }

        if (!empty($filters['poste'])) {
            $builder->where('poste', $filters['poste']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Calcule l'ancienneté en années
     */
    public function getAnciennete(int $employeId): float
    {
        $employe = $this->find($employeId);
        if (!$employe || empty($employe['date_embauche'])) {
            return 0;
        }

        $dateEmbauche = strtotime($employe['date_embauche']);
        $now = time();

        return round(($now - $dateEmbauche) / (365.25 * 24 * 3600), 1);
    }

    /**
     * Vérifie si l'employé a un PIN valide
     */
    public function hasValidPin(int $employeId): bool
    {
        $employe = $this->find($employeId);
        return $employe && !empty($employe['pin_kiosque']) && strlen($employe['pin_kiosque']) === 4;
    }

    /**
     * Met à jour le PIN kiosque
     */
    public function updatePin(int $employeId, string $pin): bool
    {
        return $this->update($employeId, ['pin_kiosque' => password_hash($pin, PASSWORD_BCRYPT, ['cost' => 12])]);
    }

    /**
     * Récupère la liste des employés actifs
     */
    public function getActiveList(): array
    {
        return $this->where('statut', 'actif')
            ->orderBy('nom', 'ASC')
            ->orderBy('prenom', 'ASC')
            ->findAll();
    }

    /**
     * @return string[]
     */
    private function getTableFields(): array
    {
        if ($this->tableFieldsCache !== null) {
            return $this->tableFieldsCache;
        }

        try {
            $this->tableFieldsCache = $this->db->getFieldNames($this->table);
        } catch (\Throwable) {
            $this->tableFieldsCache = [];
        }

        return $this->tableFieldsCache;
    }

    private function hasColumn(string $column): bool
    {
        return in_array($column, $this->getTableFields(), true);
    }
}
