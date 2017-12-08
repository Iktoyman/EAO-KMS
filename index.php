<?php
	require "connect.php";
	require "../connect.php";
	require "timezones.php";
	session_start();
	require "establish_user.php";

	// GET variable
	if (isset($_SESSION['e'])) {
		$event = $_SESSION['e'];
		unset($_SESSION['e']);
	}
	else
		$event = '';

	// GET TEAM ACCOUNT ARRAY
	$managed_teams_ids = array();
	$managed_teams_names = array();
	if ($_SESSION['ct_team'] == 99) {
		$managed_teams_res = mysqli_query($ch_conn, "SELECT mr.team_id, t.team_name FROM manager_responsibility mr, team t WHERE mr.team_id = t.team_id AND mr.user_id = " . $_SESSION['ct_uid'] . " ORDER BY mr.team_id");
		while ($mt_id = mysqli_fetch_array($managed_teams_res)) {
			$managed_teams_ids[] = $mt_id['team_id'];
			$managed_teams_names[] = $mt_id['team_name'];
		}
		$get_team_accounts_qry = "SELECT acct_id, acct_abbrev, acct_name FROM account WHERE team_id = " . $managed_teams_ids[0] . " ORDER BY acct_abbrev";
	}
	else {
		$get_team_accounts_qry = "SELECT acct_id, acct_abbrev, acct_name FROM account WHERE team_id = " . $_SESSION['ct_team'] . " ORDER BY acct_abbrev";
		$managed_teams_ids[] = $_SESSION['ct_team'];
	}
	$get_team_accounts = mysqli_query($ch_conn, $get_team_accounts_qry);
	$team_accounts = array();
	while ($acct_row = mysqli_fetch_array($get_team_accounts)) {
		$team_accounts[] = $acct_row; 
	}

	// GET ACCOUNTS WITH CHANGES ARRAY
	$get_accounts_qry = "SELECT a.acct_abbrev, a.acct_name FROM account a, items i WHERE a.acct_id = i.account_id AND a.team_id IN (" . implode(', ', $managed_teams_ids) . ") AND a.acct_id IN (SELECT DISTINCT account_id FROM items) GROUP BY a.acct_abbrev ORDER BY a.acct_abbrev";
	$get_accounts = mysqli_query($ch_conn, $get_accounts_qry);
	$accounts = array();
	while ($acct_row = mysqli_fetch_array($get_accounts)) {
		$accounts[] = $acct_row; 
	}


	// GET TIMEZONES ARRAY
	$timezones = array();
	$timezones = initializeTimezones();

	require "acct_summary_queries.php";

	// Get Changes
	$changes = array();
	if ($_SESSION['ct_team'] == 99)
		$chg_qry = "SELECT i.item_id, i.change_ticket_id, t.team_name, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE i.account_id = a.acct_id AND a.team_id = t.team_id AND i.primary_resource = u.user_id AND a.team_id IN (" . implode(', ', $managed_teams_ids) . ") ORDER BY i.pht_start_datetime DESC";
	else 
		$chg_qry = "SELECT i.item_id, i.change_ticket_id, t.team_name, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE i.account_id = a.acct_id AND a.team_id = t.team_id AND i.primary_resource = u.user_id AND a.team_id = " . $_SESSION['ct_team'] . " ORDER BY i.pht_start_datetime DESC";
	$chg_res = mysqli_query($ch_conn, $chg_qry);
	$a = 0;
	while ($chg_row = mysqli_fetch_array($chg_res)) {
		$changes[$a] = $chg_row;
		$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $chg_row['item_id']);
		$changes[$a]['name'] .= " <i>(Primary)</i><br>";
		$sr_ar = array();
		while ($sr_row = mysqli_fetch_array($sec_res_result))
			$sr_ar[] = $sr_row['name'];
			$sr = implode('; ', $sr_ar);
			$changes[$a]['name'] .= $sr;
		//if ($changes[$a]['actions'] == 'Execute Change') {
			//$add_action = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT activity_name FROM activity WHERE activity_id = (SELECT activity_id FROM items WHERE item_id = " . $chg_row['item_id'] . ")"))['activity_name'];
			//$changes[$a]['actions'] .= " - " . $add_action;
		//}
		$a++;
	}
?>

<html>
<head>
	<title> Project Delta - Change Tracker </title>
	<?php
		include "head.php";
	?>
	<script>
		var managed_teams = <?php echo json_encode($managed_teams_ids); ?>;
		var changes = <?php echo json_encode($changes); ?>;
		var trigger_event = <?php echo json_encode($event); ?>;
		var accounts = <?php echo json_encode($accounts); ?>;
		console.log("Event: " + trigger_event);
	</script>
	<script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>
	<script type="text/javascript" src="js/index.js"></script>
	<script type="text/javascript" src="js/account.js"></script>
</head>

