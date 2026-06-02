<?php
$isManager = $isManager ?? false;
$stats     = $stats ?? ['total' => 0, 'pending' => 0, 'approved' => 0, 'running' => 0];
?>

<!-- Ringkasan Statistik -->
<div class="row">
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary"><i class="fas fa-clipboard-list"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4><?= $isManager ? 'Total Proposal' : 'Proposal Saya' ?></h4></div>
        <div class="card-body"><?= (int) $stats['total'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-warning"><i class="fas fa-hourglass-half"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Menunggu Approval</h4></div>
        <div class="card-body"><?= (int) $stats['pending'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-check-circle"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Siap / Disetujui</h4></div>
        <div class="card-body"><?= (int) $stats['approved'] ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-info"><i class="fas fa-play-circle"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Sedang Berjalan</h4></div>
        <div class="card-body"><?= (int) $stats['running'] ?></div>
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
        <h4><i class="fas fa-info-circle mr-2 text-primary"></i>Tentang Peminjaman Lab &amp; Alat</h4>
      </div>
      <div class="card-body">
        <p class="text-muted">
          Modul <strong>Peminjaman Lab</strong> digunakan untuk mengelola pengajuan peminjaman
          <strong>alat</strong> maupun <strong>ruang laboratorium</strong> secara terstruktur.
          Seluruh proses terdokumentasi mulai dari pembuatan proposal, approval,
          pelaksanaan peminjaman, sampai pengembalian atau penyelesaian penggunaan ruangan.
        </p>

        <div class="row mt-4">
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-primary"><i class="fas fa-file-alt fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Buat Proposal</h6>
                <p class="text-muted small mb-0">
                  Peminjam membuat proposal berdasarkan kebutuhan: peminjaman alat atau peminjaman lab.
                  Data tujuan, jadwal, dan catatan kegiatan dicatat dalam satu formulir.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-success"><i class="fas fa-list-check fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Pilih Item Peminjaman</h6>
                <p class="text-muted small mb-0">
                  Proposal alat dapat berisi beberapa item alat, sedangkan proposal lab
                  dibatasi untuk 1 item lab agar jadwal dan kapasitas lebih terkontrol.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-warning"><i class="fas fa-user-check fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Approval Berjenjang</h6>
                <p class="text-muted small mb-0">
                  Proposal diverifikasi laboran (L1), dan jika diperlukan dilanjutkan
                  ke kepala lab (L2) sebelum dinyatakan disetujui.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-info"><i class="fas fa-undo fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Checkout &amp; Checkin</h6>
                <p class="text-muted small mb-0">
                  Untuk alat: dilakukan serah-terima (checkout), pemantauan keterlambatan,
                  dan pengembalian (checkin). Untuk lab: verifikasi mulai dan selesai penggunaan.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Alur Peminjaman -->
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-route mr-2 text-success"></i>Alur Peminjaman Lab &amp; Alat</h4>
      </div>
      <div class="card-body">
        <div class="timeline">

          <div class="d-flex mb-4">
            <div class="mr-3 text-center" style="min-width:48px;">
              <div class="badge badge-primary badge-pill p-2" style="width:36px;height:36px;line-height:22px;font-size:14px;">1</div>
              <div style="width:2px;background:#dee2e6;height:calc(100% - 36px);margin:4px auto 0;"></div>
            </div>
            <div class="pb-3">
              <h6 class="mb-1 font-weight-bold">Buat Proposal &amp; Pilih Item <span class="badge badge-secondary">Dosen / Mahasiswa</span></h6>
              <p class="text-muted small mb-1">
                Pemohon membuat proposal, menentukan jadwal, lalu menambahkan item sesuai tipe peminjaman
                (alat atau lab) sebelum proposal dikirim ke approval.
              </p>
              <span class="badge badge-light text-muted">Status: <strong>Draft</strong></span>
            </div>
          </div>

          <div class="d-flex mb-4">
            <div class="mr-3 text-center" style="min-width:48px;">
              <div class="badge badge-primary badge-pill p-2" style="width:36px;height:36px;line-height:22px;font-size:14px;">2</div>
              <div style="width:2px;background:#dee2e6;height:calc(100% - 36px);margin:4px auto 0;"></div>
            </div>
            <div class="pb-3">
              <h6 class="mb-1 font-weight-bold">Verifikasi L1 <span class="badge badge-secondary">Laboran</span></h6>
              <p class="text-muted small mb-1">
                Laboran menilai kelengkapan proposal, jadwal, dan ketersediaan item.
                Proposal dapat disetujui atau ditolak pada tahap ini.
              </p>
              <span class="badge badge-warning text-white">Status: <strong>Menunggu L1</strong></span>
            </div>
          </div>

          <div class="d-flex mb-4">
            <div class="mr-3 text-center" style="min-width:48px;">
              <div class="badge badge-primary badge-pill p-2" style="width:36px;height:36px;line-height:22px;font-size:14px;">3</div>
              <div style="width:2px;background:#dee2e6;height:calc(100% - 36px);margin:4px auto 0;"></div>
            </div>
            <div class="pb-3">
              <h6 class="mb-1 font-weight-bold">Verifikasi L2 (Opsional) <span class="badge badge-secondary">Kepala Lab</span></h6>
              <p class="text-muted small mb-1">
                Jika proposal memerlukan persetujuan lanjutan, kepala lab memberikan keputusan akhir
                sebelum proposal berstatus disetujui.
              </p>
              <span class="badge badge-warning text-white">Status: <strong>Menunggu L2</strong></span>
            </div>
          </div>

          <div class="d-flex mb-4">
            <div class="mr-3 text-center" style="min-width:48px;">
              <div class="badge badge-primary badge-pill p-2" style="width:36px;height:36px;line-height:22px;font-size:14px;">4</div>
              <div style="width:2px;background:#dee2e6;height:calc(100% - 36px);margin:4px auto 0;"></div>
            </div>
            <div class="pb-3">
              <h6 class="mb-1 font-weight-bold">Pelaksanaan Peminjaman <span class="badge badge-secondary">Laboran / Asisten</span></h6>
              <p class="text-muted small mb-1">
                Untuk alat dilakukan checkout dan nanti checkin. Untuk lab, petugas menandai
                mulai penggunaan dan selesai penggunaan ruangan.
              </p>
              <span class="badge badge-info text-white">Status: <strong>Dipinjam / In Use</strong></span>
            </div>
          </div>

          <div class="d-flex mb-0">
            <div class="mr-3 text-center" style="min-width:48px;">
              <div class="badge badge-success badge-pill p-2" style="width:36px;height:36px;line-height:22px;font-size:14px;"><i class="fas fa-check"></i></div>
            </div>
            <div>
              <h6 class="mb-1 font-weight-bold">Penutupan Proposal <span class="badge badge-secondary">Sistem</span></h6>
              <p class="text-muted small mb-0">
                Setelah pengembalian alat atau selesai penggunaan lab, proposal ditutup.
                Jika ada kendala saat pengembalian, proposal dapat berstatus bermasalah.
              </p>
              <span class="badge badge-success mt-1">Status: <strong>Selesai</strong></span>
              <span class="badge badge-danger mt-1 ml-1">atau <strong>Bermasalah</strong></span>
            </div>
          </div>

        </div>
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
                <h6 class="mb-1 font-weight-bold">Mahasiswa / Dosen <span class="badge badge-light text-muted">Pemohon</span></h6>
                <p class="text-muted small mb-0">Membuat proposal, memilih item, mengirim, memantau status, dan membatalkan proposal miliknya.</p>
              </div>
            </div>
          </li>
          <li class="list-group-item px-0">
            <div class="d-flex">
              <div class="mr-3 text-success"><i class="fas fa-user-shield fa-lg"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Laboran <span class="badge badge-light text-muted">Verifikator L1</span></h6>
                <p class="text-muted small mb-0">Memverifikasi proposal, melakukan checkout/checkin alat, serta mengelola proses operasional peminjaman.</p>
              </div>
            </div>
          </li>
          <li class="list-group-item px-0">
            <div class="d-flex">
              <div class="mr-3 text-info"><i class="fas fa-user-tie fa-lg"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Kepala Lab <span class="badge badge-light text-muted">Verifikator L2</span></h6>
                <p class="text-muted small mb-0">Memberikan persetujuan lanjutan untuk proposal yang membutuhkan keputusan level 2.</p>
              </div>
            </div>
          </li>
          <li class="list-group-item px-0">
            <div class="d-flex">
              <div class="mr-3 text-secondary"><i class="fas fa-user-cog fa-lg"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Asisten <span class="badge badge-light text-muted">Operasional</span></h6>
                <p class="text-muted small mb-0">Mendukung operasional peminjaman, khususnya proses serah-terima sesuai hak akses aktif.</p>
              </div>
            </div>
          </li>
        </ul>
      </div>
      <div class="card-footer text-muted" style="font-size:11px;">
        <i class="fas fa-info-circle"></i> Superadmin memiliki akses penuh terhadap seluruh fitur peminjaman.
      </div>
    </div>

    <!-- Aksi Cepat -->
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-bolt mr-2 text-primary"></i>Aksi Cepat</h4>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <?php if (activeGroupCan('lending.request.create')): ?>
          <a href="<?= site_url('loans/create') ?>" class="list-group-item list-group-item-action px-0">
            <i class="fas fa-plus-circle text-success mr-2"></i> Buat Proposal Peminjaman
          </a>
          <?php endif; ?>

          <?php if (activeGroupCan('lending.request.track')): ?>
          <a href="<?= site_url('loans') ?>" class="list-group-item list-group-item-action px-0">
            <i class="fas fa-clipboard-list text-primary mr-2"></i> Lihat Permohonan
          </a>
          <?php endif; ?>

          <?php if (activeGroupCan('lending.analytics.view')): ?>
          <a href="<?= site_url('loans/analytics') ?>" class="list-group-item list-group-item-action px-0">
            <i class="fas fa-chart-line text-info mr-2"></i> Buka Analitik Lab
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>
