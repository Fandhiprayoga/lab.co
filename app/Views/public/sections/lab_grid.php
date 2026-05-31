<?php
$appName = $appName ?? setting('App.siteName') ?? 'LabCorner';
$showCta = $showCta ?? false;
$showFacultyFilter = $showFacultyFilter ?? false;
$faculties = $faculties ?? [];
$selectedFaculty = $selectedFaculty ?? null;
$gridClass = $gridClass ?? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8';
$cardLayout = $cardLayout ?? 'compact';
$labs = $labs ?? [];
$emptyMessage = $emptyMessage ?? 'Belum ada data laboratorium';
$emptyDescription = $emptyDescription ?? 'Data laboratorium akan muncul setelah diisi melalui menu master laboratorium.';
$ctaLabel = $ctaLabel ?? 'Lihat Seluruh Laboratorium';
$ctaUrl = $ctaUrl ?? site_url('laboratorium');

$labDisplayMeta = [
    ['icon' => 'ph-code', 'icon_bg' => 'bg-brand-50 text-brand-600'],
    ['icon' => 'ph-wifi-high', 'icon_bg' => 'bg-blue-50 text-blue-600'],
    ['icon' => 'ph-cpu', 'icon_bg' => 'bg-yellow-50 text-yellow-600'],
    ['icon' => 'ph-brain', 'icon_bg' => 'bg-purple-50 text-purple-600'],
    ['icon' => 'ph-database', 'icon_bg' => 'bg-emerald-50 text-emerald-600'],
    ['icon' => 'ph-video-camera', 'icon_bg' => 'bg-cyan-50 text-cyan-600'],
];
?>
<section id="laboratorium" class="pt-32 pb-20 lg:pt-48 lg:pb-32 bg-gray-50 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <h2 class="text-brand-600 font-semibold tracking-wide uppercase text-sm mb-3">Daftar Laboratorium</h2>
            <p class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">Laboratorium Terintegrasi</p>
            <p class="text-lg text-gray-600">Pilih dan kelola berbagai laboratorium yang tersedia dalam satu ekosistem <?= esc($appName) ?>.</p>
        </div>

        <?php if ($showFacultyFilter): ?>
        <form method="get" action="<?= site_url('laboratorium') ?>" class="max-w-3xl mx-auto mb-10">
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-4 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="flex-1">
                        <label for="faculty" class="block text-sm font-semibold text-gray-700 mb-2">Filter Fakultas</label>
                        <select id="faculty" name="faculty" class="w-full rounded-2xl border-gray-200 focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Semua fakultas</option>
                            <?php foreach ($faculties as $faculty): ?>
                                <option value="<?= esc($faculty['id']) ?>" <?= (string) $selectedFaculty === (string) $faculty['id'] ? 'selected' : '' ?>>
                                    <?= esc($faculty['name'] ?? '-') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="px-5 py-3 rounded-2xl bg-gray-900 text-white font-semibold hover:bg-gray-800 transition-colors">
                            Terapkan
                        </button>
                        <a href="<?= site_url('laboratorium') ?>" class="px-5 py-3 rounded-2xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition-colors">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
        <?php endif; ?>

        <div class="<?= esc($gridClass) ?>">
            <?php if (! empty($labs)): ?>
                <?php foreach ($labs as $index => $lab): ?>
                    <?php
                        $meta = $labDisplayMeta[$index % count($labDisplayMeta)];
                        $logoUrl = ! empty($lab['logo']) ? base_url($lab['logo']) : base_url('assets/img/stisla-fill.svg');
                        $statusRaw = strtolower(trim((string) ($lab['condition_status'] ?? 'aktif')));
                        $statusLabel = $lab['condition_status'] ? ucwords(str_replace('_', ' ', $lab['condition_status'])) : 'Aktif';

                        if (str_contains($statusRaw, 'maint')) {
                            $statusClass = 'bg-yellow-100 text-yellow-700';
                        } elseif (str_contains($statusRaw, 'inactive') || str_contains($statusRaw, 'non') || str_contains($statusRaw, 'rusak')) {
                            $statusClass = 'bg-gray-100 text-gray-600';
                        } else {
                            $statusClass = 'bg-green-100 text-green-700';
                        }
                    ?>
                    <div class="bg-white rounded-3xl p-6 shadow-lg border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group cursor-pointer">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 rounded-xl overflow-hidden bg-white border border-gray-100 flex items-center justify-center transition-colors shadow-sm">
                                <img src="<?= esc($logoUrl) ?>" alt="Logo <?= esc($lab['name'] ?? 'Lab') ?>" class="w-full h-full object-contain p-1.5">
                            </div>
                            <span class="text-xs font-semibold px-3 py-1 rounded-full <?= esc($statusClass) ?>"><?= esc($statusLabel) ?></span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?= esc($lab['name'] ?? '-') ?></h3>
                        <p class="text-gray-500 text-sm mb-4">
                            <?= esc($lab['location'] ?? 'Lokasi belum tersedia') ?>
                            <?php if (! empty($lab['faculty_name'])): ?>
                                <br>
                                <span class="text-gray-400"><?= esc($lab['faculty_name']) ?></span>
                            <?php endif; ?>
                        </p>
                        <div class="flex justify-between items-center text-sm font-medium text-gray-700 pt-4 border-t border-gray-50">
                            <div class="flex items-center gap-1.5">
                                <i class="ph ph-hash text-brand-500"></i>
                                <?= esc($lab['code'] ?? '-') ?>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <i class="ph ph-users text-brand-500"></i>
                                <?= esc($lab['capacity'] ?? '-') ?> Kursi
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full bg-white rounded-3xl p-10 text-center border border-dashed border-gray-200">
                    <div class="w-14 h-14 mx-auto mb-4 bg-gray-100 text-gray-400 rounded-2xl flex items-center justify-center">
                        <i class="ph ph-buildings text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2"><?= esc($emptyMessage) ?></h3>
                    <p class="text-gray-600"><?= esc($emptyDescription) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($showCta): ?>
            <div class="text-center mt-12">
                <a href="<?= esc($ctaUrl) ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-gray-200 hover:border-brand-500 hover:text-brand-600 text-gray-700 rounded-xl font-semibold transition-all shadow-sm">
                    <?= esc($ctaLabel) ?> <i class="ph ph-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>