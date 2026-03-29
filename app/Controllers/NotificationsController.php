<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\NotificationService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Controller pour afficher et gérer les notifications de l'utilisateur.
 * Accessible à tous les utilisateurs authentifiés.
 */
class NotificationsController extends BaseController
{
    protected NotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    /**
     * Liste paginée des notifications (toutes pour le user actuel)
     */
    public function index(): string
    {
        $userId = $this->currentUser['user_id'] ?? null;
        if (!$userId) {
            return '';
        }

        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = 25;

        $model = model(\App\Models\NotificationModel::class);
        $total = $model
            ->where('destinataire_id', $userId)
            ->countAllResults();

        $notifications = $model
            ->where('destinataire_id', $userId)
            ->orderBy('date_creation', 'DESC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->findAll();

        return $this->renderView('notifications/index', [
            'title'         => 'Mes notifications',
            'notifications' => $notifications,
            'currentPage'   => $page,
            'totalPages'    => max(1, (int) ceil($total / $perPage)),
            'totalItems'    => $total,
        ]);
    }

    /**
     * Marque une notification comme lue via POST.
     */
    public function markRead(int $id): ResponseInterface
    {
        $userId = $this->currentUser['user_id'] ?? null;
        if (!$userId) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false]);
        }

        $success = $this->notificationService->markAsRead($id, $userId);
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => $success]);
        }

        return redirect()->back();
    }

    /**
     * Marque toutes les notifications de l'utilisateur comme lues.
     */
    public function markAllRead(): ResponseInterface
    {
        $userId = $this->currentUser['user_id'] ?? null;
        if (!$userId) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false]);
        }

        $model = model(\App\Models\NotificationModel::class);
        $model->where('destinataire_id', $userId)->where('lue', 0)
            ->set(['lue' => 1, 'date_lecture' => date('Y-m-d H:i:s')])
            ->update();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }

        return redirect()->back();
    }
}
