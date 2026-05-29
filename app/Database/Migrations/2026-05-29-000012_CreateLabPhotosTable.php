<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLabPhotosTable extends Migration
{
    public function up()
    {
        if ($this->hasTable('lab_photos')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'lab_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'caption' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'sort_order' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'is_primary' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('lab_id');
        $this->forge->addForeignKey('lab_id', 'labs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('lab_photos');
    }

    public function down()
    {
        if ($this->hasTable('lab_photos')) {
            $this->forge->dropTable('lab_photos');
        }
    }

    private function hasTable(string $table): bool
    {
        return ! empty($this->db->query('SHOW TABLES LIKE ?', [$table])->getRowArray());
    }
}
