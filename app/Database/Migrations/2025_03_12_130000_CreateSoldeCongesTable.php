<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSoldeCongesTable extends Migration
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
            'annee' => [
                'type' => 'YEAR',
                'comment' => 'Year for which the balance is calculated',
            ],
            'solde_initial' => [
                'type' => 'DECIMAL',
                'precision' => 6,
                'scale' => 2,
                'comment' => 'Initial balance based on OHADA rules (ancienneté, prorata)',
            ],
            'solde_utilise' => [
                'type' => 'DECIMAL',
                'precision' => 6,
                'scale' => 2,
                'default' => 0,
                'comment' => 'Sum of approved leave days',
            ],
            'solde_restant' => [
                'type' => 'DECIMAL',
                'precision' => 6,
                'scale' => 2,
                'default' => 0,
                'comment' => 'solde_initial - solde_utilise',
            ],
            'congd_maternite_utilise' => [
                'type' => 'DECIMAL',
                'precision' => 6,
                'scale' => 2,
                'default' => 0,
                'comment' => 'Maternity leave days (not deducted from balance)',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Admin notes on balance adjustments',
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
        $this->forge->addUniqueKey(['employe_id', 'annee'], 'uk_employe_annee');
        $this->forge->addForeignKey('employe_id', 'employes', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('solde_conges');
    }

    public function down()
    {
        $this->forge->dropTable('solde_conges');
    }
}
