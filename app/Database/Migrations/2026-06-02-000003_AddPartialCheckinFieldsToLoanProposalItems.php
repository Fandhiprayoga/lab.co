<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPartialCheckinFieldsToLoanProposalItems extends Migration
{
    public function up()
    {
        if (! $this->hasTable('loan_proposal_items')) {
            return;
        }

        $fields = [];

        if (! $this->hasField('loan_proposal_items', 'qty_returned_good')) {
            $fields['qty_returned_good'] = [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
                'after'    => 'qty',
            ];
        }

        if (! $this->hasField('loan_proposal_items', 'qty_returned_damaged')) {
            $fields['qty_returned_damaged'] = [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
                'after'    => 'qty_returned_good',
            ];
        }

        if (! $this->hasField('loan_proposal_items', 'qty_returned_lost')) {
            $fields['qty_returned_lost'] = [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
                'after'    => 'qty_returned_damaged',
            ];
        }

        if (! $this->hasField('loan_proposal_items', 'returned_by_user_id')) {
            $fields['returned_by_user_id'] = [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'qty_returned_lost',
            ];
        }

        if (! $this->hasField('loan_proposal_items', 'return_condition')) {
            $fields['return_condition'] = [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
                'after'      => 'returned_by_user_id',
            ];
        }

        if (! $this->hasField('loan_proposal_items', 'return_note')) {
            $fields['return_note'] = [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'return_condition',
            ];
        }

        if (! $this->hasField('loan_proposal_items', 'maintenance_record_id')) {
            $fields['maintenance_record_id'] = [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'return_note',
            ];
        }

        if (! $this->hasField('loan_proposal_items', 'returned_at')) {
            $fields['returned_at'] = [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'maintenance_record_id',
            ];
        }

        if (! empty($fields)) {
            $this->forge->addColumn('loan_proposal_items', $fields);
        }

        $this->db->query('CREATE INDEX IF NOT EXISTS loan_proposal_items_proposal_item_idx ON loan_proposal_items (proposal_id, item_type)');
    }

    public function down()
    {
        if (! $this->hasTable('loan_proposal_items')) {
            return;
        }

        $drop = [];
        foreach ([
            'qty_returned_good',
            'qty_returned_damaged',
            'qty_returned_lost',
            'returned_by_user_id',
            'return_condition',
            'return_note',
            'maintenance_record_id',
            'returned_at',
        ] as $field) {
            if ($this->hasField('loan_proposal_items', $field)) {
                $drop[] = $field;
            }
        }

        if (! empty($drop)) {
            $this->forge->dropColumn('loan_proposal_items', $drop);
        }

        $this->db->query('DROP INDEX IF EXISTS loan_proposal_items_proposal_item_idx');
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
