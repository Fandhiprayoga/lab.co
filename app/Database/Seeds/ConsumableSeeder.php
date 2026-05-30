<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Orchestrator untuk seluruh data seed modul BHP.
 *
 * Urutan eksekusi:
 *   1. UnitSeeder              — master satuan (diperlukan oleh item)
 *   2. ConsumableCategorySeeder — kategori BHP
 *   3. ConsumableItemSeeder     — item per lab (butuh lab, unit, kategori)
 *
 * Jalankan dengan:
 *   php spark db:seed ConsumableSeeder
 */
class ConsumableSeeder extends Seeder
{
    public function run()
    {
        $this->call(UnitSeeder::class);
        $this->call(ConsumableCategorySeeder::class);
        $this->call(ConsumableItemSeeder::class);

        echo "\nConsumableSeeder selesai. Semua data BHP telah di-seed.\n";
    }
}
