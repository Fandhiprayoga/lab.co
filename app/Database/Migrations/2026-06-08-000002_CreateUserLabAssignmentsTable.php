<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserLabAssignmentsTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('user_lab_assignments')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'lab_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'assigned_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['user_id', 'lab_id']);
        $this->forge->addKey('user_id');
        $this->forge->addKey('lab_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('lab_id', 'labs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_lab_assignments');
    }

    public function down()
    {
        $this->forge->dropTable('user_lab_assignments', true);
    }
}
