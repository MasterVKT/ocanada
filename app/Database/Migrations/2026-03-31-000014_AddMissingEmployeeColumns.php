<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingEmployeeColumns extends Migration
{
    public function up(): void
    {
        if (!$this->hasTable('employes')) {
            return;
        }

        // Ajouter les colonnes manquantes à la table employes
        $fields = [
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'after'      => 'prenom',
            ],
        ];

        // Vérifier si la colonne existe déjà avant de l'ajouter
        if (!$this->hasField('employes', 'email')) {
            $this->forge->addColumn('employes', $fields);
        }
    }

    public function down(): void
    {
        if (!$this->hasTable('employes')) {
            return;
        }

        if ($this->hasField('employes', 'email')) {
            $this->forge->dropColumn('employes', 'email');
        }
    }

    private function hasField(string $table, string $field): bool
    {
        $row = $this->db->query(
            'SELECT COUNT(*) AS cnt
             FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND column_name = ?',
            [$table, $field]
        )->getRowArray();

        return (int) ($row['cnt'] ?? 0) > 0;
    }

    private function hasTable(string $table): bool
    {
        $row = $this->db->query(
            'SELECT COUNT(*) AS cnt
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = ?',
            [$table]
        )->getRowArray();

        return (int) ($row['cnt'] ?? 0) > 0;
    }
}
