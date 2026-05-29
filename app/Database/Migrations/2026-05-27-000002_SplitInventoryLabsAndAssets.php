<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SplitInventoryLabsAndAssets extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('labs')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                ],
                'code' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                ],
                'location' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'capacity' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                ],
                'is_active' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
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
            $this->forge->addKey('code');
            $this->forge->createTable('labs');
        }

        if ($this->db->tableExists('lab_assets') && ! $this->db->fieldExists('lab_id', 'lab_assets')) {
            $this->forge->addColumn('lab_assets', [
                'lab_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'id',
                ],
            ]);
        }

        if ($this->db->tableExists('lab_assets') && $this->db->tableExists('labs')) {
            $rows = $this->db->table('lab_assets')
                ->select('id, location')
                ->where('location IS NOT NULL', null, false)
                ->where('location !=', '')
                ->get()->getResultArray();

            foreach ($rows as $row) {
                $location = trim((string) $row['location']);
                if ($location === '') {
                    continue;
                }

                $existingLab = $this->db->table('labs')
                    ->where('name', $location)
                    ->orWhere('location', $location)
                    ->get()->getRowArray();

                $labId = null;
                if ($existingLab) {
                    $labId = (int) $existingLab['id'];
                } else {
                    $this->db->table('labs')->insert([
                        'name'       => $location,
                        'location'   => $location,
                        'is_active'  => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $labId = (int) $this->db->insertID();
                }

                $this->db->table('lab_assets')
                    ->where('id', (int) $row['id'])
                    ->update(['lab_id' => $labId]);
            }

            // Add index and FK in a safe way for reruns.
            try {
                $this->db->query('ALTER TABLE lab_assets ADD INDEX idx_lab_assets_lab_id (lab_id)');
            } catch (\Throwable $e) {
                // Ignore if index already exists.
            }

            try {
                $this->db->query('ALTER TABLE lab_assets ADD CONSTRAINT fk_lab_assets_lab_id FOREIGN KEY (lab_id) REFERENCES labs(id) ON UPDATE CASCADE ON DELETE SET NULL');
            } catch (\Throwable $e) {
                // Ignore if FK already exists.
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('lab_assets') && $this->db->fieldExists('lab_id', 'lab_assets')) {
            try {
                $this->db->query('ALTER TABLE lab_assets DROP FOREIGN KEY fk_lab_assets_lab_id');
            } catch (\Throwable $e) {
                // Ignore when FK is not present.
            }

            try {
                $this->db->query('ALTER TABLE lab_assets DROP INDEX idx_lab_assets_lab_id');
            } catch (\Throwable $e) {
                // Ignore when index is not present.
            }

            $this->forge->dropColumn('lab_assets', 'lab_id');
        }

        if ($this->db->tableExists('labs')) {
            $this->forge->dropTable('labs', true);
        }
    }
}
