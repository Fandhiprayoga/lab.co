<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOprekApplicationsPublicId extends Migration
{
    public function up()
    {
        $this->forge->addColumn('oprek_applications', [
            'public_id' => [
                'type'       => 'CHAR',
                'constraint' => 32,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        $this->forge->addKey('public_id', false, true);

        // Backfill existing rows
        $db = \Config\Database::connect();
        $rows = $db->table('oprek_applications')->select('id')->where('public_id IS NULL')->get()->getResult();
        foreach ($rows as $row) {
            $db->table('oprek_applications')
                ->where('id', $row->id)
                ->update(['public_id' => bin2hex(random_bytes(16))]);
        }

        // Make NOT NULL after backfill
        $this->forge->modifyColumn('oprek_applications', [
            'public_id' => [
                'type'       => 'CHAR',
                'constraint' => 32,
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropKey('oprek_applications', 'public_id');
        $this->forge->dropColumn('oprek_applications', 'public_id');
    }
}
