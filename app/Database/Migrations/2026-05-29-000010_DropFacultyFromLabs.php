<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropFacultyFromLabs extends Migration
{
    public function up()
    {
        if (! $this->hasTable('labs') || ! $this->hasField('labs', 'faculty_id')) {
            return;
        }

        try {
            $this->db->query('ALTER TABLE labs DROP FOREIGN KEY fk_labs_faculty_id');
        } catch (\Throwable $e) {
            // Ignore when FK is not present.
        }

        try {
            $this->db->query('ALTER TABLE labs DROP INDEX idx_labs_faculty_id');
        } catch (\Throwable $e) {
            // Ignore when index is not present.
        }

        $this->forge->dropColumn('labs', 'faculty_id');
    }

    public function down()
    {
        if (! $this->hasTable('labs') || ! $this->hasTable('faculties')) {
            return;
        }

        if (! $this->hasField('labs', 'faculty_id')) {
            $this->forge->addColumn('labs', [
                'faculty_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'code',
                ],
            ]);
        }

        try {
            $this->db->query('ALTER TABLE labs ADD INDEX idx_labs_faculty_id (faculty_id)');
        } catch (\Throwable $e) {
        }

        try {
            $this->db->query('ALTER TABLE labs ADD CONSTRAINT fk_labs_faculty_id FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON UPDATE CASCADE ON DELETE SET NULL');
        } catch (\Throwable $e) {
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
