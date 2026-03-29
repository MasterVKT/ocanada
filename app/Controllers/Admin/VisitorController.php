<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VisiteurModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface as HttpResponseInterface;
use Psr\Log\LoggerInterface;

class VisitorController extends BaseController
{
    protected VisiteurModel $visiteurModel;
    private string $qrcodeDir = 'uploads/qrcodes';

    public function initController(RequestInterface $request, HttpResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->visiteurModel = model(VisiteurModel::class);
    }

    /**
     * List all visitors with filters
     */
    public function index(): string
    {
        // Filters
        $filter = $this->request->getGet('filter') ?? 'present'; // present|today|week|all
        $searchTerm = $this->request->getGet('search') ?? '';
        $perPage = 20;
        $page = (int)($this->request->getGet('page') ?? 1);

        $builder = $this->visiteurModel->builder();

        // Apply filter
        switch ($filter) {
            case 'present':
                $builder->where('statut', 'present');
                break;
            case 'today':
                $builder->whereDate('date_creation', date('Y-m-d'));
                break;
            case 'week':
                $builder->where('date_creation >=', date('Y-m-d H:i:s', strtotime('-7 days')));
                break;
            default: // all
                break;
        }

        // Search
        if ($searchTerm) {
            $builder->groupStart()
                ->like('nom', $searchTerm)
                ->orLike('prenom', $searchTerm)
                ->orLike('email', $searchTerm)
                ->orLike('motif', $searchTerm)
                ->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $visitors = $builder
            ->orderBy('date_creation', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResult('array');

        return view('admin/visitors/index', [
            'visitors'    => $visitors,
            'filter'      => $filter,
            'searchTerm'  => $searchTerm,
            'currentPage' => $page,
            'totalPages'  => ceil($total / $perPage),
            'total'       => $total,
        ]);
    }

    /**
     * Show visitor detail with QR badge
     */
    public function show(int $id): string
    {
        $visitor = $this->visiteurModel->find($id);

        if (!$visitor) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Calculate time spent
        $timeSpent = null;
        if ($visitor['heure_arrivee']) {
            $arrival = new \DateTime($visitor['date_creation']);
            if ($visitor['heure_depart'] && $visitor['statut'] === 'parti') {
                $departure = new \DateTime($visitor['date_modification']);
            } else {
                $departure = new \DateTime();
            }
            $interval = $arrival->diff($departure);
            $timeSpent = $interval->format('%H:%I:%S');
        }

        // Generate QR code if not exists
        if (!$visitor['badge_id']) {
            $badgeId = $this->generateBadgeNumber($id);
            $this->visiteurModel->update($id, ['badge_id' => $badgeId]);
            $visitor['badge_id'] = $badgeId;
        }

        return view('admin/visitors/show', [
            'visitor'   => $visitor,
            'timeSpent' => $timeSpent,
        ]);
    }

    /**
     * Display printable QR badge
     */
    public function badge(int $id): string
    {
        $visitor = $this->visiteurModel->find($id);

        if (!$visitor) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Generate badge if needed
        if (!$visitor['badge_id']) {
            $badgeId = $this->generateBadgeNumber($id);
            $this->visiteurModel->update($id, ['badge_id' => $badgeId]);
            $visitor['badge_id'] = $badgeId;
        }

        return view('admin/visitors/badge', [
            'visitor'  => $visitor,
            'qrCodeUrl' => $this->getQRCodeUrl($visitor['badge_id']),
        ]);
    }

    /**
     * Manual checkout visitor
     */
    public function checkout(int $id): ResponseInterface
    {
        $visitor = $this->visiteurModel->find($id);

        if (!$visitor) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Visiteur non trouvé',
            ])->setStatusCode(404);
        }

