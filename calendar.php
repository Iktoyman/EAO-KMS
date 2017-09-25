<?php
	require "../connect.php";
	require "connect.php";
	require "timezones.php";
	session_start();
	require "establish_user.php";

	if (!isset($_GET['activities'])) {
		$action = 'Execute Change';
		$eventlimit_flag = false;
	}
	else {
		if ($_GET['activities'] == 'changes') {
			$action = 'Execute Change';
			$eventlimit_flag = false;
		}
		else {
			$action = 'Import Transport';
			$eventlimit_flag = true;
		}
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
		var limit = <?php echo json_encode($eventlimit_flag); ?>;
		var action = '<?php echo $action; ?>';

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
	<div class="sidebar-div-container">
		<div class="sidebar-div">
			<div class="sidebar-header-div">
				<a id='header-sidebar-btnlink-open'><span class="glyphicon glyphicon-menu-hamburger header-sidebar-btn"></span></a>
				<span class="header-title"> PROJECT DELTA </span>	
			</div>
			<div class="sidebar-body-div">
				<ul>
					<li> <a id='new-item_link' onclick='triggerHomeEvent("new_item")'> NEW ITEM </a> </li>
					<li> <a id='my-accounts_link' onclick='triggerHomeEvent("my_accounts")'> MY ACCOUNTS </a> </li>
					<li> <a href='calendar.php'> CHANGE CALENDAR </a> </li>
					<li> <a href='sow.php'> START OF WEEK </a> </li>
				</ul>
			</div>
		</div>
	</div>	

	<div class="header_div">
		<?php
		require "navbar.php";
		?>
	</div>

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