<?php
	ob_start();
	require "connect.php";
	session_start();

	if ($_POST['action'] == 'show_details') {
		$chg = mysqli_fetch_array(mysqli_query($ch_conn, "SELECT i.change_ticket_id, i.change_type, i.description, i.sys_id, i.server, a.acct_id, CONCAT(a.acct_abbrev, ' - ', a.acct_name) AS account, i.primary_resource, CONCAT(u.last_name, ', ', u.first_name) AS primary_res, i.actions, i.pht_start_datetime, i.pht_end_datetime, i.customer_start_datetime, i.customer_end_datetime, i.customer_timezone, i.reference, i.status, i.is_prechecked, i.is_approved FROM users u, items i, account a WHERE i.account_id = a.acct_id AND i.primary_resource = u.user_id AND i.item_id = " . $_POST['id']));

		$sec_res = mysqli_query($ch_conn, "SELECT CONCAT(u.last_name, ', ', u.first_name) AS name FROM users u, activity_sec_resources asr WHERE u.user_id = asr.user_id AND asr.item_id = " . $_POST['id']);
		while ($sr_row = mysqli_fetch_assoc($sec_res))
			$sec_ar[] = $sr_row['name'];
		$sec = implode('; ', $sec_ar);
		$chg['resources'] = $chg['primary_res'] . " (Primary)<br>" . $sec;

		$sec_res = mysqli_query($ch_conn, "SELECT user_id FROM activity_sec_resources WHERE item_id = " . $_POST['id']);
		while ($sr_row = mysqli_fetch_assoc($sec_res))
			$sec_ids_ar[] = $sr_row['user_id'];
		$sec = implode(';', $sec_ids_ar);
		$chg['sec_ids'] = $sec; 

		$os_res = mysqli_query($ch_conn, "SELECT o.os_name FROM operating_system o, item_os i WHERE i.os_id = o.os_id AND i.item_id = " . $_POST['id']);
		if (mysqli_num_rows($os_res) > 0) {
			while ($os_row = mysqli_fetch_assoc($os_res)) 
				$os_ar[] = $os_row['os_name'];
			$os = implode('; ', $os_ar);
			$chg['os'] = $os;
		}
		else $chg['os'] = '';

		$db_res = mysqli_query($ch_conn, "SELECT d.db_name FROM db_type d, item_db i WHERE i.db_id = d.db_id AND i.item_id = " . $_POST['id']);
		if (mysqli_num_rows($db_res) > 0) {
			while ($db_row = mysqli_fetch_assoc($db_res)) 
				$db_ar[] = $db_row['db_name'];
			$db = implode('; ', $db_ar);
			$chg['db'] = $db;
		}
		else $chg['db'] = '';

		$sp_res = mysqli_query($ch_conn, "SELECT s.sp_name FROM sap_products s, item_sp i WHERE i.sp_id = s.sp_id AND i.item_id = " . $_POST['id']);
		if (mysqli_num_rows($sp_res) > 0) {
			while ($sp_row = mysqli_fetch_assoc($sp_res)) 
				$sp_ar[] = $sp_row['sp_name'];
			$sp = implode('; ', $sp_ar);
			$chg['sp'] = $sp;
		}
		else $chg['sp'] = '';

		if ($chg['actions'] == 'Execute Change')
			$add_action = " - " . mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT a.activity_name FROM activity a, items i WHERE i.activity_id = a.activity_id AND i.item_id = " . $_POST['id']))['activity_name'];
		else
			$add_action = "";
		$chg['actions'] = $chg['actions'] . $add_action;

		$chg['ph_sdate'] = $chg['pht_start_datetime'];
		$chg['ph_edate'] = $chg['pht_end_datetime'];
		$chg['cu_sdate'] = $chg['customer_start_datetime'];
		$chg['cu_edate'] = $chg['customer_end_datetime'];

		$chg['pht_start_datetime'] = date("M d, Y (h:i A)", strtotime($chg['pht_start_datetime']));
		$chg['pht_end_datetime'] = date("M d, Y (h:i A)", strtotime($chg['pht_end_datetime']));
		$chg['customer_start_datetime'] = date("M d, Y (h:i A)", strtotime($chg['customer_start_datetime']));
		$chg['customer_end_datetime'] = date("M d, Y (h:i A)", strtotime($chg['customer_end_datetime']));

		$note_qry = mysqli_query($ch_conn, "SELECT i.note_date, i.note_details, CONCAT(u.first_name, ' ', u.last_name) AS name FROM item_notes i, users u WHERE i.note_uploader = u.user_id AND i.item_id = " . $_POST['id'] . " ORDER BY note_date DESC LIMIT 1");
		$note_row = mysqli_fetch_array($note_qry);
		$chg['note'] = date("[M d, Y - h:i A]", strtotime($note_row['note_date'])) . " by " . $note_row['name'] . "<br><br>" . $note_row['note_details'];

		$chg['is_approved'] = $chg['is_approved'] ? 'Yes' : 'No';
		$chg['is_prechecked'] = $chg['is_prechecked'] ? 'Yes' : 'No';

		/*
		$chg['can_edit'] = 0;
		$is_uploader = mysqli_num_rows(mysqli_query($ch_conn, "SELECT item_id FROM items WHERE item_id = " . $_POST['id'] . " AND uploader_id = " . $_SESSION['ct_uid']));
		if ($is_uploader > 0) 
			$chg['can_edit'] = 1;
		else {
			$primary = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT primary_resource FROM items WHERE item_id = " . $_POST['id']))['primary_resource'];
			if ($primary == $_SESSION['ct_uid'])
				$chg['can_edit'] = 1;
			else {
				$is_secondary = mysqli_num_rows(mysqli_query($ch_conn, "SELECT item_id FROM activity_sec_resources WHERE item_id = " . $_POST['id'] . " AND user_id = " . $_SESSION['ct_uid']));
				if ($is_secondary > 0)
					$chg['can_edit'] = 1;
			}
		}
		*/
		$kms_res = mysqli_query($ch_conn, "SELECT document_id FROM item_kms WHERE item_id = " . $_POST['id']);
		if (mysqli_num_rows($kms_res) > 0)
			$chg['kms_id'] = mysqli_fetch_assoc($kms_res)['document_id'];
		else
			$chg['kms_id'] = "N/A";

		echo json_encode($chg);
	}
	else if ($_POST['action'] == 'show_notes') {
		$id = $_POST['id'];
		$notes = array();

		$qry = "SELECT DATE_FORMAT(i.note_date, '%b %d, %Y <br> %h:%i%p') as note_date, i.note_details, CONCAT(u.first_name, ' ', u.last_name) AS name FROM item_notes i, users u WHERE i.note_uploader = u.user_id AND i.item_id = " . $id . " ORDER BY i.note_date ASC";
		$res = mysqli_query($ch_conn, $qry);
		while ($note_row = mysqli_fetch_array($res))
			$notes[] = $note_row;

		echo json_encode($notes);	
	}
	else if ($_POST['action'] == 'add_note') {
		$id = $_POST['id'];
		$note = htmlentities(mysqli_real_escape_string($ch_conn, $_POST['note']), ENT_QUOTES, 'UTF-8');

		if (mysqli_query($ch_conn, "INSERT INTO item_notes(item_id, note_date, note_details, note_uploader) VALUES(" . $id . ", NOW(), '" . $note . "', " . $_SESSION['ct_uid'] . ")"))
			$response = 1;
		else
			$response = 0;

		echo json_encode($response);
	}
	else if ($_POST['action'] == 'add_precheck_note') {
		$id = $_POST['id'];
		$precheck_note = "Pre-check Details:<br>Done by: " . $_SESSION['user_fullname'] . "<hr>";
		$note = htmlentities(mysqli_real_escape_string($ch_conn, ($precheck_note . $_POST['note'])), ENT_QUOTES, 'UTF-8');

		if (mysqli_query($ch_conn, "INSERT INTO item_notes(item_id, note_date, note_details, note_uploader) VALUES($id, NOW(), '$note', " . $_SESSION['ct_uid'] . ")"))
			$response = 1;
		else
			$response = 0;

		echo json_encode($response);
	}
	else if ($_POST['action'] == 'quick_status_change') {
		$id = $_POST['id'];
		$status = $_POST['status'];
		if ($status == 'Open') {
			$result = 'In Progress';
			$note = "Change has been set to In Progress.";
		}
		else if ($status == 'In Progress') {
			$result = 'Completed'; 
			$note = "Change has been set to Completed.";
		}

		if (mysqli_query($ch_conn, "UPDATE items SET status = '" . $result . "' WHERE item_id = " . $id))
			mysqli_query($ch_conn, "INSERT INTO item_notes(item_id, note_date, note_details, note_uploader) VALUES(" . $id . ", NOW(), '" . $note . "', " . $_SESSION['ct_uid'] . ")");

		echo json_encode($result);
	}
	else if ($_POST['action'] == 'save_changes') {
		$id = $_POST['id'];
		$title = htmlentities(mysqli_real_escape_string($ch_conn, $_POST['title']), ENT_QUOTES, 'UTF-8');
		$sec_resources = array();
		$sr = array();

		$chg_summary = "Summary:<br>";
		$res = mysqli_query($ch_conn, "SELECT i.change_ticket_id, i.description, i.primary_resource, CONCAT(u.first_name, ' ', u.last_name) AS name, i.pht_start_datetime, i.pht_end_datetime, i.customer_start_datetime, i.customer_end_datetime, i.customer_timezone, i.status, i.is_approved, i.is_prechecked FROM items i, users u WHERE i.primary_resource = u.user_id AND i.item_id = " . $id);
		$chg = mysqli_fetch_array($res);
		if (html_entity_decode($chg['change_ticket_id']) != $_POST['chg_id'])
			$chg_summary .= "Changed ticket ID from <b>" . $chg['change_ticket_id'] . "</b> to <b>" . $_POST['chg_id'] . "</b>.<br>";
		if ($chg['description'] != $title)
			$chg_summary .= "Changed title from <b>" . $chg['description'] . "</b> to <b>" . $title . "</b>.<br>";
		if ($chg['primary_resource'] != $_POST['primary_res'])
			$chg_summary .= "Changed primary resource from <b>" . $chg['name'] . "</b> to <b>" . mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT CONCAT(first_name, ' ', last_name) AS name FROM users WHERE user_id = " . $_POST['primary_res']))['name'] . "</b>.<br>";
		$sr = mysqli_fetch_array(mysqli_query($ch_conn, "SELECT user_id FROM activity_sec_resources WHERE item_id = " . $id));
		$diff = array_diff($sr, $_POST['sec_res']);
		$diff2 = array_diff($_POST['sec_res'], $sr);
		if (sizeof($diff) > 0 || sizeof($diff2) > 0) {
			foreach($_POST['sec_res'] as $selected) {
				 $sec_resources[] = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT CONCAT(first_name, ' ', last_name) AS name FROM users WHERE user_id = " . $selected))['name'];
			}
			$chg_summary .= "Secondary resources are now: <b>" . implode(', ', $sec_resources) . "</b>.<br>";
		}
		if ($_POST['date3'] == '2999-12-31 00:00:00' && $chg['customer_start_datetime'] != $_POST['date3']) {
			$chg_summary .= "Schedule has been changed to indefinite, the change is in the pipeline.<br>";
		}
		else {
			if ($chg['customer_start_datetime'] != $_POST['date3'])
				$chg_summary .= "Changed Start Time from <b>" . date("M d, Y (h:i A)", strtotime($chg['customer_start_datetime'])) . "</b> to <b>" . date("M d, Y (h:i A)", strtotime($_POST['date3'])) . "</b>.<br>";
			if ($chg['customer_end_datetime'] != $_POST['date4'])
				$chg_summary .= "Changed End Time from <b>" . date("M d, Y (h:i A)", strtotime($chg['customer_end_datetime'])) . "</b> to <b>" . date("M d, Y (h:i A)", strtotime($_POST['date4'])) . "</b>.<br>";
			if ($chg['customer_timezone'] != $_POST['timezone'])
				$chg_summary .= "Changed timezone from <b>" . $chg['customer_timezone'] . "</b> to <b>" . $_POST['timezone'] . "</b>.<br>";
		}
		if ($chg['status'] != $_POST['status'])
			$chg_summary .= "Changed status from <b>" . $chg['status'] . "</b> to <b>" . $_POST['status'] . "</b>.<br>";
		if ($chg['is_approved'] != $_POST['is_approved'] && $_POST['is_approved'])
			$chg_summary .= "Change has been marked as <b>Approved and Ready for Implementation</b><br>";	
		else if ($chg['is_approved'] != $_POST['is_approved'] && !$_POST['is_approved'])
			$chg_summary .= "Change has been marked as <b>Not Ready for Implementation</b><br>";

		if ($chg['is_prechecked'] != $_POST['is_prechecked'] && $_POST['is_prechecked'])
			$chg_summary .= "Change has been marked as <b>Pre-checked</b> by the Executor.<br>";
		else if ($chg['is_prechecked'] != $_POST['is_prechecked'] && !$_POST['is_prechecked'])
			$chg_summary .= "Change has been marked as <b>Not Pre-checked</b> by the Executor.<br>";

		$note = htmlentities(mysqli_real_escape_string($ch_conn, $chg_summary), ENT_QUOTES, 'UTF-8');

		mysqli_query($ch_conn, "INSERT INTO item_notes(item_id, note_date, note_details, note_uploader) VALUES(" . $id . ", NOW(), '" . $note . "', " . $_SESSION['ct_uid'] . ")");

		mysqli_query($ch_conn, "UPDATE items SET change_ticket_id = '" . $_POST['chg_id'] . "', description = '" . $title . "', primary_resource = " . $_POST['primary_res'] . ", pht_start_datetime = '" . $_POST['date1'] . "', pht_end_datetime = '" . $_POST['date2'] . "', customer_start_datetime = '" . $_POST['date3'] . "', customer_end_datetime = '" . $_POST['date4'] . "', customer_timezone = '" . $_POST['timezone'] . "', status = '" . $_POST['status'] . "', is_approved = " . $_POST['is_approved'] . ", is_prechecked = " . $_POST['is_prechecked'] . " WHERE item_id = " . $id);
		mysqli_query($ch_conn, "DELETE FROM activity_sec_resources WHERE item_id = " . $id);

		foreach($_POST['sec_res'] as $selected) {
			mysqli_query($ch_conn, "INSERT INTO activity_sec_resources(user_id, item_id) VALUES(" . $selected . ", " . $id . ")");
		}
	}
	else if ($_POST['action'] == 'change_status') {
		$id = $_POST['id'];
		$status = $_POST['status'];
		$note = "Change has been set to " . $status . ".";

		if (mysqli_query($ch_conn, "UPDATE items SET status = '" . $status . "' WHERE item_id = " . $id)) {
			mysqli_query($ch_conn, "INSERT INTO item_notes(item_id, note_date, note_details, note_uploader) VALUES(" . $id . ", NOW(), '" . $note . "', " . $_SESSION['ct_uid'] . ")");
			$result = 1;
		}
		else
			$result = 0;

		echo json_encode($result);
	}
	else if ($_POST['action'] == 'delete_item') {
		$id = $_POST['id'];

		mysqli_query($ch_conn, "DELETE FROM activity_sec_resources WHERE item_id = " . $id);
		mysqli_query($ch_conn, "DELETE FROM item_db WHERE item_id = " . $id);
		mysqli_query($ch_conn, "DELETE FROM item_kms WHERE item_id = " . $id);
		mysqli_query($ch_conn, "DELETE FROM item_os WHERE item_id = " . $id);
		mysqli_query($ch_conn, "DELETE FROM item_sp WHERE item_id = " . $id);
		mysqli_query($ch_conn, "DELETE FROM item_notes WHERE item_id = " . $id);
		mysqli_query($ch_conn, "DELETE FROM items WHERE item_id = " . $id);
	}
	else if ($_POST['action'] == 'retrieve_team') {
		$id = $_POST['id'];

		$res = mysqli_query($ch_conn, "SELECT a.team_id FROM account a, items i WHERE i.account_id = a.acct_id AND i.item_id = " . $id);
		$team = mysqli_fetch_assoc($res)['team_id'];

		echo json_encode($team);
	}
	else {
		$ar = array();
		$teams = array();
		if ($_SESSION['ct_team'] == 99) {
		$teams_res = mysqli_query($ch_conn, "SELECT team_id FROM manager_responsibility WHERE user_id = " . $_SESSION['ct_uid']);
		while ($teams_row = mysqli_fetch_assoc($teams_res))
			$teams[] = $teams_row['team_id'];
		}
		else 
			$teams[] = $_SESSION['ct_team'];
		$a_id = $_POST['a_id'];
		if ($_POST['class_type'] == 'week') {	
			$week = $_POST['action'];

			$monday = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT STR_TO_DATE(CONCAT(YEAR(NOW()), $week, 'Monday'), '%X%V %W') AS mon"))['mon'];
			$sunday = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT STR_TO_DATE(CONCAT(YEAR(NOW()), $week + 1, 'Sunday'), '%X%V %W') AS sun"))['sun'];

			$qry = "SELECT i.item_id, CONCAT(u.first_name, ' ', u.last_name) as name, i.upload_date, i.change_ticket_id, i.change_type, i.description, i.sys_id, i.server, CONCAT(p.first_name, ' ', p.last_name) AS primary_res, i.actions, i.pht_start_datetime, i.pht_end_datetime, i.customer_start_datetime, i.customer_end_datetime, i.customer_timezone, i.reference FROM items i, users u, users p, account a WHERE i.uploader_id = u.user_id AND i.account_id = a.acct_id AND i.primary_resource = p.user_id AND a.acct_abbrev = '" . $a_id . "' AND a.team_id IN (" . implode(', ', $teams) . ") AND (i.pht_start_datetime BETWEEN '" . $monday . " 00:00:00' AND '" . $sunday . " 23:59:59') ORDER BY i.pht_start_datetime";
		}
		else if ($_POST['class_type'] == 'month') {
			$qry = "SELECT i.item_id, CONCAT(u.first_name, ' ', u.last_name) as name, i.upload_date, i.change_ticket_id, i.change_type, i.description, i.sys_id, i.server, CONCAT(p.first_name, ' ', p.last_name) AS primary_res, i.actions, i.pht_start_datetime, i.pht_end_datetime, i.customer_start_datetime, i.customer_end_datetime, i.customer_timezone, i.reference FROM items i, users u, users p, account a WHERE i.uploader_id = u.user_id AND i.account_id = a.acct_id AND i.primary_resource = p.user_id AND a.acct_abbrev = '" . $a_id . "' AND a.team_id IN (" . implode(', ', $teams) . ") AND MONTH(i.pht_start_datetime) = '" . $_POST['action'] . "' ORDER BY i.pht_start_datetime";
		}
		else if ($_POST['class_type'] == 'type') {
			$qry = "SELECT i.item_id, CONCAT(u.first_name, ' ', u.last_name) as name, i.upload_date, i.change_ticket_id, i.change_type, i.description, i.sys_id, i.server, CONCAT(p.first_name, ' ', p.last_name) AS primary_res, i.actions, i.pht_start_datetime, i.pht_end_datetime, i.customer_start_datetime, i.customer_end_datetime, i.customer_timezone, i.reference FROM items i, users u, users p, account a WHERE i.uploader_id = u.user_id AND i.account_id = a.acct_id AND i.primary_resource = p.user_id AND a.acct_abbrev = '" . $a_id . "' AND a.team_id IN (" . implode(', ', $teams) . ") AND i.change_type LIKE '" . $_POST['action'] . "%' ORDER BY i.pht_start_datetime";
		}
		else if ($_POST['class_type'] == 'status') {
			$qry = "SELECT i.item_id, CONCAT(u.first_name, ' ', u.last_name) as name, i.upload_date, i.change_ticket_id, i.change_type, i.description, i.sys_id, i.server, CONCAT(p.first_name, ' ', p.last_name) AS primary_res, i.actions, i.pht_start_datetime, i.pht_end_datetime, i.customer_start_datetime, i.customer_end_datetime, i.customer_timezone, i.reference FROM items i, users u, users p, account a WHERE i.uploader_id = u.user_id AND i.account_id = a.acct_id AND i.primary_resource = p.user_id AND a.acct_abbrev = '" . $a_id . "' AND a.team_id IN (" . implode(', ', $teams) . ") AND i.status = '" . $_POST['action'] . "' ORDER BY i.pht_start_datetime";
		}

		

		$a = 0;
		$res = mysqli_query($ch_conn, $qry);
		while ($row = mysqli_fetch_array($res)) {
			$sec_resources = array();
			$sec_res = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE asr.user_id = u.user_id AND asr.item_id = " . $row['item_id']);
			while ($secondary_row = mysqli_fetch_array($sec_res)) {
				$sec_resources[] = $secondary_row['name']; 
			}
			$sr = implode('; ', $sec_resources);
			
			$ar[$a]['id'] = $row['item_id'];
			$ar[$a]['name'] = $row['name'];
			$ar[$a]['up_date'] = date("M d, Y <\b\\r> h:i A", strtotime($row['upload_date']));
			$ar[$a]['chg_id'] = $row['change_ticket_id'];
			$ar[$a]['chg_type'] = $row['change_type'];
			$ar[$a]['chg_desc'] = $row['description'];
			$ar[$a]['sid'] = $row['sys_id'];
			$ar[$a]['server'] = $row['server'];
			$ar[$a]['assignees'] = $row['primary_res'] . "*<br><i>" . $sr . "</i>";
			if ($row['actions'] == 'Execute Change') 
				$add_action = " - " . mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT a.activity_name FROM activity a, items i WHERE i.activity_id = a.activity_id AND i.item_id = " . $row['item_id']))['activity_name'];
			else
				$add_action = "";
			$ar[$a]['action'] = $row['actions'] . $add_action;
			$ar[$a]['ph_sd'] = date("M d, Y <\b\\r> h:i A", strtotime($row['pht_start_datetime']));
			$ar[$a]['ph_ed'] = date("M d, Y <\b\\r> h:i A", strtotime($row['pht_end_datetime']));
			$ar[$a]['cu_sd'] = date("M d, Y <\b\\r> h:i A", strtotime($row['customer_start_datetime']));
			$ar[$a]['cu_ed'] = date("M d, Y <\b\\r> h:i A", strtotime($row['customer_end_datetime']));
			$ar[$a]['ref'] = $row['reference'];
			$ar[$a]['notes'] = "View Notes";
			$a++;
		}

		echo json_encode($ar);
	}
	ob_flush();
?>