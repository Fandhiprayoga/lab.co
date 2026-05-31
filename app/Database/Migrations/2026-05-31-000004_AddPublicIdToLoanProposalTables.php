<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublicIdToLoanProposalTables extends Migration
{
    public function up()
    {
        $this->forge->addColumn('loan_proposals', [
            'public_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        $this->forge->addColumn('loan_proposal_items', [
            'public_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 36,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);

        $proposalRows = $this->db->table('loan_proposals')->select('id')->get()->getResultArray();
        foreach ($proposalRows as $row) {
            $this->db->table('loan_proposals')
                ->where('id', (int) ($row['id'] ?? 0))
                ->update(['public_id' => $this->uuidV4()]);
        }

        $itemRows = $this->db->table('loan_proposal_items')->select('id')->get()->getResultArray();
        foreach ($itemRows as $row) {
            $this->db->table('loan_proposal_items')
                ->where('id', (int) ($row['id'] ?? 0))
                ->update(['public_id' => $this->uuidV4()]);
        }

        $this->db->query('CREATE UNIQUE INDEX loan_proposals_public_id_uq ON loan_proposals (public_id)');
        $this->db->query('CREATE UNIQUE INDEX loan_proposal_items_public_id_uq ON loan_proposal_items (public_id)');
    }

    public function down()
    {
        $this->db->query('DROP INDEX loan_proposals_public_id_uq ON loan_proposals');
        $this->db->query('DROP INDEX loan_proposal_items_public_id_uq ON loan_proposal_items');

        $this->forge->dropColumn('loan_proposals', 'public_id');
        $this->forge->dropColumn('loan_proposal_items', 'public_id');
    }

    private function uuidV4(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        $hex = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}
