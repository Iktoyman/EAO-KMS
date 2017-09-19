<?php
	require "../connect.php";
	require "connect.php";
	require "timezones.php";
	session_start();
	require "establish_user.php";

	if (!isset($_GET['activities'])) {
		$action = 'Execute Change';
	}
	else {
		if ($_GET['activities'] == 'changes')
			$action = 'Execute Change';
		else
			$action = 'Import Transport';
	}

	$month_activities = array();
	$get_dates = mysqli_query($ch_conn, "SELECT DISTINCT DATE(pht_start_datetime) AS dates FROM items WHERE actions = '$action' ORDER BY dates ASC");
	$a = 0;
	while ($date_row = mysqli_fetch_array($get_dates)) {
		$reference_date = $date_row['dates'];
		$get_activities = mysqli_query($ch_conn, "SELECT item_id, description, pht_start_datetime, pht_end_datetime, status FROM items WHERE actions = '$action' AND DATE(pht_start_datetime) = '$reference_date' ORDER BY pht_start_datetime, description ASC");
		$disp = 0;
		while ($row = mysqli_fetch_array($get_activities)) {
			$month_activities[$a]['id'] = $row['item_id'];
			$month_activities[$a]['title'] = html_entity_decode($row['description']);
			$month_activities[$a]['start'] = $row['pht_start_datetime'];
			$month_activities[$a]['end'] = $row['pht_end_datetime'];
			if ($row['status'] == 'Open')
				$month_activities[$a]['className'] = 'cal_event_div-open';
			else if ($row['status'] == 'In Progress')
				$month_activities[$a]['className'] = 'cal_event_div-inpr';
			else if ($row['status'] == 'Completed')
				$month_activities[$a]['className'] = 'cal_event_div-comp';
			else if ($row['status'] == 'Failed')
				$month_activities[$a]['className'] = 'cal_event_div-fail';
			else if ($row['status'] == 'Overdue')
				$month_activities[$a]['className'] = 'cal_event_div-over';
			else if ($row['status'] == 'Cancelled')
				$month_activities[$a]['className'] = 'cal_event_div-canc';
			if ($disp > 4) 
				$month_activities[$a]['className'] .= ' event-excess';
			$disp++;
			$a++;
		}
	}

	// GET TIMEZONES ARRAY
	$timezones = array();
	$timezones = initializeTimezones();
?>

<html>
<head>
	<title> Project Delta - Change Calendar</title>
	<link href="js/fullcalendar/fullcalendar.print.css" type="text/css" rel="stylesheet" media="print">
	<?php
		require "head.php";
	?>
	<script type="text/javascript" src="js/account.js"></script>
	<script type="text/javascript" src="js/moment/min/moment.min.js"></script>
	<script type="text/javascript" src="js/fullcalendar/fullcalendar.js"></script>
	<link href="js/fullcalendar/fullcalendar.min.css" type="text/css" rel="stylesheet">

	<script>
		var events = <?php echo json_encode($month_activities); ?>;

		$(document).ready(function() {
			$('#chg_calendar').fullCalendar({
				firstDay: 1,	
				header: {
					left: 'prev,next today',
					center: 'title',
					right: 'month,basicWeek,listWeek,listDay'
				},
				timeFormat: 'HH:mmt',
				columnFormat: 'dddd',
				weekNumbers: true,				
				editable: true,
				eventLimit: true,
				navLinks: true,
				events: events,
				eventClick: function(calEvent) {
					showDetails(calEvent.id);
				}
			});

			$('.fc-today-button').html("Today");
			$('.fc-month-button').html("Month View");
			$('.fc-basicWeek-button').html("Week View");
			$('.fc-listWeek-button').html("List by Week");
			$('.fc-listDay-button').html("List by Day");

			//$('.event-excess').css("display", 'none');

		})
	</script>
</head>

<body>
	<div class="header_div">
		<div class="header_name_div">
			<a href="../delta"> <span class="header_name"> CHANGE TRACKER </span> </a>
		</div>
		<div class="header_navbar_div">
			
		</div>
		<div class="header_user_div">

		</div>	

	</div>

	<div class="body_div">
		<div id="chg_calendar">

		</div>
		<hr>
		<ul class='change-calendar-view-ul'>
			<li><a href='calendar.php?activities=changes'> Change Activities </a></li>
			<li><a href='calendar.php?activities=transports'> Transports </a></li>
		</ul>
	</div>
<?php
	require "account_modals.php";
?>
</body>
</html>