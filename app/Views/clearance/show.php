<?php
$request         = $request ?? [];
$outstanding     = $outstanding ?? ['clear' => true, 'items' => []];
$liveOutstanding = $liveOutstanding ?? null;
$isManager       = $isManager ?? false;
$canVerify       = $canVerify ?? false;
$isOwner         = $isOwner ?? false;

$statusMap = [
    'submitted' => ['label' => 'Diajukan',   'badge' => 'badge-warning', 'icon' => 'fa-clock'],
    'approved'  => ['label' => 'Terbit',      'badge' => 'badge-success', 'icon' => 'fa-check-circle'],
    'rejected'  => ['label' => 'Ditolak',     'badge' => 'badge-danger',  'icon' => 'fa-times-circle'],
    'canceled'  => ['label' => 'Dibatalkan',  'badge' => 'badge-dark',    'icon' => 'fa-ban'],
];
$s = $statusMap[$request['status']] ?? ['label' => $request['status'], 'badge' => 'badge-secondary', 'icon' => 'fa-circle'];

$panel = $liveOutstanding ?? $outstanding;
?>

<div class="row">
  <div class="col-12 col-lg-8">

    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-file-signature mr-1"></i> <?= esc($request['request_code']) ?></h4>
        <div class="card-header-action">
          <span class="badge <?= $s['badge'] ?>"><i class="fas <?= $s['icon'] ?> mr-1"></i><?= esc($s['label']) ?></span>
        </div>
      </div>
      <div class="card-body">
        <table class="table table-borderless table-sm mb-0">
          <tr><th style="width:200px">Pemohon</th><td><?= esc($request['requester_name'] ?? '-') ?></td></tr>
          <tr><th>Nama Pemohon</th><td><?= esc($request['applicant_name']) ?></td></tr>
          <tr><th>NIM / NIK</th><td><?= esc($request['nim_nik'] ?? '-') ?></td></tr>
          <tr><th>Program Studi</th><td><?= esc($request['prodi'] ?? '-') ?></td></tr>
          <tr><th>No. HP</th><td><?= esc($request['phone'] ?? '-') ?></td></tr>
          <tr><th>Email</th><td><?= esc($request['email'] ?? '-') ?></td></tr>
          <tr><th>Alamat</th><td><?= nl2br(esc($request['address'] ?? '-')) ?></td></tr>
          <tr><th>Judul Skripsi</th><td><?= esc($request['thesis_title'] ?? '-') ?></td></tr>
          <tr><th>Lab</th><td><?= esc($request['lab_name'] ?? 'Semua Lab') ?></td></tr>
          <tr><th>Keperluan</th><td><?= esc($request['purpose'] ?? '-') ?></td></tr>
          <?php if (! empty($request['note'])): ?>
          <tr><th>Catatan</th><td><?= nl2br(esc($request['note'])) ?></td></tr>
          <?php endif; ?>
          <tr><th>Diajukan</th><td><?= $request['submitted_at'] ? date('d M Y H:i', strtotime($request['submitted_at'])) : '-' ?></td></tr>
        </table>
      </div>
    </div>

    <?php if ($request['status'] === 'approved'): ?>
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-stamp mr-1"></i> Surat Terbit</h4></div>
      <div class="card-body">
        <table class="table table-borderless table-sm">
          <tr><th style="width:200px">Nomor Surat</th><td><?= esc($request['letter_number'] ?? '-') ?></td></tr>
          <tr><th>Verifikator</th><td><?= esc($request['verifier_name'] ?? '-') ?></td></tr>
          <tr><th>Tanggal Terbit</th><td><?= $request['letter_issued_at'] ? date('d M Y H:i', strtotime($request['letter_issued_at'])) : '-' ?></td></tr>
          <?php if (! empty($request['verified_note'])): ?>
          <tr><th>Catatan</th><td><?= nl2br(esc($request['verified_note'])) ?></td></tr>
          <?php endif; ?>
        </table>
        <?php if (activeGroupCan('clearance.letter.download')): ?>
        <a href="<?= base_url('clearance/' . $request['public_id'] . '/download') ?>" class="btn btn-success" <?= ! empty($request['letter_external_url']) && empty($request['letter_file_path']) ? 'target="_blank"' : '' ?>>
          <i class="fas fa-download mr-1"></i> <?= ! empty($request['letter_file_path']) ? 'Unduh Surat' : 'Buka Surat' ?>
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($request['status'] === 'rejected' && ! empty($request['rejected_reason'])): ?>
    <div class="alert alert-danger">
      <div class="alert-title"><i class="fas fa-times-circle mr-1"></i> Pengajuan Ditolak</div>
      <?= nl2br(esc($request['rejected_reason'])) ?>
    </div>
    <?php endif; ?>

    <?php if ($request['status'] === 'canceled' && ! empty($request['cancel_reason'])): ?>
    <div class="alert alert-dark">
      <div class="alert-title"><i class="fas fa-ban mr-1"></i> Dibatalkan</div>
      <?= nl2br(esc($request['cancel_reason'])) ?>
    </div>
    <?php endif; ?>

  </div>

  <div class="col-12 col-lg-4">

    <!-- Outstanding panel -->
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-clipboard-check mr-1"></i> Status Tanggungan</h4></div>
      <div class="card-body">
        <?php if ($panel['clear'] ?? true): ?>
          <div class="text-success"><i class="fas fa-check-circle mr-1"></i> Tidak ada tanggungan peminjaman.</div>
        <?php else: ?>
          <div class="text-danger mb-2"><i class="fas fa-exclamation-triangle mr-1"></i> Masih ada tanggungan:</div>
          <ul class="pl-3 mb-0">
            <?php foreach ($panel['items'] as $item): ?>
            <li><?= esc($item['asset_name'] ?? 'Aset') ?> &mdash; <?= esc($item['status']) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <?php if ($liveOutstanding !== null): ?>
        <small class="form-text text-muted">Diperiksa langsung dari modul peminjaman.</small>
        <?php endif; ?>
      </div>
    </div>

    <!-- Owner: cancel -->
    <?php if ($isOwner && $request['status'] === 'submitted' && activeGroupCan('clearance.request.cancel')): ?>
    <div class="card">
      <div class="card-header"><h4>Batalkan Pengajuan</h4></div>
      <form action="<?= base_url('clearance/' . $request['public_id'] . '/cancel') ?>" method="post">
        <?= csrf_field() ?>
        <div class="card-body">
          <div class="form-group mb-0">
            <label>Alasan (opsional)</label>
            <textarea class="form-control" name="cancel_reason" rows="2"></textarea>
          </div>
        </div>
        <div class="card-footer text-right">
          <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Batalkan pengajuan ini?')">
            <i class="fas fa-ban mr-1"></i> Batalkan
          </button>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <!-- Laboran: verify -->
    <?php if ($canVerify && $request['status'] === 'submitted'): ?>
    <div class="card">
      <div class="card-header"><h4><i class="fas fa-check-double mr-1"></i> Terbitkan Surat</h4></div>
      <form action="<?= base_url('clearance/' . $request['public_id'] . '/approve') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="card-body">
          <div class="form-group">
            <label>Nomor Surat</label>
            <input type="text" class="form-control" name="letter_number" placeholder="Kosongkan untuk nomor otomatis">
          </div>
          <div class="form-group">
            <label>File Surat <small class="text-muted">(PDF/gambar)</small></label>
            <input type="file" class="form-control-file" name="letter_file" accept=".pdf,.png,.jpg,.jpeg,.webp,.doc,.docx">
          </div>
          <div class="form-group">
            <label>atau Tautan Surat (URL)</label>
            <input type="url" class="form-control" name="letter_external_url" placeholder="https://...">
            <small class="form-text text-muted">Isi salah satu: unggah file atau tautan.</small>
          </div>
          <div class="form-group">
            <label>Catatan Verifikasi</label>
            <textarea class="form-control" name="verified_note" rows="2"></textarea>
          </div>
          <div class="form-group custom-checkbox custom-control">
            <input type="checkbox" name="confirm_clear" value="1" class="custom-control-input" id="confirmClear" required>
            <label for="confirmClear" class="custom-control-label">Saya konfirmasi pemohon telah bebas tanggungan lab.</label>
          </div>
        </div>
        <div class="card-footer text-right">
          <button type="submit" class="btn btn-success" onclick="return confirm('Terbitkan surat? Status pemohon akan berubah menjadi Alumni.')">
            <i class="fas fa-stamp mr-1"></i> Terbitkan
          </button>
        </div>
      </form>
    </div>

    <div class="card">
      <div class="card-header"><h4><i class="fas fa-times mr-1"></i> Tolak Pengajuan</h4></div>
      <form action="<?= base_url('clearance/' . $request['public_id'] . '/reject') ?>" method="post">
        <?= csrf_field() ?>
        <div class="card-body">
          <div class="form-group mb-0">
            <label>Alasan Penolakan <span class="text-danger">*</span></label>
            <textarea class="form-control" name="rejected_reason" rows="2" required></textarea>
          </div>
        </div>
        <div class="card-footer text-right">
          <button type="submit" class="btn btn-danger" onclick="return confirm('Tolak pengajuan ini?')">
            <i class="fas fa-times mr-1"></i> Tolak
          </button>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <div class="mt-2">
      <a href="<?= base_url('clearance') ?>" class="btn btn-light btn-block"><i class="fas fa-arrow-left mr-1"></i> Kembali</a>
    </div>

  </div>
</div>
