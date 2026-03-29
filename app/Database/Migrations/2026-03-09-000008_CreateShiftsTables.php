<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateShiftsTables extends Migration
{
    public function up(): void
    {
        // Modèles de shifts
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
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
                'default'    => 60,
            ],
            'jours_actifs' => [
                'type'    => 'VARCHAR',
                'constraint' => 50,
                'comment' => '1,2,3,4,5 pour lun-ven',
            ],
            'actif' => [
                'type'       => 'BOOLEAN',
                'default'    => true,
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
        $this->forge->createTable('shifts_modeles', true);

        // Affectations de shifts
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'employe_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'shift_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'date_debut' => [
                'type' => 'DATE',
            ],
            'date_fin' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'actif' => [
                'type'    => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addKey(['employe_id', 'date_debut', 'actif']);
        $this->forge->createTable('affectations_shifts', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('affectations_shifts', true);
        $this->forge->dropTable('shifts_modeles', true);
    }
}