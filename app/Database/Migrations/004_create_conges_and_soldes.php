<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCongesAndSoldes extends Migration
{
    public function up(): void
    {
        // demandes_conge
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
            'type_conge' => [
                'type'       => 'ENUM',
                'constraint' => ['annuel', 'maladie', 'maternite_paternite', 'sans_solde', 'autre'],
            ],
            'type_detail' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'date_debut' => [
                'type' => 'DATE',
            ],
            'date_fin' => [
                'type' => 'DATE',
            ],
            'jours_ouvrables' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'motif' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'statut' => [
                'type'       => 'ENUM',
                'constraint' => ['en_attente', 'approuvee', 'refusee', 'annulee'],
                'default'    => 'en_attente',
            ],
            'date_soumission' => [
                'type' => 'DATETIME',
            ],
            'date_traitement' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'traite_par' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'commentaire_admin' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('statut');
        $this->forge->addForeignKey('employe_id', 'employes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('traite_par', 'utilisateurs', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('demandes_conge', false, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Demandes de congé',
        ]);

        // soldes_conges
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
                'type'       => 'YEAR',
                'null'       => false,
            ],
            'jours_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,1',
            ],
            'jours_pris' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,1',
                'default'    => 0,
            ],
            'jours_restants' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,1',
            ],
            'date_mise_a_jour' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['employe_id', 'annee'], 'uk_employe_annee');
        $this->forge->addForeignKey('employe_id', 'employes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('soldes_conges', false, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Soldes de congés annuels',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('soldes_conges', true);
        $this->forge->dropTable('demandes_conge', true);
    }
}

