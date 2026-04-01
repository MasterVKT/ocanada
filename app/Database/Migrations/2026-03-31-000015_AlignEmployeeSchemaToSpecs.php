<?php

declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlignEmployeeSchemaToSpecs extends Migration
{
    public function up(): void
    {
        if (! $this->hasTable('employes')) {
            return;
        }

        $fields = [];

        if (! $this->hasField('employes', 'telephone_1')) {
            $fields['telephone_1'] = [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ];
        }

        if (! $this->hasField('employes', 'telephone_2')) {
            $fields['telephone_2'] = [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
            ];
        }

        if (! $this->hasField('employes', 'genre')) {
            $fields['genre'] = [
                'type' => 'ENUM',
                'constraint' => ['homme', 'femme'],
                'null' => true,
            ];
        }

        if (! $this->hasField('employes', 'nationalite')) {
            $fields['nationalite'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ];
        }

        if (! $this->hasField('employes', 'numero_cni')) {
            $fields['numero_cni'] = [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ];
        }

        if (! $this->hasField('employes', 'type_contrat')) {
            $fields['type_contrat'] = [
                'type' => 'ENUM',
                'constraint' => ['CDI', 'CDD', 'stage', 'consultant'],
                'null' => true,
            ];
        }

        if (! $this->hasField('employes', 'date_fin_contrat')) {
            $fields['date_fin_contrat'] = [
                'type' => 'DATE',
                'null' => true,
            ];
        }

        if (! $this->hasField('employes', 'salaire_journalier')) {
            $fields['salaire_journalier'] = [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ];
        }

        if (! $this->hasField('employes', 'salaire_base')) {
            $fields['salaire_base'] = [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
            ];
        }

        if (! $this->hasField('employes', 'heure_debut_travail')) {
            $fields['heure_debut_travail'] = [
                'type' => 'TIME',
                'null' => true,
            ];
        }

        if (! $this->hasField('employes', 'heure_fin_travail')) {
            $fields['heure_fin_travail'] = [
                'type' => 'TIME',
                'null' => true,
            ];
        }

        if (! $this->hasField('employes', 'date_desactivation')) {
            $fields['date_desactivation'] = [
                'type' => 'DATE',
                'null' => true,
            ];
        }

        if (! $this->hasField('employes', 'date_creation')) {
            $fields['date_creation'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (! $this->hasField('employes', 'date_modification')) {
            $fields['date_modification'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('employes', $fields);
        }

        // Backfill for compatibility between legacy and spec salary columns.
        if ($this->hasField('employes', 'salaire_base') && $this->hasField('employes', 'salaire_journalier')) {
            $this->db->query("UPDATE employes SET salaire_journalier = ROUND(salaire_base / 22, 2) WHERE salaire_journalier IS NULL AND salaire_base IS NOT NULL");
            $this->db->query("UPDATE employes SET salaire_base = ROUND(salaire_journalier * 22, 2) WHERE salaire_base IS NULL AND salaire_journalier IS NOT NULL");
        }

        // Backfill principal phone from legacy telephone column.
        if ($this->hasField('employes', 'telephone') && $this->hasField('employes', 'telephone_1')) {
            $this->db->query("UPDATE employes SET telephone_1 = telephone WHERE (telephone_1 IS NULL OR telephone_1 = '') AND telephone IS NOT NULL AND telephone != ''");
        }

        // Sensible defaults on schedules when available.
        if ($this->hasField('employes', 'heure_debut_travail')) {
            $this->db->query("UPDATE employes SET heure_debut_travail = '08:00:00' WHERE heure_debut_travail IS NULL");
        }
        if ($this->hasField('employes', 'heure_fin_travail')) {
            $this->db->query("UPDATE employes SET heure_fin_travail = '17:00:00' WHERE heure_fin_travail IS NULL");
        }
    }

    public function down(): void
    {
        // Non-destructive rollback by design (safe migration for mixed schemas).
    }

    private function hasTable(string $table): bool
    {
        $row = $this->db->query(
            'SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table]
        )->getRowArray();

        return isset($row['cnt']) && (int) $row['cnt'] > 0;
    }

    private function hasField(string $table, string $field): bool
    {
        $row = $this->db->query(
            'SELECT COUNT(*) AS cnt FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $field]
        )->getRowArray();

        return isset($row['cnt']) && (int) $row['cnt'] > 0;
    }
}
