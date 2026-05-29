<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class AssetCategorySeeder extends Seeder
{
    public function run()
    {
        if (ENVIRONMENT !== 'development') {
            throw new RuntimeException('AssetCategorySeeder hanya boleh dieksekusi pada environment development.');
        }

        if (! $this->db->tableExists('asset_categories')) {
            throw new RuntimeException('Tabel asset_categories belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $rows = [
            [
                'name'        => 'Alat Laboratorium',
                'description' => null,
                'is_active'   => 1,
                'sort_order'  => 10,
            ],
            [
                'name'        => 'Invetaris Ruangan',
                'description' => null,
                'is_active'   => 1,
                'sort_order'  => 20,
            ],
        ];

        $table = $this->db->table('asset_categories');
        $now   = date('Y-m-d H:i:s');

        // Reset agar kategori tersisa hanya dua data master ini.
        $table->truncate();

        foreach ($rows as $row) {
            $table->insert([
                'name'        => $row['name'],
                'description' => $row['description'],
                'is_active'   => $row['is_active'],
                'sort_order'  => $row['sort_order'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        echo "AssetCategorySeeder selesai. Master kategori alat direset ke 2 data default.\n";
    }
}
