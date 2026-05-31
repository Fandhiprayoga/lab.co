<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<?php
$lab    = $lab ?? [];
$errors = $errors ?? [];
$old    = $old ?? [];
$token  = $token ?? '';
$purposeOptions = [
    'praktikum'        => 'Praktikum',
    'penelitian'       => 'Penelitian',
    'kunjungan'        => 'Kunjungan',
    'pengambilan_alat' => 'Pengambilan Alat',
    'lainnya'          => 'Lainnya',
];
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center pt-32 pb-20 lg:pt-48 lg:pb-32 px-4">
  <div class="w-full max-w-md">

    <!-- Header Card -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">

      <!-- Lab header -->
      <div class="bg-brand-500 text-white px-6 py-5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
            <i class="ph ph-door text-xl"></i>
          </div>
          <div>
            <p class="text-sm text-white text-opacity-80">Check In</p>
            <h1 class="text-lg font-bold leading-tight"><?= esc($lab['name'] ?? '-') ?></h1>
            <?php if (!empty($lab['location'])): ?>
              <p class="text-xs text-white text-opacity-70 mt-0.5"><i class="ph ph-map-pin mr-1"></i><?= esc($lab['location']) ?></p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Form body -->
      <form method="post" action="<?= site_url('labs/scan/' . esc($token)) ?>" class="px-6 py-6 space-y-5" id="checkin-form">
        <input type="hidden" name="_action" value="checkin">

        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
          <ul class="space-y-1 list-disc list-inside">
            <?php foreach ($errors as $e): ?>
              <li><?= esc($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>

        <!-- Nama -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Nama Lengkap <span class="text-red-500">*</span>
          </label>
          <input
            type="text"
            name="visitor_name"
            value="<?= esc($old['visitor_name'] ?? '') ?>"
            placeholder="Masukkan nama lengkap"
            class="w-full border <?= isset($errors['visitor_name']) ? 'border-red-400 bg-red-50' : 'border-gray-300' ?> rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 <?= !empty($old['visitor_name']) ? 'bg-gray-50' : '' ?>"
            required
          >
          <?php if (!empty($old['visitor_name'])): ?>
          <p class="text-xs text-gray-500 mt-1"><i class="ph ph-info"></i> Terisi otomatis dari akun Anda</p>
          <?php endif; ?>
        </div>

        <!-- Instansi / Kelas -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Instansi / Kelas / Prodi
          </label>
          <input
            type="text"
            name="visitor_institution"
            value="<?= esc($old['visitor_institution'] ?? '') ?>"
            placeholder="Contoh: Teknik Informatika A, SMA N 1 ..."
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 <?= !empty($old['visitor_institution']) ? 'bg-gray-50' : '' ?>"
          >
          <?php if (!empty($old['visitor_institution'])): ?>
          <p class="text-xs text-gray-500 mt-1"><i class="ph ph-info"></i> Terisi otomatis dari akun Anda (dapat diubah)</p>
          <?php endif; ?>
        </div>

        <!-- Keperluan -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Keperluan <span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 gap-2" id="purpose-grid">
            <?php foreach ($purposeOptions as $val => $label): ?>
            <label class="purpose-option flex items-center gap-2 border rounded-lg px-3 py-2.5 cursor-pointer transition <?= ($old['purpose'] ?? '') === $val ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-gray-300' ?>">
              <input type="radio" name="purpose" value="<?= $val ?>" class="sr-only" <?= ($old['purpose'] ?? '') === $val ? 'checked' : '' ?>>
              <span class="w-4 h-4 rounded-full border-2 flex-shrink-0 purpose-radio <?= ($old['purpose'] ?? '') === $val ? 'border-brand-500 bg-brand-500' : 'border-gray-400' ?>"></span>
              <span class="text-sm text-gray-700"><?= esc($label) ?></span>
            </label>
            <?php endforeach; ?>
          </div>
          <?php if (isset($errors['purpose'])): ?>
            <p class="text-xs text-red-500 mt-1"><?= esc($errors['purpose']) ?></p>
          <?php endif; ?>
        </div>

        <!-- Catatan (muncul hanya saat "lainnya") -->
        <div id="purpose-note-wrap" class="<?= ($old['purpose'] ?? '') === 'lainnya' ? '' : 'hidden' ?>">
          <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
          <input
            type="text"
            name="purpose_note"
            value="<?= esc($old['purpose_note'] ?? '') ?>"
            placeholder="Jelaskan keperluan Anda"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
          >
        </div>

        <button
          type="submit"
          class="w-full bg-brand-500 hover:bg-brand-600 text-white font-semibold py-3 rounded-xl transition text-sm"
        >
          <i class="ph ph-sign-in mr-1"></i> Check In Sekarang
        </button>

        <p class="text-center text-xs text-gray-400">
          <?= date('l, d F Y') ?> &bull; <?= date('H:i') ?>
        </p>
      </form>
    </div>

  </div>
</div>

<script>
(function () {
  var options = document.querySelectorAll('.purpose-option');
  var noteWrap = document.getElementById('purpose-note-wrap');
  var form = document.getElementById('checkin-form');

  if (form) {
    form.addEventListener('submit', function(e) {
      var formData = new FormData(form);
      var visitorName = formData.get('visitor_name');
      var purpose = formData.get('purpose');
      
      if (!visitorName || visitorName.trim() === '') {
        alert('Nama lengkap harus diisi');
        e.preventDefault();
        return false;
      }
      
      if (!purpose) {
        alert('Pilih salah satu keperluan');
        e.preventDefault();
        return false;
      }
    });
  }

  options.forEach(function (label) {
    label.addEventListener('click', function () {
      var radio = label.querySelector('input[type=radio]');
      if (!radio) return;
      radio.checked = true;

      options.forEach(function (el) {
        el.classList.remove('border-brand-500', 'bg-brand-50');
        el.classList.add('border-gray-200');
        var dot = el.querySelector('.purpose-radio');
        if (dot) { dot.classList.remove('border-brand-500', 'bg-brand-500'); dot.classList.add('border-gray-400'); }
      });

      label.classList.add('border-brand-500', 'bg-brand-50');
      label.classList.remove('border-gray-200');
      var dot = label.querySelector('.purpose-radio');
      if (dot) { dot.classList.add('border-brand-500', 'bg-brand-500'); dot.classList.remove('border-gray-400'); }

      if (noteWrap) {
        noteWrap.classList.toggle('hidden', radio.value !== 'lainnya');
      }
    });
  });
})();
</script>
<?= $this->endSection() ?>