<body style="height: 100%">
	<div class='loading'>
		<img id='loading-img' src='https://mcyprian.fedorapeople.org/loading.gif'>
		<h3 id='loading-label'> Loading ... </h3>
	</div>

	<div class="sidebar-div-container">
		<div class="sidebar-div">
			<div class="sidebar-header-div">
				<a id='header-sidebar-btnlink-open'><span class="glyphicon glyphicon-menu-hamburger header-sidebar-btn"></span></a>
				<span class="header-title"> PROJECT DELTA </span>	
			</div>
			<div class="sidebar-body-div">
				<ul>
					<li> <a id='new-item_link'> NEW ITEM </a> </li>
					<li> <a data-toggle="collapse" href="#acct_collapse" aria-expanded="false" aria-controls="acct_collapse"> ALL ACCOUNT CHANGES </a></li>
						<div class='collapse acct_collapse' id='acct_collapse'>
							<div class="inner_acct-collapse">
							<?php
								echo "<ul>";
								for ($x = 0; $x < sizeof($all_accounts); $x++) {
									echo "<li> <a class='all-acct-list_li' id='all-" . $all_accounts[$x]['acct_abbrev'] . "'> " . $all_accounts[$x]['acct_abbrev'] . " - " . $all_accounts[$x]['acct_name'] . "</a></li>";
								}
								echo "</ul>";
							?>
							</div>
						</div>
					<li> <a id='my-accounts_link'> TEAM ACCOUNT CHANGES </a> </li>
					<li> <a href='calendar.php'> CHANGE CALENDAR </a> </li>
					<li> <a href='sow.php'> START OF WEEK </a> </li>
				</ul>
			</div>
		</div>
	</div>

	<div class="header_div">
		<div class="header_name_div">
			<a id='header-sidebar-btnlink'><span class="glyphicon glyphicon-menu-hamburger header-sidebar-btn"></span></a>
			<a href='../delta'> <span class="header-title"> PROJECT DELTA </span> </a>
		</div>
		<a id='user-dropdown'>
			<span class="glyphicon glyphicon-user welcome_message"></span>
		</a>
		<a id='menu-dropdown'>
			<span class="glyphicon glyphicon-th welcome_message"></span>
		</a>
	</div>

	<div id="header_user-dropdown-div">
		<ul id='header_user-dropdown-list'>
			<li style="padding: 0"> Welcome, <?php echo $_SESSION['user_fullname']; ?>! </li>
			<li><hr></li>
			<li><a id='my-uploads_link'> My Uploads </a></li>
			<li><a id='my-changes-link'> My Change Activities </a></li>
			<li><a href='../logout.php'> Logout </a></li>
		</ul>
	</div>
	<div id="menu-dropdown-div">
		<ul id='menu-dropdown-list'>
			<li align=center><b> TOOLS </b></li>
			<li><hr></li>
			<li><a href='../'> EAO - KMS </a></li>
			<li><a href='../observer'> OBServer </a></li>
			<li><a href='http://16.146.6.254:7080/apollo/home.php'> Apollo </a></li>
			<li><a href='https://ent302.sharepoint.hpe.com/teams/EAOPH-Quality/Shared%20Documents/Forms/AllItems.aspx?RootFolder=%2fteams%2fEAOPH%2dQuality%2fShared%20Documents%2f40%20EAO%20RST%20Scorecard%20Tools&FolderCTID=0x012000877D17965246E0459CBE002116CCE1F8'> AQUA </a></li>
		</ul>
	</div>

	<div class="body_div">

		<table class="table table-hover change-list home-change-list" id='change-list-thead' style="font-size: 0.7vw">
			<thead>
			<tr id='change-list-thead-tr'>
				<th class='change-list-th' id='1' width=8.5%> <span id='1-label'>Change ID</span> 
					<input type="text" class="change-list-filter" id='chg-list-th-1' onkeyup='filterColumn(1)'>
					<a class='glyphicon glyphicon-search filter-btn'></a></th>
				<th class='change-list-th' width=6.5%> Team </th>

				<th class='change-list-th' id='3' width=14%> <span id='3-label'>Account</span>
					<input type="text" class="change-list-filter" id='chg-list-th-3' onkeyup='filterColumn(3)'>
					<a class="glyphicon glyphicon-sort-by-alphabet sort-btn"></a>
					<a class="glyphicon glyphicon-filter filter-btn"></a></th>
				<th class='change-list-th' id='5' width=24.75%> <span id='5-label'>Title</span> 
					<input type="text" class="change-list-filter" id='chg-list-th-5' onkeyup='filterColumn(5)'>
					<a class="glyphicon glyphicon-sort-by-alphabet sort-btn"></a>
					<a class="glyphicon glyphicon-filter filter-btn"></a></th>
				<th class='change-list-th' id='6' width=13.5%> <span id='6-label'> Resources </span>
						
				<th class='change-list-th' id='7' width=10%> Planned Start 
					<a class="glyphicon glyphicon-sort-by-attributes sort-btn"></th>
				<th class='change-list-th' id='8' width=10%> Planned End 
					<a class="glyphicon glyphicon-sort-by-attributes sort-btn"></th>
				<th class='change-list-th' id='9' > <span id='9-label'>Status</span> 
					<input type="text" class="change-list-filter" id='chg-list-th-9' onkeyup='filterColumn(9)'>
					<a class="glyphicon glyphicon-sort-by-alphabet sort-btn"></a>
					<a class="glyphicon glyphicon-filter filter-btn"></a></th>
			</tr>
			</thead>
		</table>

		<div id='change-list-tbody-div'>	
		<table class="table table-hover change-list" style="font-size: 0.7vw">
			<tbody id='change-list-tbody'>
			<?php
				$row_limit = 24;
				for ($x = 0; $x < sizeof($changes); $x++) {
					echo "<tr>";
					echo "<td width=8.5% id='chg_list-id'><a onclick='showDetails(" . $changes[$x]['item_id'] .")'>" . $changes[$x]['change_ticket_id'] . "</a></td>";
					echo "<td width=6.5%>" . $changes[$x]['team_name'] . "</td>";
					echo "<td width=6% id='chg_list-aa'>" . $changes[$x]['acct_abbrev'] . "</td>";
					echo "<td width=8% id='chg_list-an'>" . $changes[$x]['acct_name'] . "</td>";
					//echo "<td width=25%>" . $changes[$x]['actions'] . "</td>";
					echo "<td width=25% id='chg_list-cd'>" . $changes[$x]['description'] . "</td>";
					echo "<td width=13.5% id='chg_list-res'>" . $changes[$x]['name'] . "</td>";
					if ($changes[$x]['pht_start_datetime'] == 'Dec 31, 2999 - 12:00AM' && $changes[$x]['pht_end_datetime'] == 'Dec 31, 2999 - 11:59PM') {
						if ($changes[$x]['status'] == 'Completed') {
							$completion_date_res = mysqli_query($ch_conn, "SELECT DATE_FORMAT(note_date, '%b %d, %Y - %h:%i%p') AS note_date FROM item_notes WHERE item_id = " . $changes[$x]['item_id'] . " AND (note_details = 'Change has been set to Completed.' OR note_date = (SELECT note_date FROM item_notes WHERE item_id = " . $changes[$x]['item_id'] . " ORDER BY note_date DESC LIMIT 1))");
							$completion_date = mysqli_fetch_assoc($completion_date_res)['note_date'];
							echo "<td width=25.5% colspan=2 id='chg_list-st'> Completed on $completion_date </td>";
						}
						else
							echo "<td width=25.5% colspan=2 id='chg_list-st'> - No schedule yet: Tentatively planned for the future - </td>";
					}
					else {
						echo "<td width=10.25% id='chg_list-st'>" . $changes[$x]['pht_start_datetime'] . "</td>";
						echo "<td width=10.25% id='chg_list-et'>" . $changes[$x]['pht_end_datetime'] . "</td>";
					}
					if ($changes[$x]['status'] == 'In Progress')
						$stat_hl = "status-inprogress";
					else if ($changes[$x]['status'] == 'Completed')
						$stat_hl = "status-completed";
					else if ($changes[$x]['status'] == 'Failed')
						$stat_hl = "status-failed";
					else if ($changes[$x]['status'] == 'Overdue')
						$stat_hl = "status-overdue";
					else if ($changes[$x]['status'] == 'Cancelled')
						$stat_hl = "status-cancelled";
					else 
						$stat_hl = "";
					echo "<td class='".$stat_hl."' id='chg_list-status'>" . $changes[$x]['status'] . "<br><br><i><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(".$changes[$x]['item_id'].")'>View Notes</a></i></td>";
					echo "</tr>";
					if ($x == $row_limit) {
						$next_row = $x + 1;
						echo "<tr><td colspan=9 id='show-more_row'> <a onclick='showMoreChanges(" . $next_row . ")'>Show more</a></td></tr>";
						break;
					}
				}
			?>
			</tbody>
		</table>
		</div>
	<?php
		echo "<span id='change-list_showlabel'> Showing " . ($x + 1) . " of " . sizeof($changes) . "</span><br>";
	?>

	<ul class="change-list-view-ul">
		<li><a class='change-list-view-status-btn' id='chg-view-all'> All Changes </a></li>
		<li><a class='change-list-view-status-btn' id='chg-view-inpr'> Changes In Progress </a></li>
		<li><a class='change-list-view-status-btn' id='chg-view-comp'> Completed Changes </a></li>
		<li><a class='change-list-view-status-btn' id='chg-view-overdue'> Overdue Changes </a></li>
		<li><a class='change-list-view-date-btn' id='chg-view-month'> Changes this Month </a></li>
		<li><a class='change-list-view-date-btn' id='chg-view-week'> Changes this Week </a></li>
		<li><a class='change-list-view-date-btn' id='chg-view-day'> Changes this Day </a></li>
		<li><a class='change-list-view-date-btn' id='chg-view-pipeline'> Forward Schedule of Changes </a></li>
	</ul>
	<!--<hr style="margin: 1% 0 0 0">-->
	</div>

</body>

<?php 
	require "modals.php"; 
	include "account_modals.php";
?>

</html>