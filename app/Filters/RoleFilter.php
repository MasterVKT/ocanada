<?php
declare(strict_types=1);

namespace App\Filters;

use App\Models\AuditLogModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ResponseInterface|string|null
    {
        $session = Services::session();
        $role    = $session->get('role');

        $requiredRole = is_array($arguments) && isset($arguments[0]) ? (string) $arguments[0] : null;

        if ($requiredRole === null) {
            return null;
        }

        if ($role !== $requiredRole) {
            /** @var AuditLogModel $audit */
            $audit = model(AuditLogModel::class);
            $audit->log('ACCES_NON_AUTORISE', $session->get('user_id'), sprintf('Rôle requis: %s, rôle courant: %s', $requiredRole, (string) $role));

            return service('response')
                ->setStatusCode(403)
                ->setBody(view('errors/403'));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // Aucun traitement après-réponse requis.
    }
}

