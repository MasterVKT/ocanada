<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle pour la gestion des jours fériés
 */
class JourFerieModel extends Model
{
    protected $table            = 'jours_feries';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'date_ferie',
        'designation',
        'type',
        'annee'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules = [
        'date_ferie'  => 'required|valid_date',
        'designation' => 'required|max_length[100]',
        'type'        => 'required|in_list[fixe,variable]',
        'annee'       => 'required|integer|greater_than[2000]',
    ];

    protected $validationMessages = [
        'date_ferie' => [
            'required'   => 'La date est obligatoire.',
            'valid_date' => 'La date n\'est pas valide.',
        ],
        'designation' => [
            'required'   => 'La désignation est obligatoire.',
            'max_length' => 'La désignation ne peut pas dépasser 100 caractères.',
        ],
        'type' => [
            'required' => 'Le type est obligatoire.',
            'in_list'  => 'Le type doit être fixe ou variable.',
        ],
        'annee' => [
            'required' => 'L\'année est obligatoire.',
            'integer'  => 'L\'année doit être un entier.',
            'greater_than' => 'L\'année doit être supérieure à 2000.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Récupère les jours fériés pour une année donnée
     */
    public function getHolidaysForYear(int $year): array
    {
        return $this->where('annee', $year)->findAll();
    }

    /**
     * Vérifie si une date est fériée
     */
    public function isHoliday(string $date): bool
    {
        return $this->where('date_ferie', $date)->countAllResults() > 0;
    }

    /**
     * Récupère tous les jours fériés
     */
    public function getAllHolidays(): array
    {
        return $this->orderBy('date_ferie', 'ASC')->findAll();
    }
}