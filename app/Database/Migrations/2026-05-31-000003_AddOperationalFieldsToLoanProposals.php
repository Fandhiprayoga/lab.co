<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOperationalFieldsToLoanProposals extends Migration
{
    public function up()
    {
        $this->forge->addColumn('loan_proposals', [
            'checkout_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'approval_l2_at',
            ],
            'checkout_condition' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
                'after'      => 'checkout_by',
            ],
            'checkout_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'checkout_condition',
            ],
            'checkin_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'checkout_at',
            ],
            'checkin_condition' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
                'after'      => 'checkin_by',
            ],
            'checkin_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'checkin_condition',
            ],
            'started_use_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'checkin_at',
            ],
            'started_use_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'started_use_by',
            ],
            'finished_use_by' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'started_use_at',
            ],
            'finished_use_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'finished_use_by',
            ],
            'is_late' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'finished_use_at',
            ],
            'issue_flag' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'is_late',
            ],
            'issue_note' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'issue_flag',
            ],
        ]);

        $this->db->query("CREATE INDEX IF NOT EXISTS loan_proposals_status_idx ON loan_proposals (status)");
        $this->db->query("CREATE INDEX IF NOT EXISTS loan_proposals_type_status_idx ON loan_proposals (loan_type, status)");
    }

    public function down()
    {
        $this->forge->dropColumn('loan_proposals', [
            'checkout_by',
            'checkout_condition',
            'checkout_at',
            'checkin_by',
            'checkin_condition',
            'checkin_at',
            'started_use_by',
            'started_use_at',
            'finished_use_by',
            'finished_use_at',
            'is_late',
            'issue_flag',
            'issue_note',
        ]);
    }
}
