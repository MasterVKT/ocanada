<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLegacyLeaveCompatStructures extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('soldes_conges')) {
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
                'annee' => [
                    'type' => 'YEAR',
                ],
                'solde_annuel' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,1',
                    'default'    => 0,
                ],
                'pris' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,1',
                    'default'    => 0,
                ],
                'restant' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,1',
                    'default'    => 0,
                ],
                'reporte' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,1',
                    'default'    => 0,
                ],
                'maladie_pris' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,1',
                    'default'    => 0,
                ],
                'maladie_restant' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '5,1',
                    'default'    => 30,
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
            $this->forge->addUniqueKey(['employe_id', 'annee'], 'uk_soldes_employe_annee');
            $this->forge->addForeignKey('employe_id', 'employes', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('soldes_conges', true, ['ENGINE' => 'InnoDB']);
        }

        if (! $this->db->tableExists('demandes_conge') && $this->db->tableExists('conge_demandes')) {
            $this->db->query('CREATE VIEW demandes_conge AS SELECT * FROM conge_demandes');
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('demandes_conge')) {
            $this->db->query('DROP VIEW IF EXISTS demandes_conge');
        }

        $this->forge->dropTable('soldes_conges', true);
    }
}
