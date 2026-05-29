<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssetDocumentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'asset_id'      => ['type' => 'INT', 'unsigned' => true],
            'document_type' => [
                'type'       => 'ENUM',
                'constraint' => ['invoice', 'bast', 'manual', 'warranty', 'photo', 'other'],
                'default'    => 'other',
            ],
            'title'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'file_path'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'file_size'     => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'mime_type'     => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'uploaded_by'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('asset_id');
        $this->forge->addKey('document_type');
        $this->forge->addForeignKey('asset_id', 'lab_assets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('asset_documents', true);
    }

    public function down()
    {
        $this->forge->dropTable('asset_documents', true);
    }
}
