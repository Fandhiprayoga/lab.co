<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$lab   = $lab ?? [];
$visit = $visit ?? [];
$token = $token ?? '';
$purposeLabels = [
    'praktikum'        => 'Praktikum',
    'penelitian'       => 'Penelitian',
    'kunjungan'        => 'Kunjungan',
    'pengambilan_alat' => 'Pengambilan Alat',
    'lainnya'          => 'Lainnya',
];
$checkinTime = $visit['checked_in_at'] ?? '';
$duration = '';
if ($checkinTime) {
    $diff = time() - strtotime($checkinTime);
    $h = floor($diff / 3600);
    $m = floor(($diff % 3600) / 60);
    $duration = $h > 0 ? "{$h} jam {$m} menit" : "{$m} menit";
}
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center pt-32 pb-20 lg:pt-48 lg:pb-32 px-4">
  <div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

      <!-- Lab header -->
      <div class="bg-amber-500 text-white px-6 py-5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
            <i class="ph ph-sign-out text-xl"></i>
          </div>
          <div>
            <p class="text-sm text-white text-opacity-80">Check Out</p>
            <h1 class="text-lg font-bold leading-tight"><?= esc($lab['name'] ?? '-') ?></h1>
            <?php if (!empty($lab['location'])): ?>
              <p class="text-xs text-white text-opacity-70 mt-0.5"><i class="ph ph-map-pin mr-1"></i><?= esc($lab['location']) ?></p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Info kunjungan -->
      <div class="px-6 py-5 space-y-4">
        <div class="bg-gray-50 rounded-xl p-4 space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-500">Nama</span>
            <span class="font-semibold text-gray-800"><?= esc($visit['visitor_name'] ?? '-') ?></span>
          </div>
          <?php if (!empty($visit['visitor_institution'])): ?>
          <div class="flex justify-between">
            <span class="text-gray-500">Instansi</span>
            <span class="text-gray-700"><?= esc($visit['visitor_institution']) ?></span>
          </div>
          <?php endif; ?>
          <div class="flex justify-between">
            <span class="text-gray-500">Keperluan</span>
            <span class="text-gray-700"><?= esc($purposeLabels[$visit['purpose'] ?? ''] ?? $visit['purpose'] ?? '-') ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Masuk</span>
            <span class="text-gray-700"><?= esc(date('H:i', strtotime($checkinTime))) ?></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Durasi</span>
            <span class="font-medium text-amber-600"><?= esc($duration) ?></span>
          </div>
        </div>

        <p class="text-sm text-center text-gray-600">Konfirmasi bahwa Anda akan meninggalkan lab?</p>

        <!-- Tombol checkout -->
        <form method="post" action="<?= site_url('labs/scan/' . esc($token)) ?>" id="checkout-form">
          <input type="hidden" name="_action" value="checkout">
          <button type="submit"
            class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl transition text-sm">
            <i class="ph ph-sign-out mr-1"></i> Ya, Check Out Sekarang
          </button>
        </form>

        <!-- Batal -->
        <a href="javascript:history.back()"
           class="block text-center text-sm text-gray-400 hover:text-gray-600 mt-1">
          Batal
        </a>

        <p class="text-center text-xs text-gray-400">
          <?= date('l, d F Y') ?> &bull; <?= date('H:i') ?>
        </p>
      </div>

    </div>
  </div>
</div>
<?= $this->endSection() ?>
