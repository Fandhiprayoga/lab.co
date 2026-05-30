<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConsumableItemsTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('consumable_items')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 200,
                ],
                'category_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'unit_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'lab_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'stock_total' => [
                    'type'    => 'DECIMAL',
                    'constraint' => '12,4',
                    'default' => '0.0000',
                ],
                'stock_available' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,4',
                    'default'    => '0.0000',
                ],
                'min_stock' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,4',
                    'default'    => '0.0000',
                ],
                'location' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 200,
                    'null'       => true,
                ],
                'expiry_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'requires_approval' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
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
            $this->forge->addKey('lab_id');
            $this->forge->addKey('category_id');
            $this->forge->createTable('consumable_items');
        }
    }

    public function down()
    {
        $this->forge->dropTable('consumable_items', true);
    }
}
