<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class AssetSeeder extends Seeder
{
    public function run()
    {
        if (ENVIRONMENT !== 'development') {
            throw new RuntimeException('AssetSeeder hanya boleh dieksekusi pada environment development.');
        }

        if (! $this->db->tableExists('lab_assets')) {
            throw new RuntimeException('Tabel lab_assets belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        if (! $this->db->tableExists('labs')) {
            throw new RuntimeException('Tabel labs belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        if (! $this->db->tableExists('asset_categories')) {
            throw new RuntimeException('Tabel asset_categories belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $categoryMasterName = 'Alat Laboratorium';
        $categoryExists = $this->db->table('asset_categories')
            ->where('name', $categoryMasterName)
            ->countAllResults() > 0;

        if (! $categoryExists) {
            throw new RuntimeException('Kategori master "' . $categoryMasterName . '" belum tersedia. Jalankan AssetCategorySeeder terlebih dahulu.');
        }

        $labRows = $this->db->table('labs')
            ->select('id, code')
            ->whereIn('code', [
                'LAB-JK',
                'LAB-PA',
                'LAB-MM',
                'LAB-DATA',
                'LAB-HPC',
                'LAB-EGOV',
                'LAB-TETD',
                'LAB-SISKOM',
                'LAB-SWT',
                'LAB-MBIO',
                'LAB-PI',
                'LAB-IOE',
            ])
            ->get()
            ->getResultArray();

        $labMap = [];
        foreach ($labRows as $labRow) {
            $labMap[$labRow['code']] = (int) $labRow['id'];
        }

        $requiredLabCodes = [
            'LAB-JK',
            'LAB-PA',
            'LAB-MM',
            'LAB-DATA',
            'LAB-HPC',
            'LAB-EGOV',
            'LAB-TETD',
            'LAB-SISKOM',
            'LAB-SWT',
            'LAB-MBIO',
            'LAB-PI',
            'LAB-IOE',
        ];

        foreach ($requiredLabCodes as $requiredCode) {
            if (! isset($labMap[$requiredCode])) {
                throw new RuntimeException('Data lab dengan kode ' . $requiredCode . ' belum tersedia. Jalankan LabSeeder terlebih dahulu.');
            }
        }

        $assets = [
            [
                'lab_code'        => 'LAB-JK',
                'name'            => 'Router Mikrotik RB750Gr3',
                'category'        => 'Jaringan',
                'specifications'  => '5-port Gigabit Ethernet, CPU dual-core 880 MHz',
                'max_loan_hours'  => 0,
                'stock_total'     => 8,
                'stock_available' => 8,
                'is_active'       => 1,
            ],
            [
                'lab_code'        => 'LAB-PA',
                'name'            => 'Laptop Praktikum Pemrograman',
                'category'        => 'Komputasi',
                'specifications'  => 'Core i5, RAM 16GB, SSD 512GB',
                'max_loan_hours'  => 0,
                'stock_total'     => 20,
                'stock_available' => 20,
                'is_active'       => 1,
            ],
            [
                'lab_code'        => 'LAB-MM',
                'name'            => 'Kamera DSLR Canon 80D',
                'category'        => 'Multimedia',
                'specifications'  => 'Sensor APS-C 24.2MP, lensa kit 18-55mm',
                'max_loan_hours'  => 0,
                'stock_total'     => 6,
                'stock_available' => 6,
                'is_active'       => 1,
            ],
            [
                'lab_code'        => 'LAB-DATA',
                'name'            => 'Server Mini Analitik Data',
                'category'        => 'Server',
                'specifications'  => 'AMD EPYC, RAM 64GB, NVMe 2TB',
                'max_loan_hours'  => 0,
                'stock_total'     => 3,
                'stock_available' => 3,
                'is_active'       => 1,
            ],
            [
                'lab_code'        => 'LAB-HPC',
                'name'            => 'Node Komputasi GPU',
                'category'        => 'HPC',
                'specifications'  => 'Dual GPU RTX, RAM 128GB, CPU 32-core',
                'max_loan_hours'  => 0,
                'stock_total'     => 4,
                'stock_available' => 4,
                'is_active'       => 1,
            ],
            [
                'lab_code'        => 'LAB-EGOV',
                'name'            => 'Tablet Survei Lapangan',
                'category'        => 'Mobile',
                'specifications'  => 'Android 10 inch, RAM 6GB, storage 128GB',
                'max_loan_hours'  => 0,
                'stock_total'     => 10,
                'stock_available' => 10,
                'is_active'       => 1,
            ],
            [
                'lab_code'        => 'LAB-TETD',
                'name'            => 'Kit Mikrokontroler STM32',
                'category'        => 'Elektronika',
                'specifications'  => 'Development board STM32 + sensor set',
                'max_loan_hours'  => 0,
                'stock_total'     => 12,
                'stock_available' => 12,
                'is_active'       => 1,
            ],
            [
                'lab_code'        => 'LAB-SISKOM',
                'name'            => 'Spectrum Analyzer Portable',
                'category'        => 'Telekomunikasi',
                'specifications'  => '9kHz-3GHz, battery-powered',
                'max_loan_hours'  => 0,
                'stock_total'     => 2,
                'stock_available' => 2,
                'is_active'       => 1,
            ],
            [
                'lab_code'        => 'LAB-SWT',
                'name'            => 'Managed Switch Layer 3',
                'category'        => 'Jaringan',
                'specifications'  => '24-port Gigabit, VLAN, QoS, OSPF',
                'max_loan_hours'  => 0,
                'stock_total'     => 5,
                'stock_available' => 5,
                'is_active'       => 1,
            ],
            [
                'lab_code'        => 'LAB-MBIO',
                'name'            => 'Digital Microscope',
                'category'        => 'Biomedis',
                'specifications'  => 'Magnification up to 1600x, USB output',
                'max_loan_hours'  => 0,
                'stock_total'     => 4,
                'stock_available' => 4,
                'is_active'       => 1,
            ],
            [
                'lab_code'        => 'LAB-PI',
                'name'            => 'Digital Oscilloscope 100MHz',
                'category'        => 'Instrumentasi',
                'specifications'  => '2-channel, sample rate 1GSa/s',
                'max_loan_hours'  => 0,
                'stock_total'     => 6,
                'stock_available' => 6,
                'is_active'       => 1,
            ],
            [
                'lab_code'        => 'LAB-IOE',
                'name'            => 'IoT Sensor Starter Kit',
                'category'        => 'IoT',
                'specifications'  => 'ESP32 + sensor suhu, kelembapan, relay, gateway',
                'max_loan_hours'  => 0,
                'stock_total'     => 15,
                'stock_available' => 15,
                'is_active'       => 1,
            ],
        ];

        $table = $this->db->table('lab_assets');
        $now   = date('Y-m-d H:i:s');

        foreach ($assets as $asset) {
            $labId = $labMap[$asset['lab_code']];
            $assetCategory = $categoryMasterName;

            $existing = $table
                ->where('asset_type', 'equipment')
                ->where('lab_id', $labId)
                ->where('name', $asset['name'])
                ->get()
                ->getRowArray();

            if ($existing) {
                $table
                    ->where('id', (int) $existing['id'])
                    ->update([
                        'category'        => $assetCategory,
                        'specifications'  => $asset['specifications'],
                        'max_loan_hours'  => $asset['max_loan_hours'],
                        'stock_total'     => $asset['stock_total'],
                        'stock_available' => $asset['stock_available'],
                        'is_active'       => $asset['is_active'],
                        'is_loanable'     => 1,
                        'condition_status'=> 'baik',
                        'updated_at'      => $now,
                    ]);
                continue;
            }

            $table->insert([
                'name'            => $asset['name'],
                'lab_id'          => $labId,
                'asset_type'      => 'equipment',
                'category'        => $assetCategory,
                'location'        => null,
                'specifications'  => $asset['specifications'],
                'max_loan_hours'  => $asset['max_loan_hours'],
                'stock_total'     => $asset['stock_total'],
                'stock_available' => $asset['stock_available'],
                'is_active'       => $asset['is_active'],
                'is_loanable'     => 1,
                'condition_status'=> 'baik',
                'created_by'      => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        echo "AssetSeeder selesai. Data master alat berhasil disiapkan.\n";
    }
}
