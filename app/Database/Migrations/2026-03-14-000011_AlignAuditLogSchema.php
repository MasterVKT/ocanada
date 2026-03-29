<?php
declare(strict_types=1);

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlignAuditLogSchema extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('audit_log')) {
            return;
        }

        $this->forge->addColumn('audit_log', [
            'type_evenement' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'donnees_avant' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'donnees_apres' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'date_evenement' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // Optional index used by log queries and filters.
        $this->db->query('CREATE INDEX idx_audit_type_evenement ON audit_log(type_evenement)');
    }

    public function down(): void
    {
        if (! $this->db->tableExists('audit_log')) {
            return;
        }

        $this->forge->dropColumn('audit_log', [
            'type_evenement',
            'description',
            'donnees_avant',
            'donnees_apres',
            'date_evenement',
        ]);

        $this->db->query('DROP INDEX idx_audit_type_evenement ON audit_log');
    }
}
