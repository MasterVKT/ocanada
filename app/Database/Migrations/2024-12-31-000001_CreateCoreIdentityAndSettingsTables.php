<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCoreIdentityAndSettingsTables extends Migration
{
    public function up(): void
    {
        // utilisateurs
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
                'default'    => 'employe',
            ],
            'statut' => [
                'type'       => 'ENUM',
                'constraint' => ['actif', 'inactif'],
                'default'    => 'actif',
            ],
            'employe_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'reset_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'reset_expires_at' => [
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
            'date_creation' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'derniere_connexion' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addKey('role');
        $this->forge->createTable('utilisateurs', true, ['ENGINE' => 'InnoDB']);

        // employes
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
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'telephone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'date_naissance' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'date_embauche' => [
                'type' => 'DATE',
                'null' => true,
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
            'salaire_base' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => true,
            ],
            'pin_kiosque' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'photo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'adresse' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ville' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'code_postal' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'pays' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
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
            'date_modification' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('matricule');
        $this->forge->addUniqueKey('email');
        $this->forge->addForeignKey('utilisateur_id', 'utilisateurs', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('employes', true, ['ENGINE' => 'InnoDB']);

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
        $this->forge->addKey(['destinataire_id', 'lue']);
        $this->forge->addForeignKey('destinataire_id', 'utilisateurs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('notifications', true, ['ENGINE' => 'InnoDB']);

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
                'type' => 'YEAR',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('date_ferie');
        $this->forge->createTable('jours_feries', true, ['ENGINE' => 'InnoDB']);

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
        $this->forge->createTable('config_systeme', true, ['ENGINE' => 'InnoDB']);

        // audit_log
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'utilisateur_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'details' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ip_adresse' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'date_creation' => [
                'type' => 'DATETIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('utilisateur_id');
        $this->forge->addForeignKey('utilisateur_id', 'utilisateurs', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('audit_log', true, ['ENGINE' => 'InnoDB']);
    }

    public function down(): void
    {
        $this->forge->dropTable('audit_log', true);
        $this->forge->dropTable('config_systeme', true);
        $this->forge->dropTable('jours_feries', true);
        $this->forge->dropTable('notifications', true);
        $this->forge->dropTable('employes', true);
        $this->forge->dropTable('utilisateurs', true);
    }
}
