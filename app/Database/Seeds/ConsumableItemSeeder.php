<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

/**
 * Seed sample consumable items per lab untuk keperluan testing modul BHP.
 *
 * Prasyarat:
 *   - UnitSeeder        (tabel units)
 *   - LabSeeder         (tabel labs)
 *   - ConsumableCategorySeeder (tabel consumable_categories)
 */
class ConsumableItemSeeder extends Seeder
{
    public function run()
    {
        if (ENVIRONMENT !== 'development') {
            throw new RuntimeException('ConsumableItemSeeder hanya boleh dieksekusi pada environment development.');
        }

        foreach (['consumable_items', 'consumable_categories', 'labs', 'units'] as $tbl) {
            if (! $this->db->tableExists($tbl)) {
                throw new RuntimeException("Tabel {$tbl} belum tersedia. Jalankan migrasi terlebih dahulu.");
            }
        }

        // ----------------------------------------------------------------
        // Resolve ID: kategori
        // ----------------------------------------------------------------
        $cats = $this->db->table('consumable_categories')
            ->select('id, name')
            ->get()->getResultArray();

        if (empty($cats)) {
            throw new RuntimeException('Data consumable_categories kosong. Jalankan ConsumableCategorySeeder terlebih dahulu.');
        }

        $catMap = [];
        foreach ($cats as $c) {
            $catMap[$c['name']] = (int) $c['id'];
        }

        // ----------------------------------------------------------------
        // Resolve ID: satuan
        // ----------------------------------------------------------------
        $units = $this->db->table('units')
            ->select('id, symbol')
            ->get()->getResultArray();

        if (empty($units)) {
            throw new RuntimeException('Data units kosong. Jalankan UnitSeeder terlebih dahulu.');
        }

        $unitMap = [];
        foreach ($units as $u) {
            $unitMap[$u['symbol']] = (int) $u['id'];
        }

        // ----------------------------------------------------------------
        // Resolve ID: lab (gunakan dua lab pertama yang aktif)
        // ----------------------------------------------------------------
        $labs = $this->db->table('labs')
            ->select('id, code')
            ->where('is_active', 1)
            ->orderBy('id', 'ASC')
            ->limit(3)
            ->get()->getResultArray();

        if (empty($labs)) {
            throw new RuntimeException('Data labs kosong. Jalankan LabSeeder terlebih dahulu.');
        }

        // Map code => id; fallback ke index numerik jika kode tidak cocok
        $labMap = [];
        foreach ($labs as $l) {
            $labMap[$l['code']] = (int) $l['id'];
        }

        // Ambil dua lab pertama sebagai target seeder
        $lab1Id = (int) $labs[0]['id'];
        $lab2Id = isset($labs[1]) ? (int) $labs[1]['id'] : $lab1Id;
        $lab3Id = isset($labs[2]) ? (int) $labs[2]['id'] : $lab2Id;

        // ----------------------------------------------------------------
        // Definisi item — [name, cat_name, unit_sym, lab_id, stock, min, requires_approval]
        // ----------------------------------------------------------------
        $items = [
            // Lab 1 — Bahan Kimia & Sanitasi
            ['Alkohol Isopropil 70%',      'Bahan Kimia',           'L',   $lab1Id, 10.0, 2.0,  0, 'Rak B1, lemari B'],
            ['Cairan Pembersih LCD',       'Bahan Kimia',           'L',   $lab1Id,  5.0, 1.0,  0, 'Rak B1'],
            ['Thermal Paste CPU',          'Bahan Elektronik',      'pcs', $lab1Id, 20,   5,    0, 'Laci teknisi'],
            ['Kabel Ties 20cm',            'Bahan Elektronik',      'pak', $lab1Id,  8,   2,    0, 'Laci teknisi'],
            ['Tisu Microfiber',            'Sanitasi & Kebersihan', 'pak', $lab1Id, 15,   3,    0, 'Lemari kebersihan'],
            ['Sarung Tangan Latex (S)',    'Perlengkapan Keselamatan', 'pak', $lab1Id, 5, 1,    1, 'Lemari APD'],
            ['Kertas HVS A4 80gsm',        'Kertas & Media Cetak',  'pak', $lab1Id, 10,   2,    0, 'Rak kertas'],
            ['Toner HP LaserJet Pro',      'Tinta & Toner',         'pcs', $lab1Id,  3,   1,    1, 'Lemari printer'],
            ['Spidol Whiteboard Hitam',    'Alat Tulis & Kantor',   'pak', $lab1Id,  4,   1,    0, 'Meja instruktur'],
            ['Penghapus Whiteboard',       'Alat Tulis & Kantor',   'pcs', $lab1Id,  6,   2,    0, 'Meja instruktur'],

            // Lab 2 — Elektronik & Kimia
            ['Timah Solder 60/40 0.8mm',   'Bahan Elektronik',      'pak', $lab2Id,  5,   1,    0, 'Meja soldering'],
            ['Flux Pasta Solder',          'Bahan Kimia',           'pcs', $lab2Id,  8,   2,    0, 'Meja soldering'],
            ['Compressed Air Spray',       'Sanitasi & Kebersihan', 'pcs', $lab2Id, 10,   3,    0, 'Rak B2'],
            ['Masker Debu N95',            'Perlengkapan Keselamatan', 'pak', $lab2Id, 3, 1,    0, 'Lemari APD'],
            ['Kabel UTP Cat6 (roll 50m)',   'Bahan Elektronik',      'unit',$lab2Id,  4,   1,    1, 'Gudang kabel'],
            ['RJ45 Connector Cat6',        'Bahan Elektronik',      'pak', $lab2Id,  6,   2,    0, 'Laci konektor'],
            ['Label Kabel Brother',        'Kertas & Media Cetak',  'pak', $lab2Id,  5,   1,    0, 'Meja labeling'],
            ['Tinta Printer Epson Black',  'Tinta & Toner',         'pcs', $lab2Id,  4,   1,    1, 'Lemari printer'],

            // Lab 3 — Media & Cetak
            ['Kertas Foto Glossy A4',      'Kertas & Media Cetak',  'pak', $lab3Id,  5,   1,    0, 'Rak media'],
            ['Tinta Sublimasi Cyan',       'Tinta & Toner',         'pcs', $lab3Id,  3,   1,    1, 'Lemari tinta'],
            ['Tinta Sublimasi Magenta',    'Tinta & Toner',         'pcs', $lab3Id,  3,   1,    1, 'Lemari tinta'],
            ['Tinta Sublimasi Yellow',     'Tinta & Toner',         'pcs', $lab3Id,  3,   1,    1, 'Lemari tinta'],
            ['Tinta Sublimasi Black',      'Tinta & Toner',         'pcs', $lab3Id,  3,   1,    1, 'Lemari tinta'],
            ['Cairan Pembersih Print Head','Bahan Kimia',           'pcs', $lab3Id,  4,   1,    0, 'Meja printer'],
            ['Spidol Permanen Hitam',      'Alat Tulis & Kantor',   'pak', $lab3Id,  3,   1,    0, 'Meja instruktur'],
        ];

        $table = $this->db->table('consumable_items');
        $now   = date('Y-m-d H:i:s');
        $count = 0;

        foreach ($items as [$name, $catName, $unitSym, $labId, $stock, $minStock, $requiresApproval, $location]) {
            // Skip jika sudah ada item dengan nama + lab yang sama
            $exists = $table->where('name', $name)->where('lab_id', $labId)->countAllResults();
            if ($exists > 0) {
                continue;
            }

            $catId  = $catMap[$catName]  ?? null;
            $unitId = $unitMap[$unitSym] ?? null;

            if ($catId === null || $unitId === null) {
                echo "  [SKIP] '{$name}' — kategori/satuan tidak ditemukan.\n";
                continue;
            }

            $table->insert([
                'name'              => $name,
                'category_id'       => $catId,
                'unit_id'           => $unitId,
                'lab_id'            => $labId,
                'stock_total'       => $stock,
                'stock_available'   => $stock,
                'min_stock'         => $minStock,
                'location'          => $location,
                'expiry_date'       => null,
                'requires_approval' => $requiresApproval,
                'notes'             => null,
                'is_active'         => 1,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $count++;
        }

        echo "ConsumableItemSeeder selesai. {$count} item BHP ditambahkan.\n";
    }
}
