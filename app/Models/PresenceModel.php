<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle pour la gestion des présences
 */
class PresenceModel extends Model
{
    protected $table            = 'presences';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'employe_id',
        'date_pointage',
        'heure_pointage',
        'heure_sortie',
        'statut',
        'retard_minutes',
        'corrige',
        'motif_correction',
        'corrige_par_utilisateur_id',
        'date_correction',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'retard_minutes' => 'integer',
    ];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'date_creation';
    protected $updatedField  = 'date_modification';

    // Validation
    protected $validationRules = [
        'employe_id'                => 'required|integer|is_not_unique[employes.id]',
        'date_pointage'             => 'required|valid_date',
        'heure_pointage'            => 'permit_empty|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
        'heure_sortie'              => 'permit_empty|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
        'statut'                    => 'required|in_list[present,retard,absent]',
        'retard_minutes'            => 'permit_empty|integer|greater_than_equal_to[0]',
        'corrige'                   => 'permit_empty|in_list[0,1]',
        'motif_correction'          => 'permit_empty|max_length[255]',
        'corrige_par_utilisateur_id'=> 'permit_empty|integer|is_not_unique[utilisateurs.id]',
    ];

    protected $validationMessages = [
        'employe_id' => [
            'required' => 'L\'ID employé est obligatoire.',
            'integer'  => 'L\'ID employé doit être un entier.',
            'is_not_unique' => 'L\'employé n\'existe pas.',
        ],
        'date_pointage' => [
            'required'    => 'La date de pointage est obligatoire.',
            'valid_date'  => 'La date de pointage n\'est pas valide.',
        ],
        'heure_pointage' => [
            'regex_match' => 'L\'heure d\'arrivée n\'est pas au format HH:MM.',
        ],
        'heure_sortie' => [
            'regex_match' => 'L\'heure de départ n\'est pas au format HH:MM.',
        ],
        'statut' => [
            'required' => 'Le statut est obligatoire.',
            'in_list'  => 'Le statut doit être present, retard ou absent.',
        ],
        'retard_minutes' => [
            'integer' => 'Les minutes de retard doivent être un entier.',
            'greater_than_equal_to' => 'Les minutes de retard ne peuvent pas être négatives.',
        ],
        'motif_correction' => [
            'max_length' => 'Le motif de correction ne peut pas dépasser 255 caractères.',
        ],
        'corrige_par_utilisateur_id' => [
            'integer' => 'L\'ID du correcteur doit être un entier.',
            'is_not_unique' => 'Le correcteur n\'existe pas.',
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
     * Enregistre un pointage arrivée
     */
    public function pointageArrivee(int $employeId, string $heureArrivee): bool
    {
        $date = date('Y-m-d');

        // Vérifier si déjà pointé aujourd'hui
        $existing = $this->where('employe_id', $employeId)
            ->where('date_pointage', $date)
            ->first();

        if ($existing && !empty($existing['heure_pointage'])) {
            return false; // Déjà pointé arrivée
        }

        $data = [
            'employe_id'   => $employeId,
            'date_pointage'=> $date,
            'heure_pointage' => $heureArrivee,
            'statut'       => 'present', // Temporaire, sera recalculé
        ];

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert($data);
        }
    }

    /**
     * Enregistre un pointage départ
     */
    public function pointageDepart(int $employeId, string $heureDepart): bool
    {
        $date = date('Y-m-d');

        $existing = $this->where('employe_id', $employeId)
            ->where('date_pointage', $date)
            ->first();

        if (!$existing) {
            return false; // Pas de pointage arrivée
        }

        return $this->update($existing['id'], [
            'heure_sortie' => $heureDepart,
        ]);
    }

    /**
     * Récupère les présences par date
     */
    public function getByDate(string $date): array
    {
        return $this->where('date_pointage', $date)
            ->findAll();
    }

    /**
     * Récupère les présences d'un employé
     */
    public function getByEmploye(int $employeId, ?string $dateDebut = null, ?string $dateFin = null): array
    {
        $builder = $this->where('employe_id', $employeId);

        if ($dateDebut) {
            $builder->where('date_pointage >=', $dateDebut);
        }

        if ($dateFin) {
            $builder->where('date_pointage <=', $dateFin);
        }

        return $builder->orderBy('date_pointage', 'DESC')
            ->findAll();
    }

    /**
     * Calcule les statistiques de présence pour une période
     */
    public function getStats(int $employeId, string $dateDebut, string $dateFin): array
    {
        $presences = $this->getByEmploye($employeId, $dateDebut, $dateFin);

        $stats = [
            'total_jours'     => 0,
            'presents'        => 0,
            'retards'         => 0,
            'absents'         => 0,
            'total_retard_min' => 0,
        ];

        foreach ($presences as $presence) {
            $stats['total_jours']++;

            switch ($presence['statut']) {
                case 'present':
                    $stats['presents']++;
                    break;
                case 'retard':
                    $stats['retards']++;
                    $stats['total_retard_min'] += $presence['retard_minutes'] ?? 0;
                    break;
                case 'absent':
                    $stats['absents']++;
                    break;
            }
        }

        return $stats;
    }

    /**
     * Marque les absences pour une journée
     */
    public function markAbsencesForDate(string $date): int
    {
        // Récupérer tous les employés actifs
        $employeModel = model(EmployeModel::class);
        $employes = $employeModel->where('statut', 'actif')->findAll();

        $count = 0;

        foreach ($employes as $employe) {
            // Vérifier si déjà une présence enregistrée
            $existing = $this->where('employe_id', $employe['id'])
                ->where('date_pointage', $date)
                ->first();

            if (!$existing) {
                $this->insert([
                    'employe_id'     => $employe['id'],
                    'date_pointage'  => $date,
                    'statut'        => 'absent',
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Corrige manuellement une présence
     */
    public function correctionManuelle(int $presenceId, array $data, int $corrigePar): bool
    {
        $data['corrige_par_utilisateur_id'] = $corrigePar;
        $data['date_correction'] = date('Y-m-d H:i:s');

        return $this->update($presenceId, $data);
    }
}