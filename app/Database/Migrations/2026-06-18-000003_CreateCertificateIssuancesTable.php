<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCertificateIssuancesTable extends Migration
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
            'public_id' => [
                'type'       => 'CHAR',
                'constraint' => 36,
                'unique'     => true,
            ],
            'cert_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'unique'     => true,
            ],
            'template_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'recipient_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'recipient_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'recipient_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'issued_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'issued_at' => [
                'type' => 'DATETIME',
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_revoked' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'revoked_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'revoked_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'revoke_reason' => [
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
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('template_id');
        $this->forge->addKey('recipient_user_id');
        $this->forge->addKey('is_revoked');
        $this->forge->addForeignKey('template_id', 'certificate_templates', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('issued_by', 'users', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('certificate_issuances', true);
    }

    public function down()
    {
        $this->forge->dropTable('certificate_issuances', true);
    }
}
