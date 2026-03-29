<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class FinanceController extends BaseController
{
    public function exportCsv(): ResponseInterface
    {
        $filters = $this->resolveFilters();
        $summary = $this->buildSummary($filters['periodStart'], $filters['periodEnd'], $filters['departement']);
        $employeeRanking = $this->buildEmployeeRanking($filters['periodStart'], $filters['periodEnd'], $filters['departement']);
        $departmentBreakdown = $this->buildDepartmentBreakdown($filters['periodStart'], $filters['periodEnd'], $filters['departement']);

        $filename = sprintf('finance-absenteisme-%s.csv', $filters['periodEnd']);
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            return $this->response->setStatusCode(500)->setBody('Impossible de générer le CSV.');
        }

        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['Tableau financier Ô Canada']);
        fputcsv($stream, ['Période', $filters['periodStart'], $filters['periodEnd']]);
        fputcsv($stream, ['Département', $filters['departement'] !== '' ? $filters['departement'] : 'Tous']);
        fputcsv($stream, []);
        fputcsv($stream, ['Synthèse']);
        fputcsv($stream, ['Coût absences', $summary['cout_absenteisme']]);
        fputcsv($stream, ['Coût retards', $summary['cout_retards']]);
        fputcsv($stream, ['Coût total', $summary['cout_total']]);
        fputcsv($stream, ['Absences', $summary['total_absences']]);
        fputcsv($stream, ['Minutes retard', $summary['total_retard_minutes']]);
        fputcsv($stream, ['Jours équivalents retard', $summary['retard_equivalent_jours']]);
        fputcsv($stream, []);
        fputcsv($stream, ['Ventilation par département']);
        fputcsv($stream, ['Département', 'Absences', 'Retards (min)', 'Coût total']);

        foreach ($departmentBreakdown as $row) {
            fputcsv($stream, [
                $row['departement'],
                $row['absences'],
                $row['retard_minutes'],
                $row['cout_total'],
            ]);
        }

        fputcsv($stream, []);
        fputcsv($stream, ['Classement employés']);
        fputcsv($stream, ['Matricule', 'Employé', 'Département', 'Présences', 'Absences', 'Retards (min)', 'Taux présence (%)', 'Coût total']);

        foreach ($employeeRanking as $row) {
            fputcsv($stream, [
                $row['matricule'],
                trim(((string) $row['prenom']) . ' ' . ((string) $row['nom'])),
                $row['departement'],
                $row['jours_presence'],
                $row['jours_absence'],
                $row['retard_minutes'],
                $row['taux_presence'],
                $row['cout_total'],
            ]);
        }

        rewind($stream);
        $content = stream_get_contents($stream) ?: '';
        fclose($stream);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($content);
    }

    public function index(): string
    {
        $filters = $this->resolveFilters();
        $summary = $this->buildSummary($filters['periodStart'], $filters['periodEnd'], $filters['departement']);
        $employeeRanking = $this->buildEmployeeRanking($filters['periodStart'], $filters['periodEnd'], $filters['departement']);
        $departmentBreakdown = $this->buildDepartmentBreakdown($filters['periodStart'], $filters['periodEnd'], $filters['departement']);
        $monthlyComparison = $this->buildMonthlyComparison($filters['anchorMonth'], $filters['departement']);
        $departements = $this->listDepartments();

        return $this->renderView('admin/finance/index', [
            'title' => 'Tableau financier',
            'filters' => $filters,
            'month' => $filters['anchorMonth'],
            'periodStart' => $filters['periodStart'],
            'periodEnd' => $filters['periodEnd'],
            'summary' => $summary,
            'employeeRanking' => $employeeRanking,
            'departmentBreakdown' => $departmentBreakdown,
            'monthlyComparison' => $monthlyComparison,
            'departements' => $departements,
        ]);
    }

    /**
     * @return array{periode:string,anchorMonth:string,periodStart:string,periodEnd:string,departement:string}
     */
    private function resolveFilters(): array
    {
        $periode = (string) ($this->request->getGet('periode') ?? 'mois_courant');
        $departement = trim((string) ($this->request->getGet('departement') ?? ''));
        $month = (string) ($this->request->getGet('mois') ?? date('Y-m'));
        $dateDebut = (string) ($this->request->getGet('date_debut') ?? '');
        $dateFin = (string) ($this->request->getGet('date_fin') ?? '');

        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $anchorMonth = $month;
        $periodStart = $anchorMonth . '-01';
        $periodEnd = date('Y-m-t', strtotime($periodStart));

        if ($periode === 'mois_precedent') {
            $anchorMonth = date('Y-m', strtotime(date('Y-m') . '-01 -1 month'));
            $periodStart = $anchorMonth . '-01';
            $periodEnd = date('Y-m-t', strtotime($periodStart));
        } elseif ($periode === 'personnalise' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateDebut) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFin)) {
            if ($dateFin < $dateDebut) {
                [$dateDebut, $dateFin] = [$dateFin, $dateDebut];
            }
            $periodStart = $dateDebut;
            $periodEnd = $dateFin;
            $anchorMonth = substr($periodEnd, 0, 7);
        }

        return [
            'periode' => $periode,
            'anchorMonth' => $anchorMonth,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'departement' => $departement,
        ];
    }

    /**
     * @return array<string,int|float>
     */
    private function buildSummary(string $periodStart, string $periodEnd, string $departement = ''): array
    {
        $db = db_connect();
        $builder = $db->table('presences p')
            ->select(
                "COUNT(*) AS total_pointages,
                SUM(CASE WHEN p.statut = 'absent' THEN 1 ELSE 0 END) AS total_absences,
                SUM(CASE WHEN p.statut = 'retard' THEN IFNULL(p.retard_minutes, 0) ELSE 0 END) AS total_retard_minutes,
                SUM(CASE WHEN p.statut = 'absent' THEN IFNULL(e.salaire_base, 0) / 22 ELSE 0 END) AS cout_absenteisme,
                SUM(CASE WHEN p.statut = 'retard' THEN ((IFNULL(e.salaire_base, 0) / 22) / 8) * (IFNULL(p.retard_minutes, 0) / 60) ELSE 0 END) AS cout_retards,
                SUM(CASE WHEN p.statut IN ('present', 'retard') THEN 1 ELSE 0 END) AS total_presence"
            )
            ->join('employes e', 'e.id = p.employe_id', 'left')
            ->where('p.date_pointage >=', $periodStart)
            ->where('p.date_pointage <=', $periodEnd);

        if ($departement !== '') {
            $builder->where('e.departement', $departement);
        }

        $summary = $builder->get()->getFirstRow('array') ?? [];
        $coutAbsenteisme = (float) ($summary['cout_absenteisme'] ?? 0);
        $coutRetards = (float) ($summary['cout_retards'] ?? 0);
        $retardMinutes = (int) ($summary['total_retard_minutes'] ?? 0);

        return [
            'total_pointages' => (int) ($summary['total_pointages'] ?? 0),
            'total_presence' => (int) ($summary['total_presence'] ?? 0),
            'total_absences' => (int) ($summary['total_absences'] ?? 0),
            'total_retard_minutes' => $retardMinutes,
            'retard_equivalent_jours' => round($retardMinutes / 480, 2),
            'cout_absenteisme' => round($coutAbsenteisme, 2),
            'cout_retards' => round($coutRetards, 2),
            'cout_total' => round($coutAbsenteisme + $coutRetards, 2),
        ];
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function buildEmployeeRanking(string $periodStart, string $periodEnd, string $departement = ''): array
    {
        $db = db_connect();
        $builder = $db->table('presences p')
            ->select(
                "p.employe_id,
                e.matricule,
                e.nom,
                e.prenom,
                COALESCE(NULLIF(e.departement, ''), 'Non renseigné') AS departement,
                COUNT(*) AS jours_total,
                SUM(CASE WHEN p.statut IN ('present', 'retard') THEN 1 ELSE 0 END) AS jours_presence,
                SUM(CASE WHEN p.statut = 'absent' THEN 1 ELSE 0 END) AS jours_absence,
                SUM(CASE WHEN p.statut = 'retard' THEN IFNULL(p.retard_minutes, 0) ELSE 0 END) AS retard_minutes,
                SUM(CASE WHEN p.statut = 'absent' THEN IFNULL(e.salaire_base, 0) / 22 ELSE 0 END) AS cout_absence,
                SUM(CASE WHEN p.statut = 'retard' THEN ((IFNULL(e.salaire_base, 0) / 22) / 8) * (IFNULL(p.retard_minutes, 0) / 60) ELSE 0 END) AS cout_retard"
            )
            ->join('employes e', 'e.id = p.employe_id', 'left')
            ->where('p.date_pointage >=', $periodStart)
            ->where('p.date_pointage <=', $periodEnd)
            ->groupBy(['p.employe_id', 'e.matricule', 'e.nom', 'e.prenom', 'e.departement']);

        if ($departement !== '') {
            $builder->where('e.departement', $departement);
        }

        $rows = $builder->get()->getResultArray();

        foreach ($rows as &$row) {
            $joursTotal = max(1, (int) ($row['jours_total'] ?? 0));
            $joursPresence = (int) ($row['jours_presence'] ?? 0);
            $row['taux_presence'] = round(($joursPresence / $joursTotal) * 100, 1);
            $row['cout_total'] = round((float) ($row['cout_absence'] ?? 0) + (float) ($row['cout_retard'] ?? 0), 2);
        }
        unset($row);

        usort($rows, static function (array $left, array $right): int {
            return [$right['taux_presence'], $left['retard_minutes']] <=> [$left['taux_presence'], $right['retard_minutes']];
        });

        return $rows;
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function buildDepartmentBreakdown(string $periodStart, string $periodEnd, string $departement = ''): array
    {
        $db = db_connect();
        $builder = $db->table('presences p')
            ->select(
                "COALESCE(NULLIF(e.departement, ''), 'Non renseigné') AS departement,
                SUM(CASE WHEN p.statut = 'absent' THEN 1 ELSE 0 END) AS absences,
                SUM(CASE WHEN p.statut = 'retard' THEN IFNULL(p.retard_minutes, 0) ELSE 0 END) AS retard_minutes,
                SUM(CASE WHEN p.statut = 'absent' THEN IFNULL(e.salaire_base, 0) / 22 ELSE 0 END) +
                SUM(CASE WHEN p.statut = 'retard' THEN ((IFNULL(e.salaire_base, 0) / 22) / 8) * (IFNULL(p.retard_minutes, 0) / 60) ELSE 0 END) AS cout_total"
            )
            ->join('employes e', 'e.id = p.employe_id', 'left')
            ->where('p.date_pointage >=', $periodStart)
            ->where('p.date_pointage <=', $periodEnd)
            ->groupBy('e.departement');

        if ($departement !== '') {
            $builder->where('e.departement', $departement);
        }

        $rows = $builder->get()->getResultArray();
        usort($rows, static fn(array $left, array $right): int => ((float) $right['cout_total']) <=> ((float) $left['cout_total']));

        return $rows;
    }

    /**
     * @return list<array{label:string,cout_absences:float,cout_retards:float,cout_total:float}>
     */
    private function buildMonthlyComparison(string $anchorMonth, string $departement = ''): array
    {
        $series = [];

        for ($offset = 5; $offset >= 0; $offset--) {
            $month = date('Y-m', strtotime($anchorMonth . '-01 -' . $offset . ' month'));
            $periodStart = $month . '-01';
            $periodEnd = date('Y-m-t', strtotime($periodStart));
            $summary = $this->buildSummary($periodStart, $periodEnd, $departement);

            $series[] = [
                'label' => date('M Y', strtotime($periodStart)),
                'cout_absences' => (float) $summary['cout_absenteisme'],
                'cout_retards' => (float) $summary['cout_retards'],
                'cout_total' => (float) $summary['cout_total'],
            ];
        }

        return $series;
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
}
