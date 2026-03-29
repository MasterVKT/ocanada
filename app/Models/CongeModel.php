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
        'nombre_jours',
        'motif',
        'statut',
        'approuve_par',
        'refus_motif',
        'commentaire',
        'date_demande',
        'date_approbation',
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
        'nombre_jours' => 'required|numeric|greater_than[0]',
        'motif'        => 'permit_empty|max_length[255]',
        'statut'       => 'required|in_list[en_attente,approuve,refuse,annule]',
        'approuve_par' => 'permit_empty|integer|is_not_unique[utilisateurs.id]',
        'refus_motif'  => 'permit_empty|max_length[255]',
        'commentaire'  => 'permit_empty|max_length[255]',
    ];

    protected $validationMessages = [
        'employe_id' => [
            'required' => 'L\'ID employé est obligatoire.',
            'integer'  => 'L\'ID employé doit être un entier.',
            'is_not_unique' => 'L\'employé n\'existe pas.',
        ],
        'type_conge' => [
            'required' => 'Le type de congé est obligatoire.',
            'in_list'  => 'Le type de congé doit être annuel, maladie, maternité/paternité, sans solde ou autre.',
        ],
        'date_debut' => [
            'required'   => 'La date de début est obligatoire.',
            'valid_date' => 'La date de début n\'est pas valide.',
        ],
        'date_fin' => [
            'required'   => 'La date de fin est obligatoire.',
            'valid_date' => 'La date de fin n\'est pas valide.',
        ],
        'nombre_jours' => [
            'required' => 'Le nombre de jours est obligatoire.',
            'numeric'  => 'Le nombre de jours doit être un nombre.',
            'greater_than' => 'Le nombre de jours doit être positif.',
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
     * Récupère les demandes de congé en attente
     */
    public function getPendingRequests(): array
    {
        return $this->where('statut', 'en_attente')
            ->orderBy('date_demande', 'ASC')
            ->findAll();
    }

    /**
     * Récupère les demandes de congé d'un employé
     */
    public function getEmployeeRequests(int $employeId): array
    {
        return $this->where('employe_id', $employeId)
            ->orderBy('date_demande', 'DESC')
            ->findAll();
    }

    /**
     * Approuve une demande de congé
     */
    public function approveRequest(int $congeId, int $approuvePar): bool
    {
        // Mettre à jour le solde de congé
        $conge = $this->find($congeId);

        if (!$conge || $conge['statut'] !== 'en_attente') {
            return false;
        }

        $soldeModel = model(SoldeCongeModel::class);
        $soldeModel->updateAfterLeaveApproval(
            $conge['employe_id'],
            $conge['nombre_jours'],
            $conge['type_conge']
        );

        return $this->update($congeId, [
            'statut' => 'approuve',
            'approuve_par' => $approuvePar,
            'date_approbation' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Refuse une demande de congé
     */
    public function rejectRequest(int $congeId, string $motif = '', int $refusePar = null): bool
    {
        $data = [
            'statut' => 'refuse',
            'refus_motif' => $motif,
        ];
        
        if ($refusePar) {
            $data['approuve_par'] = $refusePar; // Using same field as approval for who processed it
        }
        
        return $this->update($congeId, $data);
    }

    /**
     * Annule une demande de congé approuvée
     */
    public function cancelRequest(int $congeId, int $annulePar = null): bool
    {
        $conge = $this->find($congeId);

        if (!$conge || $conge['statut'] !== 'approuve') {
            return false;
        }

        // Restaurer le solde si nécessaire
        $soldeModel = model(SoldeCongeModel::class);
        $solde = $soldeModel->getCurrentSolde($conge['employe_id']);

        if ($solde) {
            if ($conge['type_conge'] === 'maladie') {
                $newRestant = $solde['maladie_restant'] + $conge['nombre_jours'];
                $newPris = max(0, $solde['maladie_pris'] - $conge['nombre_jours']);

                $soldeModel->update($solde['id'], [
                    'maladie_restant' => $newRestant,
                    'maladie_pris' => $newPris,
                ]);
            } else {
                $newRestant = $solde['restant'] + $conge['nombre_jours'];
                $newPris = max(0, $solde['pris'] - $conge['nombre_jours']);

                $soldeModel->update($solde['id'], [
                    'restant' => $newRestant,
                    'pris' => $newPris,
                ]);
            }
        }

        $data = [
            'statut' => 'annule',
        ];
        
        if ($annulePar) {
            $data['approuve_par'] = $annulePar; // Using same field as approval for who processed it
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
        $cutoffTime = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));

        return $this->where('statut', 'en_attente')
            ->where('date_demande <', $cutoffTime)
            ->findAll();
    }
}