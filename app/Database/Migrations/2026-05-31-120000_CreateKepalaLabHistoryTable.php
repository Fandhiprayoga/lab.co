<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKepalaLabHistoryTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('kepala_lab_history')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
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
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('kepala_lab_history');
    }

    public function down()
    {
        $this->forge->dropTable('kepala_lab_history', true);
    }
}
