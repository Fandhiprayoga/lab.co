<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOprekFinalDecisionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'application_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'decision_status' => [
                'type'       => 'ENUM',
                'constraint' => ['accepted', 'rejected', 'waitlist'],
            ],
            'final_score' => [
                'type'     => 'DECIMAL',
                'constraint' => '8,2',
                'null'     => true,
            ],
            'decided_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'decision_note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'decided_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('application_id');
        $this->forge->addForeignKey('application_id', 'oprek_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('decided_by', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('oprek_final_decisions');
    }

    public function down()
    {
        $this->forge->dropTable('oprek_final_decisions', true);
    }
}
