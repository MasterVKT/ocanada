<?php
declare(strict_types=1);

namespace App\Libraries;

/**
 * Limiteur de taux pour les appels API
 */
class RateLimiter
{
    protected int $maxRequestsPerHour = 20;
    protected string $cacheKey = 'anthropic_requests';

    /**
     * Vérifie si une requête peut être faite
     */
    public function canMakeRequest(): bool
    {
        return $this->canMakeRequestFor($this->cacheKey, $this->maxRequestsPerHour, 3600);
    }

    public function canMakeRequestFor(string $bucketKey, int $maxRequests, int $windowSeconds): bool
    {
        $cache = \Config\Services::cache();
        $cacheKey = 'rate_limit_' . md5($bucketKey);

        $requests = $cache->get($cacheKey) ?: [];

        $now = time();
        $requests = array_filter($requests, static fn($timestamp) => is_int($timestamp) && ($now - $timestamp) < $windowSeconds);

        if (count($requests) >= $maxRequests) {
            return false;
        }

        $requests[] = $now;
        $cache->save($cacheKey, $requests, $windowSeconds);

        return true;
    }

    /**
     * Réinitialise le compteur
     */
    public function reset(): void
    {
        $cache = \Config\Services::cache();
        $cache->delete($this->cacheKey);
    }
}