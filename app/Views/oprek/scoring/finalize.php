<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($page_title) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('oprek/' . $campaign->id . '/scoring') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <?php if (empty($applications)): ?>
          <p class="text-muted text-center py-4">Tidak ada kandidat yang siap diverifikasi.</p>
        <?php else: ?>
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Verifikasi akhir akan menutup oprek dan mempublikasikan hasil.
            Proses ini tidak dapat dibatalkan.
          </div>

          <form method="post" action="<?= base_url('oprek/' . $campaign->id . '/finalize/store') ?>">
            <?= csrf_field() ?>
            <div class="table-responsive">
              <table class="table table-bordered table-sm">
                <thead class="thead-light">
                  <tr>
                    <th>#</th>
                    <th>Pendaftar</th>
                    <th>NIM</th>
                    <th>Nilai Akhir</th>
                    <th>Ranking</th>
                    <th>Keputusan</th>
                    <th>Catatan</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($applications as $i => $app): ?>
                  <?php $rank = null; foreach ($rankings as $r => $rk) { if ($rk['application_id'] == $app->id) { $rank = $r + 1; break; } } ?>
                  <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= esc($app->username) ?></td>
                    <td><?= esc($app->nim_nik ?? '-') ?></td>
                    <td class="text-center font-weight-bold">
                      <?= isset($appScores[$app->id]) ? number_format($appScores[$app->id], 2) : '-' ?>
                    </td>
                    <td class="text-center"><?= $rank ? '#' . $rank : '-' ?></td>
                    <td>
                      <select name="decisions[<?= $app->id ?>]" class="form-control form-control-sm">
                        <option value="">-- Pilih --</option>
                        <option value="accepted">Diterima</option>
                        <option value="rejected">Ditolak</option>
                        <option value="waitlist">Cadangan</option>
                      </select>
                    </td>
                    <td>
                      <input type="text" name="decision_notes[<?= $app->id ?>]" class="form-control form-control-sm" placeholder="Catatan (opsional)">
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <button type="submit" class="btn btn-success btn-lg btn-block" onclick="return confirm('Yakin simpan keputusan akhir? Oprek akan ditutup dan hasil dipublikasikan.')">
              <i class="fas fa-check-double"></i> Simpan & Publikasikan Hasil Akhir
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
