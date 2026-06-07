<?php
/**
 * Reusable FullCalendar component.
 *
 * Usage in any view:
 *   echo view('components/calendar', [
 *       'calendarId'  => 'my-calendar',
 *       'eventSource' => base_url('some/url/json'),
 *       'options'     => ['defaultView' => 'month', 'height' => 600],
 *   ]);
 *
 * @var string  $calendarId   DOM element ID (default: 'app-calendar')
 * @var string  $eventSource  URL to fetch JSON events
 * @var array   $options      Additional FullCalendar options (key-value)
 */

$calendarId  = $calendarId ?? 'app-calendar';
$eventSource = $eventSource ?? '';
$options     = $options ?? [];
?>

<?= $this->section('css') ?>
<link rel="stylesheet" href="<?= base_url('assets/modules/fullcalendar/fullcalendar.min.css') ?>">
<style>
  #<?= $calendarId ?> { max-width: 100%; }
</style>
<?= $this->endSection() ?>

<div id="<?= $calendarId ?>"></div>

<?= $this->section('js') ?>
<script src="<?= base_url('assets/modules/fullcalendar/fullcalendar.min.js') ?>"></script>
<script src="<?= base_url('assets/modules/fullcalendar/locale/id.js') ?>"></script>
<script>
  $(function () {
    var calId   = '#<?= $calendarId ?>';
    var source  = <?= $eventSource !== '' ? '"' . $eventSource . '"' : 'null' ?>;
    var extras  = <?= json_encode($options) ?>;

    var defaults = {
      locale: 'id',
      timezone: 'local',
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'month,agendaWeek,agendaDay,listMonth'
      },
      buttonText: {
        today: 'Hari Ini',
        month: 'Bulan',
        week: 'Minggu',
        day: 'Hari',
        list: 'Agenda'
      },
      monthNames: ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
      monthNamesShort: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
      dayNames: ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],
      dayNamesShort: ['Min','Sen','Sel','Rab','Kam','Jum','Sab'],
      firstDay: 1,
      height: 'auto',
      eventLimit: true,
      eventLimitText: function (n) { return '+ ' + n + ' lagi'; },
      noEventsMessage: 'Tidak ada jadwal.',
      loading: function (isLoading) {
        if (isLoading) {
          $(calId).parent().css('opacity', '0.6');
        } else {
          $(calId).parent().css('opacity', '1');
        }
      }
    };

    if (source) {
      defaults.events = source;
    }

    // Convert string callback params to actual functions (passed as JSON from PHP)
    ['eventClick', 'eventRender', 'dayClick', 'viewRender'].forEach(function (name) {
      if (extras[name] && typeof extras[name] === 'string') {
        extras[name] = new Function('event', 'jsEvent', 'view', extras[name]);
      }
    });

    $.extend(defaults, extras);

    $(calId).fullCalendar(defaults);
  });
</script>
<?= $this->endSection() ?>
