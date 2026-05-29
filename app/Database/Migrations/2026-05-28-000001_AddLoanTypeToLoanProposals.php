<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoanTypeToLoanProposals extends Migration
{
    public function up()
    {
        $this->forge->addColumn('loan_proposals', [
            'loan_type' => [
                'type'       => 'ENUM',
                'constraint' => ['equipment', 'lab'],
                'default'    => 'equipment',
                'after'      => 'proposer_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('loan_proposals', 'loan_type');
    }
}
