<?php
declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Commands;
use App\Models\VisiteurModel;

class CloseVisitsCommand extends BaseCommand
{
    protected $group       = 'Visitor';
    protected $name        = 'ocanada:close-visits';
    protected $description = 'Close long-pending visitor visits (runs daily at 23:59)';
    protected $usage       = 'ocanada:close-visits [hours]';
    protected $arguments   = [
        'hours' => 'Hours threshold for auto-close (default: 8)',
    ];

    protected VisiteurModel $visiteurModel;

    public function __construct(\Psr\Log\LoggerInterface $logger, Commands $commands)
    {
        parent::__construct($logger, $commands);
        $this->visiteurModel = model(VisiteurModel::class);
    }

    public function run(array $params = [])
    {
        $maxHours = (int) ($params[0] ?? 8);

        // Get all open visits that exceed max hours
        $db = db_connect();
        $openVisits = $db->table('visiteurs')
            ->where('statut', 'present')
            ->where("TIMESTAMPDIFF(HOUR, DATE_ADD(heure_arrivee, INTERVAL TIME_TO_SEC(DATE(date_creation)) SECOND), NOW()) >= ", $maxHours)
            ->get()
            ->getResultArray();

        if (empty($openVisits)) {
            CLI::write('✓ No long-pending visits found', 'green');
            return;
        }

        $closed = 0;

        foreach ($openVisits as $visit) {
            // Close the visit
            $db->table('visiteurs')->update(
                [
                    'heure_depart'      => date('H:i:s'),
                    'statut'            => 'departi',
                    'date_modification' => date('Y-m-d H:i:s'),
                ],
                ['id' => $visit['id']]
            );

            $closed++;

            // Log activity
            log_activity(
                'VISITE_AUTO_FERMEE',
                "Visite de {$visit['prenom']} {$visit['nom']} automatiquement fermée après {$maxHours}h",
                'visiteurs',
                $visit['id'],
                null,
                'CRON'
            );
        }

        CLI::write("✓ Closed {$closed} long-pending visitor visit(s)", 'green');

        // Notify admins
        $notificationService = service('notification');
        $notificationService->notifyAdmins(
            'VISITES_FERMEES',
            'Visites automatiquement fermées',
            "{$closed} visite(s) fermée(s) automatiquement (seuil: {$maxHours}h)",
            '/visitor/history'
        );
    }
}