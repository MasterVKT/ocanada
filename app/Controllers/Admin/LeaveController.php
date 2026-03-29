<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CongeModel;
use App\Models\SoldeCongeModel;
use App\Models\EmployeModel;
use CodeIgniter\HTTP\ResponseInterface;

class LeaveController extends BaseController
{
    protected CongeModel $congeModel;
    protected SoldeCongeModel $soldeModel;
    protected EmployeModel $employeModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->congeModel = model(CongeModel::class);
        $this->soldeModel = model(SoldeCongeModel::class);
        $this->employeModel = model(EmployeModel::class);
    }

    /**
     * List leave requests with filters
     */
    public function index(): string
    {
        $requestedStatus = (string) ($this->request->getGet('statut') ?? 'en_attente');
        $status = $this->normalizeStatusFilter($requestedStatus);
        $page   = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 20;
        $db = db_connect();

        // Count total
        $countBuilder = $db->table('demandes_conge');
        if ($status !== 'tous') {
            $countBuilder->where('statut', $status);
        }
        $total = $countBuilder->countAllResults();

        // Get paginated requests with employee details
        $leavesQuery = $db->table('demandes_conge cd')
            ->select('cd.*, cd.nombre_jours as jours_ouvrables, cd.date_demande as date_soumission, e.prenom, e.nom, e.matricule, a.prenom as approuve_par_prenom, a.nom as approuve_par_nom')
            ->join('employes e', 'e.id = cd.employe_id', 'left')
            ->join('utilisateurs u', 'u.id = cd.approuve_par', 'left')
            ->join('employes a', 'a.id = u.employe_id', 'left');
            
        if ($status !== 'tous') {
            $leavesQuery->where('cd.statut', $status);
        }
            
        $leaves = $leavesQuery
            ->orderBy('cd.date_demande', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        // Count pending requests
        $pendingCount = (int) $db->table('demandes_conge')
            ->where('statut', 'en_attente')
            ->countAllResults();

        $approvedCount = (int) $db->table('demandes_conge')
            ->groupStart()
                ->where('statut', 'approuve')
                ->orWhere('statut', 'approuvee')
            ->groupEnd()
            ->countAllResults();

        $rejectedCount = (int) $db->table('demandes_conge')
            ->groupStart()
                ->where('statut', 'refuse')
                ->orWhere('statut', 'refusee')
            ->groupEnd()
            ->countAllResults();

        $cancelledCount = (int) $db->table('demandes_conge')
            ->groupStart()
                ->where('statut', 'annule')
                ->orWhere('statut', 'annulee')
            ->groupEnd()
            ->countAllResults();

        $pageCount = ceil($total / $perPage);

        return $this->renderView('admin/leaves/index', [
            'title'         => 'Gestion des conges',
            'leaves'        => $leaves,
            'requests'      => $leaves,
            'stats'         => [
                'pending'   => $pendingCount,
                'approved'  => $approvedCount,
                'rejected'  => $rejectedCount,
                'cancelled' => $cancelledCount,
            ],
            'status'        => $requestedStatus,
            'page'          => $page,
            'pageCount'     => $pageCount,
            'totalCount'    => $total,
            'pendingCount'  => $pendingCount,
        ]);
    }

    /**
     * View leave request details and approve/reject
     */
    public function show($leaveId): string
    {
        $leave = $this->congeModel->find($leaveId);

        if (!$leave) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Get employee details
        $employe = $this->employeModel->find($leave['employe_id']);
        $solde = $this->soldeModel->getCurrentSolde($leave['employe_id']);

        // Get approver name if approved
        $approver = null;
        if ($leave['approuve_par']) {
            $db = db_connect();
            $approver = $db->table('utilisateurs u')
                ->select('e.nom, e.prenom')
                ->join('employes e', 'e.id = u.employe_id', 'left')
                ->where('u.id', $leave['approuve_par'])
                ->get()
                ->getFirstRow('array');
        }

        return $this->renderView('admin/leaves/show', [
            'title'    => 'Detail de la demande de conge',
            'leave'    => $leave,
            'employe'  => $employe,
            'solde'    => $solde,
            'approver' => $approver,
        ]);
    }

    /**
     * Approve leave request
     */
    public function approve($leaveId): ResponseInterface
    {
        $leave = $this->congeModel->find($leaveId);
        $commentaire = trim((string) ($this->request->getPost('commentary') ?? $this->request->getPost('commentaire') ?? ''));

        if (!$leave) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Demande de congé non trouvée',
            ])->setStatusCode(404);
        }

        if ($leave['statut'] !== 'en_attente') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cette demande ne peut pas être approuvée (statut: ' . $leave['statut'] . ')',
            ])->setStatusCode(422);
        }

        // Approve and deduct from solde (atomic operation)
        $workingDays = $this->calculateWorkingDays($leave['date_debut'], $leave['date_fin']);
        
        $this->db->transStart();
        try {
            $approved = $this->congeModel->approveRequest($leaveId, $this->currentUser['user_id']);
            
            if (!$approved) {
                throw new \RuntimeException('Erreur lors de l\'approbation de la demande');
            }

            if ($commentaire !== '') {
                $this->congeModel->update($leaveId, ['commentaire' => $commentaire]);
            }

            // Audit log
            $this->auditLog(
                'APPROBATION_CONGE',
                "Approbation congé employé {$leave['employe_id']}: {$workingDays} jours",
                null,
                ['demande_id' => $leaveId, 'traite_par' => $this->currentUser['user_id'], 'commentaire' => $commentaire]
            );

            // Notify employee
            $notificationService = service('notification');
            $notificationService->notifyCongeDecision($leave);

            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Transaction failed during leave approval: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erreur lors de l\'approbation de la demande',
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Demande approuvée avec succès',
        ]);
    }

    /**
     * Reject leave request
     */
    public function reject($leaveId): ResponseInterface
    {
        $leave = $this->congeModel->find($leaveId);

        if (!$leave) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Demande de congé non trouvée',
            ])->setStatusCode(404);
        }

        if ($leave['statut'] !== 'en_attente') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cette demande ne peut pas être refusée',
            ])->setStatusCode(422);
        }

        $motif = trim((string) $this->request->getPost('motif_refus'));
        $commentaire = trim((string) ($this->request->getPost('commentary') ?? $this->request->getPost('commentaire') ?? ''));

        if (empty($motif) || strlen($motif) < 5) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Un motif de refus est obligatoire (min 5 caractères)',
            ])->setStatusCode(422);
        }

        $this->db->transStart();
        try {
            $rejected = $this->congeModel->rejectRequest($leaveId, $motif, $this->currentUser['user_id']);
            
            if (!$rejected) {
                throw new \RuntimeException('Erreur lors du refus de la demande');
            }

            if ($commentaire !== '') {
                $this->congeModel->update($leaveId, ['commentaire' => $commentaire]);
            }

            // Audit log
            $this->auditLog(
                'REFUS_CONGE',
                "Refus congé employé {$leave['employe_id']}: {$motif}",
                null,
                ['demande_id' => $leaveId, 'traite_par' => $this->currentUser['user_id'], 'commentaire' => $commentaire]
            );

            // Notify employee
            $notificationService = service('notification');
            $notificationService->notifyCongeDecision($leave);

            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Transaction failed during leave rejection: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erreur lors du refus de la demande',
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Demande refusée avec succès',
        ]);
    }

    /**
     * Cancel approved leave request
     */
    public function cancel($leaveId): ResponseInterface
    {
        $leave = $this->congeModel->find($leaveId);

        if (!$leave) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Demande de congé non trouvée',
            ])->setStatusCode(404);
        }

        if ($leave['statut'] !== 'approuve') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Seules les demandes approuvées peuvent être annulées',
            ])->setStatusCode(422);
        }

        $motif = trim((string) ($this->request->getPost('motif_annulation') ?? ''));

        // Cancel and restore solde
        $this->db->transStart();
        try {
            $cancelled = $this->congeModel->cancelRequest($leaveId, $this->currentUser['user_id']);
            
            if (!$cancelled) {
                throw new \RuntimeException('Erreur lors de l\'annulation');
            }

            if ($motif !== '') {
                $this->congeModel->update($leaveId, ['commentaire' => $motif]);
            }

            // Audit log
            $this->auditLog(
                'ANNULATION_CONGE',
                "Annulation congé employé {$leave['employe_id']}: {$motif}",
                null,
                ['demande_id' => $leaveId, 'traite_par' => $this->currentUser['user_id']]
            );

            // Notify employee
            $notificationService = service('notification');
            $notificationService->notifyCongeDecision($leave);

            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Transaction failed during leave cancellation: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation',
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Congé annulé et solde restauré',
        ]);
    }

    /**
     * Calculate working days between two dates
     */
    private function calculateWorkingDays(string $dateDebut, string $dateFin): int
    {
        $calculator = new \App\Libraries\WorkingDaysCalculator();
        return $calculator->calculateWorkingDays($dateDebut, $dateFin);
    }

    private function normalizeStatusFilter(string $status): string
    {
        return match ($status) {
            'approuvee' => 'approuve',
            'refusee' => 'refuse',
            'annulee' => 'annule',
            default => $status,
        };
    }
}
