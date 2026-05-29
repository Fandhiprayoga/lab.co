<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssetMaintenancesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'asset_id'              => ['type' => 'INT', 'unsigned' => true],
            'maintenance_type'      => [
                'type'       => 'ENUM',
                'constraint' => ['preventive', 'corrective', 'calibration', 'inspection'],
                'default'    => 'corrective',
            ],
            'scheduled_date'        => ['type' => 'DATE', 'null' => true],
            'performed_date'        => ['type' => 'DATE', 'null' => true],
            'status'                => [
                'type'       => 'ENUM',
                'constraint' => ['scheduled', 'in_progress', 'completed', 'cancelled'],
                'default'    => 'scheduled',
            ],
            'performed_by'          => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'cost'                  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
            'description'           => ['type' => 'TEXT'],
            'result_notes'          => ['type' => 'TEXT', 'null' => true],
            'next_maintenance_date' => ['type' => 'DATE', 'null' => true],
            'created_by'            => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('asset_id');
        $this->forge->addKey('status');
        $this->forge->addKey('scheduled_date');
        $this->forge->addForeignKey('asset_id', 'lab_assets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('asset_maintenances', true);
    }

    public function down()
    {
        $this->forge->dropTable('asset_maintenances', true);
    }
}
