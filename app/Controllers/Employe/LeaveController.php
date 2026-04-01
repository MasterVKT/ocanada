<?php

declare(strict_types=1);

namespace App\Controllers\Employe;

use App\Controllers\BaseController;
use App\Models\CongeModel;
use App\Models\SoldeCongeModel;
use App\Libraries\WorkingDaysCalculator;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface as HttpResponseInterface;
use Psr\Log\LoggerInterface;

class LeaveController extends BaseController
{
    protected CongeModel $congeModel;
    protected SoldeCongeModel $soldeModel;
    protected WorkingDaysCalculator $calculator;

    public function initController(RequestInterface $request, HttpResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->congeModel  = model(CongeModel::class);
        $this->soldeModel  = model(SoldeCongeModel::class);
        $this->calculator  = new WorkingDaysCalculator();
    }

    /**
     * List employee's leave requests
     */
    public function index(): string|ResponseInterface
    {
        $employeId = $this->currentUser['employe_id'] ?? null;

        if (!$employeId) {
            return $this->response->setStatusCode(403)->setBody(view('errors/403'));
        }

        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        $filterStatus = (string) ($this->request->getGet('statut') ?? '');
        $normalizedStatus = $this->normalizeLeaveStatus($filterStatus);

        $countBuilder = $this->congeModel->builder()
            ->where('employe_id', $employeId)
            ->whereIn('statut', ['en_attente', 'approuve', 'refuse', 'annule']);

        if ($normalizedStatus !== null) {
            $countBuilder->where('statut', $normalizedStatus);
        }

        $total = $countBuilder->countAllResults();

        $requestBuilder = $this->congeModel->builder()
            ->select('demandes_conge.*, nombre_jours as jours_ouvrables, date_demande as date_soumission')
            ->where('employe_id', $employeId)
            ->whereIn('statut', ['en_attente', 'approuve', 'refuse', 'annule']);

        if ($normalizedStatus !== null) {
            $requestBuilder->where('statut', $normalizedStatus);
        }

        $requests = $requestBuilder
            ->orderBy('date_demande', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        $solde = $this->soldeModel->getCurrentSolde($employeId);

        $pageCount = (int) ceil($total / $perPage);

        return $this->renderView('employe/leaves/index', [
            'title'      => 'Mes Demandes de Congé',
            'requests'   => $requests,
            'solde'      => $solde,
            'page'       => $page,
            'pageCount'  => $pageCount,
            'totalCount' => $total,
            'filter_status' => $filterStatus,
            'employeId'  => $employeId,
        ]);
    }

    /**
     * Display leave request creation form
     */
    public function create(): string|ResponseInterface
    {
        $employeId = $this->currentUser['employe_id'] ?? null;

        if (!$employeId) {
            return $this->response->setStatusCode(403)->setBody(view('errors/403'));
        }

        $solde = $this->soldeModel->getCurrentSolde($employeId);

        return $this->renderView('employe/leaves/create', [
            'title'     => 'Nouvelle Demande de Congé',
            'solde'     => $solde,
            'employeId' => $employeId,
        ]);
    }

    /**
     * Store leave request
     */
    public function store(): ResponseInterface
    {
        $employeId = $this->currentUser['employe_id'] ?? null;

        if (!$employeId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Employé non trouvé',
            ])->setStatusCode(403);
        }

