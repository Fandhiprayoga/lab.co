<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLogoToStudyPrograms extends Migration
{
    public function up()
    {
        if ($this->hasTable('study_programs') && ! $this->hasField('study_programs', 'logo')) {
            $this->forge->addColumn('study_programs', [
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
        if ($this->hasTable('study_programs') && $this->hasField('study_programs', 'logo')) {
            $this->forge->dropColumn('study_programs', 'logo');
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
