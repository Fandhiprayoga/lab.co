<?php
$appName = $appName ?? setting('App.siteName') ?? 'LabCorner';
$appNameShort = $appNameShort ?? setting('App.siteNameShort') ?? $appName;
?>
<section id="beranda" class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-[20%] -right-[10%] w-[70%] h-[70%] rounded-full bg-brand-50 blur-3xl opacity-60"></div>
        <div class="absolute top-[40%] -left-[10%] w-[50%] h-[50%] rounded-full bg-blue-50 blur-3xl opacity-60"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brand-50 text-brand-600 font-medium text-sm mb-8 border border-brand-100 shadow-sm">
            <span class="flex h-2 w-2 rounded-full bg-brand-500 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
            </span>
            Sistem Manajemen Lab Terpadu <?= esc($appNameShort) ?> v2.0
        </div>

        <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-gray-900 tracking-tight leading-tight mb-8">
            Kelola Laboratorium,<br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-500 to-rose-600">Tanpa Kerumitan.</span>
        </h1>

        <p class="mt-6 text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed mb-10">
            <?= esc($appName) ?> menyatukan manajemen asisten, rekrutmen, peminjaman alat, papan informasi, hingga urusan honor dalam satu platform minimalis.
        </p>

        <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
            <?php if (auth()->loggedIn()): ?>
            <a href="<?= site_url('dashboard') ?>" class="w-full sm:w-auto px-8 py-4 bg-brand-600 hover:bg-brand-700 text-white rounded-2xl font-semibold text-lg transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-1 flex items-center justify-center gap-2">
                <i class="ph ph-squares-four text-xl"></i> Ke Dashboard
            </a>
            <?php else: ?>
            <a href="<?= site_url('login') ?>" class="w-full sm:w-auto px-8 py-4 bg-gray-900 hover:bg-gray-800 text-white rounded-2xl font-semibold text-lg transition-all shadow-xl hover:shadow-2xl transform hover:-translate-y-1 flex items-center justify-center gap-2">
                Mulai Sekarang <i class="ph ph-arrow-right font-bold"></i>
            </a>
            <?php endif; ?>
            <a href="<?= site_url('laboratorium') ?>" class="w-full sm:w-auto px-8 py-4 bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 rounded-2xl font-semibold text-lg transition-all shadow-sm flex items-center justify-center gap-2">
                Lihat Semua Lab <i class="ph ph-buildings text-xl"></i>
            </a>
        </div>

        <div class="mt-20 relative max-w-4xl mx-auto">
            <div class="absolute -inset-x-4 -inset-y-4 z-0 bg-gradient-to-r from-brand-100 via-blue-50 to-teal-100 blur-2xl opacity-70 rounded-3xl pointer-events-none"></div>

            <div class="relative z-10 grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                <div class="bg-white/90 backdrop-blur-md rounded-2xl p-6 shadow-xl shadow-gray-200/50 border border-white/50 flex flex-col items-center justify-center text-center transform hover:-translate-y-1 transition-transform duration-300">
                    <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mb-4 shadow-inner">
                        <i class="ph ph-users text-2xl"></i>
                    </div>
                    <h3 class="text-3xl font-extrabold text-gray-900 mb-1">120+</h3>
                    <p class="text-sm text-gray-500 font-medium">Asisten Aktif</p>
                </div>
                <div class="bg-white/90 backdrop-blur-md rounded-2xl p-6 shadow-xl shadow-gray-200/50 border border-white/50 flex flex-col items-center justify-center text-center transform hover:-translate-y-1 transition-transform duration-300 delay-75">
                    <div class="w-12 h-12 bg-brand-50 text-brand-500 rounded-full flex items-center justify-center mb-4 shadow-inner">
                        <i class="ph ph-door text-2xl"></i>
                    </div>
                    <h3 class="text-3xl font-extrabold text-gray-900 mb-1">15</h3>
                    <p class="text-sm text-gray-500 font-medium">Ruangan Lab</p>
                </div>
                <div class="bg-white/90 backdrop-blur-md rounded-2xl p-6 shadow-xl shadow-gray-200/50 border border-white/50 flex flex-col items-center justify-center text-center transform hover:-translate-y-1 transition-transform duration-300 delay-150">
                    <div class="w-12 h-12 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mb-4 shadow-inner">
                        <i class="ph ph-wrench text-2xl"></i>
                    </div>
                    <h3 class="text-3xl font-extrabold text-gray-900 mb-1">450</h3>
                    <p class="text-sm text-gray-500 font-medium">Inventaris Alat</p>
                </div>
                <div class="bg-white/90 backdrop-blur-md rounded-2xl p-6 shadow-xl shadow-gray-200/50 border border-white/50 flex flex-col items-center justify-center text-center transform hover:-translate-y-1 transition-transform duration-300 delay-200">
                    <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-full flex items-center justify-center mb-4 shadow-inner">
                        <i class="ph ph-calendar-check text-2xl"></i>
                    </div>
                    <h3 class="text-3xl font-extrabold text-gray-900 mb-1">2.5k</h3>
                    <p class="text-sm text-gray-500 font-medium">Sesi Praktikum</p>
                </div>
            </div>
        </div>
    </div>
</section>