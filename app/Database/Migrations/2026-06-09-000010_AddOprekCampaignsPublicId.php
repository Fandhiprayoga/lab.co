<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOprekCampaignsPublicId extends Migration
{
    public function up()
    {
        // Add public_id as CHAR(32) hex unique
        $this->forge->addColumn('oprek_campaigns', [
            'public_id' => [
                'type'       => 'CHAR',
                'constraint' => 32,
                'null'       => true,
                'unique'     => true,
                'after'      => 'id',
            ],
        ]);

        // Generate public_id for existing rows
        $db = \Config\Database::connect();
        $rows = $db->table('oprek_campaigns')->select('id')->get()->getResult();
        foreach ($rows as $row) {
            $db->table('oprek_campaigns')
                ->where('id', $row->id)
                ->update(['public_id' => bin2hex(random_bytes(16))]);
        }

        // Now make it NOT NULL
        $this->forge->modifyColumn('oprek_campaigns', [
            'public_id' => [
                'type'       => 'CHAR',
                'constraint' => 32,
                'null'       => false,
                'unique'     => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('oprek_campaigns', 'public_id');
    }
}
