<?php
$isManager = $isManager ?? false;
$isAlumni  = $isAlumni  ?? false;
$stats     = $stats     ?? ['total' => 0, 'submitted' => 0, 'approved' => 0, 'rejected' => 0];
?>

<!-- Ringkasan Statistik -->
<div class="row">
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary"><i class="fas fa-file-signature"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4><?= $isManager ? 'Total Pengajuan' : 'Pengajuan Saya' ?></h4></div>
        <div class="card-body"><?= (int) $stats['total'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-warning"><i class="fas fa-hourglass-half"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4><?= $isManager ? 'Menunggu Verifikasi' : 'Sedang Diproses' ?></h4></div>
        <div class="card-body"><?= (int) $stats['submitted'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Surat Terbit</h4></div>
        <div class="card-body"><?= (int) $stats['approved'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-danger"><i class="fas fa-times-circle"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Ditolak</h4></div>
        <div class="card-body"><?= (int) $stats['rejected'] ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row">

  <!-- Kolom Utama -->
  <div class="col-lg-8">

    <!-- Tentang Modul -->
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-info-circle mr-2 text-primary"></i>Tentang Modul Surat Bebas Lab</h4>
      </div>
      <div class="card-body">
        <p class="text-muted">
          <strong>Surat Bebas Lab (SBL)</strong> adalah dokumen administrasi yang menyatakan bahwa seorang
          mahasiswa telah <strong>bebas dari seluruh tanggungan</strong> terkait peminjaman alat maupun
          ruang laboratorium. Surat ini umumnya menjadi salah satu syarat kelulusan atau pengambilan ijazah.
        </p>

        <div class="row mt-4">
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-primary"><i class="fas fa-file-signature fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Ajukan Surat Bebas</h6>
                <p class="text-muted small mb-0">
                  Mahasiswa mengajukan permohonan surat bebas lab dengan mengisi data diri,
                  program studi, dan judul tugas akhir. Sistem otomatis memeriksa tanggungan peminjaman
                  yang belum dikembalikan.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-success"><i class="fas fa-clipboard-check fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Verifikasi &amp; Penerbitan</h6>
                <p class="text-muted small mb-0">
                  Laboran memverifikasi pengajuan, memastikan tidak ada tanggungan, lalu menerbitkan
                  surat resmi (lampiran file atau tautan) beserta nomor surat.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-info"><i class="fas fa-search fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Cek Tanggungan Otomatis</h6>
                <p class="text-muted small mb-0">
                  Sistem menampilkan daftar peminjaman berstatus <em>dipinjam</em>, <em>terlambat</em>,
                  atau <em>bermasalah</em> sebagai bahan pertimbangan verifikator.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-warning"><i class="fas fa-download fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Unduh Surat &amp; Riwayat</h6>
                <p class="text-muted small mb-0">
                  Setelah surat terbit, pemohon dapat mengunduh surat dan mengakses riwayat
                  pengajuannya kapan saja, termasuk setelah berstatus alumni.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Alur Pengajuan -->
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-route mr-2 text-success"></i>Alur Pengajuan Surat Bebas Lab</h4>
      </div>
      <div class="card-body">
        <div class="timeline">

          <!-- Step 1 -->
          <div class="d-flex mb-4">
            <div class="mr-3 text-center" style="min-width:48px;">
              <div class="badge badge-primary badge-pill p-2" style="width:36px;height:36px;line-height:22px;font-size:14px;">1</div>
              <div style="width:2px;background:#dee2e6;height:calc(100% - 36px);margin:4px auto 0;"></div>
            </div>
            <div class="pb-3">
              <h6 class="mb-1 font-weight-bold">Ajukan Permohonan <span class="badge badge-secondary">Mahasiswa</span></h6>
              <p class="text-muted small mb-1">
                Buka menu <strong>Ajukan Surat Bebas</strong>, lengkapi data diri, program studi, dan judul
                tugas akhir. Sistem menampilkan tanggungan peminjaman (jika ada) lalu menyimpannya sebagai bukti.
              </p>
              <span class="badge badge-warning text-white">Status: <strong>Diajukan</strong></span>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="d-flex mb-4">
            <div class="mr-3 text-center" style="min-width:48px;">
              <div class="badge badge-primary badge-pill p-2" style="width:36px;height:36px;line-height:22px;font-size:14px;">2</div>
              <div style="width:2px;background:#dee2e6;height:calc(100% - 36px);margin:4px auto 0;"></div>
            </div>
            <div class="pb-3">
              <h6 class="mb-1 font-weight-bold">Verifikasi Tanggungan <span class="badge badge-secondary">Laboran</span></h6>
              <p class="text-muted small mb-1">
                Laboran membuka detail pengajuan dan memeriksa kembali daftar tanggungan peminjaman
                pemohon secara real-time untuk memastikan tidak ada alat/ruang yang belum dikembalikan.
              </p>
              <span class="badge badge-warning text-white">Status: <strong>Diajukan</strong></span>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="d-flex mb-4">
            <div class="mr-3 text-center" style="min-width:48px;">
              <div class="badge badge-primary badge-pill p-2" style="width:36px;height:36px;line-height:22px;font-size:14px;">3</div>
              <div style="width:2px;background:#dee2e6;height:calc(100% - 36px);margin:4px auto 0;"></div>
            </div>
            <div class="pb-3">
              <h6 class="mb-1 font-weight-bold">Terbitkan / Tolak Surat <span class="badge badge-secondary">Laboran</span></h6>
              <p class="text-muted small mb-1">
                Bila bersih dari tanggungan, laboran <strong>menerbitkan surat</strong>: melampirkan file
                atau tautan surat dan mengisi nomor surat. Bila masih ada kendala, pengajuan dapat
                <strong>ditolak</strong> dengan alasan yang jelas.
              </p>
              <span class="badge badge-success">Status: <strong>Terbit</strong></span>
              <span class="badge badge-danger ml-1">atau <strong>Ditolak</strong></span>
            </div>
          </div>

          <!-- Step 4 -->
          <div class="d-flex mb-0">
            <div class="mr-3 text-center" style="min-width:48px;">
              <div class="badge badge-success badge-pill p-2" style="width:36px;height:36px;line-height:22px;font-size:14px;"><i class="fas fa-check"></i></div>
            </div>
            <div>
              <h6 class="mb-1 font-weight-bold">Unduh Surat &amp; Status Alumni <span class="badge badge-secondary">Mahasiswa &rarr; Alumni</span></h6>
              <p class="text-muted small mb-0">
                Pemohon menerima notifikasi, lalu mengunduh surat bebas lab. Setelah surat terbit,
                status pengguna otomatis beralih menjadi <strong>Alumni</strong> dengan akses
                riwayat surat (hanya-baca).
              </p>
              <span class="badge badge-success mt-1">Status: <strong>Terbit</strong></span>
            </div>
          </div>

        </div><!-- /.timeline -->
      </div>
    </div>
  </div>

  <!-- Kolom Kanan -->
  <div class="col-lg-4">

    <!-- Role yang Terlibat -->
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-users-cog mr-2 text-warning"></i>Role yang Terlibat</h4>
      </div>
      <div class="card-body">
        <ul class="list-group list-group-flush">
          <li class="list-group-item px-0">
            <div class="d-flex">
              <div class="mr-3 text-primary"><i class="fas fa-user-graduate fa-lg"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Mahasiswa <span class="badge badge-light text-muted">Pemohon</span></h6>
                <p class="text-muted small mb-0">Mengajukan, memantau, membatalkan, dan mengunduh surat bebas lab miliknya.</p>
              </div>
            </div>
          </li>
          <li class="list-group-item px-0">
            <div class="d-flex">
              <div class="mr-3 text-success"><i class="fas fa-user-shield fa-lg"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Laboran <span class="badge badge-light text-muted">Verifikator</span></h6>
                <p class="text-muted small mb-0">Memverifikasi tanggungan, menerbitkan atau menolak surat, dan mengelola seluruh pengajuan.</p>
              </div>
            </div>
          </li>
          <li class="list-group-item px-0">
            <div class="d-flex">
              <div class="mr-3 text-info"><i class="fas fa-user-tie fa-lg"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Kepala Lab <span class="badge badge-light text-muted">Pemantau</span></h6>
                <p class="text-muted small mb-0">Memantau pengajuan dan rekap surat bebas lab di lingkungan laboratorium.</p>
              </div>
            </div>
          </li>
          <li class="list-group-item px-0">
            <div class="d-flex">
              <div class="mr-3 text-secondary"><i class="fas fa-user-check fa-lg"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Alumni <span class="badge badge-light text-muted">Hanya Baca</span></h6>
                <p class="text-muted small mb-0">Mantan mahasiswa yang suratnya telah terbit; dapat melihat dan mengunduh riwayat suratnya.</p>
              </div>
            </div>
          </li>
        </ul>
      </div>
      <div class="card-footer text-muted" style="font-size:11px;">
        <i class="fas fa-info-circle"></i> Superadmin memiliki akses penuh ke seluruh fitur Surat Bebas Lab.
      </div>
    </div>

    <!-- Aksi Cepat -->
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-bolt mr-2 text-primary"></i>Aksi Cepat</h4>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <?php if (! $isAlumni && activeGroupCan('clearance.request.create')): ?>
          <a href="<?= site_url('clearance/create') ?>" class="list-group-item list-group-item-action px-0">
            <i class="fas fa-plus-circle text-success mr-2"></i> Ajukan Surat Bebas
          </a>
          <?php endif; ?>
          <?php if (activeGroupCan('clearance.request.track')): ?>
          <a href="<?= site_url('clearance') ?>" class="list-group-item list-group-item-action px-0">
            <i class="fas fa-clipboard-check text-primary mr-2"></i>
            <?= $isManager ? 'Verifikasi Surat Bebas' : 'Surat Bebas Saya' ?>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>
