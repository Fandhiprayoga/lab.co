<div class="row">
  <div class="col-lg-8 offset-lg-2">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-user-graduate"></i> <?= esc($page_title) ?></h4>
        <div class="card-header-action">
          <a href="<?= base_url('oprek/' . $campaign->id) ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
          </a>
        </div>
      </div>
      <div class="card-body">

        <?php if ($hasAsisten): ?>
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle"></i>
          <strong>Perhatian:</strong> Mahasiswa ini sudah memiliki role <span class="badge badge-info">Asisten Lab</span>.
          Aktivasi akan menambah assignment lab, tidak menghapus role yang sudah ada.
        </div>
        <?php endif; ?>

        <!-- Student Info -->
        <div class="row mb-4">
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr><th width="120">Nama</th><td><?= esc($application->username) ?></td></tr>
              <tr><th>NIM/NIK</th><td><?= esc($application->nim_nik ?? '-') ?></td></tr>
              <tr><th>Prodi</th><td><?= esc($application->prodi ?? '-') ?></td></tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-sm table-borderless">
              <tr><th width="120">Oprek</th><td><?= esc($campaign->period_name ?? '-') ?></td></tr>
              <tr><th>Lab Oprek</th><td><span class="badge badge-primary"><?= esc($campaign->lab_name ?? '-') ?></span></td></tr>
              <tr><th>Role Saat Ini</th>
                <td>
                  <?php foreach ($userGroups as $group): ?>
                    <span class="badge badge-secondary mr-1"><?= esc($group) ?></span>
                  <?php endforeach; ?>
                </td>
              </tr>
            </table>
          </div>
        </div>

        <?php if (! empty($assignedLabs)): ?>
        <div class="alert alert-info">
          <strong>Lab sudah di-assign:</strong>
          <?php
            $assignedNames = array_filter($allLabs, fn($lab) => in_array($lab['id'], $assignedLabs));
            echo implode(', ', array_map(fn($l) => esc($l['name']), $assignedNames));
          ?>
        </div>
        <?php endif; ?>

        <hr>
        <h5>Assign Lab untuk Asisten</h5>
        <p class="text-muted small">Pilih lab tempat asisten akan bertugas. Lab dari oprek otomatis terpilih. Bisa pilih lebih dari satu.</p>

        <form method="post" action="<?= base_url('oprek/activate/' . $application->public_id . '/store') ?>">
          <?= csrf_field() ?>

          <div class="form-group">
            <?php foreach ($allLabs as $lab): ?>
            <div class="custom-control custom-checkbox mb-2">
              <input
                type="checkbox"
                class="custom-control-input"
                id="lab_<?= $lab['id'] ?>"
                name="lab_ids[]"
                value="<?= $lab['id'] ?>"
                <?= in_array((int) $lab['id'], $preselectedIds) ? 'checked' : '' ?>
              >
              <label class="custom-control-label" for="lab_<?= $lab['id'] ?>">
                <?= esc($lab['name']) ?>
                <?php if ((int) $lab['id'] === $campaignLabId): ?>
                  <span class="badge badge-info ml-1">Lab Oprek</span>
                <?php endif; ?>
              </label>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="alert alert-light border">
            <i class="fas fa-info-circle"></i>
            Role <strong>mahasiswa</strong> tetap dipertahankan. Asisten dapat switch role melalui menu profil (dropdown navbar).
          </div>

          <button type="submit" class="btn btn-lg btn-success" onclick="return confirm('Aktivasi asisten dan assign lab?')">
            <i class="fas fa-user-graduate"></i> Aktivasi Asisten
          </button>
        </form>

      </div>
    </div>
  </div>
</div>
