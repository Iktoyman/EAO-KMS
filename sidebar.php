<div class="sidebar-div-container">
	<div class="sidebar-div">
		<div class="sidebar-header-div">
			<a id='header-sidebar-btnlink-open'><span class="glyphicon glyphicon-menu-hamburger header-sidebar-btn"></span></a>
			<span class="header-title"> PROJECT DELTA </span>	
		</div>
		<div class="sidebar-body-div">
			<ul>
				<li> <a id='new-item_link' onclick='triggerHomeEvent("new_item")'> NEW ITEM </a> </li>
				<li> <a data-toggle="collapse" href="#acct_collapse" aria-expanded="false" aria-controls="acct_collapse"> ALL ACCOUNT CHANGES </a></li>
					<div class='collapse acct_collapse' id='acct_collapse'>
						<div class="inner_acct-collapse">
						<?php
							echo "<ul>";
							for ($x = 0; $x < sizeof($all_accounts); $x++) {
								echo "<li> <a class='all-acct-list_li' id='all-" . $all_accounts[$x]['acct_abbrev'] . "' onclick=\"triggerHomeEvent('all-".$all_accounts[$x]['acct_abbrev']."')\" > " . $all_accounts[$x]['acct_abbrev'] . " - " . $all_accounts[$x]['acct_name'] . "</a></li>";
							}
							echo "</ul>";
						?>
						</div>
					</div>
				<li> <a id='my-accounts_link' onclick='triggerHomeEvent("my_accounts")'> TEAM ACCOUNT CHANGES </a> </li>
				<li> <a href='calendar.php'> CHANGE CALENDAR </a> </li>
				<li> <a href='sow.php'> START OF WEEK </a> </li>
			</ul>
		</div>
	</div>
</div>