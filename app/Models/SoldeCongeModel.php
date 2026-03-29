<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle pour la gestion des soldes de congé
 */
class SoldeCongeModel extends Model
{
    protected $table            = 'soldes_conges';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'employe_id',
        'annee',
        'solde_annuel',
        'pris',
        'restant',
        'reporte',
        'maladie_pris',
        'maladie_restant',
        'date_creation',
        'date_modification',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'solde_annuel'    => 'float',
        'pris'           => 'float',
        'restant'        => 'float',
        'reporte'        => 'float',
        'maladie_pris'   => 'float',
        'maladie_restant' => 'float',
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'date_creation';
    protected $updatedField  = 'date_modification';

    // Validation
    protected $validationRules = [
        'employe_id'      => 'required|integer|is_not_unique[employes.id]',
        'annee'          => 'required|integer|greater_than[2000]',
        'solde_annuel'   => 'required|numeric|greater_than_equal_to[0]',
        'pris'           => 'required|numeric|greater_than_equal_to[0]',
        'restant'        => 'required|numeric|greater_than_equal_to[0]',
        'reporte'        => 'permit_empty|numeric|greater_than_equal_to[0]',
        'maladie_pris'   => 'required|numeric|greater_than_equal_to[0]',
        'maladie_restant' => 'required|numeric|greater_than_equal_to[0]',
    ];

    protected $validationMessages = [
        'employe_id' => [
            'required' => 'L\'ID employé est obligatoire.',
            'integer'  => 'L\'ID employé doit être un entier.',
            'is_not_unique' => 'L\'employé n\'existe pas.',
        ],
        'annee' => [
            'required' => 'L\'année est obligatoire.',
            'integer'  => 'L\'année doit être un entier.',
            'greater_than' => 'L\'année doit être supérieure à 2000.',
        ],
        'solde_annuel' => [
            'required' => 'Le solde annuel est obligatoire.',
            'numeric'  => 'Le solde annuel doit être un nombre.',
            'greater_than_equal_to' => 'Le solde annuel ne peut pas être négatif.',
        ],
        'pris' => [
            'required' => 'Le nombre de jours pris est obligatoire.',
            'numeric'  => 'Le nombre de jours pris doit être un nombre.',
            'greater_than_equal_to' => 'Le nombre de jours pris ne peut pas être négatif.',
        ],
        'restant' => [
            'required' => 'Le solde restant est obligatoire.',
            'numeric'  => 'Le solde restant doit être un nombre.',
            'greater_than_equal_to' => 'Le solde restant ne peut pas être négatif.',
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
     * Initialise le solde de congé pour un employé
     */
    public function initForEmployee(int $employeId): bool
    {
        $employeModel = model(EmployeModel::class);
        $employe = $employeModel->find($employeId);

        if (!$employe) {
            return false;
        }

        $annee = (int) date('Y');
        $anciennete = $employeModel->getAnciennete($employeId);

        // Calcul selon règles OHADA
        $soldeAnnuel = $this->calculateSoldeAnnuel($anciennete);

        // Vérifier si existe déjà
        $existing = $this->where('employe_id', $employeId)
            ->where('annee', $annee)
            ->first();

        if ($existing) {
            return true; // Déjà initialisé
        }

        return $this->insert([
            'employe_id'      => $employeId,
            'annee'          => $annee,
            'solde_annuel'   => $soldeAnnuel,
            'pris'           => 0,
            'restant'        => $soldeAnnuel,
            'reporte'        => 0,
            'maladie_pris'   => 0,
            'maladie_restant' => 30, // 30 jours maladie par an
        ]);
    }

    /**
     * Calcule le solde annuel selon l'ancienneté (règles OHADA)
     */
    protected function calculateSoldeAnnuel(float $anciennete): float
    {
        if ($anciennete < 1) {
            return 0; // Pas de congé avant 1 an
        } elseif ($anciennete < 5) {
            return 25; // 25 jours pour 1-4 ans
        } elseif ($anciennete < 10) {
            return 30; // 30 jours pour 5-9 ans
        } else {
            return 35; // 35 jours pour 10+ ans
        }
    }

    /**
     * Met à jour le solde après approbation d'un congé
     */
    public function updateAfterLeaveApproval(int $employeId, float $joursPris, string $typeConge = 'annuel'): bool
    {
        $annee = (int) date('Y');
        $solde = $this->where('employe_id', $employeId)
            ->where('annee', $annee)
            ->first();

        if (!$solde) {
            return false;
        }

        if ($typeConge === 'maladie') {
            $nouveauPris = $solde['maladie_pris'] + $joursPris;
            $nouveauRestant = max(0, $solde['maladie_restant'] - $joursPris);

            return $this->update($solde['id'], [
                'maladie_pris' => $nouveauPris,
                'maladie_restant' => $nouveauRestant,
            ]);
        } else {
            $nouveauPris = $solde['pris'] + $joursPris;
            $nouveauRestant = max(0, $solde['restant'] - $joursPris);

            return $this->update($solde['id'], [
                'pris' => $nouveauPris,
                'restant' => $nouveauRestant,
            ]);
        }
    }

    /**
     * Récupère le solde actuel d'un employé
     */
    public function getCurrentSolde(int $employeId): ?array
    {
        $annee = (int) date('Y');
        return $this->where('employe_id', $employeId)
            ->where('annee', $annee)
            ->first();
    }

    /**
     * Vérifie si l'employé a assez de solde pour le congé demandé
     */
    public function hasEnoughSolde(int $employeId, float $joursDemandes, string $typeConge = 'annuel'): bool
    {
        $solde = $this->getCurrentSolde($employeId);

        if (!$solde) {
            return false;
        }

        if ($typeConge === 'maladie') {
            return $solde['maladie_restant'] >= $joursDemandes;
        } else {
            return $solde['restant'] >= $joursDemandes;
        }
    }
}