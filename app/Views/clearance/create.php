<?php
$studyPrograms = $studyPrograms ?? [];
$labs          = $labs ?? [];
$prefill       = $prefill ?? [];
$outstanding   = $outstanding ?? ['clear' => true, 'items' => []];
?>

<div class="row">
  <div class="col-12 col-lg-8">

    <?php if (! ($outstanding['clear'] ?? true)): ?>
    <div class="alert alert-warning">
      <div class="alert-title"><i class="fas fa-exclamation-triangle mr-1"></i> Anda masih memiliki tanggungan lab</div>
      <p class="mb-2">Berikut peminjaman yang belum diselesaikan. Anda tetap dapat mengajukan, namun laboran akan memverifikasi terlebih dahulu.</p>
      <ul class="mb-0">
        <?php foreach ($outstanding['items'] as $item): ?>
        <li><?= esc($item['asset_name'] ?? 'Aset') ?> <?= ! empty($item['lab_name']) ? '(' . esc($item['lab_name']) . ')' : '' ?> &mdash; <?= esc($item['status']) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-file-signature mr-1"></i> Form Pengajuan Surat Bebas Lab</h4>
      </div>
      <form action="<?= base_url('clearance/store') ?>" method="post">
        <?= csrf_field() ?>
        <div class="card-body">

          <div class="form-group">
            <label>Tujuan / Keperluan</label>
            <input type="text" class="form-control" name="purpose"
                   value="<?= old('purpose', 'Syarat Yudisium/Kelulusan') ?>"
                   placeholder="Syarat Yudisium/Kelulusan">
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Nama Pemohon <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="applicant_name" required
                     value="<?= old('applicant_name', esc($prefill['applicant_name'] ?? '')) ?>">
            </div>
            <div class="form-group col-md-6">
              <label>NIM / NIK <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="nim_nik" required
                     value="<?= old('nim_nik', esc($prefill['nim_nik'] ?? '')) ?>">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>No. HP / WhatsApp <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="phone" required
                     value="<?= old('phone', esc($prefill['phone'] ?? '')) ?>">
            </div>
            <div class="form-group col-md-6">
              <label>Email</label>
              <input type="email" class="form-control" value="<?= esc($prefill['email'] ?? '') ?>" readonly>
              <small class="form-text text-muted">Email mengikuti akun Anda.</small>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Program Studi <span class="text-danger">*</span></label>
              <select class="form-control" name="prodi" required>
                <option value="">-- Pilih Program Studi --</option>
                <?php $selProdi = old('prodi', $prefill['prodi'] ?? ''); ?>
                <?php foreach ($studyPrograms as $sp): ?>
                <option value="<?= esc($sp['name']) ?>" <?= $selProdi === $sp['name'] ? 'selected' : '' ?>>
                  <?= esc($sp['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label>Lab</label>
              <select class="form-control" name="lab_id">
                <option value="">Semua Lab</option>
                <?php $selLab = old('lab_id', ''); ?>
                <?php foreach ($labs as $lab): ?>
                <option value="<?= $lab['id'] ?>" <?= (string) $selLab === (string) $lab['id'] ? 'selected' : '' ?>>
                  <?= esc($lab['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Kosongkan jika untuk semua lab.</small>
            </div>
          </div>

          <div class="form-group">
            <label>Alamat Rumah <span class="text-danger">*</span></label>
            <textarea class="form-control" name="address" rows="2" required><?= old('address') ?></textarea>
          </div>

          <div class="form-group">
            <label>Judul Skripsi / Tugas Akhir <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="thesis_title" required
                   value="<?= old('thesis_title') ?>">
          </div>

          <div class="form-group">
            <label>Catatan Tambahan</label>
            <textarea class="form-control" name="note" rows="2" placeholder="Opsional"><?= old('note') ?></textarea>
          </div>

        </div>
        <div class="card-footer text-right">
          <a href="<?= base_url('clearance') ?>" class="btn btn-secondary">Batal</a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane mr-1"></i> Kirim Pengajuan
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card">
      <div class="card-header"><h4>Informasi</h4></div>
      <div class="card-body">
        <p class="text-muted">Surat bebas lab adalah syarat administrasi kelulusan yang menyatakan Anda telah bebas dari tanggungan peminjaman alat/ruang laboratorium.</p>
        <ul class="text-muted pl-3 mb-0">
          <li>Pengajuan akan diverifikasi oleh laboran.</li>
          <li>Pastikan semua alat yang dipinjam telah dikembalikan.</li>
          <li>Setelah surat terbit, status akun Anda berubah menjadi <strong>Alumni</strong>.</li>
        </ul>
      </div>
    </div>
  </div>
</div>
