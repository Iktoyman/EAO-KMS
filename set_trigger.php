<?php
	require "connect.php";
	session_start();

	$_SESSION['e'] = $_POST['e'];
	header("Location: ../delta");
?>