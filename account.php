<?php
	require "../connect.php";
	require "connect.php";
	require "timezones.php";
	session_start();
	require "establish_user.php";

	// GET ACCOUNT ARRAY
	$teams = array();
	if ($_SESSION['ct_team'] == 99) {
		$teams_res = mysqli_query($ch_conn, "SELECT team_id FROM manager_responsibility WHERE user_id = " . $_SESSION['ct_uid']);
		while ($teams_row = mysqli_fetch_assoc($teams_res))
			$teams[] = $teams_row['team_id'];
	}
	else 
		$teams[] = $_SESSION['ct_team'];
	$get_accounts = mysqli_query($ch_conn, "SELECT acct_abbrev, acct_name FROM account WHERE team_id IN (" . implode(', ', $teams) . ") GROUP BY acct_abbrev ORDER BY acct_abbrev");
	$accounts = array();
	while ($acct_row = mysqli_fetch_array($get_accounts)) {
		$accounts[] = $acct_row; 
	}

	$a_id = $_GET['id'];
	$acct_res = mysqli_query($ch_conn, "SELECT acct_abbrev, acct_name FROM account WHERE acct_abbrev = '" . $a_id . "'");
	$acct_row = mysqli_fetch_array($acct_res);
		$a_abbrev = $acct_row['acct_abbrev'];
		$a_name = $acct_row['acct_name'];

	$summ = array();
	$summ['total'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(i.item_id) as ct FROM items i, account a WHERE i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $teams) . ") AND a.acct_abbrev = '" . $a_id . "'"))['ct'];
	$summ['open'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(i.item_id) as ct FROM items i, account a WHERE i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $teams) . ") AND a.acct_abbrev = '" . $a_id . "' AND status = 'Open'"))['ct'];
	$summ['inpr'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(i.item_id) as ct FROM items i, account a WHERE i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $teams) . ") AND a.acct_abbrev = '" . $a_id . "' AND status = 'In Progress'"))['ct'];
	$summ['comp'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(i.item_id) as ct FROM items i, account a WHERE i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $teams) . ") AND a.acct_abbrev = '" . $a_id . "' AND status = 'Completed'"))['ct'];
	$summ['canc'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(i.item_id) as ct FROM items i, account a WHERE i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $teams) . ") AND a.acct_abbrev = '" . $a_id . "' AND status = 'Cancelled'"))['ct'];
	$summ['fail'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(i.item_id) as ct FROM items i, account a WHERE i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $teams) . ") AND a.acct_abbrev = '" . $a_id . "' AND status = 'Failed'"))['ct'];
	$summ['week'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(i.item_id) as ct FROM items i, account a WHERE i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $teams) . ") AND a.acct_abbrev = '" . $a_id . "' AND WEEK(pht_start_datetime, 1) = WEEK(NOW(), 1) AND YEAR(pht_start_datetime) = YEAR(NOW())"))['ct'];
	$summ['mont'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(i.item_id) as ct FROM items i, account a WHERE i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $teams) . ") AND a.acct_abbrev = '" . $a_id . "' AND MONTH(pht_start_datetime) = MONTH(NOW()) AND YEAR(pht_start_datetime) = YEAR(NOW())"))['ct'];
	$summ['norm'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(i.item_id) as ct FROM items i, account a WHERE i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $teams) . ") AND a.acct_abbrev = '" . $a_id . "' AND change_type LIKE 'Normal%'"))['ct'];
	$summ['stnd'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(i.item_id) as ct FROM items i, account a WHERE i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $teams) . ") AND a.acct_abbrev = '" . $a_id . "' AND change_type = 'Standard'"))['ct'];
	$summ['tran'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(i.item_id) as ct FROM items i, account a WHERE i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $teams) . ") AND a.acct_abbrev = '" . $a_id . "' AND actions = 'Import Transport'"))['ct'];
	$summ['emer'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(i.item_id) as ct FROM items i, account a WHERE i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $teams) . ") AND a.acct_abbrev = '" . $a_id . "' AND change_type = 'Emergency'"))['ct'];
	
	// GET TIMEZONES ARRAY
	$timezones = array();
	$timezones = initializeTimezones();
?>

<html>
<head>
	<title> Project Delta - <?php echo $a_abbrev; ?> </title>
	<?php
		require "head.php";
	?>
	<script>
		var a_id = '<?php echo $a_id; ?>';
		var manual_close = true;
	</script>
	<script type="text/javascript" src="js/account.js"></script>
</head>

