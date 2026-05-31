<?= $this->extend('layouts/public') ?>

<?= $this->section('css') ?>
<style>
    .photo-gallery-thumb {
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .photo-gallery-thumb:hover {
        transform: scale(1.03);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    .photo-gallery-thumb.active {
        ring: 2px;
        outline: 3px solid #cc141c;
        outline-offset: 2px;
    }
    #lightbox {
        animation: fadeIn 0.2s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $appName     = setting('App.siteName') ?? 'LabCorner';
    $statusRaw   = strtolower(trim((string) ($lab['condition_status'] ?? 'aktif')));
    $statusLabel = $lab['condition_status'] ? ucwords(str_replace('_', ' ', $lab['condition_status'])) : 'Aktif';

    if (str_contains($statusRaw, 'maint')) {
        $statusClass = 'bg-yellow-100 text-yellow-700 border-yellow-200';
    } elseif (str_contains($statusRaw, 'inactive') || str_contains($statusRaw, 'non') || str_contains($statusRaw, 'rusak')) {
        $statusClass = 'bg-gray-100 text-gray-600 border-gray-200';
    } else {
        $statusClass = 'bg-green-100 text-green-700 border-green-200';
    }

    $logoUrl      = ! empty($lab['logo']) ? base_url($lab['logo']) : base_url('assets/img/stisla-fill.svg');
    $primaryPhoto = null;
    $otherPhotos  = [];

    foreach ($photos as $photo) {
        if ($photo['is_primary'] && $primaryPhoto === null) {
            $primaryPhoto = $photo;
        } else {
            $otherPhotos[] = $photo;
        }
    }

    $allPhotos     = $primaryPhoto ? array_merge([$primaryPhoto], $otherPhotos) : $otherPhotos;
    $hasPhotos     = ! empty($allPhotos);
    $mainPhotoUrl  = $hasPhotos ? base_url($allPhotos[0]['file_path']) : null;
?>

<!-- Page Header -->
<section class="relative pt-32 pb-10 lg:pt-40 lg:pb-14 overflow-hidden">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full -z-10 overflow-hidden pointer-events-none">
        <div class="absolute -top-[18%] -right-[10%] w-[55%] h-[55%] rounded-full bg-brand-50 blur-3xl opacity-60"></div>
        <div class="absolute top-[35%] -left-[12%] w-[40%] h-[40%] rounded-full bg-blue-50 blur-3xl opacity-60"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="<?= site_url('laboratorium') ?>" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-brand-600 transition-colors mb-6">
            <i class="ph ph-arrow-left"></i> Kembali ke Daftar Laboratorium
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
            <div class="w-16 h-16 rounded-2xl overflow-hidden bg-white border border-gray-100 shadow-md flex items-center justify-center flex-shrink-0">
                <img src="<?= esc($logoUrl) ?>" alt="Logo <?= esc($lab['name']) ?>" class="w-full h-full object-contain p-2">
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-3 mb-2">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full bg-brand-50 text-brand-600 border border-brand-100">
                        <?= esc($lab['code'] ?? '-') ?>
                    </span>
                    <span class="inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full border <?= esc($statusClass) ?>">
                        <?= esc($statusLabel) ?>
                    </span>
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight">
                    <?= esc($lab['name']) ?>
                </h1>
                <?php if (! empty($lab['location'])): ?>
                <p class="mt-2 text-gray-500 flex items-center gap-1.5">
                    <i class="ph ph-map-pin text-brand-500"></i>
                    <?= esc($lab['location']) ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="pb-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">

            <!-- Left: Photo Gallery -->
            <div class="lg:col-span-2 space-y-4">
                <?php if ($hasPhotos): ?>
                    <!-- Main photo display -->
                    <div class="relative bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100 aspect-video">
                        <img
                            id="main-photo"
                            src="<?= esc(base_url($allPhotos[0]['file_path'])) ?>"
                            alt="<?= esc($allPhotos[0]['caption'] ?? $lab['name']) ?>"
                            class="w-full h-full object-cover cursor-zoom-in"
                            onclick="openLightbox(0)"
                        >
                        <?php if (! empty($allPhotos[0]['caption'])): ?>
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent px-5 py-4">
                            <p id="main-caption" class="text-white text-sm font-medium"><?= esc($allPhotos[0]['caption']) ?></p>
                        </div>
                        <?php else: ?>
                        <div id="main-caption-wrapper" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent px-5 py-4 hidden">
                            <p id="main-caption" class="text-white text-sm font-medium"></p>
                        </div>
                        <?php endif; ?>
                        <button onclick="openLightbox(0)" class="absolute top-4 right-4 w-9 h-9 bg-black/40 hover:bg-black/60 text-white rounded-xl flex items-center justify-center transition-colors backdrop-blur-sm">
                            <i class="ph ph-arrows-out text-lg"></i>
                        </button>
                    </div>

                    <!-- Thumbnails -->
                    <?php if (count($allPhotos) > 1): ?>
                    <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-3">
                        <?php foreach ($allPhotos as $i => $photo): ?>
                        <div
                            class="photo-gallery-thumb aspect-square rounded-2xl overflow-hidden border-2 <?= $i === 0 ? 'border-brand-500' : 'border-transparent' ?>"
                            id="thumb-<?= $i ?>"
                            onclick="switchPhoto(<?= $i ?>)"
                            data-src="<?= esc(base_url($photo['file_path'])) ?>"
                            data-caption="<?= esc($photo['caption'] ?? '') ?>"
                        >
                            <img src="<?= esc(base_url($photo['file_path'])) ?>" alt="Foto <?= $i + 1 ?>" class="w-full h-full object-cover">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- No photos placeholder -->
                    <div class="bg-white rounded-3xl border-2 border-dashed border-gray-200 aspect-video flex flex-col items-center justify-center text-gray-400">
                        <i class="ph ph-image text-5xl mb-3"></i>
                        <p class="font-medium text-gray-500">Belum ada foto tersedia</p>
                        <p class="text-sm mt-1">Foto laboratorium akan ditambahkan oleh pengelola.</p>
                    </div>
                <?php endif; ?>

                <!-- Description -->
                <?php if (! empty($lab['description'])): ?>
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="ph ph-info text-brand-500"></i> Deskripsi
                    </h2>
                    <p class="text-gray-600 leading-relaxed"><?= nl2br(esc($lab['description'])) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right: Lab Info Card -->
            <div class="space-y-5">
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <i class="ph ph-buildings text-brand-500"></i> Informasi Laboratorium
                    </h2>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-hash"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Kode</p>
                                <p class="font-semibold text-gray-800"><?= esc($lab['code'] ?? '-') ?></p>
                            </div>
                        </li>
                        <?php if (! empty($lab['location'])): ?>
                        <li class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-map-pin"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Lokasi</p>
                                <p class="font-semibold text-gray-800"><?= esc($lab['location']) ?></p>
                            </div>
                        </li>
                        <?php endif; ?>
                        <?php if (! empty($lab['capacity'])): ?>
                        <li class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-users"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Kapasitas</p>
                                <p class="font-semibold text-gray-800"><?= esc($lab['capacity']) ?> Kursi</p>
                            </div>
                        </li>
                        <?php endif; ?>
                        <li class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-activity"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Status</p>
                                <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full border mt-0.5 <?= esc($statusClass) ?>">
                                    <?= esc($statusLabel) ?>
                                </span>
                            </div>
                        </li>
                        <?php if (isset($lab['is_loanable'])): ?>
                        <li class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-yellow-50 text-yellow-600 flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-key"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Peminjaman</p>
                                <p class="font-semibold text-gray-800">
                                    <?= $lab['is_loanable'] ? 'Dapat Dipinjam' : 'Tidak Tersedia untuk Peminjaman' ?>
                                </p>
                            </div>
                        </li>
                        <?php endif; ?>
                        <?php if (! empty($photos)): ?>
                        <li class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center flex-shrink-0">
                                <i class="ph ph-images"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Foto</p>
                                <p class="font-semibold text-gray-800"><?= count($photos) ?> foto tersedia</p>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- CTA: loan if loanable -->
                <?php if (! empty($lab['is_loanable'])): ?>
                <div class="bg-brand-50 rounded-3xl p-6 border border-brand-100">
                    <h3 class="font-bold text-gray-900 mb-2 flex items-center gap-2">
                        <i class="ph ph-paper-plane-tilt text-brand-600"></i> Ajukan Peminjaman
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">Laboratorium ini tersedia untuk dipinjam. Login untuk mengajukan permohonan.</p>
                    <a href="<?= site_url('login') ?>" class="block text-center px-5 py-3 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-2xl transition-colors text-sm">
                        Masuk &amp; Ajukan Peminjaman
                    </a>
                </div>
                <?php endif; ?>

                <a href="<?= site_url('laboratorium') ?>" class="flex items-center justify-center gap-2 w-full px-5 py-3 bg-white border border-gray-200 hover:border-brand-400 hover:text-brand-600 text-gray-700 rounded-2xl font-semibold transition-all shadow-sm text-sm">
                    <i class="ph ph-arrow-left"></i> Lihat Semua Laboratorium
                </a>
            </div>

        </div>
    </div>
</section>

<!-- Lightbox -->
<div id="lightbox" class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center p-4 hidden" onclick="closeLightboxOnBackdrop(event)">
    <button onclick="closeLightbox()" class="absolute top-4 right-4 w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-xl flex items-center justify-center transition-colors">
        <i class="ph ph-x text-xl"></i>
    </button>
    <button onclick="prevPhoto()" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-xl flex items-center justify-center transition-colors" id="btn-prev">
        <i class="ph ph-caret-left text-xl"></i>
    </button>
    <button onclick="nextPhoto()" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/10 hover:bg-white/20 text-white rounded-xl flex items-center justify-center transition-colors" id="btn-next">
        <i class="ph ph-caret-right text-xl"></i>
    </button>
    <div class="max-w-4xl w-full mx-auto text-center">
        <img id="lightbox-img" src="" alt="" class="max-h-[80vh] w-auto mx-auto rounded-2xl shadow-2xl object-contain">
        <p id="lightbox-caption" class="mt-4 text-white/80 text-sm font-medium"></p>
        <p id="lightbox-counter" class="mt-1 text-white/50 text-xs"></p>
    </div>
</div>

<script>
    const photos = <?= json_encode(array_map(function($p) {
        return ['src' => base_url($p['file_path']), 'caption' => $p['caption'] ?? ''];
    }, $allPhotos)) ?>;

    let currentIndex = 0;

    function switchPhoto(index) {
        currentIndex = index;
        const photo = photos[index];

        // Update main image
        document.getElementById('main-photo').src = photo.src;

        // Update caption
        const captionEl = document.getElementById('main-caption');
        const wrapperEl = document.getElementById('main-caption-wrapper');
        if (captionEl) captionEl.textContent = photo.caption;
        if (wrapperEl) wrapperEl.classList.toggle('hidden', !photo.caption);

        // Update thumbnail borders
        document.querySelectorAll('[id^="thumb-"]').forEach((el, i) => {
            el.classList.toggle('border-brand-500', i === index);
            el.classList.toggle('border-transparent', i !== index);
        });
    }

    function openLightbox(index) {
        currentIndex = index;
        updateLightbox();
        document.getElementById('lightbox').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function closeLightboxOnBackdrop(e) {
        if (e.target === document.getElementById('lightbox')) closeLightbox();
    }

    function updateLightbox() {
        const photo = photos[currentIndex];
        document.getElementById('lightbox-img').src = photo.src;
        document.getElementById('lightbox-caption').textContent = photo.caption;
        document.getElementById('lightbox-counter').textContent = (currentIndex + 1) + ' / ' + photos.length;
        document.getElementById('btn-prev').style.display = photos.length <= 1 ? 'none' : '';
        document.getElementById('btn-next').style.display = photos.length <= 1 ? 'none' : '';
    }

    function prevPhoto() {
        currentIndex = (currentIndex - 1 + photos.length) % photos.length;
        updateLightbox();
    }

    function nextPhoto() {
        currentIndex = (currentIndex + 1) % photos.length;
        updateLightbox();
    }

    document.addEventListener('keydown', (e) => {
        const lb = document.getElementById('lightbox');
        if (lb.classList.contains('hidden')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') prevPhoto();
        if (e.key === 'ArrowRight') nextPhoto();
    });
</script>

<?= $this->endSection() ?>