        $rules = [
            'type_conge' => 'required|in_list[annuel,maladie,autre,maternite_paternite,sans_solde]',
            'date_debut' => 'required|valid_date[Y-m-d]',
            'date_fin'   => 'required|valid_date[Y-m-d]|greater_than_equal_to[date_debut]',
            'motif'      => 'required|string|min_length[10]|max_length[500]',
            'nombre_jours' => 'permit_empty|numeric|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'errors'  => $this->validator->getErrors(),
            ])->setStatusCode(422);
        }

        $type      = (string) $this->request->getPost('type_conge');
        $dateDebut = $this->request->getPost('date_debut');
        $dateFin   = $this->request->getPost('date_fin');
        $motif     = $this->request->getPost('motif');

        $workingDays = max(
            (float) ($this->request->getPost('nombre_jours') ?? 0),
            (float) $this->calculator->calculateWorkingDays($dateDebut, $dateFin)
        );

        $solde = $this->soldeModel->getCurrentSolde($employeId);

        if ($solde === null) {
            $this->soldeModel->initForEmployee($employeId);
            $solde = $this->soldeModel->getCurrentSolde($employeId);
        }

        if ($solde === null) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Impossible de charger votre solde de conge.',
            ])->setStatusCode(500);
        }

        if ($type === 'annuel' && $solde['restant'] < $workingDays) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solde insuffisant. Vous avez ' . $solde['restant'] . ' jour(s) disponible(s).',
            ])->setStatusCode(422);
        }

        if ($type === 'maladie' && $solde['maladie_restant'] < $workingDays) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solde maladie insuffisant. Vous avez ' . $solde['maladie_restant'] . ' jour(s) disponible(s).',
            ])->setStatusCode(422);
        }

        $inserted = $this->congeModel->insert([
            'employe_id'    => $employeId,
            'type_conge'    => $type,
            'date_debut'    => $dateDebut,
            'date_fin'      => $dateFin,
            'nombre_jours'  => $workingDays,
            'motif'         => $motif,
            'statut'        => 'en_attente',
            'date_demande'  => date('Y-m-d H:i:s'),
        ]);

        if (!$inserted) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erreur lors de la création de la demande',
            ])->setStatusCode(500);
        }

        // Audit log
        log_activity(
            'DEMANDE_CONGE',
            "Demande de congé {$type} du {$dateDebut} au {$dateFin}",
            'demandes_conge',
            $this->congeModel->getInsertID(),
            isset($this->currentUser['id']) ? (int) $this->currentUser['id'] : null,
            $this->request->getIPAddress()
        );

        // Notify admins
        $notificationService = service('notification');
        $notificationService->notifyAdmins(
            'NOUVELLE_DEMANDE_CONGE',
            'Nouvelle demande de congé',
            "Nouvelle demande de congé ({$type}) du {$dateDebut} au {$dateFin}",
            '/admin/leaves'
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Demande de congé créée avec succès',
            'redirect' => base_url('employe/leaves'),
        ]);
    }

    /**
     * View leave request details
     */
    public function show($leaveId): string|ResponseInterface
    {
        $employeId = $this->currentUser['employe_id'] ?? null;

        if (!$employeId) {
            return $this->response->setStatusCode(403)->setBody(view('errors/403'));
        }

        $leave = $this->congeModel->where('id', (int) $leaveId)
            ->where('employe_id', $employeId)
            ->select('demandes_conge.*, nombre_jours as jours_ouvrables, date_demande as date_soumission, date_approbation as date_traitement, refus_motif as commentaire_admin')
            ->first();

        if (!$leave) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $solde = $this->soldeModel->getCurrentSolde($employeId);

        return $this->renderView('employe/leaves/show', [
            'title'     => 'Détail de ma Demande',
            'leave'     => $leave,
            'solde'     => $solde,
            'employeId' => $employeId,
        ]);
    }

    /**
     * Cancel a pending leave request owned by the current employee.
     */
    public function cancel(int $leaveId): ResponseInterface
    {
        $employeId = $this->currentUser['employe_id'] ?? null;

        if (!$employeId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Employé non trouvé',
            ])->setStatusCode(403);
        }

        $leave = $this->congeModel
            ->where('id', $leaveId)
            ->where('employe_id', $employeId)
            ->first();

        if ($leave === null) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Demande introuvable',
            ])->setStatusCode(404);
        }

        if (($leave['statut'] ?? '') !== 'en_attente') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Seules les demandes en attente peuvent être annulées.',
            ])->setStatusCode(422);
        }

        $updated = $this->congeModel->update($leaveId, [
            'statut' => 'annule',
            'commentaire' => 'Annulation par l employe',
        ]);

        if (! $updated) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Impossible d annuler cette demande pour le moment.',
            ])->setStatusCode(500);
        }

        $this->auditLog('DEMANDE_CONGE_ANNULEE_EMPLOYE', [
            'leave_id' => $leaveId,
            'employe_id' => $employeId,
        ], $leave, [
            'statut' => 'annule',
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Demande annulée avec succès.',
        ]);
    }

    /**
     * Calculate working days (AJAX)
     */
    public function calculateWorkingDays(): ResponseInterface
    {
        $payload = [];
        $contentType = strtolower($this->request->getHeaderLine('Content-Type'));

        if (str_contains($contentType, 'application/json')) {
            try {
                $parsed = $this->request->getJSON(true);
                if (is_array($parsed)) {
                    $payload = $parsed;
                }
            } catch (\Throwable $e) {
                $payload = [];
            }
        }

        $dateDebut = $payload['date_debut'] ?? $this->request->getGet('date_debut') ?? $this->request->getPost('date_debut');
        $dateFin   = $payload['date_fin'] ?? $this->request->getGet('date_fin') ?? $this->request->getPost('date_fin');

        if (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateDebut) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFin)
        ) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Format de date invalide',
            ])->setStatusCode(422);
        }

        $workingDays = $this->calculator->calculateWorkingDays($dateDebut, $dateFin);

        return $this->response->setJSON([
            'success'       => true,
            'working_days'  => $workingDays,
            'date_debut'    => $dateDebut,
            'date_fin'      => $dateFin,
        ]);
    }

    private function normalizeLeaveStatus(string $status): ?string
    {
        if ($status === '') {
            return null;
        }

        return match ($status) {
            'approuvee' => 'approuve',
            'refusee' => 'refuse',
            'annulee' => 'annule',
            default => $status,
        };
    }
}
