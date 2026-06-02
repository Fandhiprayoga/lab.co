<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAssetItemIdToAssetMovements extends Migration
{
    public function up()
    {
        try {
            $this->forge->addColumn('asset_movements', [
                'asset_item_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'asset_id',
                ],
            ]);
        } catch (\Throwable $e) {
            // Ignore when column already exists.
        }

        try {
            $this->db->query('ALTER TABLE asset_movements ADD INDEX idx_asset_movements_asset_item_id (asset_item_id)');
        } catch (\Throwable $e) {
            // Ignore when index already exists.
        }

        try {
            $this->db->query('ALTER TABLE asset_movements ADD CONSTRAINT fk_asset_movements_asset_item_id FOREIGN KEY (asset_item_id) REFERENCES asset_items(id) ON UPDATE CASCADE ON DELETE SET NULL');
        } catch (\Throwable $e) {
            // Ignore when constraint already exists.
        }
    }

    public function down()
    {
        try {
            $this->db->query('ALTER TABLE asset_movements DROP FOREIGN KEY fk_asset_movements_asset_item_id');
        } catch (\Throwable $e) {
            // Ignore when FK is not present.
        }

        try {
            $this->db->query('ALTER TABLE asset_movements DROP INDEX idx_asset_movements_asset_item_id');
        } catch (\Throwable $e) {
            // Ignore when index is not present.
        }

        try {
            $this->forge->dropColumn('asset_movements', 'asset_item_id');
        } catch (\Throwable $e) {
            // Ignore when column is not present.
        }
    }
}