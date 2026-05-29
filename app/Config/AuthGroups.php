<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     */
    public string $defaultGroup = 'user';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     */
    public array $groups = [
        'superadmin' => [
            'title'       => 'Super Admin',
            'description' => 'Kontrol penuh terhadap seluruh sistem.',
        ],
        'admin' => [
            'title'       => 'Admin',
            'description' => 'Administrator harian sistem.',
        ],
        'manager' => [
            'title'       => 'Manager',
            'description' => 'Manajer yang dapat melihat laporan dan mengelola data.',
        ],
        'user' => [
            'title'       => 'User',
            'description' => 'Pengguna umum dengan akses terbatas.',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     */
    public array $permissions = [
        // Admin area
        'admin.access'        => 'Dapat mengakses area admin',
        'admin.settings'      => 'Dapat mengakses pengaturan sistem',

        // User management
        'users.list'          => 'Dapat melihat daftar pengguna',
        'users.create'        => 'Dapat membuat pengguna baru',
        'users.edit'          => 'Dapat mengedit pengguna',
        'users.delete'        => 'Dapat menghapus pengguna',
        'users.manage-roles'  => 'Dapat mengatur role pengguna',

        // Role management
        'roles.list'          => 'Dapat melihat daftar role',
        'roles.create'        => 'Dapat membuat role baru',
        'roles.edit'          => 'Dapat mengedit role',
        'roles.delete'        => 'Dapat menghapus role',

        // Dashboard
        'dashboard.access'    => 'Dapat mengakses dashboard',
        'dashboard.stats'     => 'Dapat melihat statistik',

        // Reports
        'reports.view'        => 'Dapat melihat laporan',
        'reports.export'      => 'Dapat mengekspor laporan',

        // Lending module
        'lending.access'              => 'Dapat mengakses modul peminjaman',
        'lending.catalog.view'        => 'Dapat melihat katalog aset lab',
        'lending.request.create'      => 'Dapat membuat permohonan peminjaman',
        'lending.request.submit'      => 'Dapat mengirim proposal peminjaman ke approval',
        'lending.request.cancel'      => 'Dapat membatalkan permohonan peminjaman',
        'lending.request.track'       => 'Dapat melacak status dan melihat detail peminjaman',
        'lending.request.history'     => 'Dapat melihat riwayat peminjaman',
        'lending.request.manage-all'  => 'Dapat mengelola seluruh permohonan peminjaman',
        'lending.approval.l1'         => 'Dapat approval level 1 sebagai laboran',
        'lending.approval.l2'         => 'Dapat approval level 2 sebagai kepala lab',
        'lending.checkout'            => 'Dapat memproses check-out alat/ruang',
        'lending.checkin'             => 'Dapat memproses check-in pengembalian',
        'lending.issue.report'        => 'Dapat mencatat laporan kerusakan/kehilangan',
        'lending.analytics.view'      => 'Dapat melihat analitik pemanfaatan lab',
        'lending.master.manage'       => 'Dapat mengelola master data modul peminjaman',
        'lending.master.labs.manage'  => 'Dapat mengelola master data ruangan/lab',
        'lending.master.faculties.manage' => 'Dapat mengelola master data fakultas',
        'lending.master.study_programs.manage' => 'Dapat mengelola master data program studi',
        'lending.master.units.manage' => 'Dapat mengelola master data satuan',
        'lending.master.movements.manage' => 'Dapat mengelola catatan mutasi aset',
        'lending.master.maintenances.manage' => 'Dapat mengelola catatan pemeliharaan aset',
        'lending.master.documents.manage' => 'Dapat mengelola dokumen lampiran aset',

        // Visitor log
        'visits.list'   => 'Dapat melihat buku kunjungan lab',
        'visits.manage' => 'Dapat mengelola data kunjungan lab',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     */
    public array $matrix = [
        'superadmin' => [
            'admin.*',
            'users.*',
            'roles.*',
            'dashboard.*',
            'reports.*',
            'lending.*',
            'visits.*',
        ],
        'admin' => [
            'admin.access',
            'users.list',
            'users.create',
            'users.edit',
            'users.delete',
            'dashboard.*',
            'reports.*',
            'lending.access',
            'lending.catalog.view',
            'lending.request.track',
            'lending.request.history',
            'lending.request.manage-all',
            'lending.request.submit',
            'lending.approval.l1',
            'lending.checkout',
            'lending.checkin',
            'lending.issue.report',
            'lending.analytics.view',
            'lending.master.manage',
            'lending.master.labs.manage',
            'lending.master.faculties.manage',
            'lending.master.study_programs.manage',
            'lending.master.units.manage',
            'lending.master.movements.manage',
            'lending.master.maintenances.manage',
            'lending.master.documents.manage',
            'visits.list',
            'visits.manage',
        ],
        'manager' => [
            'admin.access',
            'users.list',
            'dashboard.*',
            'reports.*',
            'lending.access',
            'lending.catalog.view',
            'lending.request.track',
            'lending.request.history',
            'lending.request.manage-all',
            'lending.approval.l2',
            'lending.analytics.view',
            'visits.list',
        ],
        'user' => [
            'dashboard.access',
            'lending.access',
            'lending.catalog.view',
            'lending.request.create',
            'lending.request.cancel',
            'lending.request.track',
            'lending.request.history',
            'lending.request.submit',
        ],
    ];
}
