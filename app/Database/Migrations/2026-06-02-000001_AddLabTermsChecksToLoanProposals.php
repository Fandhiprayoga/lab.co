<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLabTermsChecksToLoanProposals extends Migration
{
    public function up()
    {
        $this->forge->addColumn('loan_proposals', [
            'lab_terms_checks' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'issue_note',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('loan_proposals', 'lab_terms_checks');
    }
}
