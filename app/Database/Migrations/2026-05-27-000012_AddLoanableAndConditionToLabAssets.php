<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoanableAndConditionToLabAssets extends Migration
{
    public function up()
    {
        if (! $this->hasTable('lab_assets')) {
            return;
        }

        if (! $this->hasField('lab_assets', 'is_loanable')) {
            $this->forge->addColumn('lab_assets', [
                'is_loanable' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                    'after'      => 'is_active',
                ],
            ]);
        }

        if (! $this->hasField('lab_assets', 'condition_status')) {
            $this->forge->addColumn('lab_assets', [
                'condition_status' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => 'baik',
                    'after'      => 'is_loanable',
                ],
            ]);
        }

        $this->db->table('lab_assets')
            ->where('condition_status IS NULL', null, false)
            ->orWhere("TRIM(condition_status) = ''", null, false)
            ->set('condition_status', 'baik')
            ->update();

        $this->db->table('lab_assets')
            ->where('is_loanable IS NULL', null, false)
            ->set('is_loanable', 1)
            ->update();
    }

    public function down()
    {
        if (! $this->hasTable('lab_assets')) {
            return;
        }

        if ($this->hasField('lab_assets', 'condition_status')) {
            $this->forge->dropColumn('lab_assets', 'condition_status');
        }

        if ($this->hasField('lab_assets', 'is_loanable')) {
            $this->forge->dropColumn('lab_assets', 'is_loanable');
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
