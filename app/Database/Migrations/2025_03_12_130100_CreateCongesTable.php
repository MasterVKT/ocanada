<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCongesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'employe_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
            ],
            'type_conge' => [
                'type' => 'ENUM',
                'constraint' => ['conge_annuel', 'conge_maternite', 'conge_paternite', 'conge_maladie', 'autre'],
                'default' => 'conge_annuel',
                'comment' => 'Type of leave request',
            ],
            'date_debut' => [
                'type' => 'DATE',
                'comment' => 'Start date of leave',
            ],
            'date_fin' => [
                'type' => 'DATE',
                'comment' => 'End date of leave',
            ],
            'nombre_jours' => [
                'type' => 'DECIMAL',
                'precision' => 6,
                'scale' => 2,
                'comment' => 'Number of working days calculated by system',
            ],
            'motif' => [
                'type' => 'TEXT',
                'comment' => 'Reason for leave request',
            ],
            'statut' => [
                'type' => 'ENUM',
                'constraint' => ['en_attente', 'approuve', 'refuse', 'annule'],
                'default' => 'en_attente',
                'comment' => 'Status of leave request',
            ],
            'approuve_par_id' => [
                'type' => 'INT',
                'constraint' => 10,
                'unsigned' => true,
                'null' => true,
                'comment' => 'Admin user who approved/rejected',
            ],
            'date_approbation' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Date when approved/rejected',
            ],
            'motif_refus' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Reason for rejection',
            ],
            'date_creation' => [
                'type' => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'date_modification' => [
                'type' => 'DATETIME',
                'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
                'on_update' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('employe_id');
        $this->forge->addKey('statut');
        $this->forge->addKey(['date_debut', 'date_fin']);
        $this->forge->addForeignKey('employe_id', 'employes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approuve_par_id', 'utilisateurs', 'id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('conges');
    }

    public function down()
    {
        $this->forge->dropTable('conges');
    }
}
