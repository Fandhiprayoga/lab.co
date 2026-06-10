<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOprekActivityLogsTable extends Migration
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
                'null'       => true,
            ],
            'application_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'actor_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'action_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'action_payload' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('campaign_id');
        $this->forge->addKey('application_id');
        $this->forge->addKey('actor_user_id');
        $this->forge->addForeignKey('campaign_id', 'oprek_campaigns', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('application_id', 'oprek_applications', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('actor_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('oprek_activity_logs');
    }

    public function down()
    {
        $this->forge->dropTable('oprek_activity_logs', true);
    }
}
