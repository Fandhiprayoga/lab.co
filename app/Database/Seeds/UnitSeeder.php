<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class UnitSeeder extends Seeder
{
    public function run()
    {
        if (! $this->db->tableExists('units')) {
            throw new RuntimeException('Tabel units belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $rows = [
            ['name' => 'Buah',      'symbol' => 'pcs', 'sort_order' => 10],
            ['name' => 'Unit',      'symbol' => 'unit','sort_order' => 20],
            ['name' => 'Set',       'symbol' => 'set', 'sort_order' => 30],
            ['name' => 'Box',       'symbol' => 'box', 'sort_order' => 40],
            ['name' => 'Pak',       'symbol' => 'pak', 'sort_order' => 50],
            ['name' => 'Meter',     'symbol' => 'm',   'sort_order' => 60],
            ['name' => 'Liter',     'symbol' => 'L',   'sort_order' => 70],
            ['name' => 'Kilogram',  'symbol' => 'kg',  'sort_order' => 80],
        ];

        $table = $this->db->table('units');
        $now   = date('Y-m-d H:i:s');

        foreach ($rows as $row) {
            $existing = $table->where('name', $row['name'])->countAllResults();
            if ($existing > 0) {
                continue;
            }

            $table->insert([
                'name'       => $row['name'],
                'symbol'     => $row['symbol'],
                'is_active'  => 1,
                'sort_order' => $row['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        echo "UnitSeeder selesai. Master satuan default ditambahkan.\n";
    }
}
