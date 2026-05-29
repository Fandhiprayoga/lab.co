<div class="row">
  <!-- Left: Upload Form -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">
        <h4>Import Data Master Alat</h4>
        <div class="card-header-action">
          <a href="<?= base_url('admin/loans/assets') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">

        <?php if (session()->getFlashdata('error')): ?>
          <div class="alert alert-danger">
            <i class="fas fa-times-circle mr-1"></i> <?= esc(session()->getFlashdata('error')) ?>
          </div>
        <?php endif; ?>

        <div class="alert alert-info mb-4">
          <strong><i class="fas fa-info-circle mr-1"></i> Petunjuk Import:</strong>
          <ol class="mb-0 mt-2 pl-4">
            <li>Download template CSV di bawah</li>
            <li>Isi data sesuai format — kolom bertanda <strong>*</strong> wajib diisi</li>
            <li>Gunakan nilai <strong>persis sama</strong> dengan daftar referensi di sebelah kanan</li>
            <li>Upload file → sistem akan menampilkan <strong>preview &amp; validasi</strong> per baris</li>
            <li>Tinjau hasilnya, lalu klik <strong>Proses Import</strong> untuk menyimpan baris yang valid</li>
          </ol>
        </div>

        <div class="form-group">
          <label class="font-weight-bold">Template Import</label>
          <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('admin/loans/assets/import/template') ?>" class="btn btn-outline-success mr-2" target="_blank">
              <i class="fas fa-file-csv mr-1"></i> Download Template CSV
            </a>
            <a href="<?= base_url('admin/loans/assets/import/sample') ?>" class="btn btn-outline-info" target="_blank">
              <i class="fas fa-vial mr-1"></i> Download Contoh Data (Siap Import)
            </a>
          </div>
          <small class="text-muted">Template: semua kolom + 1 baris contoh.<br>Contoh Data: 10 baris data nyata yang langsung bisa diimport untuk testing.</small>
        </div>

        <hr>

        <form action="<?= base_url('admin/loans/assets/import/preview') ?>" method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="form-group">
            <label for="import_file" class="font-weight-bold">
              Pilih File CSV <span class="text-danger">*</span>
            </label>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="import_file" name="import_file" accept=".csv" required>
              <label class="custom-file-label" for="import_file">Pilih file CSV...</label>
            </div>
            <small class="text-muted">Maks 2 MB. Hanya format <code>.csv</code>.</small>
          </div>
          <div class="form-group mb-0 text-right">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-eye mr-1"></i> Preview &amp; Validasi
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <!-- Right: Reference Data -->
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-book-open mr-1"></i> Referensi Nilai Valid</h4>
        <div class="card-header-action">
          <small class="text-muted">Klik nilai untuk menyalin</small>
        </div>
      </div>
      <div class="card-body p-0">

        <!-- Nav Tabs -->
        <ul class="nav nav-tabs px-3 pt-2" id="refTabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="tab-lab" data-toggle="tab" href="#ref-lab" role="tab">
              <i class="fas fa-flask mr-1"></i> Lab
              <span class="badge badge-secondary ml-1"><?= count($labs) ?></span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="tab-cat" data-toggle="tab" href="#ref-cat" role="tab">
              <i class="fas fa-tag mr-1"></i> Kategori
              <span class="badge badge-secondary ml-1"><?= count($categories) ?></span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="tab-unit" data-toggle="tab" href="#ref-unit" role="tab">
              <i class="fas fa-ruler mr-1"></i> Satuan
              <span class="badge badge-secondary ml-1"><?= count($units) ?></span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="tab-enum" data-toggle="tab" href="#ref-enum" role="tab">
              <i class="fas fa-list-ul mr-1"></i> Nilai Tetap
            </a>
          </li>
        </ul>

        <div class="tab-content p-3">

          <!-- Lab Tab -->
          <div class="tab-pane fade show active" id="ref-lab" role="tabpanel">
            <p class="text-muted small mb-2">Isi kolom <strong>"Nama Lab*"</strong> dengan salah satu nama berikut:</p>
            <?php if (empty($labs)): ?>
              <div class="alert alert-warning py-2 mb-0">Belum ada lab aktif.</div>
            <?php else: ?>
            <div class="ref-grid">
              <?php foreach ($labs as $lab): ?>
                <span class="ref-badge" data-copy="<?= esc($lab['name']) ?>" title="Klik untuk menyalin">
                  <?= esc($lab['name']) ?>
                </span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>

          <!-- Kategori Tab -->
          <div class="tab-pane fade" id="ref-cat" role="tabpanel">
            <p class="text-muted small mb-2">Isi kolom <strong>"Kategori*"</strong> dengan salah satu nama berikut:</p>
            <?php if (empty($categories)): ?>
              <div class="alert alert-warning py-2 mb-0">Belum ada kategori aktif.</div>
            <?php else: ?>
            <div class="ref-grid">
              <?php foreach ($categories as $cat): ?>
                <span class="ref-badge" data-copy="<?= esc($cat['name']) ?>" title="Klik untuk menyalin">
                  <?= esc($cat['name']) ?>
                </span>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>

          <!-- Satuan Tab -->
          <div class="tab-pane fade" id="ref-unit" role="tabpanel">
            <p class="text-muted small mb-2">Isi kolom <strong>"Satuan"</strong> dengan simbol berikut:</p>
            <?php if (empty($units)): ?>
              <div class="alert alert-warning py-2 mb-0">Belum ada satuan aktif.</div>
            <?php else: ?>
            <table class="table table-sm table-bordered mb-0">
              <thead class="thead-light"><tr><th>Simbol (isi di CSV)</th><th>Nama Satuan</th></tr></thead>
              <tbody>
                <?php foreach ($units as $unit): ?>
                <tr>
                  <td>
                    <span class="ref-badge" data-copy="<?= esc($unit['symbol']) ?>" title="Klik untuk menyalin">
                      <?= esc($unit['symbol']) ?>
                    </span>
                  </td>
                  <td class="text-muted"><?= esc($unit['name']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <?php endif; ?>
          </div>

          <!-- Enum Tab -->
          <div class="tab-pane fade" id="ref-enum" role="tabpanel">
            <div class="row">
              <div class="col-md-6">
                <p class="font-weight-bold small mb-1">Kondisi* <code>kondisi</code></p>
                <div class="ref-grid mb-3">
                  <span class="ref-badge ref-success" data-copy="baik">baik</span>
                  <span class="ref-badge ref-warning" data-copy="perlu_perbaikan">perlu_perbaikan</span>
                  <span class="ref-badge ref-danger" data-copy="rusak">rusak</span>
                </div>

                <p class="font-weight-bold small mb-1">Sumber Perolehan</p>
                <div class="ref-grid mb-3">
                  <span class="ref-badge" data-copy="pembelian">pembelian</span>
                  <span class="ref-badge" data-copy="hibah">hibah</span>
                  <span class="ref-badge" data-copy="pinjaman">pinjaman</span>
                  <span class="ref-badge" data-copy="produksi">produksi</span>
                </div>

                <p class="font-weight-bold small mb-1">Boleh Dipinjam / Status Aktif</p>
                <div class="ref-grid mb-3">
                  <span class="ref-badge ref-success" data-copy="Ya">Ya</span>
                  <span class="ref-badge ref-danger" data-copy="Tidak">Tidak</span>
                </div>
              </div>
              <div class="col-md-6">
                <p class="font-weight-bold small mb-1">Status Inventaris</p>
                <div class="ref-grid mb-3">
                  <span class="ref-badge ref-success" data-copy="aktif">aktif</span>
                  <span class="ref-badge ref-info" data-copy="dipinjam">dipinjam</span>
                  <span class="ref-badge ref-warning" data-copy="dalam_perbaikan">dalam_perbaikan</span>
                  <span class="ref-badge ref-danger" data-copy="dihapuskan">dihapuskan</span>
                  <span class="ref-badge ref-secondary" data-copy="hilang">hilang</span>
                </div>

                <p class="font-weight-bold small mb-1">Format Tanggal</p>
                <div class="ref-grid">
                  <span class="ref-badge ref-info" data-copy="2025-01-15">YYYY-MM-DD</span>
                </div>
                <small class="text-muted">Contoh: <code>2025-01-15</code></small>
              </div>
            </div>
          </div>

        </div><!-- /tab-content -->
      </div><!-- /card-body -->
    </div><!-- /card -->

    <!-- Toast notification -->
    <div id="copy-toast" style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;" class="alert alert-success py-2 px-3 shadow">
      <i class="fas fa-check mr-1"></i> Disalin ke clipboard!
    </div>
  </div>

</div>

<?= $this->section('css') ?>
<style>
.ref-grid { display: flex; flex-wrap: wrap; gap: .35rem; }
.ref-badge {
  display: inline-block;
  padding: .25rem .55rem;
  border-radius: .3rem;
  font-size: .82rem;
  font-family: monospace;
  background: #f0f4ff;
  border: 1px solid #c5d0e6;
  color: #2c3e50;
  cursor: pointer;
  transition: background .15s, transform .1s;
  user-select: none;
}
.ref-badge:hover { background: #dce6ff; transform: translateY(-1px); }
.ref-badge.ref-success { background: #d4edda; border-color: #b8dfc5; color: #155724; }
.ref-badge.ref-warning { background: #fff3cd; border-color: #ffd98e; color: #856404; }
.ref-badge.ref-danger  { background: #f8d7da; border-color: #f0b8bc; color: #721c24; }
.ref-badge.ref-info    { background: #d1ecf1; border-color: #9dd9e4; color: #0c5460; }
.ref-badge.ref-secondary { background: #e2e3e5; border-color: #bfc1c4; color: #383d41; }
</style>
<?= $this->endSection() ?>

<?= $this->section('js') ?>
<script>
  // Update file input label
  document.getElementById('import_file').addEventListener('change', function () {
    var label = this.nextElementSibling;
    label.textContent = this.files.length > 0 ? this.files[0].name : 'Pilih file CSV...';
  });

  // Copy-to-clipboard for ref badges
  var copyToast;
  document.querySelectorAll('.ref-badge[data-copy]').forEach(function (el) {
    el.addEventListener('click', function () {
      var text = this.getAttribute('data-copy');
      if (navigator.clipboard) {
        navigator.clipboard.writeText(text);
      } else {
        var ta = document.createElement('textarea');
        ta.value = text; document.body.appendChild(ta);
        ta.select(); document.execCommand('copy');
        document.body.removeChild(ta);
      }
      var toast = document.getElementById('copy-toast');
      toast.style.display = 'block';
      clearTimeout(copyToast);
      copyToast = setTimeout(function () { toast.style.display = 'none'; }, 1800);
    });
  });
</script>
<?= $this->endSection() ?>
