<?php
	require "connect.php";
	require "../connect.php";

	/*
	$file = fopen("resources/employee_list.txt", "r");
	$notfound_file = fopen("resources/notfound.txt", "a");
	while ($line = fgets($file)) {
		$qry = "SELECT username, first_name, last_name FROM skms.users WHERE username = '" . trim($line) . "'";
		$res = mysqli_query($conn, $qry);
		//var_dump(mysqli_fetch_fields($exist_res));
		echo "<br>";
			$row = mysqli_fetch_array($res);
			$uname = $row['username'];
			$fname = $row['first_name'];
			$lname = $row['last_name'];

		if (mysqli_num_rows($res) > 0) {

			$exist_qry = "SELECT user_id FROM change_tracker.users WHERE username = '" . $uname . "'";
			$exist_res = mysqli_query($ch_conn, $exist_qry);

			//var_dump(mysqli_fetch_fields($exist_res));
			//echo mysqli_num_rows($exist_res) . "<br>";
			if (mysqli_num_rows($exist_res) == 0) {
				$insert_qry = "INSERT INTO users(username, first_name, last_name, team_id) VALUES('$uname', '$fname', '$lname', 7)";
				echo "Added '$uname'<br>";
				mysqli_query($ch_conn, $insert_qry);
			}
			else {
				echo "User $uname already exists.<br>"; 
			}
		}
		else {
			echo "User '" . trim($line) . "' not found in KMS.<br>";
			$txt = trim($line) . "\n\r";
			fwrite($notfound_file, $txt);
		}
	}

	fclose($file);
	*/
?>