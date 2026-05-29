<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLabVisitsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'lab_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'visitor_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'visitor_institution' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'default'    => null,
            ],
            'purpose' => [
                'type'       => 'ENUM',
                'constraint' => ['praktikum', 'penelitian', 'kunjungan', 'pengambilan_alat', 'lainnya'],
                'default'    => 'kunjungan',
            ],
            'purpose_note' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'checked_in_at' => [
                'type' => 'DATETIME',
            ],
            'checked_out_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('lab_id');
        $this->forge->addKey('checked_in_at');
        $this->forge->addForeignKey('lab_id', 'labs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('lab_visits');
    }

    public function down(): void
    {
        $this->forge->dropTable('lab_visits', true);
    }
}
