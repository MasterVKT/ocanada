<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ShiftModel;
use App\Models\AffectationShiftModel;
use App\Models\EmployeModel;
use App\Models\AuditLogModel;

class PlanningController extends BaseController
{
    protected $shiftModel;
    protected $affectationModel;
    protected $employeModel;
    protected $auditLog;

    public function __construct()
    {
        $this->shiftModel = new ShiftModel();
        $this->affectationModel = new AffectationShiftModel();
        $this->employeModel = new EmployeModel();
        $this->auditLog = new AuditLogModel();
    }

    /**
     * Display planning calendar for a week
     * GET /admin/planning?week=2025-12&year=2025
     */
    public function index()
    {
        [$week, $year] = $this->resolveWeekYearFromRequest();

        // Get week start date
        $weekCursor = new \DateTime();
        $weekCursor->setISODate($year, $week, 1);
        $weekStart = $weekCursor->format('Y-m-d');
        $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));

        // Get all shifts
        $shifts = $this->shiftModel->getActive();

        // Get all active employees
        $employees = $this->employeModel->where('statut', 'actif')->findAll();

        // Get affectations for the week
        $db = db_connect();
        $affectations = $db->table('affectations_shifts as aff')
            ->select('aff.*, s.nom as shift_name, s.heure_debut, s.heure_fin, e.nom, e.prenom')
            ->join('shifts_modeles s', 's.id = aff.shift_id', 'left')
            ->join('employes e', 'e.id = aff.employe_id', 'left')
            ->groupStart()
            ->where('aff.date_debut IS NULL', null, false)
            ->orWhere('aff.date_debut <=', $weekStart)
            ->groupEnd()
            ->groupStart()
            ->where('aff.date_fin IS NULL', null, false)
            ->orWhere('aff.date_fin >=', $weekEnd)
            ->groupEnd()
            ->orderBy('e.nom', 'ASC')
            ->orderBy('s.heure_debut', 'ASC')
            ->get()
            ->getResultArray();

        // Build week array with YYYY-MM-DD for each day
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $currentDate = date('Y-m-d', strtotime($weekStart . " +{$i} days"));
            $days[$currentDate] = [
                'date' => $currentDate,
                'day' => date('l', strtotime($currentDate)),
                'dayFr' => $this->getDayNameFr((int) date('w', strtotime($currentDate))),
            ];
        }

        // Navigation previous/next week
        $prevCursor = clone $weekCursor;
        $prevCursor->modify('-7 days');
        $prevWeek = (int) $prevCursor->format('W');
        $prevYear = (int) $prevCursor->format('o');

        $nextCursor = clone $weekCursor;
        $nextCursor->modify('+7 days');
        $nextWeek = (int) $nextCursor->format('W');
        $nextYear = (int) $nextCursor->format('o');

        $data = [
            'title' => 'Planning Hebdomadaire',
            'week' => $week,
            'year' => $year,
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'days' => $days,
            'shifts' => $shifts,
            'employees' => $employees,
            'affectations' => $affectations,
            'prev_week' => $prevWeek,
            'prev_year' => $prevYear,
            'next_week' => $nextWeek,
            'next_year' => $nextYear,
        ];

        return $this->renderView('admin/planning/index', array_merge(['title' => 'Planning hebdomadaire'], $data));
    }

    /**
     * Manage shifts (CRUD)
     * GET /admin/planning/shifts
     */
    public function manageShifts()
    {
        $action = $this->request->getVar('action');

        if ($action === 'create' && $this->request->getMethod() === 'post') {
            return $this->createShift();
        } elseif ($action === 'edit' && $this->request->getMethod() === 'post') {
            return $this->updateShift();
        } elseif ($action === 'delete') {
            return $this->deleteShift();
        }

        // List all shifts
        $shifts = $this->shiftModel->getAll();

        $data = [
            'title' => 'Gestion des Shifts',
            'shifts' => $shifts,
        ];

        return $this->renderView('admin/planning/shifts', array_merge(['title' => 'Gestion des shifts'], $data));
    }

    /**
     * Create new shift
     */
    private function createShift()
    {
        $pauseMinutes = (int) ($this->request->getPost('pause_minutes') ?? $this->request->getPost('pauses') ?? 60);

        $data = [
            'nom' => $this->request->getPost('nom'),
            'heure_debut' => $this->request->getPost('heure_debut'),
            'heure_fin' => $this->request->getPost('heure_fin'),
            'pause_minutes' => max(0, $pauseMinutes),
            'jours_actifs' => '1,2,3,4,5',
            'actif' => 1,
        ];

        if (!$this->shiftModel->validate($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->shiftModel->errors());
        }

        $shiftId = $this->shiftModel->insert($data);

        // Audit log
        $this->auditLog->log(
            $this->getCurrentUserId(),
            'CREATION_SHIFT',
            'Création d\'un nouveau shift: ' . $data['nom'],
            ['shift_id' => $shiftId, 'data' => $data]
        );

        return redirect()->to('/admin/planning/shifts')
            ->with('success', 'Shift créé avec succès');
    }

    /**
     * Update shift
     */
    private function updateShift()
    {
        $shiftId = $this->request->getPost('id');
        $shift = $this->shiftModel->find($shiftId);

        if (!$shift) {
            return redirect()->back()->with('error', 'Shift non trouvé');
        }

        $pauseMinutes = (int) ($this->request->getPost('pause_minutes') ?? $this->request->getPost('pauses') ?? ($shift['pause_minutes'] ?? 60));

        $data = [
            'nom' => $this->request->getPost('nom'),
            'heure_debut' => $this->request->getPost('heure_debut'),
            'heure_fin' => $this->request->getPost('heure_fin'),
            'pause_minutes' => max(0, $pauseMinutes),
            'jours_actifs' => $shift['jours_actifs'] ?? '1,2,3,4,5',
            'actif' => isset($shift['actif']) ? (int) $shift['actif'] : 1,
        ];

        if (!$this->shiftModel->validate($data)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->shiftModel->errors());
        }

        $this->shiftModel->update($shiftId, $data);

        // Audit log
        $this->auditLog->log(
            $this->getCurrentUserId(),
            'MODIFICATION_SHIFT',
            'Modification du shift: ' . $data['nom'],
            ['shift_id' => $shiftId, 'old_data' => $shift, 'new_data' => $data]
        );

        return redirect()->to('/admin/planning/shifts')
            ->with('success', 'Shift modifié avec succès');
    }

    /**
     * Delete shift
     */
    private function deleteShift()
    {
        $shiftId = $this->request->getPost('id');
        $shift = $this->shiftModel->find($shiftId);

        if (!$shift) {
            return redirect()->back()->with('error', 'Shift non trouvé');
        }

        // Check if has active affectations
        if ($this->shiftModel->hasActiveAffectations($shiftId)) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer ce shift: il a des affectations actives');
        }

        $this->shiftModel->delete($shiftId);

        // Audit log
        $this->auditLog->log(
            $this->getCurrentUserId(),
            'SUPPRESSION_SHIFT',
            'Suppression du shift: ' . $shift['nom'],
            ['shift_id' => $shiftId, 'data' => $shift]
        );

        return redirect()->to('/admin/planning/shifts')
            ->with('success', 'Shift supprimé avec succès');
    }

    /**
     * Assign shift to employee
     * POST /admin/planning/assign
     */
    public function assignShift()
    {
        $employeId = $this->request->getPost('employe_id');
        $shiftId = $this->request->getPost('shift_id');
        $dateDebut = $this->request->getPost('date_debut');
        $dateFin = $this->request->getPost('date_fin');

        // Validation
        if (!$employeId || !$shiftId) {
            return redirect()->back()->with('error', 'Employé et Shift obligatoires');
        }

        $employe = $this->employeModel->find($employeId);
        $shift = $this->shiftModel->find($shiftId);

        if (!$employe || !$shift) {
            return redirect()->back()->with('error', 'Employé ou Shift non trouvé');
        }

        // Assign shift
        $affectId = $this->affectationModel->insert([
            'employe_id' => $employeId,
            'shift_id' => $shiftId,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
        ]);

        // Audit log
        $this->auditLog->log(
            $this->getCurrentUserId(),
            'AFFECTATION_SHIFT',
            "Affectation de {$employe['prenom']} {$employe['nom']} au shift {$shift['nom']}",
            [
                'affectation_id' => $affectId,
                'employe_id' => $employeId,
                'shift_id' => $shiftId,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
            ]
        );

        return redirect()->back()->with('success', 'Shift affecté avec succès');
    }

    /**
     * Get week start/end dates for JSON API
     * GET /api/planning/week-dates?week=12&year=2025
     */
    public function getWeekDates()
    {
        [$week, $year] = $this->resolveWeekYearFromRequest();

        $date = new \DateTime();
        $date->setISODate($year, $week, 1);
        $weekStart = $date->format('Y-m-d');
        $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));

        return $this->response->setJSON([
            'success' => true,
            'week' => $week,
            'year' => $year,
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
        ]);
    }

    /**
     * Normalize week/year inputs to safe ISO integer values.
     *
     * @return array{0:int,1:int}
     */
    private function resolveWeekYearFromRequest(): array
    {
        $weekInput = (string) ($this->request->getGet('week') ?? '');
        $yearInput = (string) ($this->request->getGet('year') ?? '');

        // Accept legacy format: week=2026-14
        if ($weekInput !== '' && preg_match('/^(\d{4})-(\d{1,2})$/', $weekInput, $matches) === 1) {
            if ($yearInput === '') {
                $yearInput = $matches[1];
            }
            $weekInput = $matches[2];
        }

        $week = filter_var($weekInput, FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => 1,
                'max_range' => 53,
            ],
        ]);
        $year = filter_var($yearInput, FILTER_VALIDATE_INT, [
            'options' => [
                'min_range' => 1970,
                'max_range' => 2100,
            ],
        ]);

        if ($week === false) {
            $week = (int) date('W');
        }

        if ($year === false) {
            $year = (int) date('o');
        }

        return [(int) $week, (int) $year];
    }

    private function getCurrentUserId(): int
    {
        return (int) ($this->currentUser['user_id'] ?? $this->session->get('user_id') ?? 0);
    }

    /**
     * Helper: Get French day name
     */
    private function getDayNameFr(int $dayOfWeek): string
    {
        $days = [
            0 => 'Dimanche',
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
        ];

        return $days[$dayOfWeek] ?? '';
    }
}
