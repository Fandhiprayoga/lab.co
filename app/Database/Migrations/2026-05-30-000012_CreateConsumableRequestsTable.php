<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConsumableRequestsTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('consumable_requests')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'request_code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 40,
                    'unique'     => true,
                ],
                'requester_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                ],
                'lab_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'purpose' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'scheduled_date' => [
                    'type' => 'DATE',
                    'null' => true,
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
                'approval_by' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'approval_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'approval_note' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'disbursed_by' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'disbursed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'realized_by' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'realized_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'canceled_reason' => [
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
            $this->forge->addKey('requester_id');
            $this->forge->addKey('lab_id');
            $this->forge->addKey('status');
            $this->forge->createTable('consumable_requests');
        }
    }

    public function down()
    {
        $this->forge->dropTable('consumable_requests', true);
    }
}
