<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCheckinPhaseToLoanProposals extends Migration
{
    public function up()
    {
        if (! $this->hasTable('loan_proposals')) {
            return;
        }

        $fields = [];

        if (! $this->hasField('loan_proposals', 'checkin_phase')) {
            $fields['checkin_phase'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
                'after'      => 'checkin_at',
            ];
        }

        if (! $this->hasField('loan_proposals', 'checkin_started_at')) {
            $fields['checkin_started_at'] = [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'checkin_phase',
            ];
        }

        if (! empty($fields)) {
            $this->forge->addColumn('loan_proposals', $fields);
        }

        $this->db->query('CREATE INDEX IF NOT EXISTS loan_proposals_checkin_phase_idx ON loan_proposals (checkin_phase)');
    }

    public function down()
    {
        if (! $this->hasTable('loan_proposals')) {
            return;
        }

        $drop = [];
        foreach (['checkin_phase', 'checkin_started_at'] as $field) {
            if ($this->hasField('loan_proposals', $field)) {
                $drop[] = $field;
            }
        }

        if (! empty($drop)) {
            $this->forge->dropColumn('loan_proposals', $drop);
        }

        $this->db->query('DROP INDEX IF EXISTS loan_proposals_checkin_phase_idx');
    }

    private function hasTable(string $table): bool
    {
        $result = $this->db->query('SHOW TABLES LIKE ?', [$table])->getRowArray();

        return ! empty($result);
    }

    private function hasField(string $table, string $field): bool
    {
        if (! $this->hasTable($table)) {
            return false;
        }

        $result = $this->db->query('SHOW COLUMNS FROM `' . $table . '` LIKE ?', [$field])->getRowArray();

        return ! empty($result);
    }
}
