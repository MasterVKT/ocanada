<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVisitorsTables extends Migration
{
    public function up(): void
    {
        // Visiteurs
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'nom' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'prenom' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'email' => [
                'type'    => 'VARCHAR',
                'constraint' => 100,
                'null'    => true,
            ],
            'telephone' => [
                'type'    => 'VARCHAR',
                'constraint' => 20,
                'null'    => true,
            ],
            'entreprise' => [
                'type'    => 'VARCHAR',
                'constraint' => 100,
                'null'    => true,
            ],
            'motif' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'personne_a_voir' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'heure_arrivee' => [
                'type' => 'TIME',
            ],
            'heure_depart' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'badge_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'statut' => [
                'type'       => 'ENUM',
                'constraint' => ['present', 'departi'],
                'default'    => 'present',
            ],
            'commentaire' => [
                'type'    => 'VARCHAR',
                'constraint' => 255,
                'null'    => true,
            ],
            'date_creation' => [
                'type' => 'DATETIME',
            ],
            'date_modification' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['statut', 'date_creation']);
        $this->forge->createTable('visiteurs', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('visiteurs', true);
    }
}