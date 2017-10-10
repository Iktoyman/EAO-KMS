<?php
	require "connect.php";

	if ($_POST['filter_type'] == 'status') {
		if ($_POST['filter'] == '')
			$filenametype = "all";
		else if ($_POST['filter'] == 'In Progress')
			$filenametype = "in-progress";
		else if ($_POST['filter'] == 'Completed')
			$filenametype = "completed";
		else if ($_POST['filter'] == 'Overdue')
			$filenametype = "overdue";
	}
	else {
		$filenametype = $_POST['filter'];
	}

	$filename ="delta-view-" . $filenametype .".xls";
  header('Content-type: application/ms-excel');
  header('Content-Disposition: attachment; filename='.$filename);

  if ($_POST['filter_type'] == 'status') {
  	$status = $_POST['filter'];

  	$res = mysqli_query($ch_conn, "SELECT i.item_id, t.team_name, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE a.team_id = t.team_id AND i.primary_resource = u.user_id AND i.account_id = a.acct_id AND a.team_id IN (1, 2, 3, 4, 5, 6, 7) AND i.status LIKE '%" . $status . "%' ORDER BY i.pht_start_datetime DESC");
  }
  else if ($_POST['filter_type'] == 'date') {
  	if ($_POST['filter'] == 'filter_month') {
  		$res_cond = " AND (MONTH(i.pht_start_datetime) = MONTH(NOW()) OR MONTH(i.pht_end_datetime) = MONTH(NOW())) ORDER BY i.pht_start_datetime ASC";
  	}
  	else if ($_POST['filter'] == 'filter_week') {
  		$week = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT WEEK(NOW()) AS week"))['week'];
			$monday = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT STR_TO_DATE(CONCAT(YEAR(NOW()), $week, 'Monday'), '%X%V %W') AS mon"))['mon'];
			$sunday = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT STR_TO_DATE(CONCAT(YEAR(NOW()), $week + 1, 'Sunday'), '%X%V %W') AS sun"))['sun'];

			$res_cond = " AND (i.pht_start_datetime BETWEEN '".$monday." 00:00:00' AND '".$sunday." 23:59:59') ORDER BY i.pht_start_datetime ASC";
  	}
  	else if ($_POST['filter'] == 'filter_day') {
  		$res_cond = " AND (CURDATE() = DATE(i.pht_start_datetime) OR CURDATE() = DATE(i.pht_end_datetime) OR (CURDATE() BETWEEN i.pht_start_datetime AND i.pht_end_datetime)) ORDER BY i.pht_start_datetime ASC";
  	}
  	else if ($_POST['filter'] == 'filter_pipeline') {
  		$res_cond = " AND DATE(i.pht_start_datetime) > NOW() ORDER BY i.pht_start_datetime DESC";
  	}

  	$res = mysqli_query($ch_conn, "SELECT i.item_id, t.team_name, i.change_ticket_id, a.acct_abbrev, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE a.team_id = t.team_id AND i.primary_resource = u.user_id AND i.account_id = a.acct_id AND a.team_id IN (1, 2, 3, 4, 5, 6, 7)" . $res_cond);
  }

  echo "Change ID \t Team \t Account \t Title/Description \t Resources \t Planned Start \t Planned End \t Status \r\n";
  while ($row = mysqli_fetch_array($res)) {
  	$sr_ar = array();
  	$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $row['item_id']);
  	while ($sr_row = mysqli_fetch_array($sec_res_result))
  		$sr_ar[] = $sr_row['name'];
  	$sr = implode('; ', $sr_ar);
  	echo $row['change_ticket_id'] . "\t" . $row['team_name'] . "\t" . $row['acct_abbrev'] . "\t" . $row['description'] . "\t" . $row['name'] . " *; " . $sr . "\t" . $row['pht_start_datetime'] . "\t" . $row['pht_end_datetime'] . "\t" . $row['status'] . "\r\n";
  }
?>