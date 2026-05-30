<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class ConsumableCategorySeeder extends Seeder
{
    public function run()
    {
        if (! $this->db->tableExists('consumable_categories')) {
            throw new RuntimeException('Tabel consumable_categories belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $rows = [
            ['name' => 'Bahan Kimia',           'description' => 'Reagen, larutan, dan bahan kimia laboratorium', 'sort_order' => 10],
            ['name' => 'Bahan Elektronik',       'description' => 'Komponen elektronik habis pakai (kabel, sekring, solder, dll.)', 'sort_order' => 20],
            ['name' => 'Alat Tulis & Kantor',    'description' => 'Alat tulis, spidol, penggaris, dan perlengkapan kantor', 'sort_order' => 30],
            ['name' => 'Kertas & Media Cetak',   'description' => 'Kertas HVS, kertas foto, label, stiker', 'sort_order' => 40],
            ['name' => 'Tinta & Toner',          'description' => 'Tinta printer, toner, cartridge', 'sort_order' => 50],
            ['name' => 'Sanitasi & Kebersihan',  'description' => 'Cairan pembersih, tisu, sabun, sarung tangan disposable', 'sort_order' => 60],
            ['name' => 'Perlengkapan Keselamatan', 'description' => 'APD, masker, kacamata lab, jas lab disposable', 'sort_order' => 70],
            ['name' => 'Lain-lain',              'description' => 'Bahan habis pakai yang tidak masuk kategori lain', 'sort_order' => 99],
        ];

        $table = $this->db->table('consumable_categories');
        $now   = date('Y-m-d H:i:s');

        foreach ($rows as $row) {
            $existing = $table->where('name', $row['name'])->countAllResults();
            if ($existing > 0) {
                continue;
            }

            $table->insert([
                'name'        => $row['name'],
                'description' => $row['description'],
                'is_active'   => 1,
                'sort_order'  => $row['sort_order'],
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        echo "ConsumableCategorySeeder selesai. " . count($rows) . " kategori BHP ditambahkan.\n";
    }
}
