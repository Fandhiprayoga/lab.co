<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConsumableStockAdjustmentsTable extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('consumable_stock_adjustments')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'consumable_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                ],
                'adjustment_type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['susut', 'rusak', 'tumpah', 'koreksi', 'masuk'],
                ],
                'qty' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '12,4',
                ],
                'reason' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'adjusted_by' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                ],
                'adjusted_at' => [
                    'type' => 'DATETIME',
                ],
                'reference_request_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
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
            $this->forge->addKey('consumable_id');
            $this->forge->createTable('consumable_stock_adjustments');
        }
    }

    public function down()
    {
        $this->forge->dropTable('consumable_stock_adjustments', true);
    }
}
