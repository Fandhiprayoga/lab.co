<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOprekCampaignsDescriptionAndPoster extends Migration
{
    public function up()
    {
        $this->forge->addColumn('oprek_campaigns', [
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'period_name',
            ],
            'requirements' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'description',
            ],
            'poster' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'requirements',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('oprek_campaigns', 'description');
        $this->forge->dropColumn('oprek_campaigns', 'requirements');
        $this->forge->dropColumn('oprek_campaigns', 'poster');
    }
}
