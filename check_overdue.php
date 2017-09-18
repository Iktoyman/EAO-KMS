<?php
	require "connect.php";

	$qry = "SELECT item_id, change_ticket_id, description FROM items WHERE pht_end_datetime <= NOW() AND (status = 'Open' OR status = 'In Progress')";
	$res = mysqli_query($ch_conn, $qry);
	while ($row = mysqli_fetch_array($res)) {
		mysqli_query($ch_conn, "UPDATE items SET status = 'Overdue' WHERE item_id = " . $row['item_id']);
		mysqli_query($ch_conn, "INSERT INTO item_notes(item_id, note_date, note_details, note_uploader) VALUES(".$row['item_id'].", NOW(), 'Change has now passed scheduled end date/time and is now Overdue.', 285)");

		$usr_qry = "SELECT CONCAT(u.username, ', ', u2.username) AS recipients FROM users u, users u2, items i WHERE i.uploader_id = u.user_id AND i.primary_resource = u2.user_id AND i.item_id = " . $row['item_id'];
		$to = mysqli_fetch_assoc(mysqli_query($ch_conn, $usr_qry))['recipients'];
		//$to2 = 'eric-xavier.car.rosales@hpe.com';
		$subj = "[DELTA] Reminder to Complete: " . $row['change_ticket_id'];
		$headers = "From: ito-dcs-phils-eao-kms@hpe.com\r\nReply-To: ito-dcs-phils-eao-kms@hpe.com\r\nCC: eric-xavier.car.rosales@hpe.com, ito-dcs-phils-eao-kms@hpe.com";
		$headers .= "\r\nContent-Type: text/html; charset=UTF-8\r\n"; 

		//$body = "Sent to: " . $to;
		$body = "<p style='font-family: Calibri'>Good day!</p>";
		$body .= "<p style='font-family: Calibri'>This is to remind you, as the person(s) responsible for the following change item, that the following has now passed its Scheduled End Date/Time with its status still marked as Open / In Progress. Kindly update the change item in <a href='http://eao-kms.phl.hp.com:8088/delta/'>the tracker</a>:</p><br>";
		$body .= "<span style='font-family: Calibri'>Change ID: <b>" . $row['change_ticket_id']. "</b></span><br>";
		$body .= "<span style='font-family: Calibri'>Title / Description: <b>" . $row['description']. "</b></span><br><br>";
		$body .= "<p style='font-family: Calibri; font-size: 11px; font-style: italic'>This is an automated e-mail, please do not reply to this e-mail.<br>Regards,<br>THE EAO-KMS Team</p>";

		ini_set("SMTP", "smtp1.hp.com");
		mail($to, $subj, $body, $headers);
		//echo $subj . "<br>";
	}
?>