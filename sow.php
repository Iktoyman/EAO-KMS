<?php
	require "../connect.php";
	require "connect.php";
	require "timezones.php";
	session_start();
	require "establish_user.php";

	// Set team
	if (!isset($_GET['team'])) {
		if ($_SESSION['ct_team'] != 99)
			$team = $_SESSION['ct_team'];
		else
			$team = 1;
	}
	else
		$team = $_GET['team'];

	$get_teams = mysqli_query($ch_conn, "SELECT team_id, team_name FROM team WHERE team_id NOT IN (98, 99)");
	$teams = array();
	while ($row = mysqli_fetch_array($get_teams))
		$teams[] = $row;

	$friday = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT STR_TO_DATE(CONCAT(YEAR(NOW()), WEEK(NOW()) - 1, 'Friday'), '%X%V %W') AS fri"))['fri'];
	$monday = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT STR_TO_DATE(CONCAT(YEAR(NOW()), WEEK(NOW()), 'Monday'), '%X%V %W') AS mon"))['mon'];
	$sunday = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT STR_TO_DATE(CONCAT(YEAR(NOW()), WEEK(NOW()) + 1, 'Sunday'), '%X%V %W') AS sun"))['sun'];
	
	// Get Weekend Changes
	$changes_ar1 = array();
	$ar1_statuses = array();
	$ar2_statuses = array();
	$ar1_statuses['Open'] = $ar2_statuses['Open'] = 0;
	$ar1_statuses['In Progress'] = $ar2_statuses['In Progress'] = 0;
	$ar1_statuses['Completed'] = $ar2_statuses['Completed'] = 0;
	$ar1_statuses['Cancelled'] = $ar2_statuses['Cancelled'] = 0;
	$ar1_statuses['Failed'] = $ar2_statuses['Failed'] = 0;
	$ar1_statuses['Overdue'] = $ar2_statuses['Overdue'] = 0;

	$chg_res = mysqli_query($ch_conn, "SELECT i.item_id, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u WHERE i.account_id = a.acct_id AND i.primary_resource = u.user_id AND a.team_id = $team AND ((i.pht_start_datetime BETWEEN '$friday 00:00:00' AND '$monday 23:59:59') OR (i.pht_end_datetime BETWEEN '$friday 00:00:00' AND '$monday 23:59:59')) AND i.actions = 'Execute Change' ORDER BY i.pht_start_datetime DESC");
	$a = 0;
	while ($chg_row = mysqli_fetch_array($chg_res)) {
		$changes_ar1[$a] = $chg_row;
		$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $chg_row['item_id']);
		$changes_ar1[$a]['name'] .= " <i>(Primary)</i><br>";
		$sr_ar = array();
		while ($sr_row = mysqli_fetch_array($sec_res_result))
			$sr_ar[] = $sr_row['name'];
		$sr = implode('; ', $sr_ar);
		$changes_ar1[$a]['name'] .= $sr;
		$ar1_statuses[$chg_row['status']]++;	
		$a++;
	}

	// Get Upcoming Changes
	$changes_ar2 = array();
	$chg_res = mysqli_query($ch_conn, "SELECT i.item_id, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u WHERE i.account_id = a.acct_id AND i.primary_resource = u.user_id AND a.team_id = $team AND ((i.pht_start_datetime BETWEEN CURDATE() AND '$sunday') OR (i.pht_end_datetime BETWEEN CURDATE() AND '$sunday')) AND i.actions = 'Execute Change' ORDER BY i.pht_start_datetime DESC");
	$a = 0;
	while ($chg_row = mysqli_fetch_array($chg_res)) {
		$changes_ar2[$a] = $chg_row;
		$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $chg_row['item_id']);
		$changes_ar2[$a]['name'] .= " <i>(Primary)</i><br>";
		$sr_ar = array();
		while ($sr_row = mysqli_fetch_array($sec_res_result))
			$sr_ar[] = $sr_row['name'];
		$sr = implode('; ', $sr_ar);
		$changes_ar2[$a]['name'] .= $sr;
		$ar2_statuses[$chg_row['status']]++;
		$a++;
	}

	// GET TIMEZONES ARRAY
	$timezones = array();
	$timezones = initializeTimezones();
?>

<html>
<head>
	<title> Project Delta - Start of Week</title>
	<?php
		require "head.php";
	?>
	<script type="text/javascript" src="js/account.js"></script>
	<script type="text/javascript" src="js/moment/min/moment.min.js"></script>

	<script>
	</script>
