<?php
	require "connect.php";

	$date = date('mdY');

	$filename ="dlmt_endofweek" . $date . ".xls";
  header('Content-type: application/ms-excel');
  header('Content-Disposition: attachment; filename='.$filename);

	$saturday1 = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT STR_TO_DATE(CONCAT(YEAR(NOW()), (SELECT WEEK(NOW())) - 1, 'Friday'), '%X%V %W') AS fri"))['fri'];
	$saturday2 = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT STR_TO_DATE(CONCAT(YEAR(NOW()), (SELECT WEEK(NOW())), 'Saturday'), '%X%V %W') AS sat"))['sat'];

	$qry = "SELECT change_ticket_id, description, DATE(customer_start_datetime) as implem_date FROM items WHERE account_id = 6 AND (pht_start_datetime BETWEEN '$saturday1' AND '$saturday2')";
	$res = mysqli_query($ch_conn, $qry);
	echo "Ticket Number \t Title/Description \t Implementation Date \t TR Number(s) \r\n";
	while ($row = mysqli_fetch_array($res)) {
		echo $row['change_ticket_id'] . "\t" . $row['description'] . "\t" . $row['implem_date'] . "\t" . " \r\n";
	}
?>