<?php
	require "../connect.php";
	require "connect.php";
	require "timezones.php";
	session_start();
	require "establish_user.php";

	$month_activities = array();
	$get_activities = mysqli_query($ch_conn, "SELECT item_id, description, pht_start_datetime, pht_end_datetime FROM items WHERE MONTH(pht_start_datetime) = MONTH(NOW()) AND actions = 'Execute Change'");
	$a = 0;
	while ($row = mysqli_fetch_array($get_activities)) {
		$month_activities[$a]['id'] = $row['item_id'];
		$month_activities[$a]['title'] = $row['description'];
		$month_activities[$a]['start'] = $row['pht_start_datetime'];
		$month_activities[$a]['end'] = $row['pht_end_datetime'];
		$a++;
	}

	// GET TIMEZONES ARRAY
	$timezones = array();
	$timezones = initializeTimezones();
?>

<html>
<head>
	<title> Project Delta - Change Calendar</title>
	<link href="js/fullcalendar/fullcalendar.print.css" type="text/css" rel="stylesheet">
	<?php
		require "head.php";
	?>
	<script type="text/javascript" src="js/account.js"></script>
	<script type="text/javascript" src="js/moment/min/moment.min.js"></script>
	<script type="text/javascript" src="js/fullcalendar/fullcalendar.min.js"></script>
	<link href="js/fullcalendar/fullcalendar.min.css" type="text/css" rel="stylesheet">

	<script>
		var events = <?php echo json_encode($month_activities); ?>;

		$(document).ready(function() {
			$('#chg_calendar').fullCalendar({
				firstDay: 1,	
				header: {
					left: 'prev,next today',
					center: 'title',
					right: 'month,basicWeek,basicDay'
				},
				editable: true,
				events: events,
				eventClick: function(calEvent) {
					showDetails(calEvent.id);
				}
			});

			$('.fc-today-button').html("Today");
			$('.fc-month-button').html("Month View");
			$('.fc-basicWeek-button').html("Week View");
			$('.fc-basicDay-button').html("Day View");

			console.log($('.fc-row.fc-week').height());
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
	</div>
<?php
	require "account_modals.php";
?>
</body>
</html>