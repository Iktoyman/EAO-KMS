	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="css/delta.css">
	<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
  <script type="text/javascript" src="/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="js/moment/moment.js"></script>

  <!-- date picker -->
  <link href="css/datepicker.min.css" rel="stylesheet" type="text/css">
  <script src="js/datepicker.min.js"></script>
  <script src="js/datepicker.en.js"></script>

  <!-- time picker -->
  <link href="timepicker/jquery.timepicker.css" rel="stylesheet" type="text/css">
  <script src="timepicker/jquery.timepicker.min.js"></script>

  <script type="text/javascript" src="js/underscore-min.js"></script>

  <!--[if IE]>
  <style>
    #show_details_modal td {
      width: 16.5%;
    }
  </style>
  <![endif]-->

<?php  
  // GET ALL ACCOUNTS REGARDLESS OF TEAM
  $get_all_accounts_qry = "SELECT acct_abbrev, acct_name FROM account GROUP BY acct_abbrev ORDER BY acct_abbrev";
  $get_all_accounts = mysqli_query($ch_conn, $get_all_accounts_qry);
  $all_accounts = array();
  while ($acct_row = mysqli_fetch_array($get_all_accounts))
    $all_accounts[] = $acct_row;
?>