        if ($visitor['statut'] === 'parti') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Le visiteur est déjà parti',
            ])->setStatusCode(422);
        }

        $this->visiteurModel->update($id, [
            'heure_depart' => date('H:i:s'),
            'statut'       => 'parti',
        ]);

        // Notification
        $notificationService = service('notification');
        $notificationService->notifyAdmins(
            'VISITEUR_DEPART',
            'Visiteur parti',
            "{$visitor['prenom']} {$visitor['nom']} a quitté les locaux",
            '/admin/visitors'
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Visiteur enregistré comme parti',
        ]);
    }

    /**
     * Get statistics dashboard
     */
    public function statistics(): string
    {
        $today = date('Y-m-d');

        // Today stats
        $todayVisitors = $this->visiteurModel->builder()
            ->where('DATE(date_creation)', $today)
            ->countAllResults();

        $todayPresent = $this->visiteurModel->builder()
            ->where('DATE(date_creation)', $today)
            ->where('statut', 'present')
            ->countAllResults();

        $todayDeparted = $todayVisitors - $todayPresent;

        // Week stats
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekVisitors = $this->visiteurModel->builder()
            ->where('date_creation >=', $weekStart)
            ->countAllResults();

        // Top visited employees
        $topEmployees = $this->visiteurModel->builder()
            ->select('personne_a_voir, COUNT(*) as count')
            ->groupBy('personne_a_voir')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get()
            ->getResult('array');

        // Top visitor motifs
        $topMotifs = $this->visiteurModel->builder()
            ->select('motif, COUNT(*) as count')
            ->groupBy('motif')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get()
            ->getResult('array');

        // Average visit duration this week
        $weekData = $this->visiteurModel->builder()
            ->select('TIMEDIFF(COALESCE(heure_depart, NOW()), heure_arrivee) as duration')
            ->where('date_creation >=', $weekStart)
            ->where('heure_depart IS NOT NULL')
            ->get()
            ->getResult('array');

        $avgDuration = '--:--:--';
        if (!empty($weekData)) {
            // Parse durations and calculate average
            $totalSeconds = 0;
            $count = 0;
            foreach ($weekData as $row) {
                if ($row['duration']) {
                    [$h, $m, $s] = explode(':', $row['duration']);
                    $totalSeconds += (int)$h * 3600 + (int)$m * 60 + (int)$s;
                    $count++;
                }
            }
            if ($count > 0) {
                $seconds = (int)($totalSeconds / $count);
                $avgDuration = sprintf('%02d:%02d:%02d', 
                    intdiv($seconds, 3600),
                    intdiv($seconds % 3600, 60),
                    $seconds % 60
                );
            }
        }

        return view('admin/visitors/statistics', [
            'todayVisitors'    => $todayVisitors,
            'todayPresent'     => $todayPresent,
            'todayDeparted'    => $todayDeparted,
            'weekVisitors'     => $weekVisitors,
            'topEmployees'     => $topEmployees,
            'topMotifs'        => $topMotifs,
            'avgDuration'      => $avgDuration,
        ]);
    }

    /**
     * Export visitor history to CSV
     */
    public function exportCsv(): ResponseInterface
    {
        $startDate = $this->request->getGet('start') ?? date('Y-m-01');
        $endDate = $this->request->getGet('end') ?? date('Y-m-d');

        $visitors = $this->visiteurModel->builder()
            ->where('DATE(date_creation) >=', $startDate)
            ->where('DATE(date_creation) <=', $endDate)
            ->orderBy('date_creation', 'DESC')
            ->get()
            ->getResult('array');

        // Generate CSV
        $csv = "Nom,Prénom,Email,Téléphone,Entreprise,Motif,Personne à voir,Badge,Date arrivée,Heure arrivée,Heure départ,Durée,Statut\n";
        foreach ($visitors as $v) {
            $duration = '--';
            if ($v['heure_arrivee'] && $v['heure_depart']) {
                $arrival = new \DateTime($v['date_creation']);
                $departure = new \DateTime($v['date_modification']);
                $interval = $arrival->diff($departure);
                $duration = $interval->format('%H:%I:%S');
            }

            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $v['nom'],
                $v['prenom'],
                $v['email'],
                $v['telephone'],
                $v['entreprise'] ?? '',
                $v['motif'],
                $v['personne_a_voir'],
                $v['badge_id'] ?? '',
                date('d/m/Y', strtotime($v['date_creation'])),
                substr($v['heure_arrivee'], 0, 5),
                $v['heure_depart'] ? substr($v['heure_depart'], 0, 5) : '--',
                $duration,
                $v['statut']
            );
        }

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="visiteurs_' . date('Y-m-d') . '.csv"')
            ->setBody($csv);
    }

    /**
     * Generate unique badge number V[YYYYMMDD]-[NNN]
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
     * Get QR code URL (external service)
     */
    private function getQRCodeUrl(string $badgeId): string
    {
        // Using QR server (no dependencies)
        return "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($badgeId);
    }
}
