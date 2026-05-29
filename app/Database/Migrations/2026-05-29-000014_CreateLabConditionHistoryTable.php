<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLabConditionHistoryTable extends Migration
{
    public function up()
    {
        if ($this->hasTable('lab_condition_history')) {
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
            'previous_condition' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'new_condition' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'changed_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('lab_id');
        $this->forge->addForeignKey('lab_id', 'labs', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('lab_condition_history');
    }

    public function down()
    {
        if ($this->hasTable('lab_condition_history')) {
            $this->forge->dropTable('lab_condition_history');
        }
    }

    private function hasTable(string $table): bool
    {
        return ! empty($this->db->query('SHOW TABLES LIKE ?', [$table])->getRowArray());
    }
}