</head>

<body>
	<?php
		require "sidebar.php";
		require "navbar.php";
	?>

	</div>

	<div class="body_div">
		<h3 style='font-family: "Montserrat"'> Start of Week - <?php echo $teams[$team - 1]['team_name']; ?></h3><hr>
			<span style='font-size: 0.9vw; font-weight: bold'> Summary of Changes </span><br>
			<div class='sow_details_div'>
			<?php
				echo "Number of Weekend Changes: " . sizeof($changes_ar1) . "<br>";
				foreach ($ar1_statuses as $key => $val) {
					if ($val == 1) {
						$res = mysqli_query($ch_conn, "SELECT i.item_id FROM items i, account a WHERE a.acct_id = i.account_id AND a.team_id = $team AND ((i.pht_start_datetime BETWEEN '$friday 00:00:00' AND '$monday 23:59:59') OR (i.pht_end_datetime BETWEEN '$friday 00:00:00' AND '$monday 23:59:59')) AND i.actions = 'Execute Change' AND i.status = '$key'");
						$id = mysqli_fetch_assoc($res)['item_id'];
						echo "- $key Changes: <a id='sow-details_link' onclick='showDetails($id)'>$val</a> <br>";
					}
					else if ($val > 1)
						echo "- $key Changes: $val <br>";
				}
				echo "<br>";
				echo "Number of Upcoming Changes: " . sizeof($changes_ar2) . "<br>";
				foreach ($ar2_statuses as $key => $val) {
					if ($val == 1) {
						$res = mysqli_query($ch_conn, "SELECT i.item_id FROM items i, account a WHERE a.acct_id = i.account_id AND a.team_id = $team AND ((i.pht_start_datetime BETWEEN CURDATE() AND '$sunday') OR (i.pht_end_datetime BETWEEN CURDATE() AND '$sunday')) AND i.actions = 'Execute Change' AND i.status = '$key'");
						$id = mysqli_fetch_assoc($res)['item_id'];
						echo "- $key Changes: <a id='sow-details_link' onclick='showDetails($id)'>$val</a> <br>";
					}
					else if ($val > 1)
						echo "- $key Changes: $val <br>";
				}
			# of Weekend Changes <br>
				//echo "- Open changes: ". $ar1_statuses['Open'] . "<br>";
				//echo "- Completed changes: " . $ar1_statuses['Completed'] . "<br>";
			# of Upcoming Changes <br>
			?>
			</div>
		<hr>
			<div class="account_details_top">
				<ul class="nav nav-tabs">
					<li role="presentation" class="active" id="overview_tab"><a href="#weekend" role="tab" data-toggle="tab" aria-controls="weekend"> Weekend Changes </a> </li>
					<li role="presentation"> <a href="#upcoming" role="tab" data-toggle="tab" aria-controls="upcoming"> Upcoming Changes this Week </a> </li>
				</ul>
			</div>
			<table class="table table-hover change-list" id='change-list-thead' style="font-size: 0.7vw">
				<thead>
				<tr id='change-list-thead-tr'>
					<th class='change-list-th' id='1' width=8.5%> <span id='1-label'>Change ID</span></th>
					<th class='change-list-th' id='2' width=16.5%> <span id='2-label'>Account</span></th>
					<th class='change-list-th' id='4' width=24.75%> <span id='4-label'>Title</span></th>
					<th class='change-list-th' id='8' width=15%> <span id='8-label'> Resources </span> </th>
					<th class='change-list-th' id='5' width=12.5%> Planned Start </th>
					<th class='change-list-th' id='6' width=12.5%> Planned End </th>
					<th class='change-list-th' id='7' > <span id='7-label'>Status</span></th>
				</tr>
				</thead>
			</table>
			<div class='tab-content' id='sow-tbody-div'>
				<div role='tabpanel' class='tab-pane active' id='weekend'>	
					<table class="table table-hover change-list" style="font-size: 0.7vw">
						<tbody id='change-list-tbody'>
						<?php
							for ($x = 0; $x < sizeof($changes_ar1); $x++) {
								echo "<tr>";
								echo "<td width=8.5% id='chg_list-id'><a onclick='showDetails(" . $changes_ar1[$x]['item_id'] .")'>" . $changes_ar1[$x]['change_ticket_id'] . "</a></td>";
								echo "<td width=6% id='chg_list-aa'>" . $changes_ar1[$x]['acct_abbrev'] . "</td>";
								echo "<td width=10.5% id='chg_list-an'>" . $changes_ar1[$x]['acct_name'] . "</td>";
								//echo "<td width=25%>" . $changes[$x]['actions'] . "</td>";
								echo "<td width=25% id='chg_list-cd'>" . $changes_ar1[$x]['description'] . "</td>";
								echo "<td width=15% id='chg_list-res'>" . $changes_ar1[$x]['name'] . "</td>";
								if ($changes_ar1[$x]['pht_start_datetime'] == 'Dec 31, 2999 - 12:00AM' && $changes_ar1[$x]['pht_end_datetime'] == 'Dec 31, 2999 - 11:59PM') {
									echo "<td width=25.5% colspan=2 id='chg_list-st'> - No schedule yet: Tentatively planned for the future - </td>";
								}
								else {
									echo "<td width=12.75% id='chg_list-st'>" . $changes_ar1[$x]['pht_start_datetime'] . "</td>";
									echo "<td width=12.75% id='chg_list-et'>" . $changes_ar1[$x]['pht_end_datetime'] . "</td>";
								}
								if ($changes_ar1[$x]['status'] == 'In Progress')
									$stat_hl = "status-inprogress";
								else if ($changes_ar1[$x]['status'] == 'Completed')
									$stat_hl = "status-completed";
								else if ($changes_ar1[$x]['status'] == 'Failed')
									$stat_hl = "status-failed";
								else 
									$stat_hl = "";
								echo "<td class='".$stat_hl."' id='chg_list-status'>" . $changes_ar1[$x]['status'] . "<br><br><i><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(".$changes_ar1[$x]['item_id'].")'>View Notes</a></i></td>";
								echo "</tr>";
							}
						?>
						</tbody>
					</table>
				</div>

				<div role='tabpanel' class='tab-pane' id='upcoming'>
					<table class="table table-hover change-list" style="font-size: 0.7vw">
						<tbody id='change-list-tbody'>
						<?php
							for ($x = 0; $x < sizeof($changes_ar2); $x++) {
								echo "<tr>";
								echo "<td width=8.5% id='chg_list-id'><a onclick='showDetails(" . $changes_ar2[$x]['item_id'] .")'>" . $changes_ar2[$x]['change_ticket_id'] . "</a></td>";
								echo "<td width=6% id='chg_list-aa'>" . $changes_ar2[$x]['acct_abbrev'] . "</td>";
								echo "<td width=10.5% id='chg_list-an'>" . $changes_ar2[$x]['acct_name'] . "</td>";
								//echo "<td width=25%>" . $changes[$x]['actions'] . "</td>";
								echo "<td width=25% id='chg_list-cd'>" . $changes_ar2[$x]['description'] . "</td>";
								echo "<td width=15% id='chg_list-res'>" . $changes_ar2[$x]['name'] . "</td>";
								if ($changes_ar2[$x]['pht_start_datetime'] == 'Dec 31, 2999 - 12:00AM' && $changes_ar2[$x]['pht_end_datetime'] == 'Dec 31, 2999 - 11:59PM') {
									echo "<td width=25.5% colspan=2 id='chg_list-st'> - No schedule yet: Tentatively planned for the future - </td>";
								}
								else {
									echo "<td width=12.75% id='chg_list-st'>" . $changes_ar2[$x]['pht_start_datetime'] . "</td>";
									echo "<td width=12.75% id='chg_list-et'>" . $changes_ar2[$x]['pht_end_datetime'] . "</td>";
								}
								if ($changes_ar2[$x]['status'] == 'In Progress')
									$stat_hl = "status-inprogress";
								else if ($changes_ar2[$x]['status'] == 'Completed')
									$stat_hl = "status-completed";
								else if ($changes_ar2[$x]['status'] == 'Failed')
									$stat_hl = "status-failed";
								else 
									$stat_hl = "";
								echo "<td class='".$stat_hl."' id='chg_list-status'>" . $changes_ar2[$x]['status'] . "<br><br><i><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(".$changes_ar2[$x]['item_id'].")'>View Notes</a></i></td>";
								echo "</tr>";
							}
						?>
						</tbody>
					</table>
				</div>
			</div>
	</div>
<?php
	require "account_modals.php";
?>
</body>
</html>