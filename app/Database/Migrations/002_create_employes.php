<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmployes extends Migration
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
            ],
            'matricule' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'nom' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'prenom' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'date_naissance' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'genre' => [
                'type'       => 'ENUM',
                'constraint' => ['homme', 'femme'],
                'null'       => true,
            ],
            'nationalite' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'numero_cni' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'adresse' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'telephone_1' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'telephone_2' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'photo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'poste' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'departement' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'type_contrat' => [
                'type'       => 'ENUM',
                'constraint' => ['CDI', 'CDD', 'stage', 'consultant'],
                'null'       => true,
            ],
            'date_embauche' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'date_fin_contrat' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'salaire_journalier' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'heure_debut_travail' => [
                'type'    => 'TIME',
                'null'    => false,
                'default' => '08:00:00',
            ],
            'heure_fin_travail' => [
                'type'    => 'TIME',
                'null'    => false,
                'default' => '17:00:00',
            ],
            'pin_kiosque' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'statut' => [
                'type'       => 'ENUM',
                'constraint' => ['actif', 'inactif'],
                'default'    => 'actif',
            ],
            'date_desactivation' => [
                'type' => 'DATE',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('utilisateur_id');
        $this->forge->addUniqueKey('matricule');
        $this->forge->addKey('departement');
        $this->forge->addKey('statut');

        $this->forge->addForeignKey('utilisateur_id', 'utilisateurs', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('employes', false, [
            'ENGINE'  => 'InnoDB',
            'COMMENT' => 'Fiches employés',
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('employes', true);
    }
}

