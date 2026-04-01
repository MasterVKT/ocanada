<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\VisiteurModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface as HttpResponseInterface;
use Psr\Log\LoggerInterface;

class VisitorController extends BaseController
{
    protected VisiteurModel $visiteurModel;

    public function initController(RequestInterface $request, HttpResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->visiteurModel = model(VisiteurModel::class);
    }

    /**
     * Register visitor - Display form or in realtime mode
     */
    public function index(): string
    {
        $presentVisitors = $this->visiteurModel->getPresentVisitors();

        return view('visitor/index', [
            'presentVisitors' => $presentVisitors,
        ]);
    }

    /**
     * Store new visitor arrival
     */
    public function register(): ResponseInterface
    {
        // Validation
        $rules = [
            'nom'              => 'required|alpha_space|min_length[2]|max_length[50]',
            'prenom'           => 'required|alpha_space|min_length[2]|max_length[50]',
            'email'            => 'required|valid_email|max_length[100]',
            'telephone'        => 'required|regex_match[/^[\d\s\-\+\(\)]+$/]|min_length[7]|max_length[20]',
            'entreprise'       => 'permit_empty|alpha_space|max_length[100]',
            'motif'            => 'required|alpha_numeric_space|min_length[3]|max_length[255]',
            'personne_a_voir'  => 'required|alpha_space|min_length[3]|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors' => $this->validator->getErrors(),
            ])->setStatusCode(422);
        }

        // Register arrival
        $data = [
            'nom'              => $this->request->getPost('nom'),
            'prenom'           => $this->request->getPost('prenom'),
            'email'            => $this->request->getPost('email'),
            'telephone'        => $this->request->getPost('telephone'),
            'entreprise'       => $this->request->getPost('entreprise') ?? null,
            'motif'            => $this->request->getPost('motif'),
            'personne_a_voir'  => $this->request->getPost('personne_a_voir'),
            'heure_arrivee'    => date('H:i:s'),
            'statut'           => 'present',
            'date_creation'    => date('Y-m-d H:i:s'),
        ];

        $visiteurId = $this->visiteurModel->insert($data);
        $visiteur = $this->visiteurModel->find($visiteurId);

        // Notify admin + personne_a_voir
        $notificationService = service('notification');
        $notificationService->notifyAdmins(
            'VISITEUR_ARRIVEE',
            'Nouvel visiteur',
            "{$visiteur['prenom']} {$visiteur['nom']} pour voir {$visiteur['personne_a_voir']}",
            '/visitor/index'
        );

        // Audit log
        log_activity(
            'VISITEUR_ARRIVEE',
            "{$visiteur['prenom']} {$visiteur['nom']} arrivé à {$visiteur['heure_arrivee']}",
            'visiteurs',
            $visiteurId,
            null,
            $this->getIpAddress()
        );

        return $this->response->setJSON([
            'success'  => true,
            'message'  => 'Visiteur enregistré avec succès',
            'badge_id' => $visiteur['badge_id'],
            'visiteur' => [
                'id'       => $visiteur['id'],
                'nom'      => $visiteur['nom'],
                'prenom'   => $visiteur['prenom'],
                'motif'    => $visiteur['motif'],
                'arrivee'  => $visiteur['heure_arrivee'],
            ],
        ]);
    }

    /**
     * Visitor checkout
     */
    public function checkout(int $visiteurId): ResponseInterface
    {
        $visiteur = $this->visiteurModel->find($visiteurId);

        if (!$visiteur) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Visiteur introuvable',
            ])->setStatusCode(404);
        }

        if ($this->isDepartedStatus((string) ($visiteur['statut'] ?? ''))) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Visiteur déjà parti',
            ])->setStatusCode(409);
        }

        // Update departure time
        $this->visiteurModel->update($visiteurId, [
            'heure_depart'      => date('H:i:s'),
            'statut'            => $this->resolveDepartedStatusValue(),
            'date_modification' => date('Y-m-d H:i:s'),
        ]);

        // Notify admin
        $notificationService = service('notification');
        $notificationService->notifyAdmins(
            'VISITEUR_DEPART',
            'Départ d\'un visiteur',
            "{$visiteur['prenom']} {$visiteur['nom']} a quitté",
            '/visitor/history'
        );

        // Audit log
        log_activity(
            'VISITEUR_DEPART',
            "{$visiteur['prenom']} {$visiteur['nom']} départ à " . date('H:i:s'),
            'visiteurs',
            $visiteurId,
            null,
            $this->getIpAddress()
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Départ du visiteur enregistré',
        ]);
    }

    /**
     * Visitor history with date filtering
     */
    public function history(): string
    {
        $dateDebut = $this->request->getGet('date_debut') ?? date('Y-m-d', strtotime('-30 days'));
        $dateFin   = $this->request->getGet('date_fin') ?? date('Y-m-d');
        $page      = (int) ($this->request->getGet('page') ?? 1);

        // Validate dates
        if (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateDebut) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFin)
        ) {
            $dateDebut = date('Y-m-d', strtotime('-30 days'));
            $dateFin   = date('Y-m-d');
        }

        $perPage = 20;
        $db      = db_connect();
        $builder = $db->table('visiteurs');

        // Query for total count
        $countBuilder = clone $builder;
        $totalRecords = $countBuilder
            ->where('DATE(date_creation) >=', $dateDebut)
            ->where('DATE(date_creation) <=', $dateFin)
            ->countAllResults();

        // Query with pagination
        $visitors = $builder
            ->where('DATE(date_creation) >=', $dateDebut)
            ->where('DATE(date_creation) <=', $dateFin)
            ->orderBy('date_creation', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        // Pagination data
        $pageCount = ceil($totalRecords / $perPage);

        return view('visitor/history', [
            'visitors'     => $visitors,
            'dateDebut'    => $dateDebut,
            'dateFin'      => $dateFin,
            'page'         => $page,
            'pageCount'    => $pageCount,
            'totalRecords' => $totalRecords,
        ]);
    }

    /**
     * Get badge info for printing/download
     */
    public function badge(int $visiteurId): ResponseInterface
    {
        $visiteur = $this->visiteurModel->find($visiteurId);

        if (!$visiteur) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Visiteur introuvable',
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'visiteur' => [
                'badge_id'   => $visiteur['badge_id'],
                'nom'        => $visiteur['nom'],
                'prenom'     => $visiteur['prenom'],
                'entreprise' => $visiteur['entreprise'],
                'motif'      => $visiteur['motif'],
                'arrivee'    => $visiteur['heure_arrivee'],
            ],
        ]);
    }

    /**
     * Get present visitors via AJAX (for realtime view)
     */
    public function getPresentAjax(): ResponseInterface
    {
        $present = $this->visiteurModel->getPresentVisitors();

        return $this->response->setJSON([
            'success'   => true,
            'visiteurs' => $present,
            'count'     => count($present),
        ]);
    }

    /**
     * Get daily visitor statistics
     */
    public function statistics(): string
    {
        $date = $this->request->getGet('date') ?? date('Y-m-d');

        // Validate date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $db      = db_connect();
        $builder = $db->table('visiteurs');

        $stats = [
            'total'   => $builder->where('DATE(date_creation)', $date)->countAllResults(),
            'presents' => (clone $builder)->where('DATE(date_creation)', $date)
                ->where('statut', 'present')
                ->countAllResults(),
            'partis'  => (clone $builder)->where('DATE(date_creation)', $date)
                ->whereIn('statut', ['departi', 'sorti', 'parti'])
                ->countAllResults(),
        ];

        // Get daily summary by motif
        $summary = (clone $builder)
            ->select('motif, COUNT(*) as count')
            ->where('DATE(date_creation)', $date)
            ->groupBy('motif')
            ->get()
            ->getResultArray();

        return view('visitor/statistics', [
            'date'    => $date,
            'stats'   => $stats,
            'summary' => $summary,
        ]);
    }

    /**
     * Get IP address of request
     */
    private function getIpAddress(): string
    {
        return $this->request->getIPAddress() ?? '';
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
}
