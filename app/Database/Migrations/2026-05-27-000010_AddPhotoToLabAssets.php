<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoToLabAssets extends Migration
{
    public function up()
    {
        if ($this->hasTable('lab_assets') && ! $this->hasField('lab_assets', 'photo')) {
            $this->forge->addColumn('lab_assets', [
                'photo' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'specifications',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->hasTable('lab_assets') && $this->hasField('lab_assets', 'photo')) {
            $this->forge->dropColumn('lab_assets', 'photo');
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
