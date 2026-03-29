<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class AuditLogModel extends Model
{
    protected $table          = 'audit_log';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;

    protected $allowedFields  = [
        'utilisateur_id',
        'type_evenement',
        'description',
        'donnees_avant',
        'donnees_apres',
        'ip_adresse',
        'date_evenement',
    ];

    protected $validationRules = [
        'type_evenement' => 'required|string|max_length[100]',
    ];

    /**
     * Enregistre un événement d'audit immuable.
     *
     * @param string               $type
     * @param int|null             $userId
     * @param string|array|null    $description
     * @param array<string,mixed>|null $before
     * @param array<string,mixed>|null $after
     */
    public function log(string $type, ?int $userId, string|array|null $description = null, ?array $before = null, ?array $after = null): void
    {
        $request = service('request');

        $this->insert([
            'utilisateur_id' => $userId,
            'type_evenement' => $type,
            'description'    => is_array($description) ? json_encode($description, JSON_THROW_ON_ERROR) : ($description ?? ''),
            'donnees_avant'  => $before ? json_encode($before, JSON_THROW_ON_ERROR) : null,
            'donnees_apres'  => $after ? json_encode($after, JSON_THROW_ON_ERROR) : null,
            'ip_adresse'     => $request->getIPAddress(),
            'date_evenement' => date('Y-m-d H:i:s'),
        ]);
    }

    // Intentionnellement aucune méthode update/delete n'est exposée.
}

