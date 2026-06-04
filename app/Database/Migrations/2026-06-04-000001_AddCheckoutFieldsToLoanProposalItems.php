<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCheckoutFieldsToLoanProposalItems extends Migration
{
    public function up()
    {
        if (! $this->hasTable('loan_proposal_items')) {
            return;
        }

        $fields = [];

        if (! $this->hasField('loan_proposal_items', 'checked_out_at')) {
            $fields['checked_out_at'] = [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'note',
            ];
        }

        if (! $this->hasField('loan_proposal_items', 'checkout_condition')) {
            $fields['checkout_condition'] = [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
                'after'      => 'checked_out_at',
            ];
        }

        if (! $this->hasField('loan_proposal_items', 'checkout_by')) {
            $fields['checkout_by'] = [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'checkout_condition',
            ];
        }

        if (! empty($fields)) {
            $this->forge->addColumn('loan_proposal_items', $fields);
        }
    }

    public function down()
    {
        if (! $this->hasTable('loan_proposal_items')) {
            return;
        }

        $drop = [];
        foreach (['checked_out_at', 'checkout_condition', 'checkout_by'] as $field) {
            if ($this->hasField('loan_proposal_items', $field)) {
                $drop[] = $field;
            }
        }

        if (! empty($drop)) {
            $this->forge->dropColumn('loan_proposal_items', $drop);
        }
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
