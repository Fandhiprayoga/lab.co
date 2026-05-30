<?php
$totalItems    = $totalItems    ?? 0;
$totalRequests = $totalRequests ?? 0;
$pendingCount  = $pendingCount  ?? 0;
$lowStockItems = $lowStockItems ?? [];
?>

<!-- Ringkasan Statistik -->
<div class="row">
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary"><i class="fas fa-vials"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Total Jenis Bahan</h4></div>
        <div class="card-body"><?= $totalItems ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-clipboard-list"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Total Permintaan</h4></div>
        <div class="card-body"><?= $totalRequests ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-warning"><i class="fas fa-hourglass-half"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Sedang Diproses</h4></div>
        <div class="card-body"><?= $pendingCount ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Stok Kritis</h4></div>
        <div class="card-body"><?= count($lowStockItems) ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row">

  <!-- Tentang Modul BHP -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-info-circle mr-2 text-primary"></i>Tentang Modul Bahan Habis Pakai (BHP)</h4>
      </div>
      <div class="card-body">
        <p class="text-muted">
          Modul <strong>Bahan Habis Pakai (BHP)</strong> adalah sistem pencatatan dan pengelolaan konsumsi
          bahan di setiap laboratorium. Modul ini memungkinkan pemantauan stok secara real-time,
          permintaan penggunaan bahan yang terstruktur, serta pelaporan analitik konsumsi.
        </p>

        <div class="row mt-4">
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-primary"><i class="fas fa-flask fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Katalog Bahan</h6>
                <p class="text-muted small mb-0">
                  Melihat seluruh daftar bahan habis pakai per lab beserta informasi stok,
                  lokasi penyimpanan, dan status ketersediaan.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-success"><i class="fas fa-clipboard-check fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Permintaan BHP</h6>
                <p class="text-muted small mb-0">
                  Mengajukan permintaan penggunaan bahan untuk kegiatan praktikum atau penelitian
                  dengan alur persetujuan yang jelas dan terdokumentasi.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-warning"><i class="fas fa-sliders-h fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Penyesuaian Stok</h6>
                <p class="text-muted small mb-0">
                  Mencatat perubahan stok akibat susut, kerusakan, tumpahan, atau penerimaan
                  bahan baru dari pengadaan.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-info"><i class="fas fa-chart-bar fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Analitik Konsumsi</h6>
                <p class="text-muted small mb-0">
                  Laporan visual tren penggunaan, bahan paling banyak dikonsumsi,
                  dan peringatan stok di bawah batas minimum.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-secondary"><i class="fas fa-tags fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Kategori & Master Bahan</h6>
                <p class="text-muted small mb-0">
                  Manajemen data master: pengelompokan bahan per kategori dan konfigurasi
                  detail setiap jenis bahan per lab.
                </p>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="d-flex">
              <div class="mr-3 text-danger"><i class="fas fa-bell fa-2x"></i></div>
              <div>
                <h6 class="mb-1 font-weight-bold">Peringatan Stok Kritis</h6>
                <p class="text-muted small mb-0">
                  Notifikasi otomatis ketika stok bahan berada di bawah batas minimum
                  yang telah ditetapkan.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Alur Kerja / Tutorial -->
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-route mr-2 text-success"></i>Alur Kerja Permintaan BHP</h4>
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
              <h6 class="mb-1 font-weight-bold">Buat Permintaan <span class="badge badge-secondary">Laboran / Asisten</span></h6>
              <p class="text-muted small mb-1">
                Buka menu <strong>Permintaan BHP → Buat Permintaan</strong>. Pilih lab, isi tujuan penggunaan,
                tanggal rencana, lalu tambahkan bahan yang dibutuhkan beserta jumlahnya.
              </p>
              <span class="badge badge-light text-muted">Status: <strong>Draft</strong></span>
            </div>
          </div>

          <!-- Step 2 -->
          <div class="d-flex mb-4">
            <div class="mr-3 text-center" style="min-width:48px;">
              <div class="badge badge-primary badge-pill p-2" style="width:36px;height:36px;line-height:22px;font-size:14px;">2</div>
              <div style="width:2px;background:#dee2e6;height:calc(100% - 36px);margin:4px auto 0;"></div>
            </div>
            <div class="pb-3">
              <h6 class="mb-1 font-weight-bold">Kirim ke Persetujuan <span class="badge badge-secondary">Laboran / Asisten</span></h6>
              <p class="text-muted small mb-1">
                Setelah diperiksa, klik <strong>Kirim Permintaan</strong> pada halaman detail.
                Jika ada bahan yang membutuhkan persetujuan kepala lab, status berubah menjadi
                <em>Menunggu Persetujuan</em>. Jika tidak ada, langsung disetujui otomatis.
              </p>
              <span class="badge badge-warning text-white">Status: <strong>Menunggu Persetujuan</strong></span>
            </div>
          </div>

          <!-- Step 3 -->
          <div class="d-flex mb-4">
            <div class="mr-3 text-center" style="min-width:48px;">
              <div class="badge badge-primary badge-pill p-2" style="width:36px;height:36px;line-height:22px;font-size:14px;">3</div>
              <div style="width:2px;background:#dee2e6;height:calc(100% - 36px);margin:4px auto 0;"></div>
            </div>
            <div class="pb-3">
              <h6 class="mb-1 font-weight-bold">Persetujuan <span class="badge badge-secondary">Kepala Lab</span></h6>
              <p class="text-muted small mb-1">
                Kepala lab membuka detail permintaan, mengisi jumlah yang disetujui per bahan,
                lalu klik <strong>Setujui</strong> atau <strong>Tolak</strong>.
              </p>
              <span class="badge badge-success">Status: <strong>Disetujui</strong></span>
            </div>
          </div>

          <!-- Step 4 -->
          <div class="d-flex mb-4">
            <div class="mr-3 text-center" style="min-width:48px;">
              <div class="badge badge-primary badge-pill p-2" style="width:36px;height:36px;line-height:22px;font-size:14px;">4</div>
              <div style="width:2px;background:#dee2e6;height:calc(100% - 36px);margin:4px auto 0;"></div>
            </div>
            <div class="pb-3">
              <h6 class="mb-1 font-weight-bold">Pengeluaran Bahan <span class="badge badge-secondary">Laboran</span></h6>
              <p class="text-muted small mb-1">
                Laboran mengeluarkan bahan dari stok fisik dan klik <strong>Keluarkan Bahan</strong>.
                Stok di sistem dikurangi secara otomatis sesuai jumlah yang disetujui.
              </p>
              <span class="badge badge-info text-white">Status: <strong>Bahan Dikeluarkan</strong></span>
            </div>
          </div>

          <!-- Step 5 -->
          <div class="d-flex mb-0">
            <div class="mr-3 text-center" style="min-width:48px;">
              <div class="badge badge-success badge-pill p-2" style="width:36px;height:36px;line-height:22px;font-size:14px;"><i class="fas fa-check"></i></div>
            </div>
            <div>
              <h6 class="mb-1 font-weight-bold">Catat Realisasi <span class="badge badge-secondary">Laboran / Asisten</span></h6>
              <p class="text-muted small mb-0">
                Setelah kegiatan selesai, catat jumlah bahan yang benar-benar terpakai
                melalui form <strong>Realisasi</strong>. Data ini digunakan untuk analitik konsumsi aktual.
              </p>
              <span class="badge badge-success mt-1">Status: <strong>Selesai</strong></span>
            </div>
          </div>

        </div><!-- /.timeline -->
      </div>
    </div>
  </div>

  <!-- Panel Kanan: Akses Role & Stok Kritis -->
  <div class="col-lg-4">

    <!-- Akses Per Role -->
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-user-shield mr-2 text-warning"></i>Hak Akses Per Role</h4>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0" style="font-size:12px;">
            <thead class="thead-light">
              <tr>
                <th style="min-width:130px;">Fitur</th>
                <th class="text-center">Laboran</th>
                <th class="text-center">Ka. Lab</th>
                <th class="text-center">Asisten</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Lihat Katalog Bahan</td>
                <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
              </tr>
              <tr>
                <td>Buat Permintaan</td>
                <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
              </tr>
              <tr>
                <td>Kirim / Batalkan</td>
                <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
              </tr>
              <tr>
                <td>Pantau Status</td>
                <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
              </tr>
              <tr>
                <td>Setujui / Tolak</td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
                <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
              </tr>
              <tr>
                <td>Keluarkan Bahan</td>
                <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
              </tr>
              <tr>
                <td>Realisasi</td>
                <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
              </tr>
              <tr>
                <td>Penyesuaian Stok</td>
                <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
              </tr>
              <tr>
                <td>Analitik Konsumsi</td>
                <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
              </tr>
              <tr>
                <td>Master Bahan & Kategori</td>
                <td class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
                <td class="text-center text-muted"><i class="fas fa-times-circle"></i></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="card-footer text-muted" style="font-size:11px;">
          <i class="fas fa-info-circle"></i> Superadmin memiliki akses penuh ke seluruh fitur BHP.
        </div>
      </div>
    </div>

    <!-- Stok Kritis -->
    <?php if (! empty($lowStockItems)): ?>
    <div class="card">
      <div class="card-header bg-danger text-white">
        <h4 class="mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>Stok Kritis</h4>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead class="thead-light">
              <tr>
                <th>Bahan</th>
                <th class="text-right">Stok</th>
                <th class="text-right">Min.</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($lowStockItems, 0, 8) as $item): ?>
              <tr>
                <td>
                  <span class="d-block font-weight-bold" style="font-size:12px;"><?= esc($item['name']) ?></span>
                  <small class="text-muted"><?= esc($item['lab_name'] ?? '—') ?></small>
                </td>
                <td class="text-right text-danger font-weight-bold" style="font-size:12px;">
                  <?= rtrim(rtrim(number_format($item['stock_available'], 4), '0'), '.') ?>
                  <small><?= esc($item['unit_symbol'] ?? '') ?></small>
                </td>
                <td class="text-right text-muted" style="font-size:12px;">
                  <?= rtrim(rtrim(number_format($item['min_stock'], 4), '0'), '.') ?>
                  <small><?= esc($item['unit_symbol'] ?? '') ?></small>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if (count($lowStockItems) > 8): ?>
        <div class="card-footer text-center py-2">
          <a href="<?= site_url('consumables?low_stock=1') ?>" class="text-danger small">
            Lihat semua <?= count($lowStockItems) ?> item kritis &rarr;
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="card border-success">
      <div class="card-body text-center py-4">
        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
        <h6 class="text-success">Semua Stok Aman</h6>
        <p class="text-muted small mb-0">Tidak ada bahan yang berada di bawah batas minimum stok.</p>
      </div>
    </div>
    <?php endif; ?>

    <!-- Aksi Cepat -->
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-bolt mr-2 text-primary"></i>Aksi Cepat</h4>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          <?php if (activeGroupCan('bhp.request.create')): ?>
          <a href="<?= site_url('consumables/requests/create') ?>" class="list-group-item list-group-item-action px-0">
            <i class="fas fa-plus-circle text-success mr-2"></i> Buat Permintaan Baru
          </a>
          <?php endif; ?>
          <?php if (activeGroupCan('bhp.request.track')): ?>
          <a href="<?= site_url('consumables/requests') ?>" class="list-group-item list-group-item-action px-0">
            <i class="fas fa-list text-primary mr-2"></i> Lihat Semua Permintaan
          </a>
          <?php endif; ?>
          <?php if (activeGroupCan('bhp.catalog.view')): ?>
          <a href="<?= site_url('consumables') ?>" class="list-group-item list-group-item-action px-0">
            <i class="fas fa-flask text-warning mr-2"></i> Katalog Bahan
          </a>
          <?php endif; ?>
          <?php if (activeGroupCan('bhp.analytics.view')): ?>
          <a href="<?= site_url('consumables/analytics') ?>" class="list-group-item list-group-item-action px-0">
            <i class="fas fa-chart-bar text-info mr-2"></i> Analitik Konsumsi
          </a>
          <?php endif; ?>
          <?php if (activeGroupCan('bhp.master.manage')): ?>
          <a href="<?= site_url('admin/consumables/items') ?>" class="list-group-item list-group-item-action px-0">
            <i class="fas fa-vials text-secondary mr-2"></i> Kelola Master Bahan
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>
