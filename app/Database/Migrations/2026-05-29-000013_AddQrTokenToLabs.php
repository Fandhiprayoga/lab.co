<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQrTokenToLabs extends Migration
{
    public function up()
    {
        if (! $this->hasTable('labs') || $this->hasField('labs', 'qr_token')) {
            return;
        }

        $this->forge->addColumn('labs', [
            'qr_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'logo',
            ],
        ]);

        try {
            $this->db->query('ALTER TABLE labs ADD UNIQUE INDEX uniq_labs_qr_token (qr_token)');
        } catch (\Throwable $e) {
        }

        // Backfill token untuk lab existing.
        $existing = $this->db->table('labs')->select('id')->where('qr_token IS NULL')->get()->getResultArray();
        foreach ($existing as $row) {
            $this->db->table('labs')->where('id', (int) $row['id'])->update([
                'qr_token' => bin2hex(random_bytes(16)),
            ]);
        }
    }

    public function down()
    {
        if (! $this->hasTable('labs') || ! $this->hasField('labs', 'qr_token')) {
            return;
        }

        try {
            $this->db->query('ALTER TABLE labs DROP INDEX uniq_labs_qr_token');
        } catch (\Throwable $e) {
        }

        $this->forge->dropColumn('labs', 'qr_token');
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
