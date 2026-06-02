<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoanProposalItemAssignmentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'public_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
            ],
            'proposal_item_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'asset_item_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'assigned_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'assigned_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'checkout_condition' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'checkout_note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'return_condition' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'return_note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'returned_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'maintenance_record_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('public_id');
        $this->forge->addKey('proposal_item_id');
        $this->forge->addKey('asset_item_id');
        $this->forge->addKey('assigned_at');
        $this->forge->addKey('returned_at');
        $this->forge->addForeignKey('proposal_item_id', 'loan_proposal_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('asset_item_id', 'asset_items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('maintenance_record_id', 'asset_maintenances', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('loan_proposal_item_assignments', true);

        try {
            $this->db->query('CREATE UNIQUE INDEX loan_proposal_item_assignments_public_id_uq ON loan_proposal_item_assignments (public_id)');
        } catch (\Throwable $e) {
            // Ignore when index already exists.
        }

        try {
            $this->db->query('CREATE UNIQUE INDEX loan_proposal_item_assignments_unique_pair ON loan_proposal_item_assignments (proposal_item_id, asset_item_id)');
        } catch (\Throwable $e) {
            // Ignore when index already exists.
        }
    }

    public function down()
    {
        try {
            $this->db->query('DROP INDEX loan_proposal_item_assignments_public_id_uq ON loan_proposal_item_assignments');
        } catch (\Throwable $e) {
            // Ignore when index is not present.
        }

        try {
            $this->db->query('DROP INDEX loan_proposal_item_assignments_unique_pair ON loan_proposal_item_assignments');
        } catch (\Throwable $e) {
            // Ignore when index is not present.
        }

        try {
            $this->forge->dropTable('loan_proposal_item_assignments', true);
        } catch (\Throwable $e) {
            // Ignore when table is not present.
        }
    }
}