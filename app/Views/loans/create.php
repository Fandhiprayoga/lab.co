<?php $type = $type ?? null; ?>

<?php
$isEquipment = $type === 'equipment';
$accentColor = $isEquipment ? '#0288d1' : '#388e3c';
$accentBg    = $isEquipment ? 'rgba(79,195,247,.08)' : 'rgba(129,199,132,.08)';
$accentBtn   = $isEquipment ? 'btn-primary' : 'btn-success';
$typeLabel   = $isEquipment ? 'Alat' : 'Laboratorium';
$typeIcon    = $isEquipment ? 'fa-tools' : 'fa-door-open';
?>

<style>
  .loan-create-page {
    --surface: #ffffff;
    --surface-soft: #f6f8fb;
    --line: #e5e9f2;
    --ink: #0f172a;
    --ink-soft: #6b7280;
    --brand: <?= $type === null ? '#1f6feb' : $accentColor ?>;
    --brand-soft: <?= $type === null ? 'rgba(31, 111, 235, 0.12)' : $accentBg ?>;
    font-family: Manrope, "Segoe UI", -apple-system, BlinkMacSystemFont, sans-serif;
    color: var(--ink);
  }
  .loan-create-page .hero {
    background:
      radial-gradient(circle at 10% 10%, rgba(255, 255, 255, 0.7), transparent 52%),
      linear-gradient(120deg, var(--brand-soft), #ffffff 74%);
    border: 1px solid var(--line);
    border-radius: 18px;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.07);
    margin-bottom: 1rem;
    overflow: hidden;
    position: relative;
  }
  .loan-create-page .hero::after {
    content: "";
    position: absolute;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: var(--brand-soft);
    right: -90px;
    bottom: -120px;
  }
  .loan-create-page .hero-body {
    position: relative;
    z-index: 1;
    padding: 1rem 1.2rem;
  }
  .loan-create-page .hero-title {
    font-size: 1.08rem;
    font-weight: 800;
    margin-bottom: 0.2rem;
  }
  .loan-create-page .hero-sub {
    color: var(--ink-soft);
    font-size: 0.84rem;
    margin-bottom: 0;
  }
  .loan-create-page .btn-modern {
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 700;
  }
  .loan-create-page .btn-action {
    border-radius: 10px;
    font-size: 0.79rem;
    font-weight: 700;
    letter-spacing: 0.01em;
  }
  .loan-create-page .btn-outline-secondary.btn-action {
    background: #ffffff !important;
    border-color: #cfd7e6 !important;
    color: #344054 !important;
  }
  .loan-create-page .btn-outline-secondary.btn-action:hover,
  .loan-create-page .btn-outline-secondary.btn-action:focus,
  .loan-create-page .btn-outline-secondary.btn-action:active {
    background: #f2f5fb !important;
    border-color: #b9c5db !important;
    color: #1f2937 !important;
  }
  .loan-create-page .shell-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 16px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    margin-bottom: 1rem;
    overflow: hidden;
  }
  .loan-create-page .shell-head {
    border-bottom: 1px solid #eef2f7;
    padding: 0.82rem 0.95rem;
  }
  .loan-create-page .shell-title {
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    margin: 0;
    text-transform: uppercase;
  }
  .loan-create-page .shell-body {
    padding: 0.95rem;
  }
  .loan-create-page .progress-wrap {
    align-items: center;
    display: flex;
    gap: 0.55rem;
  }
  .loan-create-page .progress-node {
    align-items: center;
    background: #eef2f7;
    border-radius: 999px;
    color: #64748b;
    display: inline-flex;
    font-size: 0.72rem;
    font-weight: 800;
    gap: 0.35rem;
    letter-spacing: 0.04em;
    padding: 0.35rem 0.65rem;
    text-transform: uppercase;
    white-space: nowrap;
  }
  .loan-create-page .progress-node.active {
    background: var(--brand-soft);
    color: var(--brand);
  }
  .loan-create-page .progress-node.done {
    background: rgba(40, 167, 69, 0.15);
    color: #1f8f48;
  }
  .loan-create-page .progress-line {
    background: #e5e9f2;
    border-radius: 999px;
    flex: 1;
    height: 2px;
    min-width: 20px;
  }
  .loan-create-page .type-choice {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 14px;
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
    color: inherit;
    display: block;
    height: 100%;
    padding: 1rem;
    text-decoration: none;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
  }
  .loan-create-page .type-choice:hover {
    transform: translateY(-3px);
    box-shadow: 0 14px 26px rgba(15, 23, 42, 0.1);
    text-decoration: none;
  }
  .loan-create-page .type-choice.eq:hover {
    border-color: rgba(2, 136, 209, 0.35);
  }
  .loan-create-page .type-choice.lab:hover {
    border-color: rgba(56, 142, 60, 0.35);
  }
  .loan-create-page .type-chip {
    border-radius: 999px;
    display: inline-block;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    padding: 0.24rem 0.5rem;
    text-transform: uppercase;
  }
  .loan-create-page .type-list {
    color: #667085;
    font-size: 0.78rem;
    list-style: none;
    margin: 0;
    padding: 0;
  }
  .loan-create-page .type-list li {
    margin-bottom: 0.38rem;
  }
  .loan-create-page .type-list li:last-child {
    margin-bottom: 0;
  }
  .loan-create-page .type-list i {
    color: #2b8a3e;
    margin-right: 0.42rem;
  }
  .loan-create-page .form-label-modern {
    color: #475467;
    font-size: 0.76rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    margin-bottom: 0.28rem;
    text-transform: uppercase;
  }
  .loan-create-page .form-control,
  .loan-create-page .input-group-text {
    border-radius: 10px;
  }
  .loan-create-page .form-control {
    border-color: #dce3ee;
    font-size: 0.84rem;
  }
  .loan-create-page .form-control:focus {
    border-color: #90b4ff;
    box-shadow: 0 0 0 0.15rem rgba(31, 111, 235, 0.12);
  }
  .loan-create-page textarea.form-control {
    min-height: 120px;
  }
  .loan-create-page .helper-box {
    background: var(--brand-soft);
    border: 1px solid rgba(31, 111, 235, 0.1);
    border-radius: 12px;
    padding: 0.75rem 0.8rem;
  }
  .loan-create-page .helper-title {
    color: var(--brand);
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.04em;
    margin-bottom: 0.2rem;
    text-transform: uppercase;
  }
  .loan-create-page .helper-text {
    color: #546173;
    font-size: 0.78rem;
    margin: 0;
  }
  .loan-create-page .aside-list {
    color: #667085;
    font-size: 0.78rem;
    list-style: none;
    margin: 0;
    padding: 0;
  }
  .loan-create-page .aside-list li {
    margin-bottom: 0.45rem;
  }
  .loan-create-page .aside-list li:last-child {
    margin-bottom: 0;
  }
  @media (max-width: 991.98px) {
    .loan-create-page .progress-wrap {
      overflow-x: auto;
      padding-bottom: 0.2rem;
    }
  }
