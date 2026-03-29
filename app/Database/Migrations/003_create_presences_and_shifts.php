<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePresencesAndShifts extends Migration
{
    public function up(): void
    {
        // shifts_modeles
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nom' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'heure_debut' => [
                'type' => 'TIME',
            ],
            'heure_fin' => [
                'type' => 'TIME',
            ],
            'pause_minutes' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 60,
            ],
            'jours_actifs' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'actif' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'date_creation' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'date_modification' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('shifts_modeles', false, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Modèles de shifts',
        ]);

        // affectations_shifts
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'employe_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'shift_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'date_debut' => [
                'type' => 'DATE',
            ],
            'date_fin' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'actif' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'date_creation' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'date_modification' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('employe_id', 'employes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('shift_id', 'shifts_modeles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('affectations_shifts', false, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Affectations de shifts aux employés',
        ]);

        // presences
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'employe_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'date_pointage' => [
                'type' => 'DATE',
            ],
            'heure_pointage' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'heure_sortie' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'statut' => [
                'type'       => 'ENUM',
                'constraint' => ['present', 'retard', 'absent'],
                'default'    => 'absent',
            ],
            'retard_minutes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'corrige' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'motif_correction' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'corrige_par_utilisateur_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'date_correction' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'source' => [
                'type'       => 'ENUM',
                'constraint' => ['kiosque', 'correction_admin'],
                'default'    => 'kiosque',
            ],
            'shift_modele_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'date_creation' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'date_modification' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['employe_id', 'date_pointage'], 'uk_employe_date');
        $this->forge->addKey('date_pointage');
        $this->forge->addKey('statut');
        $this->forge->addForeignKey('employe_id', 'employes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('shift_modele_id', 'shifts_modeles', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('presences', false, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Présences et absences journalières',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('presences', true);
        $this->forge->dropTable('affectations_shifts', true);
        $this->forge->dropTable('shifts_modeles', true);
    }
}

