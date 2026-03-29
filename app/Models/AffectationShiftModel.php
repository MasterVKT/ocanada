<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle pour la gestion des affectations de shifts
 */
class AffectationShiftModel extends Model
{
    protected $table            = 'affectations_shifts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'employe_id',
        'shift_id',
        'date_debut',
        'date_fin',
        'actif'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'actif' => 'boolean',
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'date_creation';
    protected $updatedField  = 'date_modification';

    // Validation
    protected $validationRules = [
        'employe_id' => 'required|integer|is_not_unique[employes.id]',
        'shift_id'   => 'required|integer|is_not_unique[shifts_modeles.id]',
        'date_debut' => 'required|valid_date',
        'date_fin'   => 'permit_empty|valid_date',
        'actif'      => 'permit_empty|in_list[0,1]',
    ];

    protected $validationMessages = [
        'employe_id' => [
            'required' => 'L\'ID employé est obligatoire.',
            'integer'  => 'L\'ID employé doit être un entier.',
            'is_not_unique' => 'L\'employé n\'existe pas.',
        ],
        'shift_id' => [
            'required' => 'L\'ID shift est obligatoire.',
            'integer'  => 'L\'ID shift doit être un entier.',
            'is_not_unique' => 'Le shift n\'existe pas.',
        ],
        'date_debut' => [
            'required'   => 'La date de début est obligatoire.',
            'valid_date' => 'La date de début n\'est pas valide.',
        ],
        'date_fin' => [
            'valid_date' => 'La date de fin n\'est pas valide.',
        ],
        'actif' => [
            'in_list' => 'Le statut actif doit être 0 ou 1.',
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
     * Récupère le shift affecté à un employé pour une date donnée
     */
    public function getShiftForEmployeeOnDate(int $employeId, string $date): ?array
    {
        $builder = $this->db->table('affectations_shifts a');
        $builder->select('s.*');
        $builder->join('shifts_modeles s', 's.id = a.shift_id');
        $builder->where('a.employe_id', $employeId);
        $builder->where('a.actif', 1);
        $builder->where('a.date_debut <=', $date);
        $builder->where('(a.date_fin IS NULL OR a.date_fin >=)', $date);

        $result = $builder->get()->getRowArray();

        return $result ?: null;
    }

    /**
     * Récupère toutes les affectations actives d'un employé
     */
    public function getActiveAffectationsForEmployee(int $employeId): array
    {
        return $this->where('employe_id', $employeId)
            ->where('actif', 1)
            ->orderBy('date_debut', 'DESC')
            ->findAll();
    }

    /**
     * Affecte un shift à un employé
     */
    public function assignShift(int $employeId, int $shiftId, string $dateDebut, ?string $dateFin = null): bool
    {
        // Désactiver les affectations précédentes qui chevauchent
        $this->where('employe_id', $employeId)
            ->where('actif', 1)
            ->where('date_debut <=', $dateFin ?: date('Y-m-d'))
            ->where('(date_fin IS NULL OR date_fin >=)', $dateDebut)
            ->set(['actif' => 0])
            ->update();

        // Créer la nouvelle affectation
        return $this->insert([
            'employe_id' => $employeId,
            'shift_id'   => $shiftId,
            'date_debut' => $dateDebut,
            'date_fin'   => $dateFin,
            'actif'      => 1,
        ]);
    }
}