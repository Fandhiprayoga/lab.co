<?php

namespace App\Config;

/**
 * Daftar statis semua menu aplikasi beserta permission yang dibutuhkan.
 * Digunakan oleh fitur pencarian menu di navbar.
 */
class MenuRegistry
{
    /**
     * Kembalikan semua item menu.
     *
     * Struktur tiap item:
     *  - label      : teks menu yang ditampilkan
     *  - url        : path relatif (tanpa base_url)
     *  - icon       : class FontAwesome
     *  - permission : permission string (activeGroupCan), atau null jika bebas
     *  - group      : nama group yang boleh mengakses (activeGroupIs), atau null
     *
     * @return array<int, array{label:string, url:string, icon:string, permission:string|null, group:string|null}>
     */
    public static function all(): array
    {
        return [
            // -------------------------------------------------------
            // Dashboard
            // -------------------------------------------------------
            [
                'label'      => 'Dashboard',
                'url'        => 'dashboard',
                'icon'       => 'fas fa-fire',
                'permission' => null,
                'group'      => null,
            ],

            // -------------------------------------------------------
            // Peminjaman Lab
            // -------------------------------------------------------
            [
                'label'      => 'Buat Proposal Peminjaman',
                'url'        => 'loans/create',
                'icon'       => 'fas fa-file-alt',
                'permission' => 'lending.request.create',
                'group'      => null,
            ],
            [
                'label'      => 'Permohonan Peminjaman',
                'url'        => 'loans',
                'icon'       => 'fas fa-clipboard-list',
                'permission' => 'lending.request.track',
                'group'      => null,
            ],
            [
                'label'      => 'Analitik Lab',
                'url'        => 'loans/analytics',
                'icon'       => 'fas fa-chart-line',
                'permission' => 'lending.analytics.view',
                'group'      => null,
            ],

            // -------------------------------------------------------
            // Administrasi
            // -------------------------------------------------------
            [
                'label'      => 'Manajemen User',
                'url'        => 'admin/users',
                'icon'       => 'fas fa-users',
                'permission' => 'users.list',
                'group'      => null,
            ],
            [
                'label'      => 'Tambah User',
                'url'        => 'admin/users/create',
                'icon'       => 'fas fa-user-plus',
                'permission' => 'users.create',
                'group'      => null,
            ],
            [
                'label'      => 'Daftar Role',
                'url'        => 'admin/roles',
                'icon'       => 'fas fa-user-shield',
                'permission' => null,
                'group'      => 'superadmin',
            ],
            [
                'label'      => 'Permission Matrix',
                'url'        => 'admin/roles/permissions',
                'icon'       => 'fas fa-shield-alt',
                'permission' => null,
                'group'      => 'superadmin',
            ],
            [
                'label'      => 'Pengaturan',
                'url'        => 'admin/settings',
                'icon'       => 'fas fa-cog',
                'permission' => 'admin.settings',
                'group'      => null,
            ],

            // -------------------------------------------------------
            // Master Data
            // -------------------------------------------------------
            [
                'label'      => 'Master Fakultas',
                'url'        => 'admin/loans/faculties',
                'icon'       => 'fas fa-university',
                'permission' => 'lending.master.faculties.manage',
                'group'      => null,
            ],
            [
                'label'      => 'Master Program Studi',
                'url'        => 'admin/loans/study-programs',
                'icon'       => 'fas fa-graduation-cap',
                'permission' => 'lending.master.study_programs.manage',
                'group'      => null,
            ],
            [
                'label'      => 'Master Satuan',
                'url'        => 'admin/loans/units',
                'icon'       => 'fas fa-ruler',
                'permission' => 'lending.master.units.manage',
                'group'      => null,
            ],

            // -------------------------------------------------------
            // Ruangan & Lab
            // -------------------------------------------------------
            [
                'label'      => 'Daftar Lab Aktif',
                'url'        => 'admin/loans/labs',
                'icon'       => 'fas fa-door-open',
                'permission' => 'lending.master.labs.manage',
                'group'      => null,
            ],
            [
                'label'      => 'Arsip Lab',
                'url'        => 'admin/loans/labs/archive',
                'icon'       => 'fas fa-archive',
                'permission' => 'lending.master.labs.manage',
                'group'      => null,
            ],
            [
                'label'      => 'QR Codes Lab',
                'url'        => 'admin/loans/labs/qr',
                'icon'       => 'fas fa-qrcode',
                'permission' => 'lending.master.labs.manage',
                'group'      => null,
            ],
            [
                'label'      => 'Riwayat Kondisi Lab',
                'url'        => 'admin/loans/labs/condition-history',
                'icon'       => 'fas fa-history',
                'permission' => 'lending.master.labs.manage',
                'group'      => null,
            ],

            // -------------------------------------------------------
            // Manajemen Aset
            // -------------------------------------------------------
            [
                'label'      => 'Kategori Alat',
                'url'        => 'admin/loans/asset-categories',
                'icon'       => 'fas fa-tags',
                'permission' => 'lending.master.manage',
                'group'      => null,
            ],
            [
                'label'      => 'Daftar Alat',
                'url'        => 'admin/loans/assets',
                'icon'       => 'fas fa-tools',
                'permission' => 'lending.master.manage',
                'group'      => null,
            ],
            [
                'label'      => 'Mutasi Aset',
                'url'        => 'admin/loans/movements',
                'icon'       => 'fas fa-exchange-alt',
                'permission' => 'lending.master.movements.manage',
                'group'      => null,
            ],
            [
                'label'      => 'Perawatan Aset',
                'url'        => 'admin/loans/maintenances',
                'icon'       => 'fas fa-wrench',
                'permission' => 'lending.master.maintenances.manage',
                'group'      => null,
            ],
            [
                'label'      => 'Dokumen Aset',
                'url'        => 'admin/loans/documents',
                'icon'       => 'fas fa-folder-open',
                'permission' => 'lending.master.documents.manage',
                'group'      => null,
            ],
            [
                'label'      => 'QR Code Alat',
                'url'        => 'admin/loans/assets/qr',
                'icon'       => 'fas fa-qrcode',
                'permission' => 'lending.master.manage',
                'group'      => null,
            ],

            // -------------------------------------------------------
            // Kunjungan
            // -------------------------------------------------------
            [
                'label'      => 'Buku Kunjungan',
                'url'        => 'admin/visits',
                'icon'       => 'fas fa-book-open',
                'permission' => 'visits.list',
                'group'      => null,
            ],

            // -------------------------------------------------------
            // Akun
            // -------------------------------------------------------
            [
                'label'      => 'Profil Saya',
                'url'        => 'profile',
                'icon'       => 'far fa-user',
                'permission' => null,
                'group'      => null,
            ],
        ];
    }
}
