<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle pour la gestion des soldes de congé
 */
class SoldeCongeModel extends Model
{
    private ?bool $usesLegacyColumns = null;

    protected $table            = 'soldes_conges';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'employe_id',
        'annee',
        'jours_total',
        'jours_pris',
        'jours_restants',
        'date_mise_a_jour',
        'solde_annuel',
        'pris',
        'restant',
        'date_creation',
        'date_modification',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'jours_total'     => '?float',
        'jours_pris'      => '?float',
        'jours_restants'  => '?float',
        'solde_annuel'    => '?float',
        'pris'            => '?float',
        'restant'         => '?float',
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $createdField  = 'date_creation';
    protected $updatedField  = 'date_modification';

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

        if ($this->isLegacySchema()) {
            return (bool) $this->insert([
                'employe_id'        => $employeId,
                'annee'             => $annee,
                'solde_annuel'      => $soldeAnnuel,
                'pris'              => 0,
                'restant'           => $soldeAnnuel,
                'date_creation'     => date('Y-m-d H:i:s'),
                'date_modification' => date('Y-m-d H:i:s'),
            ]);
        }

        return (bool) $this->insert([
            'employe_id'       => $employeId,
            'annee'            => $annee,
            'jours_total'      => $soldeAnnuel,
            'jours_pris'       => 0,
            'jours_restants'   => $soldeAnnuel,
            'date_mise_a_jour' => date('Y-m-d H:i:s'),
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
    public function updateAfterLeaveApproval(int $employeId, float $joursPris): bool
    {
        $annee = (int) date('Y');
        $solde = $this->where('employe_id', $employeId)
            ->where('annee', $annee)
            ->first();

        if (!$solde) {
            return false;
        }

        if ($this->isLegacySchema()) {
            $nouveauPris = (float) ($solde['pris'] ?? 0) + $joursPris;
            $nouveauRestant = max(0, (float) ($solde['restant'] ?? 0) - $joursPris);

            return $this->update($solde['id'], [
                'pris'              => $nouveauPris,
                'restant'           => $nouveauRestant,
                'date_modification' => date('Y-m-d H:i:s'),
            ]);
        }

        $nouveauPris = (float) ($solde['jours_pris'] ?? 0) + $joursPris;
        $nouveauRestant = max(0, (float) ($solde['jours_restants'] ?? 0) - $joursPris);

        return $this->update($solde['id'], [
            'jours_pris'       => $nouveauPris,
            'jours_restants'   => $nouveauRestant,
            'date_mise_a_jour' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Récupère le solde actuel d'un employé
     */
    public function getCurrentSolde(int $employeId): ?array
    {
        $annee = (int) date('Y');
        $solde = $this->where('employe_id', $employeId)
            ->where('annee', $annee)
            ->first();

        if (!$solde) {
            return null;
        }

        // Normalize legacy keys for current callers.
        if ($this->isLegacySchema()) {
            $solde['jours_total'] = (float) ($solde['solde_annuel'] ?? 0);
            $solde['jours_pris'] = (float) ($solde['pris'] ?? 0);
            $solde['jours_restants'] = (float) ($solde['restant'] ?? 0);
        }

        return $solde;
    }

    /**
     * Vérifie si l'employé a assez de solde pour le congé demandé
     */
    public function hasEnoughSolde(int $employeId, float $joursDemandes): bool
    {
        $solde = $this->getCurrentSolde($employeId);

        if (!$solde) {
            return false;
        }

        return (float) $solde['jours_restants'] >= $joursDemandes;
    }

    /**
     * Detects if soldes_conges uses legacy columns (solde_annuel/pris/restant).
     */
    private function isLegacySchema(): bool
    {
        if ($this->usesLegacyColumns !== null) {
            return $this->usesLegacyColumns;
        }

        $this->usesLegacyColumns = $this->db->fieldExists('solde_annuel', $this->table)
            && !$this->db->fieldExists('jours_total', $this->table);

        return $this->usesLegacyColumns;
    }
}