<body>
	<?php
		require "sidebar.php";
		require "navbar.php";
	?>

	<div class="body_div">
		<div class="account_name_div">
		<?php echo "<b style='font-family:Montserrat'>" . $a_abbrev . "</b> - <i>" . $a_name ."</i>"; ?>
		</div>

		<div class="account_details_div">
			<div class="account_details_top">
				<ul class="nav nav-tabs">
					<li role="presentation" class="active" id="overview_tab"><a href="#overview" role="tab" data-toggle="tab" aria-controls="overview"> Overview </a> </li>
					<li role="presentation"> <a href="#week" role="tab" data-toggle="tab" aria-controls="week"> By Week <span id="cur_week"> (Current Week) </span> </a> </li>
					<li role="presentation"> <a href="#mont" role="tab" data-toggle="tab" aria-controls="mont"> By Month <span id="cur_month"> (Current Month) </span> </a> </li>
					<li role="presentation"> <a href="#type" role="tab" data-toggle="tab" aria-controls="type"> Type <span id="cur_type"> (Normal Minor) </span> </a> </li>
					<li role="presentation"> <a href="#stat" role="tab" data-toggle="tab" aria-controls="stat"> Status <span id="cur_status"> (Open) </span> </a> </li>
				</ul>
			</div>

			<div class="tab-content account_details_div_tab">
				<div role="tabpanel" class="account_details_div_tab tab-pane active" id="overview">
	 				<h4> By date: </h4>
	 				<div class="account_details_item_container">
		 				<div class="account_details_item">
		 					<div id="account_details_item_label"> Total Number of Changes </div>
		 					<br>
		 					<div class="account_details_item_number custom" id='All' href='#custom' role="tab" data-toggle="tab" aria-controls="custom">
		 						<?php echo $summ['total']; ?>
		 					</div>
		 				</div>

		 				<div class="account_details_item">
		 					<div id="account_details_item_label"> Number of Changes this Week </div>
		 					<br>
		 					<div class="account_details_item_number week" id=''>
		 						<?php echo $summ['week']; ?>
		 					</div>
		 				</div>

		 				<div class="account_details_item">
		 					<div id="account_details_item_label"> Number of Changes this Month </div>
		 					<br>
		 					<div class="account_details_item_number mont" id=''>
		 						<?php echo $summ['mont']; ?>
		 					</div>
		 				</div>
	 				</div>	

	 				<h4> By status: </h4>
	 				<div class="account_details_item_container">
		 				<div class="account_details_item">
		 					<div id="account_details_item_label"> Number of Open Changes </div>
		 					<br>
		 					<div class="account_details_item_number stat" id='Open'>
		 						<?php echo $summ['open']; ?>
		 					</div>
		 				</div>

		 				<div class="account_details_item">
		 					<div id="account_details_item_label"> Number of Changes in Progress </div>
		 					<br>
		 					<div class="account_details_item_number stat" id='In Progress'>
		 						<?php echo $summ['inpr']; ?>
		 					</div>
		 				</div>

		 				<div class="account_details_item">
		 					<div id="account_details_item_label"> Number of Completed Changes </div>
		 					<br>
		 					<div class="account_details_item_number stat" id='Completed'>
		 						<?php echo $summ['comp']; ?>
		 					</div>
		 				</div>

		 				<div class="account_details_item">
		 					<div id="account_details_item_label"> Number of Cancelled Changes </div>
		 					<br>
		 					<div class="account_details_item_number stat" id='Cancelled'>
		 						<?php echo $summ['canc']; ?>
		 					</div>
		 				</div>

		 				<div class="account_details_item">
		 					<div id="account_details_item_label"> Number of Failed Changes </div>
		 					<br>
		 					<div class="account_details_item_number stat" id='Failed'>
		 						<?php echo $summ['fail']; ?>
		 					</div>
		 				</div>
	 				</div>

	 				<h4> By type: </h4>
	 				<div class="account_details_item_container">
	 					<div class="account_details_item">
	 						<div id="account_details_item_label"> Number of Normal Changes </div>
	 						<br>
	 						<div class="account_details_item_number type" id='Normal'>
	 							<?php echo $summ['norm']; ?>
	 						</div>
		 				</div>

		 				<div class="account_details_item">
		 					<div id="account_details_item_label"> Number of Standard Changes </div>
		 					<br>
		 					<div class="account_details_item_number type" id='Standard'>
		 						<?php echo $summ['stnd']; ?>
		 					</div>
		 				</div>

		 				<div class="account_details_item">
		 					<div id="account_details_item_label"> Number of Transports </div>
		 					<br>
		 					<div class="account_details_item_number custom" id='Transports' href='#trans' role="tab" data-toggle="tab" aria-controls="trans">
		 						<?php echo $summ['tran']; ?>
		 					</div>
		 				</div>

		 				<div class="account_details_item">
		 					<div id="account_details_item_label"> Number of Emergency Changes </div>
		 					<br>
		 					<div class="account_details_item_number type" id='Emergency'>
		 						<?php echo $summ['emer']; ?>
		 					</div>
		 				</div>
	 				</div>	

				</div> 

			<?php
			// Changes this Week
			require "account_week.php";

			// Changes this Month
			require "account_month.php";
			
			// Changes by Type
			require "account_type.php";

			// Changes by Status
			require "account_status.php";

			// Custom view
			require "account_custom.php";
			require "account_transports.php";
			?>
			</div>
		</div>
	</div>
	<?php
	// Pop-up modals
	require "account_modals.php";
	?>
</body>
</html>