<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\ResponseInterface;

class AuditController extends BaseController
{
    public function index(): string
    {
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = 25;

        $filters = [
            'type' => trim((string) ($this->request->getGet('type') ?? '')),
            'q' => trim((string) ($this->request->getGet('q') ?? '')),
        ];

        $baseBuilder = $this->buildAuditQuery($filters);

        $countBuilder = clone $baseBuilder;
        $total = (int) $countBuilder->countAllResults();

        $rows = $baseBuilder
            ->orderBy('a.date_evenement', 'DESC')
            ->orderBy('a.id', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        $types = db_connect()->table('audit_log')
            ->select('type_evenement')
            ->where('type_evenement IS NOT NULL', null, false)
            ->where('type_evenement !=', '')
            ->groupBy('type_evenement')
            ->orderBy('type_evenement', 'ASC')
            ->get()
            ->getResultArray();

        return $this->renderView('admin/audit/index', [
            'title' => 'Journal d audit',
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'lastPage' => max(1, (int) ceil($total / $perPage)),
            'rows' => $rows,
            'filters' => $filters,
            'types' => array_column($types, 'type_evenement'),
        ]);
    }

    public function detail(int $id): ResponseInterface
    {
        $row = db_connect()->table('audit_log a')
            ->select('a.*, u.email, e.nom, e.prenom')
            ->join('utilisateurs u', 'u.id = a.utilisateur_id', 'left')
            ->join('employes e', 'e.id = u.employe_id', 'left')
            ->where('a.id', $id)
            ->get()
            ->getFirstRow('array');

        if ($row === null) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Entree d audit introuvable.',
            ], 404);
        }

        return $this->jsonResponse([
            'success' => true,
            'row' => [
                'id' => (int) $row['id'],
                'type_evenement' => (string) ($row['type_evenement'] ?? ''),
                'description' => (string) ($row['description'] ?? ''),
                'date_evenement' => (string) ($row['date_evenement'] ?? ''),
                'ip_adresse' => (string) ($row['ip_adresse'] ?? ''),
                'email' => (string) ($row['email'] ?? ''),
                'nom_complet' => trim(((string) ($row['prenom'] ?? '')) . ' ' . ((string) ($row['nom'] ?? ''))),
                'donnees_avant' => $this->normalizeJsonField($row['donnees_avant'] ?? null),
                'donnees_apres' => $this->normalizeJsonField($row['donnees_apres'] ?? null),
            ],
        ]);
    }

    public function exportCsv(): ResponseInterface
    {
        $filters = [
            'type' => trim((string) ($this->request->getGet('type') ?? '')),
            'q' => trim((string) ($this->request->getGet('q') ?? '')),
        ];

        $rows = $this->buildAuditQuery($filters)
            ->orderBy('a.date_evenement', 'DESC')
            ->orderBy('a.id', 'DESC')
            ->get()
            ->getResultArray();

        $filename = 'audit-log-' . date('Ymd-His') . '.csv';
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['id', 'date_evenement', 'type_evenement', 'utilisateur', 'email', 'description', 'ip_adresse'], ';');

        foreach ($rows as $row) {
            fputcsv($handle, [
                (string) ($row['id'] ?? ''),
                (string) ($row['date_evenement'] ?? ''),
                (string) ($row['type_evenement'] ?? ''),
                trim(((string) ($row['prenom'] ?? '')) . ' ' . ((string) ($row['nom'] ?? ''))),
                (string) ($row['email'] ?? ''),
                (string) ($row['description'] ?? ''),
                (string) ($row['ip_adresse'] ?? ''),
            ], ';');
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }

    /**
     * @param array{type:string,q:string} $filters
     */
    private function buildAuditQuery(array $filters): BaseBuilder
    {
        $builder = db_connect()->table('audit_log a')
            ->select('a.id, a.type_evenement, a.description, a.date_evenement, a.ip_adresse, u.email, e.nom, e.prenom')
            ->join('utilisateurs u', 'u.id = a.utilisateur_id', 'left')
            ->join('employes e', 'e.id = u.employe_id', 'left');

        if ($filters['type'] !== '') {
            $builder->where('a.type_evenement', $filters['type']);
        }

        if ($filters['q'] !== '') {
            $builder->groupStart()
                ->like('a.type_evenement', $filters['q'])
                ->orLike('a.description', $filters['q'])
                ->orLike('u.email', $filters['q'])
                ->orLike('e.nom', $filters['q'])
                ->orLike('e.prenom', $filters['q'])
                ->groupEnd();
        }

        return $builder;
    }

    private function normalizeJsonField(mixed $value): array|string|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : (string) $value;
    }
}
