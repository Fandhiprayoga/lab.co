<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEquipmentTermsChecksToLoanProposals extends Migration
{
    public function up()
    {
        $this->forge->addColumn('loan_proposals', [
            'equipment_terms_checks' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'lab_terms_checks',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('loan_proposals', 'equipment_terms_checks');
    }
}
