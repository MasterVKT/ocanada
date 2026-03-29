<?php

namespace App\Models;

use CodeIgniter\Model;

class ShiftModel extends Model
{
    protected $table            = 'shifts_modeles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'nom',
        'heure_debut',
        'heure_fin',
        'pause_minutes',
        'jours_actifs',
        'actif',
    ];
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'date_creation';
    protected $updatedField  = 'date_modification';

    protected $validationRules    = [
        'nom'                     => 'required|string|max_length[100]|is_unique[shifts_modeles.nom]',
        'heure_debut'             => 'required|regex_match[/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/]',
        'heure_fin'               => 'required|regex_match[/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/]',
        'pause_minutes'           => 'permit_empty|integer|greater_than_equal_to[0]',
        'jours_actifs'            => 'permit_empty|string|max_length[50]',
        'actif'                   => 'required|in_list[0,1]',
    ];

    protected $validationMessages = [
        'nom' => [
            'required'   => 'Le nom du shift est obligatoire',
            'is_unique'  => 'Ce nom de shift existe déjà',
            'max_length' => 'Le nom ne doit pas dépasser 100 caractères',
        ],
        'heure_debut' => [
            'required'    => 'L\'heure de début est obligatoire',
            'regex_match' => 'Format doit être HH:MM',
        ],
        'heure_fin' => [
            'required'    => 'L\'heure de fin est obligatoire',
            'regex_match' => 'Format doit être HH:MM',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ['sanitizeShiftData'];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ['sanitizeShiftData'];
    protected $afterUpdate    = [];

    protected function sanitizeShiftData(array $data): array
    {
        // Trim whitespace
        if (isset($data['data']['nom'])) {
            $data['data']['nom'] = trim($data['data']['nom']);
        }

        // Defaults aligned with current DB schema.
        $data['data']['pause_minutes'] ??= 60;
        $data['data']['jours_actifs'] ??= '1,2,3,4,5';
        $data['data']['actif'] ??= 1;

        return $data;
    }

    /**
     * Get all active shifts
     */
    public function getActive(): array
    {
        return $this->where('actif', 1)
                    ->orderBy('heure_debut', 'ASC')
                    ->findAll();
    }

    /**
     * Get all shifts (active and inactive)
     */
    public function getAll(): array
    {
        return $this->orderBy('actif', 'DESC')
                    ->orderBy('heure_debut', 'ASC')
                    ->findAll();
    }

    /**
     * Get shift by ID with employee count
     */
    public function getWithStats(int $shiftId): ?array
    {
        $shift = $this->find($shiftId);
        if (!$shift) {
            return null;
        }

        // Count active affectations
        $db = db_connect();
        $affecCount = $db->table('affectations_shifts')
            ->where('shift_id', $shiftId)
            ->where('date_fin IS NULL OR date_fin >= CURDATE()', false)
            ->countAllResults();

        $shift['employes_count'] = $affecCount;

        return $shift;
    }

    /**
     * Calculate duration in minutes
     */
    public function getDurationMinutes(int $shiftId): int
    {
        $shift = $this->find($shiftId);
        if (!$shift) {
            return 0;
        }

        $start = strtotime($shift['heure_debut']);
        $end = strtotime($shift['heure_fin']);

        // Handle cross-midnight shifts
        if ($end < $start) {
            $end += 86400; // Add 24 hours
        }

        return (int) (($end - $start) / 60);
    }

    /**
     * Get shift by name (for lookups)
     */
    public function getByName(string $name): ?array
    {
        return $this->where('nom', $name)->first();
    }

    /**
     * Toggle shift active status
     */
    public function toggleActive(int $shiftId): bool
    {
        $shift = $this->find($shiftId);
        if (!$shift) {
            return false;
        }

        return $this->update($shiftId, [
            'actif' => !empty($shift['actif']) ? 0 : 1,
        ]);
    }

    /**
     * Check if shift has any active affectations
     */
    public function hasActiveAffectations(int $shiftId): bool
    {
        $db = db_connect();
        $count = $db->table('affectations_shifts')
            ->where('shift_id', $shiftId)
            ->where('date_fin IS NULL OR date_fin >= CURDATE()', false)
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Get employees assigned to this shift
     */
    public function getAssignedEmployees(int $shiftId, $date = null): array
    {
        $db = db_connect();
        $builder = $db->table('affectations_shifts as aff')
            ->select('e.*, aff.date_debut, aff.date_fin')
            ->join('employes e', 'e.id = aff.employe_id', 'inner')
            ->where('aff.shift_id', $shiftId);

        if ($date) {
            $date = date('Y-m-d', strtotime($date));
            $builder->groupStart()
                    ->where('aff.date_debut IS NULL', null, false)
                    ->orWhere('aff.date_debut <=', $date)
                ->groupEnd()
                ->groupStart()
                    ->where('aff.date_fin IS NULL', null, false)
                    ->orWhere('aff.date_fin >=', $date)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Calculate working hours for a shift in a date range
     */
    public function getWorkingHoursForRange(int $shiftId, string $dateDebut, string $dateFin): float
    {
        $shift = $this->find($shiftId);
        if (!$shift) {
            return 0;
        }

        $start = new \DateTime($dateDebut);
        $end = new \DateTime($dateFin);
        $duration = $this->getDurationMinutes($shiftId);

        $workingDays = 0;
        $currentDate = clone $start;

        // Count working days in range (excluding weekends)
        while ($currentDate <= $end) {
            $dayOfWeek = (int) $currentDate->format('w');
            if ($dayOfWeek !== 0 && $dayOfWeek !== 6) { // Exclude Sunday (0) and Saturday (6)
                $workingDays++;
            }
            $currentDate->modify('+1 day');
        }

        return ($workingDays * $duration) / 60; // Convert to hours
    }
}
