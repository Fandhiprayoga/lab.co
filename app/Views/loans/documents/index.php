<?= $this->section('css') ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<?= $this->endSection() ?>

<?php $documents = $documents ?? []; ?>
<?php $asset = $asset ?? null; ?>
<?php $assetId = (int) ($assetId ?? 0); ?>
<?php $types = $types ?? []; ?>
<?php $assets = $assets ?? []; ?>

<div class="row">
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><h4>Unggah Dokumen</h4></div>
      <div class="card-body">
        <form action="<?= base_url('admin/loans/documents/upload') ?>" method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="form-group">
            <label for="asset_id">Aset</label>
            <?php $selAsset = (string) old('asset_id', (string) $assetId); ?>
            <select id="asset_id" name="asset_id" class="form-control" required>
              <option value="">- Pilih Aset -</option>
              <?php foreach ($assets as $a): ?>
                <option value="<?= (int) $a['id'] ?>" <?= $selAsset === (string) $a['id'] ? 'selected' : '' ?>>
                  <?= esc($a['name']) ?> <?= ! empty($a['asset_code']) ? '(' . esc($a['asset_code']) . ')' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="document_type">Jenis Dokumen</label>
            <?php $selType = (string) old('document_type', 'other'); ?>
            <select id="document_type" name="document_type" class="form-control" required>
              <?php foreach ($types as $t): ?>
                <option value="<?= esc($t) ?>" <?= $selType === $t ? 'selected' : '' ?>><?= esc(ucfirst($t)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="title">Judul Dokumen</label>
            <input type="text" id="title" name="title" class="form-control" value="<?= old('title', '') ?>" maxlength="150" required>
          </div>

          <div class="form-group">
            <label for="document_file">File</label>
            <input type="file" id="document_file" name="document_file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.webp" required>
            <small class="form-text text-muted">PDF/DOC/XLS/Gambar, max 10 MB.</small>
          </div>

          <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-upload"></i> Unggah</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">
        <h4>
          <?php if ($asset): ?>
            Dokumen: <?= esc($asset['name']) ?>
          <?php else: ?>
            Semua Dokumen
          <?php endif; ?>
        </h4>
        <?php if ($asset): ?>
          <div class="card-header-action">
            <a href="<?= base_url('admin/loans/documents') ?>" class="btn btn-light btn-sm"><i class="fas fa-list"></i> Semua</a>
          </div>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table id="table-documents" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Aset</th>
                <th>Jenis</th>
                <th>Judul</th>
                <th>Ukuran</th>
                <th>Oleh</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documents as $d): ?>
                <tr>
                  <td><?= esc(substr((string) ($d['created_at'] ?? ''), 0, 16)) ?></td>
                  <td>
                    <?= esc($d['asset_name'] ?? '-') ?>
                    <?php if (! empty($d['asset_code'])): ?>
                      <br><small class="text-muted"><code><?= esc($d['asset_code']) ?></code></small>
                    <?php endif; ?>
                  </td>
                  <td><span class="badge badge-info"><?= esc(ucfirst($d['document_type'])) ?></span></td>
                  <td><?= esc($d['title']) ?></td>
                  <td><?= number_format((int) $d['file_size'] / 1024, 1) ?> KB</td>
                  <td><?= esc($d['uploaded_by_name'] ?? '-') ?></td>
                  <td>
                    <a href="<?= base_url('admin/loans/documents/download/' . (int) $d['id']) ?>" class="btn btn-sm btn-success" title="Unduh"><i class="fas fa-download"></i></a>
                    <form action="<?= base_url('admin/loans/documents/delete/' . (int) $d['id']) ?>" method="post" class="d-inline js-swal-delete-form"
                          data-swal-title="Hapus dokumen?"
                          data-swal-text="File '<?= esc($d['title']) ?>' akan dihapus permanen."
                          data-swal-confirm="Ya, hapus"
                          data-swal-cancel="Batal">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->section('js') ?>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script>
  $(function () {
    $('#table-documents').DataTable({
      pageLength: 25,
      order: [[0, 'desc']],
      columnDefs: [{ targets: [6], orderable: false, searchable: false }],
      language: { search: 'Cari:', lengthMenu: 'Tampilkan _MENU_ data', info: 'Menampilkan _START_-_END_ dari _TOTAL_', paginate: { previous: 'Sebelumnya', next: 'Selanjutnya' }, zeroRecords: 'Belum ada dokumen.' }
    });
  });
</script>
<?= $this->endSection() ?>
