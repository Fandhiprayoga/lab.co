<?php $type = $type ?? null; ?>

<?php
// Step indicator helper
function stepBadge(int $step, int $current): string {
    if ($step < $current) {
        return '<div class="d-flex align-items-center justify-content-center rounded-circle bg-success text-white" style="width:32px;height:32px;font-size:.85rem"><i class="fas fa-check"></i></div>';
    }
    if ($step === $current) {
        return '<div class="d-flex align-items-center justify-content-center rounded-circle text-white font-weight-bold" style="width:32px;height:32px;font-size:.85rem;background:#6777ef">' . $step . '</div>';
    }
    return '<div class="d-flex align-items-center justify-content-center rounded-circle bg-light text-muted font-weight-bold border" style="width:32px;height:32px;font-size:.85rem">' . $step . '</div>';
}
?>

<?php if ($type === null): ?>
<div class="row justify-content-center">
  <div class="col-lg-9 col-xl-7">

    <div class="text-center mb-5">
      <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
           style="width:64px;height:64px;background:rgba(103,119,239,.12)">
        <i class="fas fa-file-alt fa-2x" style="color:#6777ef"></i>
      </div>
      <h3 class="font-weight-bold mb-1">Buat Proposal Peminjaman</h3>
      <p class="text-muted">Pilih jenis peminjaman. Proposal untuk alat dan laboratorium diproses secara terpisah.</p>
    </div>

    <div class="row">
      <div class="col-md-6 mb-4">
        <a href="<?= site_url('loans/create?type=equipment') ?>" class="text-decoration-none">
          <div class="card h-100 border-0 shadow-sm type-card"
               style="border-left:4px solid #4fc3f7!important;transition:all .2s;cursor:pointer">
            <div class="card-body p-4">
              <div class="d-flex align-items-start mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 flex-shrink-0"
                     style="width:52px;height:52px;background:rgba(79,195,247,.12)">
                  <i class="fas fa-tools fa-lg" style="color:#0288d1"></i>
                </div>
                <div>
                  <h5 class="font-weight-bold mb-1" style="color:#0288d1">Peminjaman Alat</h5>
                  <span class="badge" style="background:rgba(79,195,247,.15);color:#0288d1;font-size:.72rem">equipment</span>
                </div>
              </div>
              <p class="text-muted small mb-3">Ajukan pinjaman peralatan dan instrumen laboratorium untuk keperluan praktikum atau penelitian.</p>
              <ul class="list-unstyled mb-0 small text-muted">
                <li class="mb-1"><i class="fas fa-check-circle text-success mr-2"></i>Alat ukur &amp; instrumen</li>
                <li class="mb-1"><i class="fas fa-check-circle text-success mr-2"></i>Perlengkapan praktikum</li>
                <li><i class="fas fa-check-circle text-success mr-2"></i>Stok tersedia real-time</li>
              </ul>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
              <span class="text-primary font-weight-medium small">Pilih Peminjaman Alat <i class="fas fa-arrow-right ml-1"></i></span>
            </div>
          </div>
        </a>
      </div>

      <div class="col-md-6 mb-4">
        <a href="<?= site_url('loans/create?type=lab') ?>" class="text-decoration-none">
          <div class="card h-100 border-0 shadow-sm type-card"
               style="border-left:4px solid #81c784!important;transition:all .2s;cursor:pointer">
            <div class="card-body p-4">
              <div class="d-flex align-items-start mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 flex-shrink-0"
                     style="width:52px;height:52px;background:rgba(129,199,132,.12)">
                  <i class="fas fa-door-open fa-lg" style="color:#388e3c"></i>
                </div>
                <div>
                  <h5 class="font-weight-bold mb-1" style="color:#388e3c">Peminjaman Lab</h5>
                  <span class="badge" style="background:rgba(129,199,132,.15);color:#388e3c;font-size:.72rem">laboratorium</span>
                </div>
              </div>
              <p class="text-muted small mb-3">Ajukan pinjaman ruangan laboratorium untuk kegiatan praktikum, workshop, atau penelitian mandiri.</p>
              <ul class="list-unstyled mb-0 small text-muted">
                <li class="mb-1"><i class="fas fa-check-circle text-success mr-2"></i>Ruang lab &amp; kelas khusus</li>
                <li class="mb-1"><i class="fas fa-check-circle text-success mr-2"></i>Jadwal ketersediaan</li>
                <li><i class="fas fa-check-circle text-success mr-2"></i>Bisa pilih beberapa ruangan</li>
              </ul>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-4">
              <span class="text-success font-weight-medium small">Pilih Peminjaman Lab <i class="fas fa-arrow-right ml-1"></i></span>
            </div>
          </div>
        </a>
      </div>
    </div>

    <div class="text-center">
      <a href="<?= base_url('loans') ?>" class="btn btn-light px-5">
        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar Proposal
      </a>
    </div>

  </div>
</div>

<style>
.type-card:hover { transform:translateY(-4px); box-shadow:0 8px 24px rgba(0,0,0,.12)!important; }
</style>

