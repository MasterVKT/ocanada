<?php

declare(strict_types=1);

namespace App\Controllers\Employe;

use App\Controllers\BaseController;
use App\Models\ShiftModel;
use App\Models\AffectationShiftModel;
use CodeIgniter\HTTP\ResponseInterface;

class PlanningController extends BaseController
{
    protected $shiftModel;
    protected $affectationModel;

    public function __construct()
    {
        $this->shiftModel = new ShiftModel();
        $this->affectationModel = new AffectationShiftModel();
    }

    /**
     * View personal shift planning
     * GET /employe/planning?week=2025-12&year=2025
     */
    public function index(): string|ResponseInterface
    {
        $employeId = $this->currentUser['employe_id'] ?? null;

        if (!$employeId) {
            return $this->response->setStatusCode(403)->setBody(view('errors/403'));
        }

        $weekInput = (string) ($this->request->getGet('week') ?? '');
        $yearInput = (string) ($this->request->getGet('year') ?? '');

        // Accept both "week=12" and legacy "week=2026-12" formats.
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

        // Get week start date
        $date = new \DateTime();
        $date->setISODate($year, $week, 1);
        $weekStart = $date->format('Y-m-d');
        $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));

        // Build week array
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $currentDate = date('Y-m-d', strtotime($weekStart . " +{$i} days"));
            $dayOfWeek = (int) date('w', strtotime($currentDate));

            $days[$currentDate] = [
                'date' => $currentDate,
                'day' => date('l', strtotime($currentDate)),
                'dayFr' => $this->getDayNameFr($dayOfWeek),
                'shift' => null,
            ];
        }

        // Get employee's shifts for the week
        $db = db_connect();
        $affectations = $db->table('affectations_shifts as aff')
            ->select('aff.*, s.nom as shift_name, s.heure_debut, s.heure_fin')
            ->join('shifts_modeles s', 's.id = aff.shift_id', 'left')
            ->where('aff.employe_id', $employeId)
            ->groupStart()
            ->where('aff.date_debut IS NULL', null, false)
            ->orWhere('aff.date_debut <=', $weekStart)
            ->groupEnd()
            ->groupStart()
            ->where('aff.date_fin IS NULL', null, false)
            ->orWhere('aff.date_fin >=', $weekEnd)
            ->groupEnd()
            ->get()
            ->getResultArray();

        // Map affectations to days (assuming daily repeating shifts)
        foreach ($affectations as $aff) {
            // For now, assign to all days in the range
            foreach ($days as &$day) {
                if ($day['date'] >= $weekStart && $day['date'] <= $weekEnd) {
                    $day['shift'] = [
                        'id' => $aff['shift_id'],
                        'name' => $aff['shift_name'],
                        'heure_debut' => $aff['heure_debut'],
                        'heure_fin' => $aff['heure_fin'],
                    ];
                    break; // One shift per day
                }
            }
        }

        // Navigation
        $prevDate = (clone $date)->modify('-1 week');
        $nextDate = (clone $date)->modify('+1 week');

        $prevWeek = (int) $prevDate->format('W');
        $prevYear = (int) $prevDate->format('o');
        $nextWeek = (int) $nextDate->format('W');
        $nextYear = (int) $nextDate->format('o');

        $data = [
            'title' => 'Mon Planning',
            'week' => $week,
            'year' => $year,
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'days' => $days,
            'prev_week' => $prevWeek,
            'prev_year' => $prevYear,
            'next_week' => $nextWeek,
            'next_year' => $nextYear,
        ];

        return $this->renderView('employe/planning/index', $data);
    }

    /**
     * Get monthly view of shifts
     * GET /employe/planning/month?month=2025-03
     */
    public function month(): string|ResponseInterface
    {
        $employeId = $this->currentUser['employe_id'] ?? null;

        if (!$employeId) {
            return $this->response->setStatusCode(403)->setBody(view('errors/403'));
        }

        $monthParam = $this->request->getVar('month') ?? date('Y-m');

        // Validate format
        if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            $monthParam = date('Y-m');
        }

        $dateDebut = $monthParam . '-01';
        $dateFin = date('Y-m-t', strtotime($dateDebut));

        // Get shifts for the month
        $db = db_connect();
        $shifts = $db->table('affectations_shifts as aff')
            ->select('aff.*, s.nom as shift_name, s.heure_debut, s.heure_fin')
            ->join('shifts_modeles s', 's.id = aff.shift_id', 'left')
            ->where('aff.employe_id', $employeId)
            ->groupStart()
            ->where('aff.date_debut IS NULL', null, false)
            ->orWhere('aff.date_debut <=', $dateFin)
            ->groupEnd()
            ->groupStart()
            ->where('aff.date_fin IS NULL', null, false)
            ->orWhere('aff.date_fin >=', $dateDebut)
            ->groupEnd()
            ->get()
            ->getResultArray();

        // Build calendar grid
        $firstDay = new \DateTime($dateDebut);
        $lastDay = new \DateTime($dateFin);
        $startOfCalendar = clone $firstDay;
        $startOfCalendar->modify('Monday this week');

        $calendar = [];
        $currentDate = clone $startOfCalendar;
        $endOfCalendar = clone $lastDay;
        $endOfCalendar->modify('Sunday this week');

        while ($currentDate <= $endOfCalendar) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateStr = $currentDate->format('Y-m-d');
                $inMonth = $currentDate->format('Y-m') === $monthParam;

                $dayShifts = array_filter(
                    $shifts,
                    fn($s) =>
                    date('Y-m-d', strtotime($s['date_debut'] ?? date('Y-m-d'))) === $dateStr
                );

                $week[$dateStr] = [
                    'date' => $dateStr,
                    'day' => $currentDate->format('d'),
                    'inMonth' => $inMonth,
                    'shifts' => array_values($dayShifts),
                ];

                $currentDate->modify('+1 day');
            }
            $calendar[] = $week;
        }

        // Navigation
        $prevMonth = date('Y-m', strtotime($monthParam . '-01 -1 month'));
        $nextMonth = date('Y-m', strtotime($monthParam . '-01 +1 month'));

        $data = [
            'title' => 'Planning Mensuel',
            'month' => $monthParam,
            'calendar' => $calendar,
            'prev_month' => $prevMonth,
            'next_month' => $nextMonth,
        ];

        return $this->renderView('employe/planning/month', $data);
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
