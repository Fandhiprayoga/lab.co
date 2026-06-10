<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOprekSelectionComponentsTable extends Migration
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
            'campaign_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'component_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'component_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'is_required' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'weight_percentage' => [
                'type'     => 'DECIMAL',
                'constraint' => '5,2',
                'default'  => 0,
            ],
            'max_score' => [
                'type'     => 'DECIMAL',
                'constraint' => '6,2',
                'default'  => 100,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
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
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['campaign_id', 'component_key']);
        $this->forge->addForeignKey('campaign_id', 'oprek_campaigns', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('oprek_selection_components');
    }

    public function down()
    {
        $this->forge->dropTable('oprek_selection_components', true);
    }
}
