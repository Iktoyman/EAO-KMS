<?php
	$summ = array();
	for($i = 0; $i < sizeof($accounts); $i++) {
		$summ[$i]['total'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(item_id) as ct FROM items WHERE account_id = " . $accounts[$i]['acct_id']))['ct'];
		$summ[$i]['open'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(item_id) as ct FROM items WHERE account_id = " . $accounts[$i]['acct_id'] . " AND status = 'Open'"))['ct'];
		$summ[$i]['inpr'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(item_id) as ct FROM items WHERE account_id = " . $accounts[$i]['acct_id'] . " AND status = 'In Progress'"))['ct'];
		$summ[$i]['comp'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(item_id) as ct FROM items WHERE account_id = " . $accounts[$i]['acct_id'] . " AND status = 'Completed'"))['ct'];
		$summ[$i]['canc'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(item_id) as ct FROM items WHERE account_id = " . $accounts[$i]['acct_id'] . " AND status = 'Cancelled'"))['ct'];
		$summ[$i]['fail'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(item_id) as ct FROM items WHERE account_id = " . $accounts[$i]['acct_id'] . " AND status = 'Failed'"))['ct'];
		$summ[$i]['week'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(item_id) as ct FROM items WHERE account_id = " . $accounts[$i]['acct_id'] . " AND WEEK(pht_start_datetime, 1) = WEEK(NOW(), 1) AND YEAR(pht_start_datetime) = YEAR(NOW())"))['ct'];
		$summ[$i]['mont'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(item_id) as ct FROM items WHERE account_id = " . $accounts[$i]['acct_id'] . " AND MONTH(pht_start_datetime) = MONTH(NOW()) AND YEAR(pht_start_datetime) = YEAR(NOW())"))['ct'];
		$summ[$i]['norm'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(item_id) as ct FROM items WHERE account_id = " . $accounts[$i]['acct_id'] . " AND change_type LIKE 'Normal%'"))['ct'];
		$summ[$i]['stnd'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(item_id) as ct FROM items WHERE account_id = " . $accounts[$i]['acct_id'] . " AND change_type = 'Standard'"))['ct'];
		$summ[$i]['tran'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(item_id) as ct FROM items WHERE account_id = " . $accounts[$i]['acct_id'] . " AND actions = 'Import Transport'"))['ct'];
		$summ[$i]['emer'] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT COUNT(item_id) as ct FROM items WHERE account_id = " . $accounts[$i]['acct_id'] . " AND change_type = 'Emergency'"))['ct'];
	}
?>