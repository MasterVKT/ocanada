<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modèle pour la gestion des visiteurs
 */
class VisiteurModel extends Model
{
    protected $table            = 'visiteurs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'entreprise',
        'motif',
        'personne_a_voir',
        'heure_arrivee',
        'heure_depart',
        'badge_id',
        'statut',
        'commentaire'
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
        'nom'             => 'required|alpha_space|min_length[2]|max_length[50]',
        'prenom'          => 'required|alpha_space|min_length[2]|max_length[50]',
        'email'           => 'permit_empty|valid_email',
        'telephone'       => 'permit_empty|regex_match[/^[0-9+\-\s()]+$/]',
        'entreprise'      => 'permit_empty|max_length[100]',
        'motif'           => 'required|max_length[255]',
        'personne_a_voir' => 'required|max_length[100]',
        'heure_arrivee'   => 'permit_empty|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
        'heure_depart'    => 'permit_empty|regex_match[/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/]',
        'badge_id'        => 'permit_empty|max_length[50]',
        'statut'          => 'required|in_list[present,departi,sorti,parti]',
        'commentaire'     => 'permit_empty|max_length[255]',
    ];

    protected $validationMessages = [
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
            'valid_email' => 'L\'email n\'est pas valide.',
        ],
        'telephone' => [
            'regex_match' => 'Le numéro de téléphone n\'est pas valide.',
        ],
        'entreprise' => [
            'max_length' => 'L\'entreprise ne peut pas dépasser 100 caractères.',
        ],
        'motif' => [
            'required'   => 'Le motif est obligatoire.',
            'max_length' => 'Le motif ne peut pas dépasser 255 caractères.',
        ],
        'personne_a_voir' => [
            'required'   => 'La personne à voir est obligatoire.',
            'max_length' => 'La personne à voir ne peut pas dépasser 100 caractères.',
        ],
        'heure_arrivee' => [
            'regex_match' => 'L\'heure d\'arrivée n\'est pas au format HH:MM.',
        ],
        'heure_depart' => [
            'regex_match' => 'L\'heure de départ n\'est pas au format HH:MM.',
        ],
        'badge_id' => [
            'max_length' => 'L\'ID du badge ne peut pas dépasser 50 caractères.',
        ],
        'statut' => [
            'required' => 'Le statut est obligatoire.',
            'in_list'  => 'Le statut doit être present, departi, sorti ou parti.',
        ],
        'commentaire' => [
            'max_length' => 'Le commentaire ne peut pas dépasser 255 caractères.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['generateBadgeId'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Génère un ID de badge unique
     */
    protected function generateBadgeId(array $data): array
    {
        if (empty($data['data']['badge_id'])) {
            $data['data']['badge_id'] = 'VIS' . date('Ymd') . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }

        return $data;
    }

    /**
     * Enregistre l'arrivée d'un visiteur
     */
    public function registerArrival(array $data): int
    {
        $data['heure_arrivee'] = date('H:i');
        $data['statut'] = 'present';

        return $this->insert($data);
    }

    /**
     * Enregistre le départ d'un visiteur
     */
    public function registerDeparture(int $visiteurId): bool
    {
        return $this->update($visiteurId, [
            'heure_depart' => date('H:i'),
            'statut' => $this->resolveDepartedStatusValue(),
        ]);
    }

    /**
     * Récupère les visiteurs présents
     */
    public function getPresentVisitors(): array
    {
        return $this->where('statut', 'present')
            ->orderBy('heure_arrivee', 'ASC')
            ->findAll();
    }

    /**
     * Récupère l'historique des visites
     */
    public function getVisitHistory(?string $dateDebut = null, ?string $dateFin = null): array
    {
        $builder = $this->orderBy('date_creation', 'DESC');

        if ($dateDebut) {
            $builder->where('DATE(date_creation) >=', $dateDebut);
        }

        if ($dateFin) {
            $builder->where('DATE(date_creation) <=', $dateFin);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Ferme automatiquement les visites longues
     */
    public function closeLongVisits(int $maxHours = 3): int
    {
        $cutoffTime = date('H:i', strtotime("-{$maxHours} hours"));

        $builder = $this->builder();
        $builder->where('statut', 'present')
            ->where('heure_arrivee <', $cutoffTime);

        $count = (int) $builder->countAllResults(false);

        if ($count === 0) {
            return 0;
        }

        $builder->set([
            'heure_depart' => date('H:i'),
            'statut' => $this->resolveDepartedStatusValue(),
            'commentaire' => 'Fermeture automatique - visite longue'
        ])->update();

        return $count;
    }

    /**
     * Compte les visiteurs présents
     */
    public function countPresent(): int
    {
        return $this->where('statut', 'present')->countAllResults();
    }

    private function resolveDepartedStatusValue(): string
    {
        foreach ($this->db->getFieldData('visiteurs') as $field) {
            if (($field->name ?? null) === 'statut' && isset($field->type) && is_string($field->type)) {
                return str_contains(strtolower($field->type), 'sorti') ? 'sorti' : 'departi';
            }
        }

        return 'departi';
    }
}
