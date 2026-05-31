<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLabClearanceRequestsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('lab_clearance_requests')) {
            return;
        }

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
            'public_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
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
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'default'    => 'Syarat Yudisium/Kelulusan',
            ],
            'applicant_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'nim_nik' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'prodi' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'thesis_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'submitted',
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'outstanding_snapshot' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'verified_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'verified_note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'verified_at' => [
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
            'letter_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
            ],
            'letter_file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'letter_external_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'letter_issued_at' => [
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
        $this->forge->addKey('requester_id');
        $this->forge->addForeignKey('requester_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('lab_id', 'labs', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('lab_clearance_requests');
    }

    public function down()
    {
        $this->forge->dropTable('lab_clearance_requests', true);
    }
}
