<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOprekComponentAssessorsTable extends Migration
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
            'component_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'assessor_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'assessor_role' => [
                'type'       => 'ENUM',
                'constraint' => ['laboran', 'active_assistant'],
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['component_id', 'assessor_user_id']);
        $this->forge->addForeignKey('component_id', 'oprek_selection_components', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assessor_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('oprek_component_assessors');
    }

    public function down()
    {
        $this->forge->dropTable('oprek_component_assessors', true);
    }
}
