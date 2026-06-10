<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOprekApplicationsTable extends Migration
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
            'student_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'form_payload' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'application_status' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'submitted',
                    'doc_revision',
                    'doc_rejected',
                    'doc_verified',
                    'in_selection',
                    'failed',
                    'accepted',
                    'onboarding_pending',
                    'onboarding_complete',
                ],
                'default' => 'submitted',
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['campaign_id', 'student_id']);
        $this->forge->addForeignKey('campaign_id', 'oprek_campaigns', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('student_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('oprek_applications');
    }

    public function down()
    {
        $this->forge->dropTable('oprek_applications', true);
    }
}
