<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsAndDocuments extends Migration
{
    public function up(): void
    {
        // notifications
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'destinataire_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'lien' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'lue' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'date_creation' => [
                'type' => 'DATETIME',
            ],
            'date_lecture' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['destinataire_id', 'lue'], false, false, 'idx_dest_lue');
        $this->forge->addForeignKey('destinataire_id', 'utilisateurs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('notifications', false, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Notifications internes',
        ]);

        // documents_rh
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
            'type_document' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'titre' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'date_document' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'chemin_fichier' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'nom_original' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'taille_octets' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'uploade_par' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'date_upload' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('employe_id', 'employes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('uploade_par', 'utilisateurs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('documents_rh', false, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Documents RH sécurisés',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('documents_rh', true);
        $this->forge->dropTable('notifications', true);
    }
}

