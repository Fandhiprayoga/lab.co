<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublicIdToConsumableRequests extends Migration
{
    public function up()
    {
        $this->forge->addColumn('consumable_requests', [
            'public_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'null'       => true, // Allow null initially for existing records
                'after'      => 'id',
            ],
        ]);

        // Add unique index for public_id
        $this->forge->addKey('public_id', false, true, 'consumable_requests_public_id_unique');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE consumable_requests DROP INDEX consumable_requests_public_id_unique');
        $this->forge->dropColumn('consumable_requests', 'public_id');
    }
}