<?php else: ?>
<?php
$isEquipment = $type === 'equipment';
$accentColor = $isEquipment ? '#0288d1' : '#388e3c';
$accentBg    = $isEquipment ? 'rgba(79,195,247,.08)' : 'rgba(129,199,132,.08)';
$accentBorder= $isEquipment ? '#4fc3f7' : '#81c784';
$accentBtn   = $isEquipment ? 'btn-primary' : 'btn-success';
$typeLabel   = $isEquipment ? 'Alat' : 'Laboratorium';
$typeIcon    = $isEquipment ? 'fa-tools' : 'fa-door-open';
?>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body py-3 px-4">
    <div class="d-flex align-items-center">
      <div class="d-flex align-items-center flex-shrink-0">
        <?= stepBadge(1, 1) ?>
        <div class="ml-2 mr-4">
          <div class="font-weight-bold small" style="color:#6777ef">Step 1</div>
          <div class="text-muted" style="font-size:.75rem">Informasi Proposal</div>
        </div>
      </div>
      <div class="flex-grow-1 mx-2" style="height:2px;background:#e9ecef"></div>
      <div class="d-flex align-items-center flex-shrink-0">
        <?= stepBadge(2, 1) ?>
        <div class="ml-2 mr-4">
          <div class="font-weight-bold small text-muted">Step 2</div>
          <div class="text-muted" style="font-size:.75rem">Pilih Item <?= $typeLabel ?></div>
        </div>
      </div>
      <div class="flex-grow-1 mx-2" style="height:2px;background:#e9ecef"></div>
      <div class="d-flex align-items-center flex-shrink-0">
        <?= stepBadge(3, 1) ?>
        <div class="ml-2">
          <div class="font-weight-bold small text-muted">Step 3</div>
          <div class="text-muted" style="font-size:.75rem">Kirim Approval</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm" style="border-top:3px solid <?= $accentBorder ?>!important">
      <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
        <div class="d-flex align-items-center">
          <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
               style="width:42px;height:42px;background:<?= $accentBg ?>">
            <i class="fas <?= $typeIcon ?>" style="color:<?= $accentColor ?>"></i>
          </div>
          <div>
            <h5 class="mb-0 font-weight-bold">Informasi Proposal Peminjaman <?= $typeLabel ?></h5>
            <p class="mb-0 text-muted small">Lengkapi data berikut untuk membuat proposal</p>
          </div>
        </div>
      </div>
      <div class="card-body px-4 pb-4">

        <?php if (session('errors')): ?>
          <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-start">
              <i class="fas fa-exclamation-circle mt-1 mr-2"></i>
              <div>
                <strong>Perbaiki kesalahan berikut:</strong>
                <ul class="mb-0 mt-1 pl-3">
                  <?php foreach (session('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
          </div>
        <?php endif; ?>

        <?php if (session('error')): ?>
          <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-2"></i><?= esc(session('error')) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
          </div>
        <?php endif; ?>

        <form action="<?= base_url('loans/store') ?>" method="post" id="proposal-form">
          <?= csrf_field() ?>
          <input type="hidden" name="loan_type" value="<?= esc($type) ?>">
          <input type="hidden" name="requires_l2" value="1">


          <div class="form-group">
            <label class="font-weight-semibold text-dark" for="title">
              Judul Proposal <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control form-control-lg" id="title" name="title"
                   value="<?= old('title') ?>"
                   placeholder="<?= $isEquipment ? 'cth. Peminjaman Alat Ukur untuk Penelitian Tugas Akhir' : 'cth. Peminjaman Lab Komputer untuk Praktikum Basis Data' ?>"
                   required>
            <small class="form-text text-muted">Buat judul yang ringkas dan menggambarkan tujuan peminjaman.</small>
          </div>


          <div class="form-group">
            <label class="font-weight-semibold text-dark d-block mb-2">
              Periode Peminjaman <span class="text-danger">*</span>
            </label>
            <div class="row">
              <div class="col-md-6">
                <label for="start_at" class="small text-muted mb-1">Mulai</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-right-0">
                      <i class="fas fa-calendar-alt text-muted"></i>
                    </span>
                  </div>
                  <input type="datetime-local" class="form-control border-left-0 pl-0"
                         id="start_at" name="start_at" value="<?= old('start_at') ?>" required>
                </div>
              </div>
              <div class="col-md-6">
                <label for="end_at" class="small text-muted mb-1">Selesai</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-right-0">
                      <i class="fas fa-calendar-check text-muted"></i>
                    </span>
                  </div>
                  <input type="datetime-local" class="form-control border-left-0 pl-0"
                         id="end_at" name="end_at" value="<?= old('end_at') ?>" required>
                </div>
              </div>
            </div>
          </div>


          <div class="form-group mb-4">
            <label class="font-weight-semibold text-dark" for="objective">
              Tujuan &amp; Ringkasan Kebutuhan <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" id="objective" name="objective" rows="5"
                      placeholder="Jelaskan secara singkat tujuan penggunaan dan kebutuhan spesifik yang diperlukan..."
                      required><?= old('objective') ?></textarea>
            <small class="form-text text-muted">Minimal 10 karakter. Semakin jelas, semakin cepat diproses.</small>
          </div>


          <div class="alert border-0 mb-4" style="background:<?= $accentBg ?>;border-left:3px solid <?= $accentBorder ?>!important">
            <div class="d-flex align-items-center">
              <i class="fas fa-shield-alt mr-3" style="color:<?= $accentColor ?>;font-size:1.1rem"></i>
              <div>
                <div class="font-weight-semibold small" style="color:<?= $accentColor ?>">Alur Persetujuan 2 Tingkat</div>
                <div class="text-muted small">Proposal akan melalui approval <strong>Laboran</strong> kemudian <strong>Kepala Lab</strong> sebelum disetujui.</div>
              </div>
            </div>
          </div>


          <div class="d-flex align-items-center gap-2">
            <button type="submit" class="btn <?= $accentBtn ?> px-4 py-2 font-weight-semibold">
              <i class="fas fa-save mr-2"></i>Simpan &amp; Pilih <?= $typeLabel ?>
              <i class="fas fa-arrow-right ml-2"></i>
            </button>
            <a href="<?= base_url('loans/create') ?>" class="btn btn-light px-4 py-2 ml-2">
              <i class="fas fa-arrow-left mr-2"></i>Ganti Tipe
            </a>
          </div>

        </form>
      </div>
    </div>
  </div>


  <div class="col-lg-4">


    <div class="card border-0 shadow-sm mb-3" style="border-left:3px solid <?= $accentBorder ?>!important">
      <div class="card-body p-3">
        <div class="d-flex align-items-center">
          <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
               style="width:40px;height:40px;background:<?= $accentBg ?>">
            <i class="fas <?= $typeIcon ?>" style="color:<?= $accentColor ?>"></i>
          </div>
          <div>
            <div class="small text-muted">Tipe Proposal</div>
            <div class="font-weight-bold" style="color:<?= $accentColor ?>">
              Peminjaman <?= $typeLabel ?>
            </div>
          </div>
        </div>
      </div>
    </div>


    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-white border-bottom py-3 px-3">
        <h6 class="mb-0 font-weight-bold">
          <i class="fas fa-map-signs text-muted mr-2"></i>Panduan Proses
        </h6>
      </div>
      <div class="card-body p-3">
        <div class="d-flex mb-3">
          <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 text-white font-weight-bold"
               style="width:26px;height:26px;font-size:.75rem;background:#6777ef;margin-top:2px">1</div>
          <div class="ml-3">
            <div class="small font-weight-semibold">Isi Informasi Proposal</div>
            <div class="small text-muted">Judul, periode, dan tujuan peminjaman</div>
          </div>
        </div>
        <div class="d-flex mb-3">
          <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 text-muted font-weight-bold border"
               style="width:26px;height:26px;font-size:.75rem;margin-top:2px">2</div>
          <div class="ml-3">
            <div class="small font-weight-semibold text-muted">Pilih <?= $typeLabel ?></div>
            <div class="small text-muted">Tambahkan item yang dibutuhkan</div>
          </div>
        </div>
        <div class="d-flex">
          <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0 text-muted font-weight-bold border"
               style="width:26px;height:26px;font-size:.75rem;margin-top:2px">3</div>
          <div class="ml-3">
            <div class="small font-weight-semibold text-muted">Kirim ke Approval</div>
            <div class="small text-muted">Laboran &rarr; Kepala Lab</div>
          </div>
        </div>
      </div>
    </div>


    <div class="card border-0 bg-light">
      <div class="card-body p-3">
        <h6 class="font-weight-bold mb-2 small">
          <i class="fas fa-lightbulb text-warning mr-2"></i>Perlu diketahui
        </h6>
        <?php if ($isEquipment): ?>
          <ul class="list-unstyled mb-0 small text-muted">
            <li class="mb-1"><i class="fas fa-circle text-muted mr-2" style="font-size:.4rem;vertical-align:middle"></i>Stok alat diperiksa saat item ditambahkan</li>
            <li class="mb-1"><i class="fas fa-circle text-muted mr-2" style="font-size:.4rem;vertical-align:middle"></i>Satu proposal bisa berisi beberapa alat</li>
            <li><i class="fas fa-circle text-muted mr-2" style="font-size:.4rem;vertical-align:middle"></i>Untuk pinjam ruangan, buat proposal <strong>Peminjaman Lab</strong> terpisah</li>
          </ul>
        <?php else: ?>
          <ul class="list-unstyled mb-0 small text-muted">
            <li class="mb-1"><i class="fas fa-circle text-muted mr-2" style="font-size:.4rem;vertical-align:middle"></i>Ketersediaan ruangan diperiksa saat item dipilih</li>
            <li class="mb-1"><i class="fas fa-circle text-muted mr-2" style="font-size:.4rem;vertical-align:middle"></i>Satu proposal bisa berisi beberapa ruangan</li>
            <li><i class="fas fa-circle text-muted mr-2" style="font-size:.4rem;vertical-align:middle"></i>Untuk pinjam alat, buat proposal <strong>Peminjaman Alat</strong> terpisah</li>
          </ul>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>
<?php endif; ?>
