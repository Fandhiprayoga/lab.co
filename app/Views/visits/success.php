<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$lab      = $lab ?? [];
$mode     = $mode ?? 'checkin';   // 'checkin' | 'checkout'
$visitor  = $visitor ?? '';
$checkin  = $checkin ?? '';
$checkout = $checkout ?? null;

$isCheckout = $mode === 'checkout';
$accentBg   = $isCheckout ? 'bg-emerald-500' : 'bg-brand-500';
$icon       = $isCheckout ? 'ph-check-circle' : 'ph-hand-waving';
$title      = $isCheckout ? 'Check Out Berhasil' : 'Check In Berhasil';
$subtitle   = $isCheckout
    ? 'Terima kasih telah berkunjung. Sampai jumpa!'
    : 'Selamat datang! Kunjungan Anda sudah tercatat.';
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center pt-32 pb-20 lg:pt-48 lg:pb-32 px-4">
  <div class="w-full max-w-sm">
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden text-center">

      <!-- Top accent -->
      <div class="<?= $accentBg ?> px-6 py-8">
        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-3">
          <i class="ph <?= $icon ?> text-4xl text-white"></i>
        </div>
        <h1 class="text-xl font-bold text-white"><?= $title ?></h1>
        <p class="text-sm text-white text-opacity-80 mt-1"><?= $subtitle ?></p>
      </div>

      <!-- Detail -->
      <div class="px-6 py-6 space-y-3 text-sm">
        <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-left">
          <div class="flex justify-between">
            <span class="text-gray-500">Lab</span>
            <span class="font-medium text-gray-800"><?= esc($lab['name'] ?? '-') ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Nama</span>
            <span class="font-semibold text-gray-800"><?= esc($visitor) ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Masuk</span>
            <span class="text-gray-700"><?= esc(date('H:i', strtotime($checkin))) ?></span>
          </div>
          <?php if ($isCheckout && $checkout): ?>
          <div class="flex justify-between">
            <span class="text-gray-500">Keluar</span>
            <span class="text-gray-700"><?= esc(date('H:i', strtotime($checkout))) ?></span>
          </div>
          <?php if ($checkin && $checkout): ?>
          <?php
            $dur = strtotime($checkout) - strtotime($checkin);
            $h   = floor($dur / 3600);
            $m   = floor(($dur % 3600) / 60);
            $durStr = $h > 0 ? "{$h} jam {$m} menit" : "{$m} menit";
          ?>
          <div class="flex justify-between">
            <span class="text-gray-500">Durasi</span>
            <span class="font-medium text-emerald-600"><?= esc($durStr) ?></span>
          </div>
          <?php endif; ?>
          <?php endif; ?>
        </div>

        <p class="text-xs text-gray-400"><?= date('l, d F Y \p\u\k\u\l H:i') ?></p>

        <a href="<?= base_url() ?>"
           class="block mt-2 text-sm text-gray-500 hover:text-gray-700 underline">
          Kembali ke Beranda
        </a>
      </div>

    </div>
  </div>
</div>
<?= $this->endSection() ?>
