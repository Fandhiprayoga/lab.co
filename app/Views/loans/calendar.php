<?= $this->section('css') ?>
<style>
  .modal-backdrop { overflow: auto !important;display: none !important;}
  /* .modal { z-index: 9999 !important; } */
</style>
<?= $this->endSection() ?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h4><i class="fas fa-calendar-alt mr-1"></i> Kalender Jadwal Peminjaman Lab</h4>
        <div class="card-header-action d-flex align-items-center">
          <div class="form-group mb-0 mr-2" style="min-width:200px;">
            <select id="filter-calendar-lab" class="form-control form-control-sm">
              <option value="">Semua Lab</option>
              <?php foreach ($labs as $lab): ?>
                <option value="<?= (int) $lab['id'] ?>"><?= esc($lab['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="button" class="btn btn-outline-secondary" id="calendar-today-btn">
            <i class="fas fa-calendar-day"></i> Hari Ini
          </button>
        </div>
      </div>
      <div class="card-body">
        <?php echo view('components/calendar', [
          'calendarId'  => 'loan-calendar',
          'eventSource' => base_url('loans/calendar/data'),
          'options'     => [
            'defaultView' => 'month',
            'height'      => 650,
            'eventClick'  => 'window.calShowEventDetail(event);',
          ],
        ]); ?>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detail Event -->
<div class="modal fade" id="modal-event-detail" tabindex="-1" role="dialog" data-backdrop="true" data-keyboard="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-event-title">Detail Peminjaman</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <td class="text-muted" style="width:100px;">Kegiatan</td>
            <td><strong id="modal-event-activity">-</strong></td>
          </tr>
          <tr>
            <td class="text-muted">Pemohon</td>
            <td id="modal-event-proposer">-</td>
          </tr>
          <tr>
            <td class="text-muted">Lab</td>
            <td id="modal-event-labs">-</td>
          </tr>
          <tr>
            <td class="text-muted">Mulai</td>
            <td id="modal-event-start">-</td>
          </tr>
          <tr>
            <td class="text-muted">Selesai</td>
            <td id="modal-event-end">-</td>
          </tr>
          <tr>
            <td class="text-muted">Status</td>
            <td><span id="modal-event-status" class="badge">-</span></td>
          </tr>
        </table>
      </div>
      <div class="modal-footer">
        <a id="modal-event-link" href="#" class="btn btn-primary" target="_blank">
          <i class="fas fa-external-link-alt mr-1"></i> Lihat Detail
        </a>
        <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<?= $this->section('js') ?>
<script>
  $(function () {
    $('#calendar-today-btn').on('click', function () {
      $('#loan-calendar').fullCalendar('today');
    });

    $('#filter-calendar-lab').on('change', function () {
      var labId = $(this).val();
      var url   = '<?= base_url('loans/calendar/data') ?>';
      if (labId) { url += '?lab_id=' + labId; }
      $('#loan-calendar').fullCalendar('removeEventSource', '<?= base_url('loans/calendar/data') ?>');
      $('#loan-calendar').fullCalendar('addEventSource', url);
    });

    // Ensure backdrop click closes modal (z-index conflict fix)
    $(document).on('click', '.modal-backdrop', function () {
      $('#modal-event-detail').modal('hide');
    });
  });

  window.calShowEventDetail = function (event) {
    function fmt(d) {
      if (!d) return '-';
      var dt = new Date(d);
      return dt.toLocaleDateString('id-ID', { weekday:'long', year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit' });
    }

    $('#modal-event-activity').text(event.title || '-');
    $('#modal-event-proposer').text(event.proposer || '-');
    $('#modal-event-labs').text(event.lab_names || '-');
    $('#modal-event-start').text(fmt(event.start));
    $('#modal-event-end').text(fmt(event.end));
    $('#modal-event-status').text(event.status || '-').css('background-color', event.color || '#6c757d').css('color', '#fff');
    $('#modal-event-link').attr('href', event.detail_url || '#');
    $('#modal-event-detail').modal('show');
  };
</script>
<?= $this->endSection() ?>
