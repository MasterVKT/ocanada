<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLog extends Migration
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
            'utilisateur_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'type_evenement' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'donnees_avant' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'donnees_apres' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'ip_adresse' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'date_evenement' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('type_evenement');
        $this->forge->addKey('utilisateur_id');
        $this->forge->addForeignKey('utilisateur_id', 'utilisateurs', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('audit_log', false, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Journal d\'audit immuable',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('audit_log', true);
    }
}

