<div class="row">
  <!-- Stat hari ini -->
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-primary"><i class="fas fa-calendar-day"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Kunjungan Hari Ini</h4></div>
        <div class="card-body"><?= (int) ($todayStats['total'] ?? 0) ?></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card card-statistic-1">
      <div class="card-icon bg-success"><i class="fas fa-user-check"></i></div>
      <div class="card-wrap">
        <div class="card-header"><h4>Masih di Dalam</h4></div>
        <div class="card-body"><?= (int) ($todayStats['inside'] ?? 0) ?></div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h4><i class="fas fa-door-open mr-2 text-muted"></i><?= esc($lab['name'] ?? '') ?></h4>
    <a href="<?= base_url('admin/visits') ?>" class="btn btn-sm btn-light">
      <i class="fas fa-arrow-left mr-1"></i> Semua Lab
    </a>
  </div>

  <!-- Filter -->
  <div class="card-body border-bottom pb-3">
    <form method="get" action="" class="form-row align-items-end">
      <div class="col-md-3 mb-2">
        <label class="d-block small font-weight-bold mb-1">Dari Tanggal</label>
        <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" class="form-control form-control-sm">
      </div>
      <div class="col-md-3 mb-2">
        <label class="d-block small font-weight-bold mb-1">Sampai Tanggal</label>
        <input type="date" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>" class="form-control form-control-sm">
      </div>
      <div class="col-md-2 mb-2">
        <label class="d-block small font-weight-bold mb-1">Status</label>
        <select name="status" class="form-control form-control-sm">
          <option value="">Semua</option>
          <option value="checkedin"  <?= ($filters['status'] ?? '') === 'checkedin'  ? 'selected' : '' ?>>Masih di Dalam</option>
          <option value="checkedout" <?= ($filters['status'] ?? '') === 'checkedout' ? 'selected' : '' ?>>Sudah Keluar</option>
        </select>
      </div>
      <div class="col-md-2 mb-2 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="fas fa-search"></i> Cari</button>
        <a href="<?= base_url('admin/loans/labs/' . (int) $lab['id'] . '/visits') ?>" class="btn btn-light btn-sm">
          <i class="fas fa-times"></i>
        </a>
      </div>
    </form>
  </div>

  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-striped table-hover mb-0">
        <thead>
          <tr>
            <th width="40">#</th>
            <th>Nama Pengunjung</th>
            <th>Instansi / Kelas</th>
            <th>Keperluan</th>
            <th>Waktu Masuk</th>
            <th>Waktu Keluar</th>
            <th>Durasi</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($visits)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-4">Belum ada data kunjungan.</td>
          </tr>
          <?php else: ?>
          <?php foreach ($visits as $i => $v): ?>
          <?php
            $isIn = empty($v['checked_out_at']);
            $dur  = '';
            if (! $isIn) {
              $s = strtotime($v['checked_out_at']) - strtotime($v['checked_in_at']);
              $h = floor($s / 3600); $m = floor(($s % 3600) / 60);
              $dur = $h > 0 ? "{$h}j {$m}m" : "{$m}m";
            } else {
              $s = time() - strtotime($v['checked_in_at']);
              $h = floor($s / 3600); $m = floor(($s % 3600) / 60);
              $dur = ($h > 0 ? "{$h}j " : '') . "{$m}m (aktif)";
            }
          ?>
          <tr>
            <td class="text-muted small"><?= $i + 1 ?></td>
            <td class="font-weight-medium"><?= esc($v['visitor_name']) ?></td>
            <td class="text-muted small"><?= esc($v['visitor_institution'] ?? '—') ?></td>
            <td>
              <span class="badge badge-light"><?= esc($purposeLabels[$v['purpose']] ?? $v['purpose']) ?></span>
              <?php if (!empty($v['purpose_note'])): ?>
                <br><small class="text-muted"><?= esc($v['purpose_note']) ?></small>
              <?php endif; ?>
            </td>
            <td class="small"><?= esc(date('d/m/Y H:i', strtotime($v['checked_in_at']))) ?></td>
            <td class="small"><?= $isIn ? '<span class="text-muted">—</span>' : esc(date('d/m/Y H:i', strtotime($v['checked_out_at']))) ?></td>
            <td class="small <?= $isIn ? 'text-success font-weight-medium' : 'text-muted' ?>"><?= esc($dur) ?></td>
            <td>
              <?php if ($isIn): ?>
                <span class="badge badge-success">Di Dalam</span>
              <?php else: ?>
                <span class="badge badge-secondary">Selesai</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if (!empty($visits)): ?>
  <div class="card-footer text-muted small">
    Total <?= count($visits) ?> kunjungan.
  </div>
  <?php endif; ?>
</div>
