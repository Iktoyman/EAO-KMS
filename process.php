<?php
	require "../connect.php";
	require "connect.php";
	session_start();

	$handled_teams = array();
	if ($_SESSION['ct_team'] == 99) {
		$teams_res = mysqli_query($ch_conn, "SELECT team_id FROM manager_responsibility WHERE user_id = " . $_SESSION['ct_uid'] . " ORDER BY team_id");
		while ($teams_row = mysqli_fetch_assoc($teams_res))
			$handled_teams[] = $teams_row['team_id'];
	}
	else 
		$handled_teams[] = $_SESSION['ct_team'];

	if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action'])) {
		if ($_POST['action'] == 'create_item') {
			$title = htmlentities(mysqli_real_escape_string($ch_conn, $_POST['chg_desc']), ENT_QUOTES, 'UTF-8');
			$notes = htmlentities(mysqli_real_escape_string($ch_conn, $_POST['notes']), ENT_QUOTES, 'UTF-8');

			if ($_POST['sids'] == "")
				$_POST['sids'] = "N/A";
			if ($_POST['servers'] == "" || $_POST['servers'] == " ")
				$_POST['servers'] = "N/A";
			if ($_POST['chg_act'] == 0)
				$qry = "INSERT INTO items(uploader_id, upload_date, change_ticket_id, change_type, description, account_id, sys_id, server, actions, activity_id, primary_resource, pht_start_datetime, pht_end_datetime, customer_start_datetime, customer_end_datetime, customer_timezone, reference, status, is_approved) VALUES(".$_SESSION['ct_uid'].", NOW(), '".$_POST['chg_id']."', '".$_POST['chg_type']."', '".$title."', ".$_POST['acct'].", '".$_POST['sids']."', '".$_POST['servers']."', '".$_POST['chg_action']."', NULL, ".$_POST['primary_res'].", '".$_POST['date1']." ".$_POST['time1']."', '".$_POST['date2']." ".$_POST['time2']."', '".$_POST['date3']." ".$_POST['time3']."', '".$_POST['date4']." ".$_POST['time4']."', '".$_POST['timezone']."', '".$_POST['reference']."', '".$_POST['status']."', ".$_POST['approved'].")";
			else
				$qry = "INSERT INTO items(uploader_id, upload_date, change_ticket_id, change_type, description, account_id, sys_id, server, actions, activity_id, primary_resource, pht_start_datetime, pht_end_datetime, customer_start_datetime, customer_end_datetime, customer_timezone, reference, status, is_approved) VALUES(".$_SESSION['ct_uid'].", NOW(), '".$_POST['chg_id']."', '".$_POST['chg_type']."', '".$title."', ".$_POST['acct'].", '".$_POST['sids']."', '".$_POST['servers']."', '".$_POST['chg_action']."', ".$_POST['chg_act'].", ".$_POST['primary_res'].", '".$_POST['date1']." ".$_POST['time1']."', '".$_POST['date2']." ".$_POST['time2']."', '".$_POST['date3']." ".$_POST['time3']."', '".$_POST['date4']." ".$_POST['time4']."', '".$_POST['timezone']."', '".$_POST['reference']."', '".$_POST['status']."', ".$_POST['approved'].")";

			mysqli_query($ch_conn, $qry);
			$item_id = mysqli_insert_id($ch_conn);

			$notes_qry = "INSERT INTO item_notes(item_id, note_date, note_details, note_uploader) VALUES(".$item_id.", NOW(), '".$notes."', " . $_SESSION['ct_uid'] . ")";
			mysqli_query($ch_conn, $notes_qry);

			foreach ($_POST['sec_res'] as $sr) 
				mysqli_query($ch_conn, "INSERT INTO activity_sec_resources(user_id, item_id) VALUES(".$sr.", ".$item_id.")");

			foreach ($_POST['os'] as $os) 
				mysqli_query($ch_conn, "INSERT INTO item_os(item_id, os_id) VALUES(".$item_id.", ".$os.")");

			foreach ($_POST['db'] as $db) 
				mysqli_query($ch_conn, "INSERT INTO item_db(item_id, db_id) VALUES(".$item_id.", ".$db.")");

			foreach ($_POST['sp'] as $sp) 
				mysqli_query($ch_conn, "INSERT INTO item_sp(item_id, sp_id) VALUES(".$item_id.", ".$sp.")");

			if ($_POST['kms_id'] != "")
				mysqli_query($ch_conn, "INSERT INTO item_kms(item_id, document_id) VALUES(".$item_id.", '".$_POST['kms_id']."')");
			//var_dump($qry);
		}
		else if ($_POST['action'] == 'check_kms') {
			$doc_id = $_POST['doc_id'];

			$ct = mysqli_num_rows(mysqli_query($conn, "SELECT document_no FROM content WHERE document_id = '" . $doc_id . "'"));
			echo json_encode($ct);
		}
		else if ($_POST['action'] == 'filter_id') {
			$ar = array();
			$text = $_POST['text'];

			$res = mysqli_query($ch_conn, "SELECT i.item_id, t.team_name, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE a.team_id = t.team_id AND i.primary_resource = u.user_id AND i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $handled_teams) . ") AND i.change_ticket_id LIKE '%" . $text . "%' ORDER BY i.pht_start_datetime DESC");
			$x = 0;
			while ($row = mysqli_fetch_array($res)) {
				$ar[$x] = $row;
				$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $row['item_id']);
				$ar[$x]['name'] .= " <i>(Primary)</i><br>";
				$sr_ar = array();
				while ($sr_row = mysqli_fetch_array($sec_res_result))
					$sr_ar[] = $sr_row['name'];
					$sr = implode('; ', $sr_ar);
					$ar[$x]['name'] .= $sr;
				$x++;
			}

			echo json_encode($ar);
		}
		else if ($_POST['action'] == 'filter_acct') {
			$ar = array();
			$text = $_POST['text'];

			$res = mysqli_query($ch_conn, "SELECT i.item_id, t.team_name, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE a.team_id = t.team_id AND i.primary_resource = u.user_id AND i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $handled_teams) . ") AND (a.acct_abbrev LIKE '".$text."%' OR a.acct_name LIKE '".$text."%') ORDER BY i.pht_start_datetime DESC");
			$x = 0;
			while ($row = mysqli_fetch_array($res)) {
				$ar[$x] = $row;
				$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $row['item_id']);
				$ar[$x]['name'] .= " <i>(Primary)</i><br>";
				$sr_ar = array();
				while ($sr_row = mysqli_fetch_array($sec_res_result))
					$sr_ar[] = $sr_row['name'];
					$sr = implode('; ', $sr_ar);
					$ar[$x]['name'] .= $sr;
				$x++;
			}

			echo json_encode($ar);
		}
		else if ($_POST['action'] == 'filter_title') {
			$ar = array();
			$text = $_POST['text'];

			$res = mysqli_query($ch_conn, "SELECT i.item_id, t.team_name, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE a.team_id = t.team_id AND i.primary_resource = u.user_id AND i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $handled_teams) . ") AND i.description LIKE '%" . $text . "%' ORDER BY i.pht_start_datetime DESC");
			$x = 0;
			while ($row = mysqli_fetch_array($res)) {
				$ar[$x] = $row;
				$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $row['item_id']);
				$ar[$x]['name'] .= " <i>(Primary)</i><br>";
				$sr_ar = array();
				while ($sr_row = mysqli_fetch_array($sec_res_result))
					$sr_ar[] = $sr_row['name'];
					$sr = implode('; ', $sr_ar);
					$ar[$x]['name'] .= $sr;
				$x++;
			}

			echo json_encode($ar);
		}
		else if ($_POST['action'] == 'filter_status') {
			$ar = array();
			$text = $_POST['text'];

			$res = mysqli_query($ch_conn, "SELECT i.item_id, t.team_name, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE a.team_id = t.team_id AND i.primary_resource = u.user_id AND i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $handled_teams) . ") AND i.status LIKE '%" . $text . "%' ORDER BY i.pht_start_datetime DESC");
			$x = 0;
			while ($row = mysqli_fetch_array($res)) {
				$ar[$x] = $row;
				$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $row['item_id']);
				$ar[$x]['name'] .= " <i>(Primary)</i><br>";
				$sr_ar = array();
				while ($sr_row = mysqli_fetch_array($sec_res_result))
					$sr_ar[] = $sr_row['name'];
					$sr = implode('; ', $sr_ar);
					$ar[$x]['name'] .= $sr;
				$x++;
			}

			echo json_encode($ar);
		}
		else if ($_POST['action'] == 'filter_month') {
			$ar = array();

			$res = mysqli_query($ch_conn, "SELECT i.item_id, t.team_name, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE a.team_id = t.team_id AND i.primary_resource = u.user_id AND i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $handled_teams) . ") AND (MONTH(i.pht_start_datetime) = MONTH(NOW()) OR MONTH(i.pht_end_datetime) = MONTH(NOW())) ORDER BY i.pht_start_datetime ASC");
			$x = 0;
			while ($row = mysqli_fetch_array($res)) {
				$ar[$x] = $row;
				$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $row['item_id']);
				$ar[$x]['name'] .= " <i>(Primary)</i><br>";
				$sr_ar = array();
				while ($sr_row = mysqli_fetch_array($sec_res_result))
					$sr_ar[] = $sr_row['name'];
					$sr = implode('; ', $sr_ar);
					$ar[$x]['name'] .= $sr;
				$x++;
			}

			echo json_encode($ar);
		}
		else if ($_POST['action'] == 'filter_week') {
			$ar = array();
			$week = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT WEEK(NOW()) AS week"))['week'];
			$monday = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT STR_TO_DATE(CONCAT(YEAR(NOW()), $week, 'Monday'), '%X%V %W') AS mon"))['mon'];
			$sunday = mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT STR_TO_DATE(CONCAT(YEAR(NOW()), $week + 1, 'Sunday'), '%X%V %W') AS sun"))['sun'];

			$qry = "SELECT i.item_id, t.team_name, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE a.team_id = t.team_id AND i.primary_resource = u.user_id AND i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $handled_teams) . ") AND (i.pht_start_datetime BETWEEN '".$monday." 00:00:00' AND '".$sunday." 23:59:59') ORDER BY i.pht_start_datetime ASC";
			$res = mysqli_query($ch_conn, $qry);
			$x = 0;
			while ($row = mysqli_fetch_array($res)) {
				$ar[$x] = $row;
				$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $row['item_id']);
				$ar[$x]['name'] .= " <i>(Primary)</i><br>";
				$sr_ar = array();
				while ($sr_row = mysqli_fetch_array($sec_res_result))
					$sr_ar[] = $sr_row['name'];
					$sr = implode('; ', $sr_ar);
					$ar[$x]['name'] .= $sr;
				$x++;
			}

			echo json_encode($ar);
		}
		else if ($_POST['action'] == 'filter_day') {
			$ar = array();

			$res = mysqli_query($ch_conn, "SELECT i.item_id, t.team_name, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE a.team_id = t.team_id AND i.primary_resource = u.user_id AND i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $handled_teams) . ") AND (CURDATE() = DATE(i.pht_start_datetime) OR CURDATE() = DATE(i.pht_end_datetime) OR (CURDATE() BETWEEN i.pht_start_datetime AND i.pht_end_datetime)) ORDER BY i.pht_start_datetime ASC");
			$x = 0;
			while ($row = mysqli_fetch_array($res)) {
				$ar[$x] = $row;
				$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $row['item_id']);
				$ar[$x]['name'] .= " <i>(Primary)</i><br>";
				$sr_ar = array();
				while ($sr_row = mysqli_fetch_array($sec_res_result))
					$sr_ar[] = $sr_row['name'];
					$sr = implode('; ', $sr_ar);
					$ar[$x]['name'] .= $sr;
				$x++;
			}

			echo json_encode($ar);
		}
		else if ($_POST['action'] == 'filter_pipeline') {
			$ar = array();

			$res = mysqli_query($ch_conn, "SELECT i.item_id, t.team_name, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE a.team_id = t.team_id AND i.primary_resource = u.user_id AND i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $handled_teams) . ") AND DATE(i.pht_start_datetime) > NOW() ORDER BY i.pht_start_datetime DESC");
			$x = 0;
			while ($row = mysqli_fetch_array($res)) {
				$ar[$x] = $row;
				$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $row['item_id']);
				$ar[$x]['name'] .= " <i>(Primary)</i><br>";
				$sr_ar = array();
				while ($sr_row = mysqli_fetch_array($sec_res_result))
					$sr_ar[] = $sr_row['name'];
					$sr = implode('; ', $sr_ar);
					$ar[$x]['name'] .= $sr;
				$x++;
			}

			echo json_encode($ar);
		}
		else if ($_POST['action'] == 'filter_uploader') {
			$ar = array(); 
			
			$res = mysqli_query($ch_conn, "SELECT i.item_id, t.team_name, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE a.team_id = t.team_id AND i.primary_resource = u.user_id AND i.account_id = a.acct_id AND a.team_id IN (" . implode(', ', $handled_teams) . ") AND i.uploader_id = " . $_SESSION['ct_uid'] . " ORDER BY i.pht_start_datetime DESC");
			$x = 0;
			while ($row = mysqli_fetch_array($res)) {
				$ar[$x] = $row;
				$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $row['item_id']);
				$ar[$x]['name'] .= " <i>(Primary)</i><br>";
				$sr_ar = array();
				while ($sr_row = mysqli_fetch_array($sec_res_result))
					$sr_ar[] = $sr_row['name'];
					$sr = implode('; ', $sr_ar);
					$ar[$x]['name'] .= $sr;
				$x++;
			}

			echo json_encode($ar);
		}
		else if ($_POST['action'] == 'acct_modal_filter') {
			$ar = array();
			
			$res = mysqli_query($ch_conn, "SELECT i.item_id, t.team_name, i.change_ticket_id, a.acct_abbrev, a.acct_name, i.description, CONCAT(u.first_name, ' ', u.last_name) AS name, DATE_FORMAT(i.pht_start_datetime, '%b %d, %Y - %h:%i%p') AS pht_start_datetime, DATE_FORMAT(i.pht_end_datetime, '%b %d, %Y - %h:%i%p') AS pht_end_datetime, i.status FROM items i, account a, users u, team t WHERE a.team_id = t.team_id AND i.primary_resource = u.user_id AND i.account_id = a.acct_id 	AND a.acct_abbrev = '" . $_POST['acct'] . "' AND a.team_id IN (" . implode(', ', $handled_teams) . ")" . $_POST['condition'] . " ORDER BY i.pht_start_datetime DESC");
			$x = 0;
			while ($row = mysqli_fetch_array($res)) {
				$ar[$x] = $row;
				$sec_res_result = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE u.user_id = asr.user_id AND asr.item_id = " . $row['item_id']);
				$ar[$x]['name'] .= " <i>(Primary)</i><br>";
				$sr_ar = array();
				while ($sr_row = mysqli_fetch_array($sec_res_result))
					$sr_ar[] = $sr_row['name'];
					$sr = implode('; ', $sr_ar);
					$ar[$x]['name'] .= $sr;
				$x++;
			}

			echo json_encode($ar);
		}
		else if ($_POST['action'] == 'get_accounts_by_team') {
			$ar = array();

			$res = mysqli_query($ch_conn, "SELECT acct_id, acct_abbrev, acct_name FROM account WHERE team_id = " . $_POST['team'] . " ORDER BY acct_abbrev");
			while ($row = mysqli_fetch_row($res))
				$ar[] = $row;

			echo json_encode($ar);
		}
		else if ($_POST['action'] == 'get_resources_by_team') {
			$ar = array();

			$res = mysqli_query($ch_conn, "SELECT user_id, CONCAT(last_name, ', ', first_name) AS name FROM users WHERE team_id = " . $_POST['team'] . " ORDER BY name");
			$ar[0][0] = $_SESSION['ct_uid'];
			$ar[0][1] = $_SESSION['last_name'] . ", " . $_SESSION['first_name'];
			while ($row = mysqli_fetch_row($res))
				$ar[] = $row;

			echo json_encode($ar);
		}
	}
?>