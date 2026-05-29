<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLogoToFaculties extends Migration
{
    public function up()
    {
        if ($this->hasTable('faculties') && ! $this->hasField('faculties', 'logo')) {
            $this->forge->addColumn('faculties', [
                'logo' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'description',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->hasTable('faculties') && $this->hasField('faculties', 'logo')) {
            $this->forge->dropColumn('faculties', 'logo');
        }
    }

    private function hasTable(string $table): bool
    {
        $result = $this->db->query('SHOW TABLES LIKE ?', [$table])->getRowArray();

        return ! empty($result);
    }

    private function hasField(string $table, string $field): bool
    {
        $result = $this->db->query('SHOW COLUMNS FROM `' . $table . '` LIKE ?', [$field])->getRowArray();

        return ! empty($result);
    }
}
