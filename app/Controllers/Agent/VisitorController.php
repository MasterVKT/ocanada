<?php

declare(strict_types=1);

namespace App\Controllers\Agent;

use App\Controllers\BaseController;
use App\Models\VisiteurModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface as HttpResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Agent Visitor Controller
 * Agents can register visitors, checkout, and view visitor history
 */
class VisitorController extends BaseController
{
    protected VisiteurModel $visiteurModel;

    public function initController(RequestInterface $request, HttpResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->visiteurModel = model(VisiteurModel::class);
    }

    /**
     * Fast visitor registration form
     * Optimized for kiosk + agent speed
     */
    public function register(): string
    {
        return $this->renderView('agent/visitors/register', [
            'title' => 'Enregistrement visiteur',
        ]);
    }

    /**
     * Store visitor arrival
     */
    public function store(): ResponseInterface
    {
        // Validation
        $rules = [
            'nom'              => 'required|regex_match[/^[\p{L}\s\'\-]+$/u]|min_length[2]|max_length[50]',
            'prenom'           => 'required|regex_match[/^[\p{L}\s\'\-]+$/u]|min_length[2]|max_length[50]',
            'email'            => 'required|valid_email|max_length[100]',
            'telephone'        => 'required|regex_match[/^[\d\s\-\+\(\)]+$/]|min_length[7]|max_length[20]',
            'motif'            => 'required|regex_match[/^[\p{L}\p{N}\s\'\-\.,\(\)\/:&]+$/u]|min_length[3]|max_length[255]',
            'personne_a_voir'  => 'required|regex_match[/^[\p{L}\s\'\-]+$/u]|min_length[3]|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success'   => false,
                'errors'    => $this->validator->getErrors(),
                'csrfToken' => csrf_hash(),
            ])->setStatusCode(422);
        }

        // Register
        $data = [
            'nom'              => $this->request->getPost('nom'),
            'prenom'           => $this->request->getPost('prenom'),
            'email'            => $this->request->getPost('email'),
            'telephone'        => $this->request->getPost('telephone'),
            'motif'            => $this->request->getPost('motif'),
            'personne_a_voir'  => $this->request->getPost('personne_a_voir'),
            'heure_arrivee'    => date('H:i'),
            'statut'           => 'present',
        ];

        $visiteurId = $this->visiteurModel->insert($data);

        if ($visiteurId === false) {
            return $this->response->setJSON([
                'success'   => false,
                'message'   => 'Impossible d\'enregistrer le visiteur.',
                'errors'    => $this->visiteurModel->errors(),
                'csrfToken' => csrf_hash(),
            ])->setStatusCode(422);
        }

        $visiteur = $this->visiteurModel->find($visiteurId);

        // Generate badge
        $badgeId = $this->generateBadgeNumber($visiteurId);
        $this->visiteurModel->update($visiteurId, ['badge_id' => $badgeId]);
        $visiteur['badge_id'] = $badgeId;

        // Notify (best-effort to avoid blocking registration if notification layer is unavailable)
        $notificationService = service('notification');
        if ($notificationService !== null && method_exists($notificationService, 'notifyAdmins')) {
            $notificationService->notifyAdmins(
                'VISITEUR_ARRIVEE',
                'Nouvel visiteur',
                "{$visiteur['prenom']} {$visiteur['nom']} pour voir {$visiteur['personne_a_voir']} (Enregistré par agent)",
                '/admin/visitors/' . $visiteurId
            );
        }

        // Return QR code for printing + fresh CSRF token for subsequent requests
        return $this->response->setJSON([
            'success'    => true,
            'visiteurId' => $visiteurId,
            'badgeId'    => $badgeId,
            'qrCodeUrl'  => $this->getQRCodeUrl($badgeId),
            'message'    => "Visiteur enregistré: {$visiteur['prenom']} {$visiteur['nom']}",
            'csrfToken'  => csrf_hash(),
        ]);
    }

    /**
     * List current visitors (present only)
     */
    public function current(): string
    {
        $visitors = $this->visiteurModel->builder()
            ->where('statut', 'present')
            ->orderBy('date_creation', 'DESC')
            ->get()
            ->getResult('array');

        return $this->renderView('agent/visitors/current', [
            'title' => 'Visiteurs actuels',
            'visitors' => $visitors,
        ]);
    }

    /**
     * Visitor search by name or ID
     */
    public function search(): ResponseInterface
    {
        $term = $this->request->getGet('q') ?? '';

        if (strlen($term) < 2) {
            return $this->response->setJSON([
                'success' => false,
                'results' => [],
            ]);
        }

        $results = $this->visiteurModel->builder()
            ->where('statut', 'present')
            ->groupStart()
            ->like('nom', $term)
            ->orLike('prenom', $term)
            ->orLike('badge_id', $term)
            ->groupEnd()
            ->orderBy('date_creation', 'DESC')
            ->limit(10)
            ->get()
            ->getResult('array');

        return $this->response->setJSON([
            'success' => true,
            'results' => array_map(function ($v) {
                return [
                    'id'       => $v['id'],
                    'name'     => "{$v['prenom']} {$v['nom']}",
                    'badgeId'  => $v['badge_id'],
                    'motif'    => $v['motif'],
                    'arrival'  => substr($v['heure_arrivee'], 0, 5),
                ];
            }, $results),
        ]);
    }

    /**
     * Quick checkout
     */
    public function checkout(int $id): ResponseInterface
    {
        $visitor = $this->visiteurModel->find($id);

        if (!$visitor || $this->isDepartedStatus((string) ($visitor['statut'] ?? ''))) {
            return $this->response->setJSON([
                'success'   => false,
                'message'   => 'Visiteur non trouvé ou déjà parti',
                'csrfToken' => csrf_hash(),
            ])->setStatusCode(404);
        }

        $this->visiteurModel->update($id, [
            'heure_depart' => date('H:i'),
            'statut'       => $this->resolveDepartedStatusValue(),
        ]);

        // Notify (best-effort)
        $notificationService = service('notification');
        if ($notificationService !== null && method_exists($notificationService, 'notifyAdmins')) {
            $notificationService->notifyAdmins(
                'VISITEUR_DEPART',
                'Visiteur parti',
                "{$visitor['prenom']} {$visitor['nom']} - Enregistré par agent",
                '/admin/visitors/' . $id
            );
        }

        return $this->response->setJSON([
            'success'   => true,
            'message'   => "Départ enregistré pour {$visitor['prenom']} {$visitor['nom']}",
            'csrfToken' => csrf_hash(),
        ]);
    }

    /**
     * Monthly visitor report for agent view
     */
    public function history(): string
    {
        $month = $this->request->getGet('month') ?? date('Y-m');

        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $visitors = $this->visiteurModel->builder()
            ->where('DATE(date_creation) >=', $startDate)
            ->where('DATE(date_creation) <=', $endDate)
            ->orderBy('date_creation', 'DESC')
            ->get()
            ->getResult('array');

        // Stats
        $totalVisitors = count($visitors);
        $avgVisitDuration = '--:--';

        if ($totalVisitors > 0) {
            $durations = [];
            foreach (array_filter($visitors, fn($v) => $v['heure_depart']) as $v) {
                $seconds = $this->computeVisitDurationSeconds(
                    (string) ($v['heure_arrivee'] ?? ''),
                    (string) ($v['heure_depart'] ?? '')
                );
                if ($seconds > 0) {
                    $durations[] = $seconds;
                }
            }
            if (!empty($durations)) {
                $avg = (int)(array_sum($durations) / count($durations));
                $avgVisitDuration = sprintf('%02d:%02d', intdiv($avg, 3600), intdiv($avg % 3600, 60));
            }
        }

        return $this->renderView('agent/visitors/history', [
            'title'         => 'Historique visiteurs',
            'visitors'      => $visitors,
            'month'         => $month,
            'totalVisitors' => $totalVisitors,
            'avgDuration'   => $avgVisitDuration,
        ]);
    }

    /**
     * Print visitor badge
     */
    public function printBadge(int $id): string
    {
        $visitor = $this->visiteurModel->find($id);

        if (!$visitor) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->renderView('agent/visitors/print-badge', [
            'visitor'    => $visitor,
            'qrCodeUrl'  => $this->getQRCodeUrl($visitor['badge_id']),
        ], 'layouts/blank');
    }

    /**
     * Dashboard widget: today's visitors summary
     */
    public function todaysSummary(): ResponseInterface
    {
        $today = date('Y-m-d');

        $total = $this->visiteurModel->builder()
            ->where('DATE(date_creation)', $today)
            ->countAllResults();

        $present = $this->visiteurModel->builder()
            ->where('DATE(date_creation)', $today)
            ->where('statut', 'present')
            ->countAllResults();

        $departed = $total - $present;

        return $this->response->setJSON([
            'success' => true,
            'total'   => $total,
            'present' => $present,
            'departed' => $departed,
        ]);
    }

    /**
     * Generate badge number
     */
    private function generateBadgeNumber(int $visitorId): string
    {
        $today = date('Ymd');
        $count = $this->visiteurModel->builder()
            ->like('badge_id', "V{$today}", 'after')
            ->countAllResults();

        return sprintf('V%s-%03d', $today, $count + 1);
    }

    /**
     * Get QR code URL
     */
    private function getQRCodeUrl(string $badgeId): string
    {
        return "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($badgeId);
    }

    private function isDepartedStatus(string $status): bool
    {
        return in_array($status, ['departi', 'sorti', 'parti'], true);
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

    private function computeVisitDurationSeconds(string $arrivalTime, string $departureTime): int
    {
        if ($arrivalTime === '' || $departureTime === '') {
            return 0;
        }

        $arrivalTs = strtotime('1970-01-01 ' . $arrivalTime);
        $departureTs = strtotime('1970-01-01 ' . $departureTime);

        if ($arrivalTs === false || $departureTs === false) {
            return 0;
        }

        if ($departureTs < $arrivalTs) {
            $departureTs += 86400;
        }

        return max(0, $departureTs - $arrivalTs);
    }
}
