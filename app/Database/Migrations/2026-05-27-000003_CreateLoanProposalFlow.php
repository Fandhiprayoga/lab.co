<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoanProposalFlow extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('loan_proposals')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'proposal_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 40,
                    'unique'     => true,
                ],
                'proposer_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 200,
                ],
                'objective' => [
                    'type' => 'TEXT',
                ],
                'start_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'end_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'requires_l2' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'draft',
                ],
                'submitted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'approval_l1_by' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'approval_l1_note' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'approval_l1_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'approval_l2_by' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'approval_l2_note' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'approval_l2_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'rejected_reason' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'cancel_reason' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'canceled_by' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'canceled_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
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

            $this->forge->addKey('id', true);
            $this->forge->addKey('status');
            $this->forge->createTable('loan_proposals');
        }

        if (! $this->db->tableExists('loan_proposal_items')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'proposal_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                ],
                'item_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'default'    => 'equipment',
                ],
                'equipment_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'lab_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'qty' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'default'  => 1,
                ],
                'note' => [
                    'type' => 'TEXT',
                    'null' => true,
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

            $this->forge->addKey('id', true);
            $this->forge->addKey('proposal_id');
            $this->forge->addKey('item_type');
            $this->forge->addForeignKey('proposal_id', 'loan_proposals', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('equipment_id', 'lab_assets', 'id', 'CASCADE', 'SET NULL');
            $this->forge->addForeignKey('lab_id', 'labs', 'id', 'CASCADE', 'SET NULL');
            $this->forge->createTable('loan_proposal_items');
        }
    }

    public function down()
    {
        $this->forge->dropTable('loan_proposal_items', true);
        $this->forge->dropTable('loan_proposals', true);
    }
}
