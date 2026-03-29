<?php
declare(strict_types=1);

namespace App\Controllers\API;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PresenceModel;
use App\Models\VisiteurModel;
use App\Models\EmployeModel;

class RealtimeController extends Controller
{
    use ResponseTrait;

    protected PresenceModel $presenceModel;
    protected VisiteurModel $visiteurModel;
    protected EmployeModel $employeModel;

    public function __construct()
    {
        $this->presenceModel = model(PresenceModel::class);
        $this->visiteurModel = model(VisiteurModel::class);
        $this->employeModel  = model(EmployeModel::class);
    }

    /**
     * Restrict realtime global data to authenticated admin/agent users.
     */
    private function authorizeRealtimeAccess(): ?ResponseInterface
    {
        $session = service('session');
        $loggedIn = (bool) ($session->get('logged_in') ?? false);

        if (! $loggedIn) {
            return $this->respond([
                'success' => false,
                'message' => 'Authentification requise',
            ], 401);
        }

        $role = (string) ($session->get('role') ?? '');

        if (! in_array($role, ['admin', 'agent'], true)) {
            return $this->respond([
                'success' => false,
                'message' => 'Acces refuse',
            ], 403);
        }

        return null;
    }

    /**
     * Get all presences for today with employee details
     * GET /api/presences/today
     */
    public function getPresencesToday()
    {
        if (($response = $this->authorizeRealtimeAccess()) !== null) {
            return $response;
        }

        $date = date('Y-m-d');
        $db = db_connect();

        $presences = $db->table('presences')
            ->select('presences.*, employes.prenom, employes.nom, employes.poste, employes.matricule')
            ->join('employes', 'employes.id = presences.employe_id', 'left')
            ->where('DATE(presences.date_pointage)', $date)
            ->where('presences.statut !=', 'absent')
            ->orderBy('presences.heure_pointage', 'DESC')
            ->get()
            ->getResultArray();

        return $this->respond([
            'success'   => true,
            'date'      => $date,
            'presents'  => $presences,
            'count'     => count($presences),
        ]);
    }

    /**
     * Get all visitors currently present
     * GET /api/visiteurs/presents
     */
    public function getVisitorsPresents()
    {
        if (($response = $this->authorizeRealtimeAccess()) !== null) {
            return $response;
        }

        $visitors = $this->visiteurModel->getPresentVisitors();

        return $this->respond([
            'success'   => true,
            'visiteurs' => $visitors,
            'count'     => count($visitors),
        ]);
    }

    /**
     * Get all absents for today
     * GET /api/presences/absents/today
     */
    public function getAbsentsToday()
    {
        if (($response = $this->authorizeRealtimeAccess()) !== null) {
            return $response;
        }

        $date = date('Y-m-d');
        $db = db_connect();

        $absents = $db->table('presences')
            ->select('presences.*, employes.prenom, employes.nom, employes.poste, employes.matricule')
            ->join('employes', 'employes.id = presences.employe_id', 'left')
            ->where('DATE(presences.date_pointage)', $date)
            ->where('presences.statut', 'absent')
            ->orderBy('employes.nom', 'ASC')
            ->get()
            ->getResultArray();

        return $this->respond([
            'success' => true,
            'date'    => $date,
            'absents' => $absents,
            'count'   => count($absents),
        ]);
    }

    /**
     * Get presence statistics for today
     * GET /api/presences/today/stats
     */
    public function getPresencesStats()
    {
        if (($response = $this->authorizeRealtimeAccess()) !== null) {
            return $response;
        }

        $date = date('Y-m-d');
        $db = db_connect();

        $stats = [
            'total' => (int) $db->table('presences')
                ->where('DATE(date_pointage)', $date)
                ->countAllResults(),
            'presents' => (int) $db->table('presences')
                ->where('DATE(date_pointage)', $date)
                ->where('statut', 'present')
                ->countAllResults(),
            'retards' => (int) $db->table('presences')
                ->where('DATE(date_pointage)', $date)
                ->where('statut', 'retard')
                ->countAllResults(),
            'absents' => (int) $db->table('presences')
                ->where('DATE(date_pointage)', $date)
                ->where('statut', 'absent')
                ->countAllResults(),
        ];

        return $this->respond([
            'success' => true,
            'date'    => $date,
            'stats'   => $stats,
        ]);
    }

