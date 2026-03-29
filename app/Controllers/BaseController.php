<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\ConfigSystemeModel;
use App\Models\NotificationModel;
use CodeIgniter\Controller;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\Session;
use Config\Database;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected BaseConnection $db;
    protected Session $session;

    /**
     * Informations de l'utilisateur courant chargées depuis la session.
     *
     * @var array{
     *     user_id?: int,
     *     role?: string,
     *     nom_complet?: string,
     *     employe_id?: int|null,
     *     photo_profil?: string|null
     * }
     */
    protected array $currentUser = [];

    /** @var list<string> */
    protected $helpers = ['url', 'ocanada'];

    /**
     * Indique si un utilisateur est connecté.
     */
    protected function isLoggedIn(): bool
    {
        return (bool) ($this->session->get('logged_in') ?? false);
    }

    /**
     * Enregistre un événement dans l'audit log pour l'utilisateur connecté.
     *
     * @param string               $type        Type d'événement (code interne)
     * @param string|array|null    $description Détails ou contexte (chaîne ou tableau)
     * @param array<string,mixed>|null $before   Données avant modification
     * @param array<string,mixed>|null $after    Données après modification
     */
    protected function auditLog(string $type, string|array|null $description = null, ?array $before = null, ?array $after = null): void
    {
        $userId = $this->session->get('user_id');
        /** @var \App\Models\AuditLogModel $model */
        $model = model(\App\Models\AuditLogModel::class);
        $model->log($type, $userId, $description, $before, $after);
    }

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $this->session = service('session');
        $this->db      = Database::connect();

        $this->loadCurrentUserFromSession();
        $this->shareCommonViewData();
    }

    /**
     * Injecte les données partagées dans le renderer, y compris pour les contrôleurs
     * qui utilisent encore view() directement.
     */
    protected function shareCommonViewData(): void
    {
        $unreadCount = 0;

        if (! empty($this->currentUser['user_id'])) {
            $notificationModel = model(NotificationModel::class);
            $unreadCount = $notificationModel->countUnread((int) $this->currentUser['user_id']);
        }

        $router = service('router');
        $matchedRoute = $router->getMatchedRoute();
        $currentRoute = is_array($matchedRoute) && isset($matchedRoute[0]) ? (string) $matchedRoute[0] : '';

        $renderer = service('renderer');
        $renderer->setVar('currentUser', $this->currentUser);
        $renderer->setVar('unreadCount', $unreadCount);
        $renderer->setVar('currentRoute', $currentRoute);
    }

    protected function loadCurrentUserFromSession(): void
    {
        $userId = $this->session->get('user_id');

        if ($userId === null) {
            $this->currentUser = [];

            return;
        }

        $this->currentUser = [
            'user_id'      => (int) $userId,
            'role'         => (string) ($this->session->get('role') ?? ''),
            'nom_complet'  => (string) ($this->session->get('nom_complet') ?? ''),
            'employe_id'   => $this->session->get('employe_id'),
            'photo_profil' => $this->session->get('photo_profil'),
        ];
    }

    /**
     * Rend une vue et injecte les données partagées.
     *
     * @param array<string,mixed> $data
     */
    protected function renderView(string $view, array $data = [], ?string $layout = null): string
    {
        $notificationModel = model(NotificationModel::class);

        $unreadCount = 0;

        if (! empty($this->currentUser['user_id'])) {
            $unreadCount = $notificationModel->countUnread((int) $this->currentUser['user_id']);
        }

        $router      = service('router');
        $currentRoute = $router->getMatchedRoute()[0] ?? '';

        $data = array_merge([
            'currentUser'  => $this->currentUser,
            'unreadCount'  => $unreadCount,
            'currentRoute' => $currentRoute,
        ], $data);

        $data['content'] ??= view($view, $data);

        $layout ??= $this->resolveLayoutForView($view);

        return view($layout, $data);
    }

    /**
     * Détermine le layout à utiliser selon le type d'écran.
     */
    protected function resolveLayoutForView(string $view): string
    {
        if (str_starts_with($view, 'auth/')) {
            return 'layouts/auth';
        }

        if (str_starts_with($view, 'kiosque/')) {
            return 'layouts/kiosque';
        }

        return 'layouts/main';
    }

    /**
     * Redirige vers le tableau de bord correspondant au rôle.
     */
    protected function redirectAfterLogin(string $role): RedirectResponse
    {
        return match ($role) {
            'admin' => redirect()->to('/admin/dashboard'),
            'agent' => redirect()->to('/agent/dashboard'),
            default => redirect()->to('/employe/dashboard'),
        };
    }

    /**
     * Retourne une réponse JSON typée.
     *
     * @param mixed $data
     */
    protected function jsonResponse(mixed $data, int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setContentType('application/json', 'utf-8')
            ->setJSON($data);
    }
}

