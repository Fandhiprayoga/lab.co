<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDescriptionToLabs extends Migration
{
    public function up()
    {
        if (! $this->hasTable('labs') || $this->hasField('labs', 'description')) {
            return;
        }

        $this->forge->addColumn('labs', [
            'description' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'faculty_id',
            ],
        ]);
    }

    public function down()
    {
        if (! $this->hasTable('labs') || ! $this->hasField('labs', 'description')) {
            return;
        }

        $this->forge->dropColumn('labs', 'description');
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
