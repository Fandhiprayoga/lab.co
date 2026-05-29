<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoanManagementTables extends Migration
{
    public function up()
    {
        // Ensure rerun safety when a previous migration attempt failed mid-way.
        $this->forge->dropTable('loan_request_logs', true);
        $this->forge->dropTable('loan_requests', true);
        $this->forge->dropTable('lab_assets', true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'asset_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'equipment',
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'location' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'specifications' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'max_loan_hours' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 24,
            ],
            'stock_total' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 1,
            ],
            'stock_available' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 1,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_by' => [
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
        $this->forge->addKey('id', true);
        $this->forge->createTable('lab_assets');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'request_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'unique'     => true,
            ],
            'requester_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'asset_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'qty' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 1,
            ],
            'purpose' => [
                'type' => 'TEXT',
            ],
            'supporting_document' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'pickup_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'return_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'default'    => 'waiting_l1',
            ],
            'requires_l2' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
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
            'checkout_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'checkout_condition' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'checkout_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'checkin_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'checkin_condition' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'checkin_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'is_late' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'issue_flag' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'issue_note' => [
                'type' => 'TEXT',
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
            'auto_canceled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
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
        $this->forge->addForeignKey('asset_id', 'lab_assets', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('loan_requests');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'loan_request_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'actor_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('loan_request_id');
        $this->forge->addForeignKey('loan_request_id', 'loan_requests', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('loan_request_logs');
    }

    public function down()
    {
        $this->forge->dropTable('loan_request_logs', true);
        $this->forge->dropTable('loan_requests', true);
        $this->forge->dropTable('lab_assets', true);
    }
}
