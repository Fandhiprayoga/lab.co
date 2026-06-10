<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOprekOnboardingProfilesTable extends Migration
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
            'application_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'bank_account_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'bank_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'bank_account_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'signature_document_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'passbook_document_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'onboarding_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'submitted', 'verified', 'revision'],
                'default'    => 'pending',
            ],
            'verified_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'verified_at' => [
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
        $this->forge->addUniqueKey('application_id');
        $this->forge->addForeignKey('application_id', 'oprek_applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('signature_document_id', 'oprek_application_documents', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('passbook_document_id', 'oprek_application_documents', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('verified_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('oprek_onboarding_profiles');
    }

    public function down()
    {
        $this->forge->dropTable('oprek_onboarding_profiles', true);
    }
}
