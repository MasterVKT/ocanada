<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CongeModel;
use App\Models\NotificationModel;
use App\Models\AuditLogModel;

class PendingLeavesCommand extends BaseCommand
{
    protected $group       = 'Leaves';
    protected $name        = 'ocanada:pending-leaves';
    protected $description = 'Notify admins about leave requests pending for >48 hours. Run daily via cron.';
    protected $usage       = 'ocanada:pending-leaves';

    /**
     * Run the command
     */
    public function run(array $params)
    {
        $congeModel = new CongeModel();
        $notificationModel = new NotificationModel();
        $auditLog = new AuditLogModel();

        CLI::write("Checking pending leave requests...", 'green');

        try {
            // Get requests pending for more than 48 hours
            $pendingRequests = $congeModel->getPendingSinceHours(48);

            if (empty($pendingRequests)) {
                CLI::write("✓ No pending requests older than 48 hours", 'green');
                return;
            }

            CLI::write("Found " . count($pendingRequests) . " request(s) pending > 48h", 'yellow');

            $notificationCount = 0;

            // Notify admins for each pending request
            foreach ($pendingRequests as $request) {
                // Create notification for all admins
                $notif_data = [
                    'titre' => 'Demande de congé en attente',
                    'message' => $request['prenom'] . ' ' . $request['nom'] . 
                                ' attend l\'approbation de son congé depuis plus de 48h',
                    'type' => 'ATTENTION_DEMANDE_CONGE',
                    'lien' => '/admin/leaves/' . $request['id'],
                    'priorite' => 'haute',
                ];

                // Notify for all admins (target_user_id = null means all admins)
                $notificationModel->insert([
                    'utilisateur_id' => null,  // Broadcast to all admins
                    'type' => 'ATTENTION_DEMANDE_CONGE',
                    'titre' => $notif_data['titre'],
                    'message' => $notif_data['message'],
                    'lien' => $notif_data['lien'],
                    'priorite' => $notif_data['priorite'],
                    'lu' => false,
                ]);

                $notificationCount++;
                CLI::write("  ✓ Notification sent for request #" . $request['id'], 'green');
            }

            // Log the command execution
            $auditLog->log(
                0, // System user
                'CRON_PENDING_LEAVES',
                'Vérification et notification des demandes de congé en attente',
                [
                    'pending_requests_found' => count($pendingRequests),
                    'notifications_sent' => $notificationCount,
                ]
            );

            CLI::write("✓ Command completed: {$notificationCount} notification(s) sent", 'green');

        } catch (\Exception $e) {
            CLI::error("Error checking pending leaves: " . $e->getMessage());
            return;
        }
    }
}
