<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSoftDeleteToLabs extends Migration
{
    public function up()
    {
        if (! $this->hasTable('labs') || $this->hasField('labs', 'deleted_at')) {
            return;
        }

        $this->forge->addColumn('labs', [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at',
            ],
        ]);

        try {
            $this->db->query('ALTER TABLE labs ADD INDEX idx_labs_deleted_at (deleted_at)');
        } catch (\Throwable $e) {
        }
    }

    public function down()
    {
        if (! $this->hasTable('labs') || ! $this->hasField('labs', 'deleted_at')) {
            return;
        }

        try {
            $this->db->query('ALTER TABLE labs DROP INDEX idx_labs_deleted_at');
        } catch (\Throwable $e) {
        }

        $this->forge->dropColumn('labs', 'deleted_at');
    }

    private function hasTable(string $table): bool
    {
        return ! empty($this->db->query('SHOW TABLES LIKE ?', [$table])->getRowArray());
    }

    private function hasField(string $table, string $field): bool
    {
        return ! empty($this->db->query('SHOW COLUMNS FROM `' . $table . '` LIKE ?', [$field])->getRowArray());
    }
}
