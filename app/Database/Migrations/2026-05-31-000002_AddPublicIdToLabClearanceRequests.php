<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublicIdToLabClearanceRequests extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('lab_clearance_requests')) {
            return;
        }

        // Skip if the column already exists (e.g. fresh install from create migration)
        if ($this->db->fieldExists('public_id', 'lab_clearance_requests')) {
            return;
        }

        $this->forge->addColumn('lab_clearance_requests', [
            'public_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'null'       => true,
                'after'      => 'request_code',
            ],
        ]);

        // Backfill existing rows with a UUID v4
        $rows = $this->db->table('lab_clearance_requests')->select('id')->get()->getResultArray();
        foreach ($rows as $row) {
            $this->db->table('lab_clearance_requests')
                ->where('id', $row['id'])
                ->update(['public_id' => $this->uuidV4()]);
        }

        // Enforce uniqueness now that all rows are populated
        $this->db->query('ALTER TABLE ' . $this->db->prefixTable('lab_clearance_requests')
            . ' ADD UNIQUE INDEX lab_clearance_requests_public_id (public_id)');
    }

    public function down()
    {
        if ($this->db->fieldExists('public_id', 'lab_clearance_requests')) {
            $this->forge->dropColumn('lab_clearance_requests', 'public_id');
        }
    }

    private function uuidV4(): string
    {
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
