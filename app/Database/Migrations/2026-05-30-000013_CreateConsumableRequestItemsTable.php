<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConsumableRequestItemsTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('consumable_request_items')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'request_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                ],
                'consumable_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                ],
                'qty_requested' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,4',
                    'default'    => '0.0000',
                ],
                'qty_approved' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,4',
                    'null'       => true,
                ],
                'qty_actual' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,4',
                    'null'       => true,
                ],
                'notes' => [
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
            $this->forge->addKey('request_id');
            $this->forge->createTable('consumable_request_items');
        }
    }

    public function down()
    {
        $this->forge->dropTable('consumable_request_items', true);
    }
}
