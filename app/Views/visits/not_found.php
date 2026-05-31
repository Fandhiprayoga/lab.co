<?= $this->extend('layouts/public') ?>

<?= $this->section('content') ?>
<div class="min-h-screen bg-gray-50 flex items-center justify-center pt-32 pb-20 lg:pt-48 lg:pb-32 px-4">
  <div class="w-full max-w-sm text-center">
    <div class="bg-white rounded-2xl shadow p-8">
      <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="ph ph-warning text-3xl text-red-500"></i>
      </div>
      <h1 class="text-lg font-bold text-gray-800">QR Code Tidak Dikenali</h1>
      <p class="text-sm text-gray-500 mt-2">QR code ini tidak valid atau lab sudah tidak aktif.</p>
      <a href="<?= base_url() ?>" class="mt-6 inline-block text-sm text-brand-500 hover:underline">Kembali ke Beranda</a>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
