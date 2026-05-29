<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStudyProgramsTable extends Migration
{
    public function up()
    {
        if ($this->hasTable('study_programs')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'faculty_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
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

        $this->forge->addKey('id', true);
        $this->forge->addKey('faculty_id');
        $this->forge->addKey('code');
        $this->forge->addForeignKey('faculty_id', 'faculties', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('study_programs');
    }

    public function down()
    {
        $this->forge->dropTable('study_programs', true);
    }

    private function hasTable(string $table): bool
    {
        $result = $this->db->query('SHOW TABLES LIKE ?', [$table])->getRowArray();

        return ! empty($result);
    }
}
