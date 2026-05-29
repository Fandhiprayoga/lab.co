<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLogoToLabs extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('labs') && ! $this->db->fieldExists('logo', 'labs')) {
            $this->forge->addColumn('labs', [
                'logo' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'capacity',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('labs') && $this->db->fieldExists('logo', 'labs')) {
            $this->forge->dropColumn('labs', 'logo');
        }
    }
}
