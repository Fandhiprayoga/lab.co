<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssetCategoriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'sort_order' => [
                'type'     => 'INT',
                'unsigned' => true,
                'default'  => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('name');
        $this->forge->createTable('asset_categories');

        // Backfill initial master categories from existing equipment records.
        $existingCategories = $this->db->table('lab_assets')
            ->select('category')
            ->where('asset_type', 'equipment')
            ->where('category IS NOT NULL', null, false)
            ->where("TRIM(category) != ''", null, false)
            ->groupBy('category')
            ->orderBy('category', 'ASC')
            ->get()
            ->getResultArray();

        $now = date('Y-m-d H:i:s');
        $sortOrder = 10;
        foreach ($existingCategories as $row) {
            $name = trim((string) ($row['category'] ?? ''));
            if ($name === '') {
                continue;
            }

            $this->db->table('asset_categories')->insert([
                'name'        => $name,
                'description' => null,
                'is_active'   => 1,
                'sort_order'  => $sortOrder,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $sortOrder += 10;
        }
    }

    public function down()
    {
        $this->forge->dropTable('asset_categories', true);
    }
}
