<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUtilisateurs extends Migration
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
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'mot_de_passe' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'role' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'employe', 'agent'],
            ],
            'statut' => [
                'type'       => 'ENUM',
                'constraint' => ['actif', 'inactif'],
                'default'    => 'actif',
            ],
            'date_creation' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'derniere_connexion' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'token_reinitialisation' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'token_expiration' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');

        $this->forge->createTable('utilisateurs', false, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Comptes utilisateurs (admin, employé, agent)',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('utilisateurs', true);
    }
}