    /**
     * Get visitor statistics for today
     * GET /api/visiteurs/today/stats
     */
    public function getVisitorsStats()
    {
        if (($response = $this->authorizeRealtimeAccess()) !== null) {
            return $response;
        }

        $date = date('Y-m-d');
        $db = db_connect();

        $total = (int) $db->table('visiteurs')
            ->where('DATE(date_creation)', $date)
            ->countAllResults();
        
        $presents = (int) $db->table('visiteurs')
            ->where('DATE(date_creation)', $date)
            ->where('statut', 'present')
            ->countAllResults();

        $stats = [
            'total'   => $total,
            'presents' => $presents,
            'partis'  => $total - $presents,
        ];

        return $this->respond([
            'success' => true,
            'date'    => $date,
            'stats'   => $stats,
        ]);
    }

    /**
     * Get detailed visitor analytics (for admin dashboard)
     * GET /api/visiteurs/analytics
     */
    public function getVisitorsAnalytics()
    {
        if (($response = $this->authorizeRealtimeAccess()) !== null) {
            return $response;
        }

        $date = date('Y-m-d');
        $db = db_connect();

        // Today stats
        $todayTotal = (int) $db->table('visiteurs')
            ->where('DATE(date_creation)', $date)
            ->countAllResults();
        
        $todayPresent = (int) $db->table('visiteurs')
            ->where('DATE(date_creation)', $date)
            ->where('statut', 'present')
            ->countAllResults();

        // Week stats
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekTotal = (int) $db->table('visiteurs')
            ->where('date_creation >=', $weekStart . ' 00:00:00')
            ->countAllResults();

        // Top motifs
        $topMotifs = $db->table('visiteurs')
            ->select('motif, COUNT(*) as count')
            ->where('DATE(date_creation)', $date)
            ->groupBy('motif')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        // Top visited employees
        $topEmployees = $db->table('visiteurs')
            ->select('personne_a_voir, COUNT(*) as count')
            ->where('DATE(date_creation)', $date)
            ->groupBy('personne_a_voir')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return $this->respond([
            'success' => true,
            'date'    => $date,
            'today'   => [
                'total'   => $todayTotal,
                'present' => $todayPresent,
                'departed' => $todayTotal - $todayPresent,
            ],
            'week'    => [
                'total'   => $weekTotal,
            ],
            'topMotifs'    => $topMotifs,
            'topEmployees' => $topEmployees,
        ]);
    }

    /**
     * Get combined realtime dashboard data
     * GET /api/realtime/dashboard
     */
    public function getDashboardData()
    {
        if (($response = $this->authorizeRealtimeAccess()) !== null) {
            return $response;
        }

        $date = date('Y-m-d');
        $db = db_connect();

        // Get presences
        $presences = $db->table('presences')
            ->select('presences.*, employes.prenom, employes.nom, employes.poste, employes.matricule')
            ->join('employes', 'employes.id = presences.employe_id', 'left')
            ->where('DATE(presences.date_pointage)', $date)
            ->where('presences.statut !=', 'absent')
            ->limit(10)
            ->get()
            ->getResultArray();

        // Get visitors
        $visitors = $this->visiteurModel->getPresentVisitors();

        // Get absents
        $absents = $db->table('presences')
            ->select('presences.*, employes.prenom, employes.nom, employes.poste')
            ->join('employes', 'employes.id = presences.employe_id', 'left')
            ->where('DATE(presences.date_pointage)', $date)
            ->where('presences.statut', 'absent')
            ->limit(10)
            ->get()
            ->getResultArray();

        return $this->respond([
            'success'   => true,
            'date'      => $date,
            'presences' => $presences,
            'visitors'  => $visitors,
            'absents'   => $absents,
            'stats'     => [
                'presences' => count($presences),
                'visitors'  => count($visitors),
                'absents'   => count($absents),
            ],
        ]);
    }
}