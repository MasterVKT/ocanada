<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle pour la gestion des demandes de congé
 */
class CongeModel extends Model
{
    protected $table            = 'demandes_conge';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'employe_id',
        'type_conge',
        'type_detail',
        'date_debut',
        'date_fin',
        'jours_ouvrables',
        'nombre_jours',
        'motif',
        'statut',
        'date_soumission',
        'date_demande',
        'date_traitement',
        'date_approbation',
        'traite_par',
        'approuve_par',
        'commentaire_admin',
        'commentaire',
        'refus_motif',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'jours_ouvrables' => 'float',
    ];
    protected array $castHandlers = [];

    // Dates - Manuel (pas d'auto-timestamps)
    protected $useTimestamps = false;

    // Validation
    protected $validationRules = [
        'employe_id'   => 'required|integer|is_not_unique[employes.id]',
        'type_conge'   => 'required|in_list[annuel,maladie,autre,maternite_paternite,sans_solde]',
        'date_debut'   => 'required|valid_date',
        'date_fin'     => 'required|valid_date',
        'jours_ouvrables' => 'required|numeric|greater_than[0]',
        'motif'        => 'permit_empty|max_length[255]',
        'statut'       => 'required|in_list[en_attente,approuvee,approuve,refusee,refuse,annulee,annule]',
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
     * Récupère les demandes de congé en attente
     */
    public function getPendingRequests(): array
    {
        $submittedAtColumn = $this->hasField('date_soumission') ? 'date_soumission' : 'date_demande';

        return $this->where('statut', 'en_attente')
            ->orderBy($submittedAtColumn, 'ASC')
            ->findAll();
    }

    /**
     * Récupère les demandes de congé d'un employé
     */
    public function getEmployeeRequests(int $employeId): array
    {
        $submittedAtColumn = $this->hasField('date_soumission') ? 'date_soumission' : 'date_demande';

        return $this->where('employe_id', $employeId)
            ->orderBy($submittedAtColumn, 'DESC')
            ->findAll();
    }

    /**
     * Approuve une demande de congé
     */
    public function approveRequest(int $congeId, int $approuvePar): bool
    {
        $conge = $this->find($congeId);

        if (!$conge || $conge['statut'] !== 'en_attente') {
            return false;
        }

        $soldeModel = model(SoldeCongeModel::class);
        $leaveDays = (float) ($conge['jours_ouvrables'] ?? $conge['nombre_jours'] ?? 0);

        // Ne pas déduire les congés maternité et paternité du solde
        if ($conge['type_conge'] !== 'maternite_paternite') {
            $soldeModel->updateAfterLeaveApproval(
                (int) $conge['employe_id'],
                $leaveDays
            );
        }

        $data = [
            'statut' => $this->resolveStatusValue('approved'),
        ];

        $processedByColumn = $this->hasField('traite_par') ? 'traite_par' : 'approuve_par';
        $processedAtColumn = $this->hasField('date_traitement') ? 'date_traitement' : 'date_approbation';

        $data[$processedByColumn] = $approuvePar;
        $data[$processedAtColumn] = date('Y-m-d H:i:s');

        return $this->update($congeId, $data);
    }

    /**
     * Refuse une demande de congé
     */
    public function rejectRequest(int $congeId, string $motif, int $refusePar = null): bool
    {
        $commentColumn = $this->hasField('commentaire_admin') ? 'commentaire_admin' : ($this->hasField('refus_motif') ? 'refus_motif' : 'commentaire');

        $data = [
            'statut' => $this->resolveStatusValue('rejected'),
            $commentColumn => $motif,
        ];

        if ($refusePar) {
            $processedByColumn = $this->hasField('traite_par') ? 'traite_par' : 'approuve_par';
            $processedAtColumn = $this->hasField('date_traitement') ? 'date_traitement' : 'date_approbation';
            $data[$processedByColumn] = $refusePar;
            $data[$processedAtColumn] = date('Y-m-d H:i:s');
        }

        return $this->update($congeId, $data);
    }

    /**
     * Annule une demande de congé approuvée
     */
    public function cancelRequest(int $congeId, int $annulePar = null): bool
    {
        $conge = $this->find($congeId);

        if (!$conge || !in_array((string) ($conge['statut'] ?? ''), ['approuvee', 'approuve'], true)) {
            return false;
        }

        // Restaurer le solde si nécessaire (sauf maternité/paternité)
        if ($conge['type_conge'] !== 'maternite_paternite') {
            $soldeModel = model(SoldeCongeModel::class);
            $solde = $soldeModel->getCurrentSolde((int) $conge['employe_id']);
            $leaveDays = (float) ($conge['jours_ouvrables'] ?? $conge['nombre_jours'] ?? 0);

            if ($solde) {
                $newRestant = (float) $solde['jours_restants'] + $leaveDays;
                $newPris = max(0, (float) $solde['jours_pris'] - $leaveDays);

                $soldeModel->update($solde['id'], [
                    'jours_restants' => $newRestant,
                    'jours_pris'    => $newPris,
                ]);
            }
        }

        $data = [
            'statut' => $this->resolveStatusValue('cancelled'),
        ];

        if ($annulePar) {
            $processedByColumn = $this->hasField('traite_par') ? 'traite_par' : 'approuve_par';
            $processedAtColumn = $this->hasField('date_traitement') ? 'date_traitement' : 'date_approbation';
            $data[$processedByColumn] = $annulePar;
            $data[$processedAtColumn] = date('Y-m-d H:i:s');
        }

        return $this->update($congeId, $data);
    }

    /**
     * Compte les demandes en attente
     */
    public function countPending(): int
    {
        return $this->where('statut', 'en_attente')->countAllResults();
    }

    /**
     * Récupère les demandes en attente depuis plus de X heures
     */
    public function getPendingSinceHours(int $hours): array
    {
        $submittedAtColumn = $this->hasField('date_soumission') ? 'date_soumission' : 'date_demande';
        $cutoffTime = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        return $this->where('statut', 'en_attente')
            ->where($submittedAtColumn . ' <', $cutoffTime)
            ->findAll();
    }

    private function hasField(string $field): bool
    {
        return $this->db->fieldExists($field, $this->table);
    }

    private function resolveStatusValue(string $semantic): string
    {
        $modern = [
            'approved' => 'approuvee',
            'rejected' => 'refusee',
            'cancelled' => 'annulee',
        ];

        $legacy = [
            'approved' => 'approuve',
            'rejected' => 'refuse',
            'cancelled' => 'annule',
        ];

        foreach ($this->db->getFieldData($this->table) as $field) {
            if (($field->name ?? null) === 'statut' && isset($field->type) && is_string($field->type)) {
                $type = strtolower($field->type);
                if (str_contains($type, $modern[$semantic])) {
                    return $modern[$semantic];
                }
                if (str_contains($type, $legacy[$semantic])) {
                    return $legacy[$semantic];
                }
            }
        }

        return $modern[$semantic];
    }
}
