<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJoursferiesAndConfig extends Migration
{
    public function up(): void
    {
        // jours_feries
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'date_ferie' => [
                'type' => 'DATE',
            ],
            'designation' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['fixe', 'variable'],
                'default'    => 'fixe',
            ],
            'annee' => [
                'type'       => 'YEAR',
                'null'       => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('date_ferie', 'uk_date_ferie');
        $this->forge->createTable('jours_feries', false, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Jours fériés officiels du Cameroun',
        ]);

        // config_systeme
        $this->forge->addField([
            'cle' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'valeur' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('cle', true);
        $this->forge->createTable('config_systeme', false, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Clés de configuration système',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('config_systeme', true);
        $this->forge->dropTable('jours_feries', true);
    }
}

