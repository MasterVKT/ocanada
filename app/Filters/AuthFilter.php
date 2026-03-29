<?php
declare(strict_types=1);

namespace App\Filters;

use App\Models\AuditLogModel;
use App\Models\UtilisateurModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ResponseInterface|string|null
    {
        $session = Services::session();

        $userId = $session->get('user_id');
        $role   = $session->get('role');

        if ($userId === null || $role === null) {
            return redirect()->to('/login');
        }

        /** @var UtilisateurModel $userModel */
        $userModel = model(UtilisateurModel::class);
        $user      = $userModel->find((int) $userId);

        if ($user === null || $user['statut'] !== 'actif') {
            $session->destroy();

            /** @var AuditLogModel $audit */
            $audit = model(AuditLogModel::class);
            $audit->log('ACCES_NON_AUTORISE', (int) $userId, 'Compte inactif ou inexistant lors de la vérification AuthFilter');

            return redirect()->to('/login');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // Aucun traitement après-réponse requis.
    }
}

