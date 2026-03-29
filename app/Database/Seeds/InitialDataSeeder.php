<?php
declare(strict_types=1);

namespace App\Database\Seeds;

use App\Models\ConfigSystemeModel;
use App\Models\UtilisateurModel;
use CodeIgniter\Database\Seeder;

class InitialDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedConfigSysteme();
        $this->seedJoursFeries();
        $this->seedDefaultShift();
        $this->seedAdminUser();
    }

    protected function seedConfigSysteme(): void
    {
        /** @var ConfigSystemeModel $config */
        $config = model(ConfigSystemeModel::class);

        $defaults = [
            'ip_kiosque_autorisees'        => '192.168.1.100',
            'heure_debut_pointage_arrivee' => '06:00',
            'heure_fin_pointage_arrivee'   => '10:30',
            'heure_debut_pointage_depart'  => '15:00',
            'heure_fin_pointage_depart'    => '21:00',
            'seuil_alerte_visiteur_heures' => '3',
            'samedi_ouvrable'              => '0',
            'shift_defaut_id'              => '1',
            'departements_liste'           => 'Direction,Comptabilité,Commercial,Logistique',
        ];

        foreach ($defaults as $key => $value) {
            if ($config->find($key) === null) {
                $config->insert([
                    'cle'        => $key,
                    'valeur'     => (string) $value,
                    'description'=> '',
                ]);
            }
        }
    }

    protected function seedJoursFeries(): void
    {
        $db   = $this->db;
        $year = (int) date('Y');

        $joursFeries = [
            ['date' => sprintf('%d-01-01', $year), 'designation' => 'Jour de l\'An'],
            ['date' => sprintf('%d-02-11', $year), 'designation' => 'Fête de la Jeunesse'],
            ['date' => sprintf('%d-05-01', $year), 'designation' => 'Fête du Travail'],
            ['date' => sprintf('%d-05-20', $year), 'designation' => 'Fête Nationale'],
            ['date' => sprintf('%d-08-15', $year), 'designation' => 'Assomption'],
            ['date' => sprintf('%d-12-25', $year), 'designation' => 'Noël'],
        ];

        foreach ($joursFeries as $jf) {
            $db->table('jours_feries')->ignore(true)->insert([
                'date_ferie' => $jf['date'],
                'designation'=> $jf['designation'],
                'type'       => 'fixe',
                'annee'      => $year,
            ]);
        }
    }

    protected function seedDefaultShift(): void
    {
        $db = $this->db;

        $db->table('shifts_modeles')->ignore(true)->insert([
            'id'            => 1,
            'nom'           => 'Journée standard',
            'heure_debut'   => '08:00:00',
            'heure_fin'     => '17:00:00',
            'pause_minutes' => 60,
            'jours_actifs'  => '1,2,3,4,5',
            'actif'         => 1,
        ]);
    }

    protected function seedAdminUser(): void
    {
        /** @var UtilisateurModel $users */
        $users = model(UtilisateurModel::class);

        if ($users->where('role', 'admin')->first() !== null) {
            return;
        }

        $password = getenv('OCANADA_ADMIN_PASSWORD') ?: 'ChangeMe123';
        $email    = getenv('OCANADA_ADMIN_EMAIL') ?: 'admin@ocanada.local';

        $users->insert([
            'email'             => $email,
            'mot_de_passe'      => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'role'              => 'admin',
            'statut'            => 'actif',
            'date_creation'     => date('Y-m-d H:i:s'),
            'derniere_connexion'=> null,
        ]);
    }
}

