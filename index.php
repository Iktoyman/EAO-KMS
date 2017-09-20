<?php
	require "connect.php";
	require "../connect.php";
	require "timezones.php";
	session_start();
	require "establish_user.php";

	// GET ALL ACCOUNT ARRAY
	$get_all_accounts = mysqli_query($ch_conn, "SELECT acct_id, acct_abbrev, acct_name FROM account WHERE team_id = " . $_SESSION['ct_team'] . " ORDER BY acct_abbrev");
	$all_accounts = array();
	while ($acct_row = mysqli_fetch_array($get_all_accounts)) {
		$all_accounts[] = $acct_row; 
	}

	// GET ACCOUNTS WITH CHANGES ARRAY
	$get_accounts = mysqli_query($ch_conn, "SELECT acct_id, acct_abbrev, acct_name FROM account WHERE team_id = " . $_SESSION['ct_team'] . " AND acct_id IN (SELECT DISTINCT account_id FROM items) ORDER BY acct_abbrev");
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
	//$chg_res = mysqli_query($ch_conn, "SELECT i.item_id, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.actions, i.pht_start_datetime, i.pht_end_datetime, i.status FROM items i, account a WHERE i.account_id = a.acct_id ORDER BY i.pht_start_datetime DESC");
	$chg_res = mysqli_query($ch_conn, "SELECT i.item_id, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u WHERE i.account_id = a.acct_id AND i.primary_resource = u.user_id AND a.team_id = " . $_SESSION['ct_team'] . " ORDER BY i.pht_start_datetime DESC");
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
	<title> Project Delta - SAT2 Change Tracker </title>
	<?php
		include "head.php";
	?>
	<script>
		var changes = <?php echo json_encode($changes); ?>;
		//console.log(changes);
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
	<h1> CHANGE TRACKER 
		<span class='welcome_message'> Welcome, <?php echo $_SESSION['user_fullname']; ?>! </span>
	</h1>
	<hr>
	<span class='logout'> <a href='../logout.php'> Logout </a></span>
	<a href='#' data-toggle='modal' data-target='#new_item'> New Item </a> <br>
	<!-- <a href='#'> Search </a> <br> -->
	<a href='#' data-toggle='modal' data-target='#my_accounts'> My Accounts </a> <br>
	<a href='calendar.php'> Change Calendar </a> <br>
	<a href='sow.php'> Start of Week </a><br><br>
	<!-- <a href='#'> View per Type </a> <br>
	<a href='#'> View by Status </a> <br> -->
	<table class="table table-hover change-list" id='change-list-thead' style="font-size: 0.7vw">
		<thead>
		<tr id='change-list-thead-tr'>
			<th class='change-list-th' id='1' width=8.5%> <span id='1-label'>Change ID</span> 
				<input type="text" class="change-list-filter" id='chg-list-th-1' onkeyup='filterColumn(1)'>
				<a class='glyphicon glyphicon-search filter-btn'></a></th>
			<th class='change-list-th' id='2' width=16.5%> <span id='2-label'>Account</span>
				<input type="text" class="change-list-filter" id='chg-list-th-2' onkeyup='filterColumn(2)'>
				<a class="glyphicon glyphicon-sort-by-alphabet sort-btn"></a>
				<a class="glyphicon glyphicon-filter filter-btn"></a></th>
			<th class='change-list-th' id='4' width=24.75%> <span id='4-label'>Title</span> 
				<input type="text" class="change-list-filter" id='chg-list-th-4' onkeyup='filterColumn(4)'>
				<a class="glyphicon glyphicon-sort-by-alphabet sort-btn"></a>
				<a class="glyphicon glyphicon-filter filter-btn"></a></th>
			<th class='change-list-th' id='8' width=15%> <span id='8-label'> Resources </span>
					
			<th class='change-list-th' id='5' width=12.5%> Planned Start 
				<a class="glyphicon glyphicon-sort-by-attributes sort-btn"></th>
			<th class='change-list-th' id='6' width=12.5%> Planned End 
				<a class="glyphicon glyphicon-sort-by-attributes sort-btn"></th>
			<th class='change-list-th' id='7' > <span id='7-label'>Status</span> 
				<input type="text" class="change-list-filter" id='chg-list-th-7' onkeyup='filterColumn(7)'>
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
				echo "<td width=6% id='chg_list-aa'>" . $changes[$x]['acct_abbrev'] . "</td>";
				echo "<td width=10.5% id='chg_list-an'>" . $changes[$x]['acct_name'] . "</td>";
				//echo "<td width=25%>" . $changes[$x]['actions'] . "</td>";
				echo "<td width=25% id='chg_list-cd'>" . $changes[$x]['description'] . "</td>";
				echo "<td width=15% id='chg_list-res'>" . $changes[$x]['name'] . "</td>";
				if ($changes[$x]['pht_start_datetime'] == 'Dec 31, 2999 - 12:00AM' && $changes[$x]['pht_end_datetime'] == 'Dec 31, 2999 - 11:59PM') {
					echo "<td width=25.5% colspan=2 id='chg_list-st'> - No schedule yet: Tentatively planned for the future - </td>";
				}
				else {
					echo "<td width=12.75% id='chg_list-st'>" . $changes[$x]['pht_start_datetime'] . "</td>";
					echo "<td width=12.75% id='chg_list-et'>" . $changes[$x]['pht_end_datetime'] . "</td>";
				}
				if ($changes[$x]['status'] == 'In Progress')
					$stat_hl = "status-inprogress";
				else if ($changes[$x]['status'] == 'Completed')
					$stat_hl = "status-completed";
				else if ($changes[$x]['status'] == 'Failed')
					$stat_hl = "status-failed";
				else if ($changes[$x]['status'] == 'Overdue')
					$stat_hl = "status-overdue";
				else 
					$stat_hl = "";
				echo "<td class='".$stat_hl."' id='chg_list-status'>" . $changes[$x]['status'] . "<br><br><i><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(".$changes[$x]['item_id'].")'>View Notes</a></i></td>";
				echo "</tr>";
				if ($x == $row_limit) {
					$next_row = $x + 1;
					echo "<tr><td colspan=8 id='show-more_row'> <a onclick='showMoreChanges(" . $next_row . ")'>Show more</a></td></tr>";
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

</body>

<?php 
	require "modals.php"; 
	include "account_modals.php";
?>

</html>