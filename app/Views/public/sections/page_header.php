<section id="laboratorium" class="relative pt-32 pb-12 lg:pt-40 lg:pb-16 overflow-hidden">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-[18%] -right-[10%] w-[55%] h-[55%] rounded-full bg-brand-50 blur-3xl opacity-60"></div>
        <div class="absolute top-[35%] -left-[12%] w-[40%] h-[40%] rounded-full bg-blue-50 blur-3xl opacity-60"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brand-50 text-brand-600 font-medium text-sm mb-8 border border-brand-100 shadow-sm">
            <span class="flex h-2 w-2 rounded-full bg-brand-500 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
            </span>
            <?= esc($pageBadge ?? 'Daftar Laboratorium') ?>
        </div>

        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight leading-tight mb-5">
            <?= esc($pageTitle ?? 'Laboratorium') ?>
        </h1>

        <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
            <?= esc($pageSubtitle ?? 'Seluruh laboratorium aktif yang tersedia ditampilkan di bawah ini.') ?>
        </p>

        <?php if (isset($labCount)): ?>
        <div class="mt-8 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white text-gray-700 font-medium text-sm border border-gray-200 shadow-sm">
            <i class="ph ph-buildings text-brand-600"></i>
            <?= esc($labCount) ?> laboratorium aktif
        </div>
        <?php endif; ?>
    </div>
</section>