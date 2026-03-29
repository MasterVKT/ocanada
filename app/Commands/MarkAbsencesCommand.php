<?php
declare(strict_types=1);

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Commands;
use App\Models\EmployeModel;
use App\Models\PresenceModel;

class MarkAbsencesCommand extends BaseCommand
{
    protected $group       = 'Presence';
    protected $name        = 'ocanada:mark-absences';
    protected $description = 'Mark employees without clock-in as absent (runs daily at 18:00)';
    protected $usage       = 'ocanada:mark-absences [date]';
    protected $arguments   = [
        'date' => 'Date to mark absences for (default: today)',
    ];

    protected EmployeModel $employeModel;
    protected PresenceModel $presenceModel;

    public function __construct(\Psr\Log\LoggerInterface $logger, Commands $commands)
    {
        parent::__construct($logger, $commands);
        $this->employeModel   = model(EmployeModel::class);
        $this->presenceModel  = model(PresenceModel::class);
    }

    public function run(array $params = [])
    {
        $date = $params[0] ?? date('Y-m-d');

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            CLI::error('Invalid date format. Use YYYY-MM-DD');
            return;
        }

        // Check if date is a weekend (Saturday = 6, Sunday = 7)
        $dayOfWeek = (int) date('N', strtotime($date));
        if ($dayOfWeek >= 6) {
            CLI::line("Date {$date} is a weekend. Skipping...");
            return;
        }

        // Get all active employees
        $activeEmployees = $this->employeModel
            ->where('statut', 'actif')
            ->findAll();

        if (empty($activeEmployees)) {
            CLI::warn('No active employees found');
            return;
        }

        $db = db_connect();
        $marked = 0;

        foreach ($activeEmployees as $employe) {
            // Check if employee has any presence record for this date
            $existingPresence = $db->table('presences')
                ->where('employe_id', $employe['id'])
                ->where('DATE(date_pointage)', $date)
                ->get()
                ->getFirstRow('array');

            if (!$existingPresence) {
                // Insert absent record
                $db->table('presences')->insert([
                    'employe_id'       => $employe['id'],
                    'date_pointage'    => $date,
                    'heure_pointage'   => null,
                    'heure_sortie'     => null,
                    'statut'           => 'absent',
                    'retard_minutes'   => 0,
                    'corrige'          => false,
                    'motif_correction' => null,
                    'corrige_par_utilisateur_id' => null,
                    'date_correction'  => null,
                    'source'           => 'correction_admin',
                    'shift_modele_id'  => null,
                    'date_creation'    => date('Y-m-d H:i:s'),
                ]);

                $marked++;

                // Log activity
                log_activity(
                    'ABSENCE_AUTO_MARQUEE',
                    "Absence automatiquement marquée pour {$employe['prenom']} {$employe['nom']} le {$date}",
                    'presences',
                    null,
                    null,
                    'CRON'
                );
            }
        }

        CLI::write("✓ Marked {$marked} employees as absent for {$date}", 'green');

        // Notify admins
        $notificationService = service('notification');
        $notificationService->notifyAdmins(
            'ABSENCES_MARQUEES',
            'Absences automatiquement marquées',
            "{$marked} employé(s) marqué(s) absent(s) pour {$date}",
            '/admin/presences/index?date=' . $date
        );
    }
}