</style>

<div class="loan-create-page">
  <section class="hero">
    <div class="hero-body d-flex align-items-center justify-content-between flex-wrap" style="gap:.75rem">
      <div>
        <h1 class="hero-title">Buat Proposal Peminjaman</h1>
        <p class="hero-sub">Lengkapi alur pengajuan dari informasi awal hingga proses approval.</p>
      </div>
      <a href="<?= base_url('loans') ?>" class="btn btn-outline-secondary btn-sm btn-modern btn-action">
        <i class="fas fa-arrow-left mr-1"></i>Kembali ke Daftar
      </a>
    </div>
  </section>

  <?php if ($type === null): ?>
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="shell-card mb-3">
          <div class="shell-body text-center">
            <div class="mb-2" style="font-size:1.8rem;color:#1f6feb;"><i class="fas fa-layer-group"></i></div>
            <h2 style="font-size:1.2rem;font-weight:800;margin-bottom:.3rem;">Pilih Jenis Proposal</h2>
            <p class="text-muted mb-0" style="font-size:.84rem;">Proposal alat dan proposal laboratorium diproses terpisah agar alur approval lebih akurat.</p>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <a href="<?= site_url('loans/create?type=equipment') ?>" class="type-choice eq">
              <div class="d-flex align-items-start mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width:46px;height:46px;background:rgba(79,195,247,.18)">
                  <i class="fas fa-tools" style="color:#0288d1"></i>
                </div>
                <div>
                  <h5 class="mb-1" style="font-size:1rem;font-weight:800;color:#075985;">Peminjaman Alat</h5>
                  <span class="type-chip" style="background:rgba(79,195,247,.2);color:#0369a1;">equipment</span>
                </div>
              </div>
              <p class="text-muted mb-3" style="font-size:.8rem;">Untuk instrumen dan peralatan laboratorium yang dipinjam sesuai ketersediaan stok.</p>
              <ul class="type-list">
                <li><i class="fas fa-check-circle"></i>Alat ukur dan instrumen</li>
                <li><i class="fas fa-check-circle"></i>Perlengkapan praktikum</li>
                <li><i class="fas fa-check-circle"></i>Validasi stok real-time</li>
              </ul>
            </a>
          </div>
          <div class="col-md-6 mb-3">
            <a href="<?= site_url('loans/create?type=lab') ?>" class="type-choice lab">
              <div class="d-flex align-items-start mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width:46px;height:46px;background:rgba(129,199,132,.18)">
                  <i class="fas fa-door-open" style="color:#388e3c"></i>
                </div>
                <div>
                  <h5 class="mb-1" style="font-size:1rem;font-weight:800;color:#1f7a33;">Peminjaman Laboratorium</h5>
                  <span class="type-chip" style="background:rgba(129,199,132,.25);color:#2b8a3e;">laboratorium</span>
                </div>
              </div>
              <p class="text-muted mb-3" style="font-size:.8rem;">Untuk pemakaian ruangan laboratorium berdasarkan jadwal dan kapasitas yang tersedia.</p>
              <ul class="type-list">
                <li><i class="fas fa-check-circle"></i>Ruang praktikum dan kelas khusus</li>
                <li><i class="fas fa-check-circle"></i>Validasi bentrok jadwal</li>
                <li><i class="fas fa-check-circle"></i>Dapat memilih lebih dari satu lab</li>
              </ul>
            </a>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>

    <div class="shell-card">
      <div class="shell-body">
        <div class="progress-wrap">
          <span class="progress-node active"><i class="fas fa-pen"></i>Step 1: Informasi</span>
          <span class="progress-line"></span>
          <span class="progress-node"><i class="fas <?= $typeIcon ?>"></i>Step 2: Pilih <?= esc($typeLabel) ?></span>
          <span class="progress-line"></span>
          <span class="progress-node"><i class="fas fa-paper-plane"></i>Step 3: Kirim Approval</span>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <section class="shell-card">
          <div class="shell-head">
            <h6 class="shell-title"><i class="fas <?= $typeIcon ?> mr-1" style="color:<?= $accentColor ?>"></i>Informasi Proposal Peminjaman <?= esc($typeLabel) ?></h6>
          </div>
          <div class="shell-body">
            <?php if (session('errors')): ?>
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Perbaiki kesalahan berikut:</strong>
                <ul class="mb-0 mt-1 pl-3">
                  <?php foreach (session('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                  <?php endforeach; ?>
                </ul>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
              </div>
            <?php endif; ?>

            <?php if (session('error')): ?>
              <div class="alert alert-danger alert-dismissible fade show">
                <?= esc(session('error')) ?>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
              </div>
            <?php endif; ?>

            <form action="<?= base_url('loans/store') ?>" method="post" id="proposal-form">
              <?= csrf_field() ?>
              <input type="hidden" name="loan_type" value="<?= esc($type) ?>">
              <input type="hidden" name="requires_l2" value="1">

              <div class="form-group">
                <label class="form-label-modern" for="title">Judul Proposal <span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control"
                       id="title"
                       name="title"
                       value="<?= esc(old('title')) ?>"
                       placeholder="<?= $isEquipment ? 'Contoh: Peminjaman Alat Ukur untuk Penelitian Tugas Akhir' : 'Contoh: Peminjaman Lab Komputer untuk Praktikum Basis Data' ?>"
                       required>
                <small class="text-muted">Buat judul singkat dan spesifik agar proses review lebih cepat.</small>
              </div>

              <div class="form-group">
                <label class="form-label-modern d-block">Periode Peminjaman <span class="text-danger">*</span></label>
                <div class="row">
                  <div class="col-md-6 mb-2 mb-md-0">
                    <label for="start_at" class="small text-muted mb-1">Mulai</label>
                    <input type="datetime-local" class="form-control" id="start_at" name="start_at" value="<?= esc(old('start_at')) ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label for="end_at" class="small text-muted mb-1">Selesai</label>
                    <input type="datetime-local" class="form-control" id="end_at" name="end_at" value="<?= esc(old('end_at')) ?>" required>
                  </div>
                </div>
              </div>

              <div class="form-group mb-3">
                <label class="form-label-modern" for="objective">Tujuan dan Ringkasan Kebutuhan <span class="text-danger">*</span></label>
                <textarea class="form-control"
                          id="objective"
                          name="objective"
                          rows="5"
                          placeholder="Jelaskan tujuan penggunaan dan kebutuhan spesifik..."
                          required><?= esc(old('objective')) ?></textarea>
                <small class="text-muted">Minimal 10 karakter. Semakin jelas deskripsi, semakin cepat diproses.</small>
              </div>

              <div class="helper-box mb-3">
                <div class="helper-title"><i class="fas fa-shield-alt mr-1"></i>Alur Persetujuan Dua Tingkat</div>
                <p class="helper-text">Proposal akan melalui approval Laboran lalu Kepala Lab sebelum dinyatakan disetujui.</p>
              </div>

              <div class="d-flex flex-wrap align-items-center" style="gap:.55rem;">
                <button type="submit" class="btn <?= $accentBtn ?> btn-modern btn-action px-3">
                  <i class="fas fa-save mr-1"></i>Simpan dan Pilih <?= esc($typeLabel) ?>
                </button>
                <a href="<?= base_url('loans/create') ?>" class="btn btn-light btn-modern btn-action px-3">
                  <i class="fas fa-sync-alt mr-1"></i>Ganti Tipe
                </a>
              </div>
            </form>
          </div>
        </section>
      </div>

      <div class="col-lg-4">
        <section class="shell-card">
          <div class="shell-head"><h6 class="shell-title">Tipe Proposal</h6></div>
          <div class="shell-body">
            <div class="d-flex align-items-center">
              <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width:40px;height:40px;background:<?= $accentBg ?>">
                <i class="fas <?= $typeIcon ?>" style="color:<?= $accentColor ?>"></i>
              </div>
              <div>
                <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;">Jenis</div>
                <div style="font-size:.9rem;font-weight:800;color:<?= $accentColor ?>;">Peminjaman <?= esc($typeLabel) ?></div>
              </div>
            </div>
          </div>
        </section>

        <section class="shell-card">
          <div class="shell-head"><h6 class="shell-title">Panduan Proses</h6></div>
          <div class="shell-body">
            <ul class="aside-list">
              <li><i class="fas fa-check-circle text-primary mr-1"></i>Isi informasi proposal pada tahap ini.</li>
              <li><i class="fas fa-check-circle text-primary mr-1"></i>Di tahap berikutnya Anda memilih <?= strtolower($typeLabel) ?>.</li>
              <li><i class="fas fa-check-circle text-primary mr-1"></i>Setelah item lengkap, proposal dikirim untuk approval berjenjang.</li>
            </ul>
          </div>
        </section>

        <section class="shell-card">
          <div class="shell-head"><h6 class="shell-title">Perlu Diketahui</h6></div>
          <div class="shell-body">
            <?php if ($isEquipment): ?>
              <ul class="aside-list">
                <li>Stok alat diverifikasi saat item ditambahkan.</li>
                <li>Satu proposal dapat berisi beberapa alat.</li>
                <li>Peminjaman ruangan dibuat pada proposal terpisah.</li>
              </ul>
            <?php else: ?>
              <ul class="aside-list">
                <li>Ketersediaan ruangan diverifikasi saat pemilihan item.</li>
                <li>Satu proposal dapat berisi beberapa ruangan.</li>
                <li>Peminjaman alat dibuat pada proposal terpisah.</li>
              </ul>
            <?php endif; ?>
          </div>
        </section>
      </div>
    </div>
  <?php endif; ?>
</div>
