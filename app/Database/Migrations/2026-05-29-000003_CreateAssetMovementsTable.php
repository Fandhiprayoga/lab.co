<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssetMovementsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'asset_id'       => ['type' => 'INT', 'unsigned' => true],
            'movement_type'  => [
                'type'       => 'ENUM',
                'constraint' => ['in', 'out', 'transfer', 'borrow', 'return', 'adjustment', 'disposal'],
                'default'    => 'in',
            ],
            'quantity'       => ['type' => 'INT', 'default' => 0],
            'from_lab_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'to_lab_id'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'reference_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'reference_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'movement_date'  => ['type' => 'DATETIME'],
            'notes'          => ['type' => 'TEXT', 'null' => true],
            'created_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('asset_id');
        $this->forge->addKey('movement_type');
        $this->forge->addKey('movement_date');
        $this->forge->addForeignKey('asset_id', 'lab_assets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('from_lab_id', 'labs', 'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('to_lab_id', 'labs', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('asset_movements', true);
    }

    public function down()
    {
        $this->forge->dropTable('asset_movements', true);
    }
}
