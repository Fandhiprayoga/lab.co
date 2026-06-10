<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOprekScoresTable extends Migration
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
            'score_value' => [
                'type'     => 'DECIMAL',
                'constraint' => '6,2',
                'null'     => true,
            ],
            'note' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'scored_at' => [
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
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['application_id', 'component_id', 'assessor_user_id']);
        $this->forge->addForeignKey('application_id', 'oprek_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('component_id', 'oprek_selection_components', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assessor_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('oprek_scores');
    }

    public function down()
    {
        $this->forge->dropTable('oprek_scores', true);
    }
}
