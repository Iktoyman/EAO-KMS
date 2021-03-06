<?php
	require "../connect.php";
	require "connect.php";
	require "timezones.php";
	session_start();
	require "establish_user.php";

	if (!isset($_GET['activities'])) {
		$action = 'Execute Change';
		$addact = " OR actions = 'Start / Stop' OR actions = 'Health Check'";
		$eventlimit_flag = false;
	}
	else {
		if ($_GET['activities'] == 'changes') {
			$action = 'Execute Change';
			$addact = " OR actions = 'Start / Stop' OR actions = 'Health Check'";
			$eventlimit_flag = false;
		}
		else if ($_GET['activities'] == 'projects') {
			$action = 'Project';
			$addact = '';
			$eventlimit_flag = true;
		}
		else {
			$action = 'Import Transport';
			$addact = '';
			$eventlimit_flag = true;
		}
	}

	$month_activities = array();
	$get_dates = mysqli_query($ch_conn, "SELECT DISTINCT DATE(pht_start_datetime) AS dates FROM items WHERE (actions = '$action'".$addact.") ORDER BY dates ASC");
	$a = 0;
	while ($date_row = mysqli_fetch_array($get_dates)) {
		$reference_date = $date_row['dates'];
		$get_activities = mysqli_query($ch_conn, "SELECT item_id, description, pht_start_datetime, pht_end_datetime, status FROM items WHERE (actions = '$action'".$addact.") AND DATE(pht_start_datetime) = '$reference_date' ORDER BY pht_start_datetime, description ASC");
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
		var limit = <?php echo json_encode($eventlimit_flag); ?>;
		var action = '<?php echo $action; ?>';
		console.log(events);

		$(document).ready(function() {
			$('#chg_calendar').fullCalendar({
				firstDay: 1,	
				header: {
					left: 'prev,next today',
					center: 'title',
					right: 'basicWeek,listWeek,listDay,month'
				},
				defaultView: 'basicWeek',
				height: 600,
				timeFormat: 'HH:mmt',
				columnFormat: 'dddd',
				weekNumbers: true,				
				editable: true,
				eventLimit: limit,
				navLinks: true,
				events: events,
				views: {
					basic: {
						eventLimit: 10
					}
				},
				eventClick: function(calEvent) {
					showDetails(calEvent.id);
				}
			});

			$('.fc-today-button').html("Today");
			$('.fc-month-button').html("Month View");
			$('.fc-basicWeek-button').html("Week View");
			$('.fc-listWeek-button').html("List by Week");
			$('.fc-listDay-button').html("List by Day");

		})
	</script>
</head>

<body>
	<?php
		require "sidebar.php";
		require "navbar.php";
	?>	

	<div class="body_div">
		<div id="chg_calendar">

		</div>
		<div class="chg_calendar_legend-div">
			<ul class="chg_calendar_legend-list">
				<li> <span style="background-color: rgb(000, 201, 255); padding: 0.1%; border-radius: 0.15vw; border: 1px solid white"> &nbsp;&nbsp;&nbsp;&nbsp; </span> &nbsp; Open Changes </li>
				<li> <span style="background-color: yellow; padding: 0.1%; border-radius: 0.15vw; border: 1px solid white"> &nbsp;&nbsp;&nbsp;&nbsp; </span> &nbsp; Changes in Progress </li>
				<li> <span style="background-color: green; padding: 0.1%; border-radius: 0.15vw; border: 1px solid white"> &nbsp;&nbsp;&nbsp;&nbsp; </span> &nbsp; Completed Changes </li>
				<li> <span style="background-color: #f97e31; padding: 0.1%; border-radius: 0.15vw; border: 1px solid white"> &nbsp;&nbsp;&nbsp;&nbsp; </span> &nbsp; Overdue Changes </li>
				<li> <span style="background-color: #e50000; padding: 0.1%; border-radius: 0.15vw; border: 1px solid white"> &nbsp;&nbsp;&nbsp;&nbsp; </span> &nbsp; Failed Changes </li>
				<li> <span style="background-color: #888; padding: 0.1%; border-radius: 0.15vw; border: 1px solid white"> &nbsp;&nbsp;&nbsp;&nbsp; </span> &nbsp; Cancelled Changes </li>
			</ul>
		</div>
	</div>
<?php
	require "account_modals.php";
?>
</body>
</html>