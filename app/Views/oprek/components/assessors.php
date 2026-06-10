<div class="row">
  <div class="col-lg-8 offset-lg-2">
    <div class="card">
      <div class="card-header">
        <h4><?= esc($page_title) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('oprek/' . $campaign->id . '/components') ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="alert alert-info">
          Komponen: <strong><?= esc($component->component_name) ?></strong> |
          Bobot: <?= $component->weight_percentage ?>% |
          Nilai Maks: <?= $component->max_score ?>
        </div>

        <form method="post" action="<?= base_url('oprek/' . $campaign->id . '/components/' . $component->id . '/assessors/store') ?>">
          <?= csrf_field() ?>

          <div class="form-group">
            <label>Daftar Penilai</label>
            <p class="text-muted small">Pilih user yang bertugas sebagai penilai untuk komponen ini.</p>

            <?php
            $assignedIds = array_map(fn($a) => $a->assessor_user_id, $assessors);
            ?>

            <?php if (empty($labAssignments)): ?>
              <div class="alert alert-warning">Tidak ada user yang ditugaskan ke lab ini.</div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-sm">
                  <thead>
                    <tr><th>Pilih</th><th>Username</th><th>Email</th><th>Role Penilai</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach ($labAssignments as $ua): ?>
                    <tr>
                      <td>
                        <input type="checkbox" name="assessor_ids[]" value="<?= $ua->user_id ?>"
                          <?= in_array($ua->user_id, $assignedIds) ? 'checked' : '' ?>>
                      </td>
                      <td><?= esc($ua->username) ?></td>
                      <td>-</td>
                      <td>
                        <select name="assessor_roles[]" class="form-control form-control-sm">
                          <option value="laboran">Laboran</option>
                          <option value="active_assistant">Asisten Aktif</option>
                        </select>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Penilai</button>
        </form>
      </div>
    </div>
  </div>
</div>
