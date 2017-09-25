<div class="header_name_div">
	<a id='header-sidebar-btnlink'><span class="glyphicon glyphicon-menu-hamburger header-sidebar-btn"></span></a>
	<a href="../delta"> <span class="header-title"> PROJECT DELTA </span> </a>
</div>

<div class="header_navbar_div">
	<a id="header_acct-dropdown">
		<?php
			if ($_SERVER['PHP_SELF'] == '/delta/calendar.php') {
				echo "Actions";
			}
			else if ($_SERVER['PHP_SELF'] == '/delta/sow.php') {
				echo "Teams";
			}
			else if ($_SERVER['PHP_SELF'] == '/delta/account.php') {
				echo "Accounts";
			}
		?> 
		<span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span> 
	</a>
	<div id="header_acct-dropdown-div">
		<ul id='header_add-list'>
		<?php
			if ($_SERVER['PHP_SELF'] == '/delta/calendar.php') {
				echo "<li><a href='calendar.php?activities=changes'> Change Activities </a></li>";
				echo "<li><a href='calendar.php?activities=transports'> Transports </a></li>";
			}
			else if ($_SERVER['PHP_SELF'] == '/delta/sow.php') {
				for ($x = 0; $x < sizeof($teams); $x++)
					echo "<li><a href='sow.php?team=".$teams[$x]['team_id']."'>" . $teams[$x]['team_name'] . "</a></li>";
			}
			else if ($_SERVER['PHP_SELF'] == '/delta/account.php') {
				for ($x = 0; $x < sizeof($accounts); $x++)
					echo "<li><a href='account.php?id=".$accounts[$x]['acct_id']."'>" . $accounts[$x]['acct_abbrev'] . " - " . $accounts[$x]['acct_name'] . "</a></li>";
			}
		?>
		</ul>
	</div>
</div>

<div class="header_user_div">
	<a id='user-dropdown'>
		<span class="glyphicon glyphicon-user welcome_message user-btn" style="padding: 1%"></span>
	</a>
	<a id='menu-dropdown'>
		<span class="glyphicon glyphicon-th welcome_message menu-btn"></span>
	</a>
	<div id="header_user-dropdown-div">
		<ul id='header_user-dropdown-list'>
			<li style="padding: 0"> Welcome, <?php echo $_SESSION['user_fullname']; ?>! </li>
			<li><hr></li>
			<li><a onclick='triggerHomeEvent("my_uploads")'> My Uploads </a></li>
			<li><a href='../logout.php'> Logout </a></li>
		</ul>
	</div>
	<div id="menu-dropdown-div">
		<ul id='menu-dropdown-list'>
			<li align=center><b> TOOLS </b></li>
			<li><hr></li>
			<li><a href='../'> EAO - KMS </a></li>
			<li><a href='http://16.146.6.254:7080/apollo/home.php'> Apollo </a></li>
			<li><a href='https://ent302.sharepoint.hpe.com/teams/EAOPH-Quality/Shared%20Documents/Forms/AllItems.aspx?RootFolder=%2fteams%2fEAOPH%2dQuality%2fShared%20Documents%2f40%20EAO%20RST%20Scorecard%20Tools&FolderCTID=0x012000877D17965246E0459CBE002116CCE1F8'> AQUA </a></li>
		</ul>
	</div>
</div>	