<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConsumableCategoriesTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('consumable_categories')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'sort_order' => [
                    'type'    => 'INT',
                    'default' => 0,
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
            $this->forge->createTable('consumable_categories');
        }
    }

    public function down()
    {
        $this->forge->dropTable('consumable_categories', true);
    }
}
