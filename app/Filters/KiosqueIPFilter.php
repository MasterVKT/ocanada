<?php
declare(strict_types=1);

namespace App\Filters;

use App\Models\AuditLogModel;
use App\Models\ConfigSystemeModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class KiosqueIPFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ResponseInterface|string|null
    {
        $ip        = $request->getIPAddress();
        $config    = model(ConfigSystemeModel::class);
        $whitelist = $config->get('ip_kiosque_autorisees', '');

        $allowedIps = array_filter(array_map('trim', explode(',', (string) $whitelist)));

        if ($allowedIps === [] || ! in_array($ip, $allowedIps, true)) {
            $this->maybeLogSuspiciousIp($ip);

            $response = Services::response();

            $html = '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">'
                . '<title>Terminal non habilité</title>'
                . '<meta name="viewport" content="width=device-width, initial-scale=1"></head>'
                . '<body style="background-color:#1A365D;color:#FFFFFF;display:flex;align-items:center;justify-content:center;height:100vh;font-family:Arial, sans-serif;">'
                . '<div style="text-align:center;max-width:480px;padding:24px;">'
                . '<h1 style="font-size:2rem;margin-bottom:1rem;">Terminal non habilité au pointage</h1>'
                . '<p style="font-size:1rem;opacity:0.9;">Ce poste n\'est pas autorisé pour le mode kiosque. Veuillez contacter l\'administrateur.</p>'
                . '</div></body></html>';

            return $response->setStatusCode(403)->setBody($html);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // Aucun traitement après-réponse requis.
    }

    protected function maybeLogSuspiciousIp(string $ip): void
    {
        $cache    = cache();
        $cacheKey = 'kiosque_ip_fail_' . $ip;

        $fails = (int) ($cache->get($cacheKey) ?? 0);
        $fails++;

        $cache->save($cacheKey, $fails, 3600);

        if ($fails === 5) {
            /** @var AuditLogModel $audit */
            $audit = model(AuditLogModel::class);
            $audit->log('ACCES_NON_AUTORISE', null, sprintf('Tentatives répétées d\'accès kiosque depuis IP %s', $ip));
        }
    }
}

