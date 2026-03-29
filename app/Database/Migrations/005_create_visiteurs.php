<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVisiteurs extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'numero_badge' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nom_complet' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'type_piece' => [
                'type'       => 'ENUM',
                'constraint' => ['CNI', 'passeport', 'permis', 'autre'],
            ],
            'numero_piece' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'telephone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'entreprise' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'motif_visite' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'motif_detail' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'personne_a_voir_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'service' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'heure_arrivee' => [
                'type' => 'DATETIME',
            ],
            'heure_sortie' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'duree_visite_minutes' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'statut' => [
                'type'       => 'ENUM',
                'constraint' => ['present', 'sorti'],
                'default'    => 'present',
            ],
            'photo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'agent_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('numero_badge');
        $this->forge->addKey('statut');
        $this->forge->addKey('heure_arrivee');
        $this->forge->addForeignKey('personne_a_voir_id', 'employes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('agent_id', 'utilisateurs', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('visiteurs', false, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Visiteurs et badges',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('visiteurs', true);
    }
}

