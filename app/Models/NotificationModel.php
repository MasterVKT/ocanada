<?php
declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table          = 'notifications';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;

    protected $allowedFields  = [
        'destinataire_id',
        'type',
        'message',
        'lien',
        'lue',
        'date_creation',
        'date_lecture',
    ];

    protected $validationRules = [
        'destinataire_id' => 'required|is_natural_no_zero',
        'type'            => 'required|string|max_length[50]',
        'message'         => 'required|string',
        'lue'             => 'in_list[0,1]',
    ];

    public function countUnread(int $userId): int
    {
        return $this->where('destinataire_id', $userId)
            ->where('lue', 0)
            ->countAllResults();
    }

    /**
     * Retourne les notifications non lues pour un utilisateur.
     */
    public function getUnreadForUser(int $userId): array
    {
        return $this->where('destinataire_id', $userId)
            ->where('lue', 0)
            ->orderBy('date_creation', 'DESC')
            ->findAll();
    }

    /**
     * Marque une notification comme lue si elle appartient à l'utilisateur.
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notif = $this->where('id', $notificationId)
            ->where('destinataire_id', $userId)
            ->first();

        if (!$notif) {
            return false;
        }

        return (bool) $this->update($notificationId, [
            'lue' => 1,
            'date_lecture' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Récupère tous les utilisateurs ayant le rôle admin.
     */
    public function getAdminUsers(): array
    {
        $db = db_connect();
        return $db->table('utilisateurs')
            ->select('id')
            ->where('role', 'admin')
            ->get()
            ->getResultArray();
    }

    /**
     * Retourne l'utilisateur correspondant à un employé donné.
     */
    public function getUserByEmployeId(int $employeId): ?array
    {
        $db = db_connect();

        return $db->table('utilisateurs')
            ->select('id')
            ->where('employe_id', $employeId)
            ->get()
            ->getRowArray();
    }
}

