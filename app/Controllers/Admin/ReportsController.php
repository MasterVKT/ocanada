<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PresenceModel;
use App\Models\CongeModel;
use App\Models\VisiteurModel;
use App\Models\AuditLogModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface as HttpResponseInterface;
use Psr\Log\LoggerInterface;

class ReportsController extends BaseController
{
    protected PresenceModel $presenceModel;
    protected CongeModel $congeModel;
    protected VisiteurModel $visiteurModel;
    /**
     * @var string[]|null
     */
    private ?array $employeeColumnsCache = null;

    public function initController(RequestInterface $request, HttpResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $this->presenceModel  = model(PresenceModel::class);
        $this->congeModel     = model(CongeModel::class);
        $this->visiteurModel  = model(VisiteurModel::class);
    }

    /**
     * Show the report generation form
     */
    public function index(): string
    {
        return $this->renderView('admin/rapports/index', [
            'title' => 'Rapports et exports',
            'departements' => $this->listDepartments(),
        ]);
    }

    /**
     * POST handler that generates the requested report
     * Accepts parameters:
     *  - type: presences_mensuel|conges_annuels|visiteurs|absenteisme
     *  - start, end (dates)
     *  - format: pdf|csv
     */
    public function generate(): ResponseInterface
    {
        $type   = (string) $this->request->getPost('type');
        $format = (string) ($this->request->getPost('format') ?? 'pdf');
        $params = $this->request->getPost();

        if (! in_array($type, ['presences_mensuel', 'conges_annuels', 'visiteurs', 'absenteisme'], true)) {
            return redirect()->back()->with('error', 'Type de rapport invalide.');
        }

        if (! in_array($format, ['pdf', 'csv'], true)) {
            return redirect()->back()->with('error', 'Format de rapport invalide.');
        }

        $start = (string) ($params['start'] ?? '');
        $end = (string) ($params['end'] ?? '');
        if ($start !== '' && $end !== '' && $start > $end) {
            return redirect()->back()->withInput()->with('error', 'La date de fin doit etre posterieure a la date de debut.');
        }

        // Build data for report
        $data = match ($type) {
            'presences_mensuel'  => $this->buildPresencesData($params),
            'conges_annuels'     => $this->buildCongesData($params),
            'visiteurs'          => $this->buildVisiteursData($params),
            'absenteisme'        => $this->buildAbsenteismeData($params),
            default              => null,
        };

        if (!$data) {
            return redirect()->back()->with('error', 'Type de rapport invalide.');
        }

        // Log audit
        model(AuditLogModel::class)->log(
            'GENERATION_RAPPORT',
            $this->currentUser['user_id'] ?? null,
            "Rapport: $type — Paramètres: " . json_encode($params)
        );

        if ($format === 'csv') {
            return $this->exportCsv($type, $data);
        }

        $dompdfClass = 'Dompdf\\Dompdf';

        if (! class_exists($dompdfClass)) {
            return redirect()->back()->with('error', 'DOMPDF n est pas disponible sur cet environnement.');
        }

        // Render HTML
        $html = view('admin/rapports/pdf/' . $type, $data);

        // Generate PDF with DOMPDF
        $dompdf = new $dompdfClass(['defaultFont' => 'DejaVu Sans']);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = "rapport_{$type}_" . date('Ymd') . ".pdf";
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->setBody($dompdf->output());
    }

