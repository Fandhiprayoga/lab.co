<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPicUserToLabs extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('pic_user_id', 'labs')) {
            return;
        }

        $this->forge->addColumn('labs', [
            'pic_user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'condition_status',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('pic_user_id', 'labs')) {
            $this->forge->dropColumn('labs', 'pic_user_id');
        }
    }
}
