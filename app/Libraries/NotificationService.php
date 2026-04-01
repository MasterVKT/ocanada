<?php

declare(strict_types=1);

namespace App\Libraries;

use App\Models\NotificationModel;
use CodeIgniter\Email\Email;

/**
 * Service de gestion des notifications
 */
class NotificationService
{
    protected Email $email;
    protected NotificationModel $notificationModel;

    public function __construct()
    {
        $this->email             = service('email');
        $this->notificationModel = model(NotificationModel::class);
    }

    /**
     * Envoie une notification à un utilisateur
     */
    public function notifyUser(int $userId, string $type, string $titre, string $message, ?string $url = null): void
    {
        $messageComplet = trim($titre) !== '' ? ($titre . ' - ' . $message) : $message;

        // Insérer en base
        $this->notificationModel->insert([
            'destinataire_id' => $userId,
            'type'           => $type,
            'message'        => $messageComplet,
            'lien'           => $url,
            'lue'            => 0,
            'date_creation'  => date('Y-m-d H:i:s'),
        ]);

        // TODO: Envoi email si configuré
    }

    /**
     * Envoie une notification à tous les admins
     */
    public function notifyAdmins(string $type, string $titre, string $message, ?string $url = null): void
    {
        $admins = $this->notificationModel->getAdminUsers();

        foreach ($admins as $admin) {
            $this->notifyUser((int) $admin['id'], $type, $titre, $message, $url);
        }
    }

    /**
     * Envoie une notification à un employé spécifique
     */
    public function notifyEmployee(int $employeId, string $type, string $titre, string $message, ?string $url = null): void
    {
        $user = $this->notificationModel->getUserByEmployeId($employeId);

        if ($user) {
            $this->notifyUser((int) $user['id'], $type, $titre, $message, $url);
        }
    }

    /**
     * Notifie un employé de la décision sur sa demande de congé
     */
    public function notifyCongeDecision(array $demande): void
    {
        $employeId = (int) ($demande['employe_id'] ?? 0);
        if ($employeId <= 0) {
            return;
        }

        $user = $this->notificationModel->getUserByEmployeId($employeId);

        if (!$user) {
            return;
        }

        $status = (string) ($demande['statut'] ?? '');
        $isApproved = in_array($status, ['approuvee', 'approuve'], true);
        $statut = $isApproved ? 'approuve' : 'refuse';
        $message = "Votre congé du {$demande['date_debut']} au {$demande['date_fin']} a été {$statut}.";
        $rejectionReason = $demande['refus_motif'] ?? $demande['commentaire_admin'] ?? null;
        if (!$isApproved && !empty($rejectionReason)) {
            $message .= " Motif : {$rejectionReason}";
        }

        $type = $isApproved ? 'NOTIF_CONGE_APPROUVE' : 'NOTIF_CONGE_REFUSE';

        $this->notifyUser((int) $user['id'], $type, 'Demande de congé traitée', $message, '/employe/leaves');
    }

    /**
     * Marque une notification comme lue
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        return $this->notificationModel->markAsRead($notificationId, $userId);
    }

    /**
     * Récupère les notifications non lues d'un utilisateur
     */
    public function getUnreadNotifications(int $userId): array
    {
        return $this->notificationModel->getUnreadForUser($userId);
    }
}
