<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PresenceModel;
use App\Models\EmployeModel;
use CodeIgniter\HTTP\ResponseInterface;

class PresencesController extends BaseController
{
    protected PresenceModel $presenceModel;
    protected EmployeModel $employeModel;

    public function __construct()
    {
        $this->presenceModel = model(PresenceModel::class);
        $this->employeModel  = model(EmployeModel::class);
    }

    /**
     * Display today's or selected date presence
     */
    public function index(): string
    {
        $date = $this->request->getGet('date') ?? date('Y-m-d');

        // Validate date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }

        $presences = $this->presenceModel->getByDate($date);

        // Calculate additional stats
        $stats = [
            'total' => count($presences),
            'presents' => count(array_filter($presences, fn($p) => $p['statut'] === 'present')),
            'retards' => count(array_filter($presences, fn($p) => $p['statut'] === 'retard')),
            'absents' => count(array_filter($presences, fn($p) => $p['statut'] === 'absent')),
        ];

        return $this->renderView('admin/presences/index', [
            'title'     => 'Pointages du jour',
            'date'      => $date,
            'presences' => $presences,
            'stats'     => $stats,
        ]);
    }

    /**
     * Display presence history with date range
     */
    public function history(): string
    {
        $employeId = $this->request->getGet('employe_id');
        $dateDebut = $this->request->getGet('date_debut') ?? date('Y-m-d', strtotime('-30 days'));
        $dateFin   = $this->request->getGet('date_fin') ?? date('Y-m-d');
        $page      = max(1, (int) ($this->request->getGet('page') ?? 1));

        // Validate dates
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateDebut) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFin)) {
            $dateDebut = date('Y-m-d', strtotime('-30 days'));
            $dateFin   = date('Y-m-d');
        }

        // Ensure the range is always valid.
        if ($dateDebut > $dateFin) {
            [$dateDebut, $dateFin] = [$dateFin, $dateDebut];
        }

        $perPage = 20;
        $db      = db_connect();
        $builder = $db->table('presences');

        if ($employeId) {
            $builder->where('employe_id', (int) $employeId);
        }

        // Get total count
        $countBuilder = clone $builder;
        $totalRecords = $countBuilder
            ->where('DATE(date_pointage) >=', $dateDebut)
            ->where('DATE(date_pointage) <=', $dateFin)
            ->countAllResults();

        // Get paginated results with employee names
        $presences = $builder
            ->select('presences.*, employes.prenom, employes.nom, employes.matricule')
            ->join('employes', 'employes.id = presences.employe_id', 'left')
            ->where('DATE(presences.date_pointage) >=', $dateDebut)
            ->where('DATE(presences.date_pointage) <=', $dateFin)
            ->orderBy('presences.date_pointage', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()
            ->getResultArray();

        // Get all employees for filter dropdown
        $employes = $this->employeModel
            ->where('statut', 'actif')
            ->orderBy('nom', 'ASC')
            ->findAll();

        $pageCount = max(1, (int) ceil($totalRecords / $perPage));

        return $this->renderView('admin/presences/history', [
            'title'        => 'Historique des pointages',
            'presences'    => $presences,
            'employes'     => $employes,
            'employeId'    => $employeId,
            'dateDebut'    => $dateDebut,
            'dateFin'      => $dateFin,
            'page'         => $page,
            'pageCount'    => $pageCount,
            'totalRecords' => $totalRecords,
        ]);
    }

    /**
     * Display correction form/modal content
     */
    public function correct($presenceId): string|ResponseInterface
    {
        $db = db_connect();
        $presence = $db->table('presences')
            ->select('presences.*, employes.prenom, employes.nom, employes.matricule')
            ->join('employes', 'employes.id = presences.employe_id', 'left')
            ->where('presences.id', (int) $presenceId)
            ->get()
            ->getFirstRow('array');

        if (!$presence) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pointage introuvable',
            ])->setStatusCode(404);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'  => true,
                'presence' => $presence,
                'csrfHash' => csrf_hash(),
            ]);
        }

        return $this->renderView('admin/presences/correct', [
            'title'    => 'Correction du pointage',
            'presence' => $presence,
        ]);
    }

    /**
     * Store corrected presence
     */
    public function storeCorrection($presenceId): ResponseInterface
    {
        $db = db_connect();
        $isAjax = $this->request->isAJAX();

        $presence = $db->table('presences')
            ->where('id', (int) $presenceId)
            ->get()
            ->getFirstRow('array');

        if (!$presence) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Pointage introuvable',
                ])->setStatusCode(404);
            }

            return redirect()
                ->to(site_url('admin/presences/index'))
                ->with('error', 'Pointage introuvable');
        }

        // Validation
        $rules = [
            'statut'              => 'required|in_list[present,retard,absent]',
            'heure_pointage'      => 'permit_empty|regex_match[/^\d{2}:\d{2}$/]',
            'heure_sortie'        => 'permit_empty|regex_match[/^\d{2}:\d{2}$/]',
            'motif_correction'    => 'permit_empty|string|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success'  => false,
                    'errors'   => $this->validator->getErrors(),
                    'csrfHash' => csrf_hash(),
                ])->setStatusCode(422);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('error', 'Le formulaire contient des erreurs.');
        }

        // Prepare update data
        $updateData = [
            'statut'                 => $this->request->getPost('statut'),
            'corrige'                => true,
            'motif_correction'       => $this->request->getPost('motif_correction') ?? null,
            'corrige_par_utilisateur_id' => (int) ($this->currentUser['user_id'] ?? 0),
            'date_modification'      => date('Y-m-d H:i:s'),
        ];

        // Update heure_pointage if provided
        if ($this->request->getPost('heure_pointage')) {
            $updateData['heure_pointage'] = $this->request->getPost('heure_pointage') . ':00';
        }

        // Update heure_sortie if provided
        if ($this->request->getPost('heure_sortie')) {
            $updateData['heure_sortie'] = $this->request->getPost('heure_sortie') . ':00';
        }

        // Update in database
        $updated = $db->table('presences')
            ->where('id', (int) $presenceId)
            ->update($updateData);

        if (!$updated) {
            if ($isAjax) {
                return $this->response->setJSON([
                    'success'  => false,
                    'message'  => 'Impossible de sauvegarder la correction.',
                    'csrfHash' => csrf_hash(),
                ])->setStatusCode(500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Impossible de sauvegarder la correction.');
        }

        // Audit log
        $this->auditLog('CORRECTION_POINTAGE', [
            'presence_id' => (int) $presenceId,
            'employe_id'  => (int) $presence['employe_id'],
            'statut'      => (string) $updateData['statut'],
            'ip'          => $this->getIpAddress(),
        ], $presence, $updateData);

        if ($isAjax) {
            return $this->response->setJSON([
                'success'  => true,
                'message'  => 'Pointage corrigé avec succès',
                'csrfHash' => csrf_hash(),
            ]);
        }

        $datePointage = (string) ($presence['date_pointage'] ?? date('Y-m-d'));
        $targetDate = substr($datePointage, 0, 10);

        return redirect()
            ->to(site_url('admin/presences/index?date=' . $targetDate))
            ->with('success', 'Pointage corrigé avec succès.');
    }

    /**
     * Get daily presence statistics
     */
    public function statistics(): string
    {
        $dateDebut = $this->request->getGet('date_debut') ?? date('Y-m-d', strtotime('-30 days'));
        $dateFin   = $this->request->getGet('date_fin') ?? date('Y-m-d');

        // Validate dates
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateDebut) || 
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFin)) {
            $dateDebut = date('Y-m-d', strtotime('-30 days'));
            $dateFin   = date('Y-m-d');
        }

        $db = db_connect();

        // Daily stats
        $dailyStats = $db->table('presences')
            ->select('DATE(date_pointage) as date, COUNT(*) as total, 
                     SUM(CASE WHEN presences.statut = "present" THEN 1 ELSE 0 END) as presents,
                     SUM(CASE WHEN presences.statut = "retard" THEN 1 ELSE 0 END) as retards,
                     SUM(CASE WHEN presences.statut = "absent" THEN 1 ELSE 0 END) as absents')
            ->where('DATE(date_pointage) >=', $dateDebut)
            ->where('DATE(date_pointage) <=', $dateFin)
            ->groupBy('DATE(date_pointage)')
            ->orderBy('DATE(date_pointage)', 'DESC')
            ->get()
            ->getResultArray();

        // Employee stats
        $employeeStats = $db->table('presences')
            ->select('employe_id, employes.nom, employes.prenom, employes.matricule,
                     COUNT(*) as total,
                     SUM(CASE WHEN presences.statut = "present" THEN 1 ELSE 0 END) as presents,
                     SUM(CASE WHEN presences.statut = "retard" THEN 1 ELSE 0 END) as retards,
                     SUM(CASE WHEN presences.statut = "absent" THEN 1 ELSE 0 END) as absents')
            ->join('employes', 'employes.id = presences.employe_id', 'left')
            ->where('DATE(presences.date_pointage) >=', $dateDebut)
            ->where('DATE(presences.date_pointage) <=', $dateFin)
            ->groupBy('employe_id')
            ->orderBy('retards', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        return $this->renderView('admin/presences/statistics', [
            'title'          => 'Statistiques des pointages',
            'dateDebut'      => $dateDebut,
            'dateFin'        => $dateFin,
            'dailyStats'     => $dailyStats,
            'employeeStats'  => $employeeStats,
        ]);
    }

    /**
     * Get IP address
     */
    private function getIpAddress(): string
    {
        return $this->request->getIPAddress() ?? '';
    }
}