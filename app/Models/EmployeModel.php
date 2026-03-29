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
        'matricule',
        'nom',
        'prenom',
        'email',
        'telephone',
        'date_naissance',
        'date_embauche',
        'poste',
        'departement',
        'salaire_base',
        'statut',
        'pin_kiosque',
        'photo',
        'adresse',
        'ville',
        'code_postal',
        'pays'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'date_creation';
    protected $updatedField  = 'date_modification';

    // Validation
    protected $validationRules = [
        'matricule'     => 'required|is_unique[employes.matricule,id,{id}]',
        'nom'           => 'required|alpha_space|min_length[2]|max_length[50]',
        'prenom'        => 'required|alpha_space|min_length[2]|max_length[50]',
        'email'         => 'required|valid_email|is_unique[employes.email,id,{id}]',
        'telephone'     => 'permit_empty|regex_match[/^[0-9+\-\s()]+$/]',
        'date_naissance' => 'required|valid_date',
        'date_embauche' => 'required|valid_date',
        'poste'         => 'required|min_length[2]|max_length[100]',
        'departement'   => 'required|min_length[2]|max_length[50]',
        'salaire_base'  => 'required|numeric|greater_than[0]',
        'statut'        => 'required|in_list[actif,inactif]',
        'pin_kiosque'   => 'permit_empty|exact_length[4]|numeric',
    ];

    protected $validationMessages = [
        'matricule' => [
            'required'   => 'Le matricule est obligatoire.',
            'is_unique'  => 'Ce matricule existe déjà.',
        ],
        'nom' => [
            'required'   => 'Le nom est obligatoire.',
            'alpha_space' => 'Le nom ne peut contenir que des lettres et espaces.',
            'min_length' => 'Le nom doit contenir au moins 2 caractères.',
            'max_length' => 'Le nom ne peut pas dépasser 50 caractères.',
        ],
        'prenom' => [
            'required'   => 'Le prénom est obligatoire.',
            'alpha_space' => 'Le prénom ne peut contenir que des lettres et espaces.',
            'min_length' => 'Le prénom doit contenir au moins 2 caractères.',
            'max_length' => 'Le prénom ne peut pas dépasser 50 caractères.',
        ],
        'email' => [
            'required'    => 'L\'email est obligatoire.',
            'valid_email' => 'L\'email n\'est pas valide.',
            'is_unique'   => 'Cet email est déjà utilisé.',
        ],
        'telephone' => [
            'regex_match' => 'Le numéro de téléphone n\'est pas valide.',
        ],
        'date_naissance' => [
            'required'    => 'La date de naissance est obligatoire.',
            'valid_date'  => 'La date de naissance n\'est pas valide.',
        ],
        'date_embauche' => [
            'required'    => 'La date d\'embauche est obligatoire.',
            'valid_date'  => 'La date d\'embauche n\'est pas valide.',
        ],
        'poste' => [
            'required'   => 'Le poste est obligatoire.',
            'min_length' => 'Le poste doit contenir au moins 2 caractères.',
            'max_length' => 'Le poste ne peut pas dépasser 100 caractères.',
        ],
        'departement' => [
            'required'   => 'Le département est obligatoire.',
            'min_length' => 'Le département doit contenir au moins 2 caractères.',
            'max_length' => 'Le département ne peut pas dépasser 50 caractères.',
        ],
        'salaire_base' => [
            'required'      => 'Le salaire de base est obligatoire.',
            'numeric'       => 'Le salaire doit être un nombre.',
            'greater_than'  => 'Le salaire doit être positif.',
        ],
        'statut' => [
            'required' => 'Le statut est obligatoire.',
            'in_list'  => 'Le statut doit être actif ou inactif.',
        ],
        'pin_kiosque' => [
            'exact_length' => 'Le PIN doit contenir exactement 4 chiffres.',
            'numeric'      => 'Le PIN ne peut contenir que des chiffres.',
        ],
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
     * Génère un matricule unique
     */
    protected function generateMatricule(array $data): array
    {
        if (empty($data['data']['matricule'])) {
            $year = date('Y');
            $count = $this->where('YEAR(date_creation)', $year)->countAllResults() + 1;
            $data['data']['matricule'] = sprintf('EMP%s%04d', $year, $count);
        }

        return $data;
    }

    /**
     * Recherche d'employés avec filtres
     */
    public function search(array $filters = []): array
    {
        $builder = $this->builder();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $builder->groupStart()
                ->like('nom', $search)
                ->orLike('prenom', $search)
                ->orLike('matricule', $search)
                ->orLike('email', $search)
                ->groupEnd();
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
        if (!$employe) {
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
        return $this->update($employeId, ['pin_kiosque' => $pin]);
    }
}