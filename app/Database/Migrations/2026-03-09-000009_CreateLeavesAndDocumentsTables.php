<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLeavesAndDocumentsTables extends Migration
{
    public function up(): void
    {
        // Demandes de congés
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
            'type_conge' => [
                'type'       => 'ENUM',
                'constraint' => ['annuel', 'maladie', 'autre'],
            ],
            'date_debut' => [
                'type' => 'DATE',
            ],
            'date_fin' => [
                'type' => 'DATE',
            ],
            'nombre_jours' => [
                'type'     => 'DECIMAL',
                'constraint' => '5,2',
            ],
            'motif' => [
                'type'    => 'VARCHAR',
                'constraint' => 255,
                'null'    => true,
            ],
            'statut' => [
                'type'       => 'ENUM',
                'constraint' => ['en_attente', 'approuve', 'refuse', 'annule'],
                'default'    => 'en_attente',
            ],
            'approuve_par' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'refus_motif' => [
                'type'    => 'VARCHAR',
                'constraint' => 255,
                'null'    => true,
            ],
            'commentaire' => [
                'type'    => 'VARCHAR',
                'constraint' => 255,
                'null'    => true,
            ],
            'date_demande' => [
                'type' => 'DATETIME',
            ],
            'date_approbation' => [
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
        $this->forge->addForeignKey('approuve_par', 'utilisateurs', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addKey(['employe_id', 'statut']);
        $this->forge->addKey(['statut', 'date_demande']);
        $this->forge->createTable('conge_demandes', true);

        // Documents RH
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'employe_id' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'titre' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'fichier' => [
                'type'    => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'uploadé_par' => [
                'type' => 'INT',
                'unsigned' => true,
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
        $this->forge->addForeignKey('employe_id', 'employes', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('uploadé_par', 'utilisateurs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['employe_id', 'type']);
        $this->forge->createTable('documents_rh', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('documents_rh', true);
        $this->forge->dropTable('conge_demandes', true);
    }
}