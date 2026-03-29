<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePresencesTableCompat extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('presences')) {
            return;
        }

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
                'type' => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addUniqueKey(['employe_id', 'date_pointage'], 'uk_presences_employe_date');
        $this->forge->addKey('date_pointage');
        $this->forge->addKey('statut');
        $this->forge->addForeignKey('employe_id', 'employes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('corrige_par_utilisateur_id', 'utilisateurs', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('shift_modele_id', 'shifts_modeles', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('presences', true, [
            'ENGINE' => 'InnoDB',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('presences', true);
    }
}
