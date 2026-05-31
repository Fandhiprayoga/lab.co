<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLabPicHistoryTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('lab_pic_history')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'lab_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 20, // assigned | revoked
            ],
            'actor_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'note' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('lab_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('lab_pic_history');
    }

    public function down()
    {
        $this->forge->dropTable('lab_pic_history', true);
    }
}
