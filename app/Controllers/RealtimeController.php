<?php
declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class RealtimeController extends BaseController
{
    public function index(): string|ResponseInterface
    {
        $role = strtolower((string) ($this->currentUser['role'] ?? ''));

        if (!in_array($role, ['admin', 'agent'], true)) {
            return $this->response->setStatusCode(403)->setBody(view('errors/403'));
        }

        $titles = [
            'admin' => 'Vue temps reel',
            'agent' => 'Vue temps reel - Accueil',
        ];

        return $this->renderView('shared/realtime', [
            'title' => $titles[$role] ?? 'Vue temps reel',
        ]);
    }
}