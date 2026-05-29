<?php
$appName = $appName ?? setting('App.siteName') ?? 'LabCorner';
$appNameShort = $appNameShort ?? setting('App.siteNameShort') ?? $appName;
$isLandingPage = uri_string() === '';
$labMenuUrl = $isLandingPage ? '#laboratorium' : site_url('laboratorium');
?>
<nav class="fixed w-full z-50 glass-nav transition-all duration-300" id="navbar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <a href="<?= site_url('/') ?>" class="flex-shrink-0 flex items-center gap-2 cursor-pointer">
                <div class="w-10 h-10 bg-brand-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-brand-500/30">
                    <i class="ph ph-flask text-2xl"></i>
                </div>
                <span class="font-bold text-2xl tracking-tight text-gray-900"><?= esc($appName) ?><span class="text-brand-500">.</span></span>
            </a>

            <div class="hidden md:flex space-x-8 items-center">
                <a href="<?= site_url('/') ?>#beranda" data-spy-link="beranda" class="public-nav-link text-gray-600 hover:text-brand-600 font-medium transition-colors">Beranda</a>
                <a href="<?= site_url('/') ?>#layanan" data-spy-link="layanan" class="public-nav-link text-gray-600 hover:text-brand-600 font-medium transition-colors">Layanan</a>
                <a href="<?= site_url('/') ?>#laboratorium" data-spy-link="laboratorium" class="public-nav-link text-gray-600 hover:text-brand-600 font-medium transition-colors">Laboratorium</a>
                <a href="<?= site_url('/') ?>#kontak" data-spy-link="kontak" class="public-nav-link text-gray-600 hover:text-brand-600 font-medium transition-colors">Kontak</a>
                <?php if (auth()->loggedIn()): ?>
                <a href="<?= site_url('dashboard') ?>" class="bg-brand-600 hover:bg-brand-700 text-white px-6 py-2.5 rounded-full font-medium transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5 flex items-center gap-1.5"><i class="ph ph-squares-four"></i> Dashboard</a>
                <?php else: ?>
                <a href="<?= site_url('login') ?>" class="bg-gray-900 hover:bg-gray-800 text-white px-6 py-2.5 rounded-full font-medium transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">Login</a>
                <?php endif; ?>
            </div>

            <div class="md:hidden flex items-center">
                <button id="mobile-menu-btn" class="text-gray-600 hover:text-gray-900 focus:outline-none p-2" type="button" aria-label="Buka menu">
                    <i class="ph ph-list text-3xl"></i>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100 absolute w-full shadow-lg">
        <div class="px-4 pt-2 pb-6 space-y-2">
            <a href="<?= site_url('/') ?>#beranda" data-spy-link="beranda" class="public-nav-link block px-3 py-3 text-base font-medium text-gray-700 hover:text-brand-600 hover:bg-gray-50 rounded-lg">Beranda</a>
            <a href="<?= site_url('/') ?>#layanan" data-spy-link="layanan" class="public-nav-link block px-3 py-3 text-base font-medium text-gray-700 hover:text-brand-600 hover:bg-gray-50 rounded-lg">Layanan</a>
            <a href="<?= site_url('/') ?>#laboratorium" data-spy-link="laboratorium" class="public-nav-link block px-3 py-3 text-base font-medium text-gray-700 hover:text-brand-600 hover:bg-gray-50 rounded-lg">Laboratorium</a>
            <a href="<?= site_url('/') ?>#kontak" data-spy-link="kontak" class="public-nav-link block px-3 py-3 text-base font-medium text-gray-700 hover:text-brand-600 hover:bg-gray-50 rounded-lg">Kontak</a>
            <?php if (auth()->loggedIn()): ?>
            <a href="<?= site_url('dashboard') ?>" class="block w-full text-center mt-4 bg-brand-600 text-white px-6 py-3 rounded-xl font-medium shadow-md flex items-center justify-center gap-2"><i class="ph ph-squares-four"></i> Dashboard</a>
            <?php else: ?>
            <a href="<?= site_url('login') ?>" class="block w-full text-center mt-4 bg-brand-600 text-white px-6 py-3 rounded-xl font-medium shadow-md">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>