    /**
     * Prepare data for presence report
     */
    private function buildPresencesData(array $params): array
    {
        $start = $params['start'] ?? date('Y-m-01');
        $end   = $params['end'] ?? date('Y-m-t');
        $departement = trim((string) ($params['departement'] ?? ''));

        if ($end < $start) {
            [$start, $end] = [$end, $start];
        }

        $db = db_connect();
        $builder = $db->table('presences p')
            ->select("p.*, e.id AS employe_id, e.prenom, e.nom, e.poste, e.matricule,
                COALESCE(NULLIF(e.departement, ''), 'Non renseigné') AS departement")
            ->join('employes e', 'e.id = p.employe_id', 'left')
            ->where('DATE(p.date_pointage) >=', $start)
            ->where('DATE(p.date_pointage) <=', $end)
            ->orderBy('p.date_pointage', 'ASC')
            ->orderBy('e.nom', 'ASC');

        if ($departement !== '') {
            $builder->where('e.departement', $departement);
        }

        $rows = $builder->get()->getResultArray();

        $summary = [
            'total_pointages' => 0,
            'total_presents' => 0,
            'total_retards' => 0,
            'total_absences' => 0,
            'total_retard_minutes' => 0,
            'total_heures_travaillees' => 0.0,
            'taux_presence_global' => 0.0,
        ];

        $byEmployee = [];

        foreach ($rows as $row) {
            $summary['total_pointages']++;

            $employeeId = (int) ($row['employe_id'] ?? 0);
            if (! isset($byEmployee[$employeeId])) {
                $byEmployee[$employeeId] = [
                    'employe_id' => $employeeId,
                    'matricule' => (string) ($row['matricule'] ?? ''),
                    'prenom' => (string) ($row['prenom'] ?? ''),
                    'nom' => (string) ($row['nom'] ?? ''),
                    'departement' => (string) ($row['departement'] ?? 'Non renseigné'),
                    'jours_total' => 0,
                    'jours_present' => 0,
                    'jours_retard' => 0,
                    'jours_absence' => 0,
                    'retard_minutes' => 0,
                    'heures_travaillees' => 0.0,
                    'taux_presence' => 0.0,
                ];
            }

            $byEmployee[$employeeId]['jours_total']++;

            $workedHours = $this->computeWorkedHours(
                (string) ($row['heure_pointage'] ?? ''),
                (string) ($row['heure_sortie'] ?? '')
            );
            $byEmployee[$employeeId]['heures_travaillees'] += $workedHours;
            $summary['total_heures_travaillees'] += $workedHours;

            $status = (string) ($row['statut'] ?? '');
            if ($status === 'present') {
                $summary['total_presents']++;
                $byEmployee[$employeeId]['jours_present']++;
            } elseif ($status === 'retard') {
                $summary['total_retards']++;
                $byEmployee[$employeeId]['jours_retard']++;
                $minutes = (int) ($row['retard_minutes'] ?? 0);
                $summary['total_retard_minutes'] += $minutes;
                $byEmployee[$employeeId]['retard_minutes'] += $minutes;
            } elseif ($status === 'absent') {
                $summary['total_absences']++;
                $byEmployee[$employeeId]['jours_absence']++;
            }
        }

        foreach ($byEmployee as &$row) {
            $joursTotal = max(1, (int) $row['jours_total']);
            $joursPresence = (int) $row['jours_present'] + (int) $row['jours_retard'];
            $row['taux_presence'] = round(($joursPresence / $joursTotal) * 100, 1);
            $row['heures_travaillees'] = round((float) $row['heures_travaillees'], 2);
        }
        unset($row);

        usort($byEmployee, static function (array $left, array $right): int {
            if ((float) $right['taux_presence'] === (float) $left['taux_presence']) {
                return ((int) $left['retard_minutes']) <=> ((int) $right['retard_minutes']);
            }

            return ((float) $right['taux_presence']) <=> ((float) $left['taux_presence']);
        });

        $joursPresenceGlobaux = $summary['total_presents'] + $summary['total_retards'];
        $summary['taux_presence_global'] = $summary['total_pointages'] > 0
            ? round(($joursPresenceGlobaux / $summary['total_pointages']) * 100, 2)
            : 0.0;
        $summary['total_heures_travaillees'] = round((float) $summary['total_heures_travaillees'], 2);

        $daysSpan = max(1, (int) ((strtotime($end) - strtotime($start)) / 86400) + 1);
        $prevEnd = date('Y-m-d', strtotime($start . ' -1 day'));
        $prevStart = date('Y-m-d', strtotime($prevEnd . ' -' . ($daysSpan - 1) . ' day'));

        $previousBuilder = $db->table('presences p')
            ->select("COUNT(*) AS total_pointages,
                SUM(CASE WHEN p.statut IN ('present', 'retard') THEN 1 ELSE 0 END) AS total_presence,
                SUM(CASE WHEN p.statut = 'retard' THEN IFNULL(p.retard_minutes, 0) ELSE 0 END) AS total_retard_minutes")
            ->join('employes e', 'e.id = p.employe_id', 'left')
            ->where('DATE(p.date_pointage) >=', $prevStart)
            ->where('DATE(p.date_pointage) <=', $prevEnd);

        if ($departement !== '') {
            $previousBuilder->where('e.departement', $departement);
        }

        $previous = $previousBuilder->get()->getFirstRow('array') ?? [];
        $previousPointages = (int) ($previous['total_pointages'] ?? 0);
        $previousPresence = (int) ($previous['total_presence'] ?? 0);
        $previousRate = $previousPointages > 0
            ? round(($previousPresence / $previousPointages) * 100, 2)
            : 0.0;

        return [
            'start' => $start,
            'end'   => $end,
            'departement' => $departement,
            'rows'  => $rows,
            'summary' => $summary,
            'by_employee' => $byEmployee,
            'comparison' => [
                'previous_start' => $prevStart,
                'previous_end' => $prevEnd,
                'previous_taux_presence_global' => $previousRate,
                'delta_taux_presence_global' => round((float) $summary['taux_presence_global'] - $previousRate, 2),
            ],
        ];
    }

    /**
     * Prepare data for leave report
     */
    private function buildCongesData(array $params): array
    {
        $start = $params['start'] ?? date('Y') . '-01-01';
        $end   = $params['end'] ?? date('Y') . '-12-31';
        $departement = trim((string) ($params['departement'] ?? ''));

        if ($end < $start) {
            [$start, $end] = [$end, $start];
        }

        $selectedYear = (int) substr($start, 0, 4);

        $db = db_connect();
        $builder = $db->table('demandes_conge c')
            ->select('c.*, c.date_demande as date_soumission, c.nombre_jours as jours_ouvrables, e.prenom, e.nom, e.matricule')
            ->join('employes e', 'e.id = c.employe_id', 'left')
            ->where('DATE(c.date_demande) >=', $start)
            ->where('DATE(c.date_demande) <=', $end)
            ->orderBy('c.date_demande', 'ASC');

        if ($departement !== '') {
            $builder->where('e.departement', $departement);
        }

        $rows = $builder->get()->getResultArray();

        $employeeIds = array_values(array_unique(array_map(static fn(array $row): int => (int) ($row['employe_id'] ?? 0), $rows)));

        $soldesByEmployee = [];
        if ($employeeIds !== []) {
            $soldes = $db->table('soldes_conges')
                ->where('annee', $selectedYear)
                ->whereIn('employe_id', $employeeIds)
                ->get()
                ->getResultArray();

            foreach ($soldes as $solde) {
                $soldesByEmployee[(int) $solde['employe_id']] = $solde;
            }
        }

        $byEmployee = [];
        $summary = [
            'total_demandes' => count($rows),
            'total_jours_approuves' => 0.0,
            'total_jours_demandes' => 0.0,
        ];

        foreach ($rows as $row) {
            $employeeId = (int) ($row['employe_id'] ?? 0);
            $jours = (float) ($row['jours_ouvrables'] ?? 0);
            $type = (string) ($row['type_conge'] ?? 'autre');
            $statut = (string) ($row['statut'] ?? '');

            if (! isset($byEmployee[$employeeId])) {
                $solde = $soldesByEmployee[$employeeId] ?? null;
                $byEmployee[$employeeId] = [
                    'employe_id' => $employeeId,
                    'matricule' => (string) ($row['matricule'] ?? ''),
                    'prenom' => (string) ($row['prenom'] ?? ''),
                    'nom' => (string) ($row['nom'] ?? ''),
                    'solde_initial' => (float) ($solde['solde_annuel'] ?? 0),
                    'solde_restant' => (float) ($solde['restant'] ?? 0),
                    'jours_pris_total' => (float) ($solde['pris'] ?? 0),
                    'jours_demandes_periode' => 0.0,
                    'jours_approuves_periode' => 0.0,
                    'par_type' => [],
                    'demandes' => 0,
                ];
            }

            $byEmployee[$employeeId]['demandes']++;
            $byEmployee[$employeeId]['jours_demandes_periode'] += $jours;

            if (! isset($byEmployee[$employeeId]['par_type'][$type])) {
                $byEmployee[$employeeId]['par_type'][$type] = [
                    'jours' => 0.0,
                    'demandes' => 0,
                ];
            }

            $byEmployee[$employeeId]['par_type'][$type]['demandes']++;

            if ($statut === 'approuve') {
                $byEmployee[$employeeId]['jours_approuves_periode'] += $jours;
                $byEmployee[$employeeId]['par_type'][$type]['jours'] += $jours;
                $summary['total_jours_approuves'] += $jours;
            }

            $summary['total_jours_demandes'] += $jours;
        }

        foreach ($byEmployee as &$employeeRow) {
            $employeeRow['jours_demandes_periode'] = round((float) $employeeRow['jours_demandes_periode'], 2);
            $employeeRow['jours_approuves_periode'] = round((float) $employeeRow['jours_approuves_periode'], 2);
            foreach ($employeeRow['par_type'] as &$typeRow) {
                $typeRow['jours'] = round((float) $typeRow['jours'], 2);
            }
            unset($typeRow);
        }
        unset($employeeRow);

        usort($byEmployee, static fn(array $left, array $right): int => strcmp($left['nom'] . $left['prenom'], $right['nom'] . $right['prenom']));

        return [
            'start' => $start,
            'end'   => $end,
            'departement' => $departement,
            'selected_year' => $selectedYear,
            'rows'  => $rows,
            'by_employee' => $byEmployee,
            'summary' => [
                'total_demandes' => (int) $summary['total_demandes'],
                'total_jours_demandes' => round((float) $summary['total_jours_demandes'], 2),
                'total_jours_approuves' => round((float) $summary['total_jours_approuves'], 2),
            ],
        ];
    }

    /**
     * Prepare data for visitor log report
     */
    private function buildVisiteursData(array $params): array
    {
        $start = $params['start'] ?? date('Y-m-01');
        $end   = $params['end'] ?? date('Y-m-d');

        if ($end < $start) {
            [$start, $end] = [$end, $start];
        }

        $db = db_connect();
        $rows = $db->table('visiteurs v')
            ->select('v.*')
            ->where('DATE(v.date_creation) >=', $start)
            ->where('DATE(v.date_creation) <=', $end)
            ->orderBy('v.date_creation', 'ASC')
            ->get()
            ->getResultArray();

        $summary = [
            'total_visites' => count($rows),
            'present' => 0,
            'departi' => 0,
            'duree_moyenne_minutes' => 0,
        ];

        $durationTotal = 0;
        $durationCount = 0;

        foreach ($rows as &$row) {
            $status = (string) ($row['statut'] ?? '');
            if ($status === 'present') {
                $summary['present']++;
            }

            if (in_array($status, ['departi', 'sorti', 'parti'], true)) {
                $summary['departi']++;
            }

            $durationMinutes = $this->computeDurationMinutes(
                (string) ($row['heure_arrivee'] ?? ''),
                (string) ($row['heure_depart'] ?? '')
            );
            $row['duree_visite_minutes_calculee'] = $durationMinutes;

            if ($durationMinutes > 0) {
                $durationTotal += $durationMinutes;
                $durationCount++;
            }
        }
        unset($row);

        $summary['duree_moyenne_minutes'] = $durationCount > 0
            ? (int) round($durationTotal / $durationCount)
            : 0;

        $daysSpan = max(1, (int) ((strtotime($end) - strtotime($start)) / 86400) + 1);
        $prevEnd = date('Y-m-d', strtotime($start . ' -1 day'));
        $prevStart = date('Y-m-d', strtotime($prevEnd . ' -' . ($daysSpan - 1) . ' day'));

        $previous = $db->table('visiteurs v')
            ->select("COUNT(*) AS total_visites,
                SUM(CASE WHEN v.statut = 'present' THEN 1 ELSE 0 END) AS present,
                SUM(CASE WHEN v.statut IN ('departi', 'sorti', 'parti') THEN 1 ELSE 0 END) AS departi")
            ->where('DATE(v.date_creation) >=', $prevStart)
            ->where('DATE(v.date_creation) <=', $prevEnd)
            ->get()
            ->getFirstRow('array') ?? [];

        return [
            'start' => $start,
            'end'   => $end,
            'rows'  => $rows,
            'summary' => $summary,
            'comparison' => [
                'previous_start' => $prevStart,
                'previous_end' => $prevEnd,
                'previous_total_visites' => (int) ($previous['total_visites'] ?? 0),
                'delta_total_visites' => (int) $summary['total_visites'] - (int) ($previous['total_visites'] ?? 0),
            ],
        ];
    }

    /**
     * Prepare data for absenteeism report
     */
    private function buildAbsenteismeData(array $params): array
    {
        $start = $params['start'] ?? date('Y-m-01');
        $end   = $params['end'] ?? date('Y-m-d');
        $departement = trim((string) ($params['departement'] ?? ''));

        if ($end < $start) {
            [$start, $end] = [$end, $start];
        }

        $db = db_connect();
        $dailySalaryExpr = $this->getDailySalaryExpression();
        $baseBuilder = $db->table('presences p')
            ->join('employes e', 'e.id = p.employe_id', 'left')
            ->where('DATE(p.date_pointage) >=', $start)
            ->where('DATE(p.date_pointage) <=', $end);

        if ($departement !== '') {
            $baseBuilder->where('e.departement', $departement);
        }

        $rows = $baseBuilder
            ->select(
                "p.date_pointage, p.statut, p.retard_minutes,
                e.id AS employe_id, e.matricule, e.prenom, e.nom,
                COALESCE(NULLIF(e.departement, ''), 'Non renseigné') AS departement,
                {$dailySalaryExpr} AS salaire_journalier_calc"
            )
            ->orderBy('p.date_pointage', 'ASC')
            ->orderBy('e.nom', 'ASC')
            ->get()
            ->getResultArray();

        $detailAbsences = array_values(array_filter($rows, static fn(array $r): bool => ($r['statut'] ?? '') === 'absent'));

        $byEmployee = [];
        $totalPointages = 0;
        $totalAbsences = 0;
        $totalRetardMinutes = 0;
        $coutAbsences = 0.0;
        $coutRetards = 0.0;

        foreach ($rows as $row) {
            $totalPointages++;

            $employeeId = (int) ($row['employe_id'] ?? 0);
            if (! isset($byEmployee[$employeeId])) {
                $byEmployee[$employeeId] = [
                    'employe_id' => $employeeId,
                    'matricule' => (string) ($row['matricule'] ?? ''),
                    'prenom' => (string) ($row['prenom'] ?? ''),
                    'nom' => (string) ($row['nom'] ?? ''),
                    'departement' => (string) ($row['departement'] ?? 'Non renseigné'),
                    'jours_total' => 0,
                    'jours_absence' => 0,
                    'retard_minutes' => 0,
                    'cout_absence' => 0.0,
                    'cout_retard' => 0.0,
                    'taux_absenteisme' => 0.0,
                ];
            }

            $byEmployee[$employeeId]['jours_total']++;
            $salaryPerDay = (float) ($row['salaire_journalier_calc'] ?? 0);

            if (($row['statut'] ?? '') === 'absent') {
                $byEmployee[$employeeId]['jours_absence']++;
                $byEmployee[$employeeId]['cout_absence'] += $salaryPerDay;
                $totalAbsences++;
                $coutAbsences += $salaryPerDay;
            }

            if (($row['statut'] ?? '') === 'retard') {
                $minutes = (int) ($row['retard_minutes'] ?? 0);
                $byEmployee[$employeeId]['retard_minutes'] += $minutes;
                $retardCost = ($salaryPerDay / 8) * ($minutes / 60);
                $byEmployee[$employeeId]['cout_retard'] += $retardCost;
                $totalRetardMinutes += $minutes;
                $coutRetards += $retardCost;
            }
        }

        foreach ($byEmployee as &$row) {
            $joursTotal = max(1, (int) $row['jours_total']);
            $row['taux_absenteisme'] = round(((int) $row['jours_absence'] / $joursTotal) * 100, 1);
            $row['cout_total'] = round((float) $row['cout_absence'] + (float) $row['cout_retard'], 2);
            $row['cout_absence'] = round((float) $row['cout_absence'], 2);
            $row['cout_retard'] = round((float) $row['cout_retard'], 2);
        }
        unset($row);

        usort($byEmployee, static function (array $left, array $right): int {
            if ((float) $right['taux_absenteisme'] === (float) $left['taux_absenteisme']) {
                return ((int) $right['retard_minutes']) <=> ((int) $left['retard_minutes']);
            }

            return ((float) $right['taux_absenteisme']) <=> ((float) $left['taux_absenteisme']);
        });

        $daysSpan = max(1, (int) ((strtotime($end) - strtotime($start)) / 86400) + 1);
        $prevEnd = date('Y-m-d', strtotime($start . ' -1 day'));
        $prevStart = date('Y-m-d', strtotime($prevEnd . ' -' . ($daysSpan - 1) . ' day'));

        $previousBuilder = $db->table('presences p')
            ->join('employes e', 'e.id = p.employe_id', 'left')
            ->where('DATE(p.date_pointage) >=', $prevStart)
            ->where('DATE(p.date_pointage) <=', $prevEnd);

        if ($departement !== '') {
            $previousBuilder->where('e.departement', $departement);
        }

        $previous = $previousBuilder
            ->select("COUNT(*) AS total_pointages,
                SUM(CASE WHEN p.statut = 'absent' THEN 1 ELSE 0 END) AS total_absences,
                SUM(CASE WHEN p.statut = 'retard' THEN IFNULL(p.retard_minutes, 0) ELSE 0 END) AS total_retard_minutes,
                SUM(CASE WHEN p.statut = 'absent' THEN {$dailySalaryExpr} ELSE 0 END) AS cout_absences,
                SUM(CASE WHEN p.statut = 'retard' THEN ({$dailySalaryExpr} / 8) * (IFNULL(p.retard_minutes, 0) / 60) ELSE 0 END) AS cout_retards")
            ->get()
            ->getFirstRow('array') ?? [];

        $globalRate = $totalPointages > 0 ? round(($totalAbsences / $totalPointages) * 100, 2) : 0.0;
        $prevPointages = (int) ($previous['total_pointages'] ?? 0);
        $prevAbsences = (int) ($previous['total_absences'] ?? 0);
        $prevRate = $prevPointages > 0 ? round(($prevAbsences / $prevPointages) * 100, 2) : 0.0;
        $currentTotalCost = round($coutAbsences + $coutRetards, 2);
        $previousTotalCost = round((float) ($previous['cout_absences'] ?? 0) + (float) ($previous['cout_retards'] ?? 0), 2);

        return [
            'start' => $start,
            'end' => $end,
            'departement' => $departement,
            'rows' => $detailAbsences,
            'by_employee' => $byEmployee,
            'summary' => [
                'total_pointages' => $totalPointages,
                'total_absences' => $totalAbsences,
                'total_retard_minutes' => $totalRetardMinutes,
                'taux_absenteisme_global' => $globalRate,
                'cout_absences' => round($coutAbsences, 2),
                'cout_retards' => round($coutRetards, 2),
                'cout_total' => $currentTotalCost,
            ],
            'comparison' => [
                'previous_start' => $prevStart,
                'previous_end' => $prevEnd,
                'previous_taux_absenteisme_global' => $prevRate,
                'delta_taux_absenteisme_global' => round($globalRate - $prevRate, 2),
                'previous_cout_total' => $previousTotalCost,
                'delta_cout_total' => round($currentTotalCost - $previousTotalCost, 2),
            ],
        ];
    }

    /**
     * Export CSV for various report types
     */
    private function exportCsv(string $type, array $data): ResponseInterface
    {
        $filename = "rapport_{$type}_" . date('Ymd') . ".csv";
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            return $this->response->setStatusCode(500)->setBody('Impossible de générer le fichier CSV.');
        }

        fwrite($stream, "\xEF\xBB\xBF");

        $writeRow = static function (array $row) use ($stream): void {
            fputcsv($stream, $row);
        };

        $blank = static function () use ($writeRow): void {
            $writeRow(['']);
        };

        switch ($type) {
            case 'presences_mensuel':
                $summary = $data['summary'] ?? [];
                $comparison = $data['comparison'] ?? [];

                $writeRow(['Synthese']);
                $writeRow(['Periode', $data['start'] ?? '', $data['end'] ?? '']);
                $writeRow(['Departement', $data['departement'] ?? 'Tous']);
                $writeRow(['Taux presence global (%)', $summary['taux_presence_global'] ?? 0]);
                $writeRow(['Total pointages', $summary['total_pointages'] ?? 0]);
                $writeRow(['Total presents', $summary['total_presents'] ?? 0]);
                $writeRow(['Total retards', $summary['total_retards'] ?? 0]);
                $writeRow(['Total absences', $summary['total_absences'] ?? 0]);
                $writeRow(['Minutes retard', $summary['total_retard_minutes'] ?? 0]);
                $writeRow(['Heures travaillees', $summary['total_heures_travaillees'] ?? 0]);
                $writeRow(['Periode precedente', $comparison['previous_start'] ?? '', $comparison['previous_end'] ?? '']);
                $writeRow(['Taux presence precedent (%)', $comparison['previous_taux_presence_global'] ?? 0]);
                $writeRow(['Delta taux presence (pts)', $comparison['delta_taux_presence_global'] ?? 0]);
                $blank();

                $writeRow(['Recap par employe']);
                $writeRow(['Matricule', 'Prenom', 'Nom', 'Departement', 'Jours total', 'Presents', 'Retards', 'Absences', 'Retard (min)', 'Heures travaillees', 'Taux presence (%)']);
                foreach ($data['by_employee'] as $r) {
                    $writeRow([
                        $r['matricule'],
                        $r['prenom'],
                        $r['nom'],
                        $r['departement'],
                        $r['jours_total'],
                        $r['jours_present'],
                        $r['jours_retard'],
                        $r['jours_absence'],
                        $r['retard_minutes'],
                        $r['heures_travaillees'],
                        $r['taux_presence']
                    ]);
                }

                $blank();
                $writeRow(['Details pointages']);
                $writeRow(['Date', 'Matricule', 'Prenom', 'Nom', 'Departement', 'Statut', 'Heure arrivee', 'Heure sortie', 'Retard (min)']);
                foreach ($data['rows'] as $r) {
                    $writeRow([
                        $r['date_pointage'],
                        $r['matricule'],
                        $r['prenom'],
                        $r['nom'],
                        $r['departement'] ?? '',
                        $r['statut'],
                        $r['heure_pointage'] ?? '',
                        $r['heure_sortie'] ?? '',
                        $r['retard_minutes'] ?? ''
                    ]);
                }
                break;
            case 'conges_annuels':
                $summary = $data['summary'] ?? [];
                $writeRow(['Synthese']);
                $writeRow(['Periode', $data['start'] ?? '', $data['end'] ?? '']);
                $writeRow(['Annee', $data['selected_year'] ?? '']);
                $writeRow(['Departement', $data['departement'] ?? 'Tous']);
                $writeRow(['Demandes', $summary['total_demandes'] ?? 0]);
                $writeRow(['Jours demandes', $summary['total_jours_demandes'] ?? 0]);
                $writeRow(['Jours approuves', $summary['total_jours_approuves'] ?? 0]);
                $blank();

                $writeRow(['Recap employes']);
                $writeRow(['Matricule', 'Prenom', 'Nom', 'Solde initial', 'Jours pris total', 'Solde restant', 'Demandes', 'Jours demandes periode', 'Jours approuves periode', 'Detail type']);
                foreach ($data['by_employee'] as $employee) {
                    $detailType = [];
                    foreach ($employee['par_type'] as $type => $details) {
                        $detailType[] = $type . ':' . $details['jours'] . 'j/' . $details['demandes'] . 'dem';
                    }

                    $writeRow([
                        $employee['matricule'],
                        $employee['prenom'],
                        $employee['nom'],
                        $employee['solde_initial'],
                        $employee['jours_pris_total'],
                        $employee['solde_restant'],
                        $employee['demandes'],
                        $employee['jours_demandes_periode'],
                        $employee['jours_approuves_periode'],
                        implode(' | ', $detailType)
                    ]);
                }

                $blank();
                $writeRow(['Details demandes']);
                $writeRow(['Date soumission', 'Matricule', 'Prenom', 'Nom', 'Type', 'Debut', 'Fin', 'Jours ouvrables', 'Statut']);
                foreach ($data['rows'] as $r) {
                    $writeRow([
                        $r['date_soumission'],
                        $r['matricule'],
                        $r['prenom'],
                        $r['nom'],
                        $r['type_conge'],
                        $r['date_debut'],
                        $r['date_fin'],
                        $r['jours_ouvrables'],
                        $r['statut']
                    ]);
                }
                break;
            case 'visiteurs':
                $summary = $data['summary'] ?? [];
                $comparison = $data['comparison'] ?? [];
                $writeRow(['Synthese']);
                $writeRow(['Periode', $data['start'] ?? '', $data['end'] ?? '']);
                $writeRow(['Total visites', $summary['total_visites'] ?? 0]);
                $writeRow(['Visiteurs presents', $summary['present'] ?? 0]);
                $writeRow(['Visiteurs sortis', $summary['departi'] ?? 0]);
                $writeRow(['Duree moyenne (min)', $summary['duree_moyenne_minutes'] ?? 0]);
                $writeRow(['Periode precedente', $comparison['previous_start'] ?? '', $comparison['previous_end'] ?? '']);
                $writeRow(['Total visites precedente', $comparison['previous_total_visites'] ?? 0]);
                $writeRow(['Delta visites', $comparison['delta_total_visites'] ?? 0]);
                $blank();

                $writeRow(['Details visiteurs (colonnes table)']);
                $writeRow(['id', 'date_creation', 'date_modification', 'badge_id', 'prenom', 'nom', 'email', 'telephone', 'entreprise', 'motif', 'personne_a_voir', 'heure_arrivee', 'heure_depart', 'statut', 'commentaire', 'duree_visite_minutes']);
                foreach ($data['rows'] as $r) {
                    $writeRow([
                        $r['id'] ?? '',
                        $r['date_creation'],
                        $r['date_modification'] ?? '',
                        $r['badge_id'] ?? '',
                        $r['prenom'],
                        $r['nom'],
                        $r['email'] ?? '',
                        $r['telephone'] ?? '',
                        $r['entreprise'] ?? '',
                        $r['motif'],
                        $r['personne_a_voir'],
                        $r['heure_arrivee'],
                        $r['heure_depart'] ?? '',
                        $r['statut'],
                        $r['commentaire'] ?? '',
                        $r['duree_visite_minutes_calculee'] ?? ''
                    ]);
                }
                break;
            case 'absenteisme':
                $summary = $data['summary'] ?? [];
                $comparison = $data['comparison'] ?? [];

                $writeRow(['Synthese']);
                $writeRow(['Periode', $data['start'] ?? '', $data['end'] ?? '']);
                $writeRow(['Departement', $data['departement'] ?? 'Tous']);
                $writeRow(['Taux absenteisme global (%)', $summary['taux_absenteisme_global'] ?? 0]);
                $writeRow(['Cout total estime', $summary['cout_total'] ?? 0]);
                $writeRow(['Periode precedente', $comparison['previous_start'] ?? '', $comparison['previous_end'] ?? '']);
                $writeRow(['Delta taux absenteisme (pts)', $comparison['delta_taux_absenteisme_global'] ?? 0]);
                $writeRow(['Delta cout total', $comparison['delta_cout_total'] ?? 0]);
                $blank();

                $writeRow(['Classement employes']);
                $writeRow(['Matricule', 'Prenom', 'Nom', 'Departement', 'Jours total', 'Jours absence', 'Retards (min)', 'Taux absenteisme (%)', 'Cout absences', 'Cout retards', 'Cout total']);
                foreach ($data['by_employee'] as $r) {
                    $writeRow([
                        $r['matricule'],
                        $r['prenom'],
                        $r['nom'],
                        $r['departement'],
                        $r['jours_total'],
                        $r['jours_absence'],
                        $r['retard_minutes'],
                        $r['taux_absenteisme'],
                        $r['cout_absence'],
                        $r['cout_retard'],
                        $r['cout_total']
                    ]);
                }

                $blank();
                $writeRow(['Details absences']);
                $writeRow(['Date', 'Matricule', 'Prenom', 'Nom', 'Departement']);
                foreach ($data['rows'] as $r) {
                    $writeRow([
                        $r['date_pointage'],
                        $r['matricule'],
                        $r['prenom'],
                        $r['nom'],
                        $r['departement'] ?? ''
                    ]);
                }
                break;
            default:
                $writeRow(['Rapport vide']);
                break;
        }

        rewind($stream);
        $content = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->setBody($content);
    }

    private function computeWorkedHours(string $heureArrivee, string $heureSortie): float
    {
        if ($heureArrivee === '' || $heureSortie === '') {
            return 0.0;
        }

        $arrivee = strtotime('1970-01-01 ' . $heureArrivee);
        $sortie = strtotime('1970-01-01 ' . $heureSortie);

        if ($arrivee === false || $sortie === false) {
            return 0.0;
        }

        if ($sortie < $arrivee) {
            $sortie += 86400;
        }

        return max(0.0, ($sortie - $arrivee) / 3600);
    }

    private function computeDurationMinutes(string $heureArrivee, string $heureSortie): int
    {
        if ($heureArrivee === '' || $heureSortie === '') {
            return 0;
        }

        $arrivee = strtotime('1970-01-01 ' . $heureArrivee);
        $sortie = strtotime('1970-01-01 ' . $heureSortie);

        if ($arrivee === false || $sortie === false) {
            return 0;
        }

        if ($sortie < $arrivee) {
            $sortie += 86400;
        }

        return (int) max(0, round(($sortie - $arrivee) / 60));
    }

    /**
     * @return list<string>
     */
    private function listDepartments(): array
    {
        $db = db_connect();
        $rows = $db->table('employes')
            ->select('departement')
            ->where('departement IS NOT NULL', null, false)
            ->where('departement !=', '')
            ->groupBy('departement')
            ->orderBy('departement', 'ASC')
            ->get()
            ->getResultArray();

        return array_values(array_map(static fn(array $row): string => (string) $row['departement'], $rows));
    }

    private function getDailySalaryExpression(): string
    {
        $columns = $this->getEmployeeColumns();
        $hasDaily = in_array('salaire_journalier', $columns, true);
        $hasMonthly = in_array('salaire_base', $columns, true);

        if ($hasDaily && $hasMonthly) {
            return 'COALESCE(e.salaire_journalier, e.salaire_base / 22, 0)';
        }

        if ($hasDaily) {
            return 'IFNULL(e.salaire_journalier, 0)';
        }

        if ($hasMonthly) {
            return 'IFNULL(e.salaire_base / 22, 0)';
        }

        return '0';
    }

    /**
     * @return string[]
     */
    private function getEmployeeColumns(): array
    {
        if ($this->employeeColumnsCache !== null) {
            return $this->employeeColumnsCache;
        }

        try {
            $this->employeeColumnsCache = db_connect()->getFieldNames('employes');
        } catch (\Throwable) {
            $this->employeeColumnsCache = [];
        }

        return $this->employeeColumnsCache;
    }
}
