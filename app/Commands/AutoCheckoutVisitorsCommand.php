<?php
declare(strict_types=1);

namespace App\Commands;

use App\Models\VisiteurModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class AutoCheckoutVisitorsCommand extends BaseCommand
{
    protected $group       = 'OCanada';
    protected $name        = 'ocanada:auto-checkout';
    protected $description = 'Auto-checkout visitors still present at end of day (23:59)';
    protected $usage       = 'ocanada:auto-checkout [options]';

    protected $arguments = [];

    protected $options = [
        '--force' => 'Force checkout even during business hours (for testing)',
    ];

    public function run(array $params)
    {
        $visiteurModel = model(VisiteurModel::class);
        $force = in_array('--force', $params);

        try {
            // Get all visitors still present today
            $today = date('Y-m-d');
            $presentToday = $visiteurModel->builder()
                ->whereDate('date_creation', $today)
                ->where('statut', 'present')
                ->get()
                ->getResult('array');

            if (empty($presentToday)) {
                CLI::write('✓ Aucun visiteur à enregistrer comme parti', 'green');
                return;
            }

            $updatedCount = 0;
            foreach ($presentToday as $visitor) {
                $visiteurModel->update($visitor['id'], [
                    'heure_depart' => date('H:i:s'),
                    'statut'       => 'parti',
                    'commentaire'  => 'Auto-clôture fin de journée',
                ]);

                $updatedCount++;

                // Notify
                $notificationService = service('notification');
                $notificationService->notifyAdmins(
                    'VISITEUR_AUTO_CHECKOUT',
                    'Visiteur auto-clôturé',
                    "{$visitor['prenom']} {$visitor['nom']} a été enregistré comme parti (auto-clôture)",
                    '/admin/visitors/' . $visitor['id']
                );
            }

            CLI::write("✓ {$updatedCount} visiteur(s) clôturé(s) avec succès", 'green');
            return 0;

        } catch (\Exception $e) {
            CLI::writeError('✗ Erreur: ' . $e->getMessage());
            return 1;
        }
    }
}
