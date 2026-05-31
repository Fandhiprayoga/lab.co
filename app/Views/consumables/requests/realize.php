<?php
$bhpRequest   = $bhpRequest ?? [];
$requestItems = $requestItems ?? [];
?>

<div class="card">
  <div class="card-header">
    <p class="text-muted mb-0">
      Catat jumlah bahan yang benar-benar terpakai. Jumlah disetujui ditampilkan sebagai referensi.
    </p>
  </div>
  <div class="card-body">
    <form method="post" action="<?= site_url('consumables/requests/' . ($bhpRequest['public_id'] ?? $bhpRequest['id']) . '/realize') ?>" id="realizeForm">
      <?= csrf_field() ?>
      <table class="table table-bordered">
        <thead class="thead-light">
          <tr>
            <th>Bahan</th>
            <th class="text-right">Qty Disetujui</th>
            <th class="text-right" style="width:180px">Qty Aktual Terpakai <span class="text-danger">*</span></th>
            <th>Satuan</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($requestItems as $ri): ?>
          <tr>
            <td><?= esc($ri['item_name'] ?? '—') ?></td>
            <td class="text-right"><?= number_format((float)($ri['qty_approved'] ?? $ri['qty_requested']), 2) ?></td>
            <td>
              <input type="number" step="0.01" min="0"
                     name="qty_actual[<?= $ri['id'] ?>]"
                     class="form-control form-control-sm text-right"
                     value="<?= number_format((float)($ri['qty_approved'] ?? $ri['qty_requested']), 2) ?>"
                     required>
            </td>
            <td><?= esc($ri['unit_symbol'] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="d-flex justify-content-between mt-3">
        <a href="<?= site_url('consumables/requests/' . ($bhpRequest['public_id'] ?? $bhpRequest['id'])) ?>" class="btn btn-light">
          <i class="fas fa-arrow-left mr-1"></i> Batal
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-clipboard-check mr-1"></i> Simpan Realisasi & Selesaikan
        </button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  $('#realizeForm').on('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
      title: 'Simpan Realisasi?',
      text: 'Data realisasi akan disimpan dan permintaan akan diselesaikan. Tindakan ini tidak dapat dibatalkan.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Simpan',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#007bff',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });
});
</script>
