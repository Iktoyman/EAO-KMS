<?php
	$activities = array();
	$act_res = mysqli_query($ch_conn, "SELECT activity_id, activity_name FROM activity WHERE activity_classification = 'Execute Change' AND activity_id != 99 ORDER BY activity_name");
	while ($act_row = mysqli_fetch_array($act_res)) 
		$activities[] = $act_row;

	$projects = array();
	$proj_res = mysqli_query($ch_conn, "SELECT activity_id, activity_name FROM activity WHERE activity_classification = 'Project' AND activity_id != 99 ORDER BY activity_id");
	while ($proj_row = mysqli_fetch_array($proj_res))
		$projects[] = $proj_row;

	$resources = array();
	$resources[0]['user_id'] = $_SESSION['ct_uid'];
	$resources[0]['name'] = $_SESSION['last_name'] . ", " . $_SESSION['first_name'];
	if ($_SESSION['ct_team'] == 99)
		$res_res = mysqli_query($ch_conn, "SELECT user_id, CONCAT(last_name, ', ', first_name) AS name FROM users WHERE team_id = " . $managed_teams_ids[0] . " ORDER BY last_name");
	else
		$res_res = mysqli_query($ch_conn, "SELECT user_id, CONCAT(last_name, ', ', first_name) AS name FROM users WHERE team_id = " . $_SESSION['ct_team'] . " ORDER BY last_name");
	while ($res_row = mysqli_fetch_array($res_res)) 
		$resources[] = $res_row;

	$os = array();
	$os_res = mysqli_query($ch_conn, "SELECT os_id, os_name FROM operating_system");
	while ($os_row = mysqli_fetch_array($os_res))
		$os[] = $os_row;

	$databases = array();
	$db_res = mysqli_query($ch_conn, "SELECT db_id, db_name FROM db_type");
	while ($db_row = mysqli_fetch_array($db_res))
		$databases[] = $db_row;

	$sap_products = array();
	$sp_res = mysqli_query($ch_conn, "SELECT sp_id, sp_name FROM sap_products");
	while ($sp_row = mysqli_fetch_array($sp_res))
		$sap_products[] = $sp_row;
?>

<!-- Create New Item Modal -->
<div class="modal fade" id="new_item" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content create_modal">
			<!--
			<div class="modal-header">
				
				<h3 class="modal-title" id="myModalLabel" style="color: black;">Track new Change Item</h3>
			</div>
			-->
			<div class="modal-body" style="color: black;">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<form method="POST" action="" id="create_change_item">
					<table class="table create_form_table">
						<?php
							if ($_SESSION['ct_team'] != 99 || sizeof($managed_teams_ids) <= 1) {
								echo "<tr style='display:none'>";
							}
							else
								echo "<tr>";
							echo "<td> Team </td>";
							echo "<td>";
								echo "<select name='team' id='team' onchange='selectTeam()'>";
									for ($i = 0; $i < sizeof($managed_teams_ids); $i++) {
										echo "<option value=" . $managed_teams_ids[$i] . "> " . $managed_teams_names[$i] . "</option>";
									}
								echo "</select>";
							echo "</td>";
							echo "</tr>";
						?>
						
						<tr>
							<td width=15%> Change Ticket ID <span class="asterisk">*</span></td>
							<td width=32.5%> <input type="text" name="chg_ticket_id" id="chg_ticket_id" /> </td>

							<td width=15%> Change Type <span class="asterisk">*</span></td>
							<td width=35%> 
								<select name="chg_type" id="chg_type">
									<option value=""> -- Select Change Type -- </option>
									<option value="Normal Minor"> Normal Minor </option>
									<option value="Normal Major"> Normal Major </option>
									<option value="Standard"> Standard </option>
									<option value="Emergency"> Emergency </option>
								</select>
							</td>

						</tr>
						<tr>
							<td> Title / Description <span class="asterisk">*</span></td>
							<td colspan=3> <input type="text" name="chg_desc" id="chg_desc"> </td>
						</tr>

						<tr>
							<td> Actions <span class="asterisk">*</span></td>
							<td> 
								<select name="actions" id="actions" onchange="checkAction()">
									<option value=""> -- Select Action -- </option>
									<option value="Project"> Projects </option>
									<option value="Execute Change"> Execute Change </option>
									<option value="Import Transport"> Import Transport </option>
									<option value="Start / Stop"> Start / Stop </option>
									<option value="Health Check"> Health Check </option>
								</select>
							</td>
							<td colspan=2 id='activity_dropdown_td'>
								<select name="activity_type" id="activity_dropdown">
									<option value=""> -- Select Activity -- </option>
									<?php
										for ($a = 0; $a < sizeof($activities); $a++) {
											echo "<option value=" . $activities[$a]['activity_id'] . "> " . $activities[$a]['activity_name'] . "</option>";
										}
									?>
									<option value=99> Others... (Indicate in Notes) </option>
								</select>
							</td>
							<td colspan=2 id='project_dropdown_td'>
								<select name='project_type' id='project_dropdown'>
									<option value=''> -- Select Project -- </option>
									<?php
										for ($b = 0; $b < sizeof($projects); $b++) {
											echo "<option value=" . $projects[$b]['activity_id'] . "> " . $projects[$b]['activity_name'] . "</option>";
										}
									?>
									<option value=99> Others... (Indicate in Notes) </option>
								</select>
							</td>
						</tr>

						<tr class="divider_tr">
							<td colspan=4 style="text-align: center;"> <hr> </td>
						</tr>

						<tr>
							<td> Account <span class="asterisk">*</span></td>
							<td> 
								<select name="account" id="account">
									<option value=""> -- Select Account -- </option>
									<?php
										for ($i = 0; $i < sizeof($team_accounts); $i++) {
											echo "<option value=" . $team_accounts[$i]['acct_id'] . "> " . $team_accounts[$i]['acct_abbrev'] . " - " . $team_accounts[$i]['acct_name'] . " </option>";
										}
									?>
								</select>
							</td>

							<td> SID(s) </td>
							<td> <input type="text" name="sids" id="sids"> </td>
						</tr>

						<tr>
							<td> Server(s) </td>
							<td colspan=2> <textarea name="servers" id="servers" rows=4> </textarea></td>
							<td> </td>
						</tr>

						<tr class='db_os_sp_tr'>
							<td> OS <span class="asterisk">*</span></td>
							<td>
								<button name="os_dd" id="os_dd"> <span class="create_modal_dropdown_text" id="os_dropdown_text"> -- Select Operating System(s) -- </span><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span></button>
								<div class="os_dropdown">
									<ul>
									<?php
										for ($a = 0; $a < sizeof($os); $a++) {
											echo "<li><input type='checkbox' class='os_chkbox' id='os_chkbox' name='os[]' value=" . $os[$a]['os_id'];
											echo " onchange='checkBoxes_os()'>"  . $os[$a]['os_name'] . " </li>";
										}
									?>
									</ul>
								</div>
							</td>
							<td> Database <span class="asterisk">*</span></td>
							<td>
								<button name="db_dd" id="db_dd"> <span class="create_modal_dropdown_text" id="db_dropdown_text"> -- Select Database(s) -- </span><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span></button>
								<div class="db_dropdown">
									<ul>
									<?php
										for ($a = 0; $a < sizeof($databases); $a++) {
											echo "<li><input type='checkbox' class='db_chkbox' id='db_chkbox' name='db[]' value=" . $databases[$a]['db_id'];
											echo " onchange='checkBoxes_db()'>"  . $databases[$a]['db_name'] . " </li>";
										}
									?>
									</ul>
								</div>
							</td>
						</tr>

						<tr class='db_os_sp_tr'>
							<td> SAP Product <span class="asterisk">*</span></td>
							<td> 
								<button name="sp_dd" id="sp_dd"> <span class="create_modal_dropdown_text" id="sp_dropdown_text"> -- Select SAP Product(s) -- </span><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span></button>
								<div class="sp_dropdown">
									<ul>
									<?php
										for ($a = 0; $a < sizeof($sap_products); $a++) {
											echo "<li><input type='checkbox' class='sp_chkbox' id='sp_chkbox' name='sp[]' value=" . $sap_products[$a]['sp_id'];
											echo " onchange='checkBoxes_sp()'>"  . $sap_products[$a]['sp_name'] . " </li>";
										}
									?>
									</ul>
								</div>
							</td>
							<td> Pipeline <br><i>(No schedule yet)</i></td>
							<td> <input type="checkbox" name="pipeline" id="pipeline"> </td>
						</tr>

						<tr class="divider_tr">
							<td colspan=4> <hr> </td>
						</tr>

						<tr>
							<td> Primary Resource <span class="asterisk">*</span></td>
							<td>
								<select name="primary_res" id="primary_res" onchange="removeName()">
									<option value=""> -- Select Primary Resource -- </option>
									<?php
										for ($a = 0; $a < sizeof($resources); $a++) {
											echo "<option value=" . $resources[$a]['user_id'] . "> " . $resources[$a]['name'] . " </option>";
										}
									?>
								</select>
							</td>
							<td> Secondary Resource(s) <span class="asterisk">*</span></td>
							<td>
								<button name="secondary_res" id="secondary_res"> <span class="create_modal_dropdown_text" id="sr_dropdown_text"> -- Select Secondary Resource(s) -- </span><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span></button>
								<div class="sec_res_dropdown">
									<ul>
									<?php
										for ($a = 0; $a < sizeof($resources); $a++) {
											echo "<li><input type='checkbox' class='sec_res_chkbox' id='sec_res_chkbox' name='sec_resources[]' value=" . $resources[$a]['user_id'] . " onchange='checkBoxes_resources()'>"  . $resources[$a]['name'] . " </li>";
										}
									?>
									</ul>
								</div>
							</td>
						</tr>

						<tr class="time_options_tr">
							<td> Immediate? </td>
							<td> <input type="checkbox" name="immediate" id="immediate"> </td>
							<td colspan=2></td>
						</tr>

						<tr class="cust_sched_tr">
							<td> Planned Start Schedule <br>(Customer Time) <span class="asterisk">*</span> </td>
							<td>
								<input type='text' class='datepicker-here' id='datepicker3' data-language='en' name="cu_sdate" placeholder="MM/DD/YYYY" /> &nbsp;
								<input type='text' name='cu_stime' id='sched_timepicker3' placeholder="HH:SS" onchange="convertTimezone()"/>
							</td>
							<td> Planned End Schedule <br>(Customer Time) <span class="asterisk">*</span> </td>
							<td>
								<input type='text' class='datepicker-here' id='datepicker4' data-language='en' name="cu_edate" placeholder="MM/DD/YYYY" /> &nbsp;
								<input type='text' name='cu_etime' id='sched_timepicker4' placeholder="HH:SS" onchange="convertTimezone()"/>
							</td>
						</tr>

						<tr class="cust_sched_tr">
							<td> Customer Timezone <span class="asterisk">*</span></td>
							<td colspan=2> 
								<select name="cust_timezone" id="cust_timezone" onchange='convertTimezone()'>
									<option value=""> -- Select Timezone -- </option>
									<?php
										for ($a = 0; $a < sizeof($timezones); $a++) {
											echo "<option value='".$timezones[$a]['tz_name']."' id='".$timezones[$a]['tz-offset']."'>" . $timezones[$a]['tz_name'] . "</option>";
										}
									?>
								</select>
							</td>
							<td> </td>
						</tr>

						<tr class="ph_sched_tr">
							<td> Planned Start Schedule <br>(PH Time) <span class="asterisk">*</span> </td>
							<td>
								<input type='text' id='datepicker1' data-language='en' name="ph_sdate" placeholder="MM/DD/YYYY" disabled/> &nbsp;
								<input type='text' name='ph_stime' id='sched_timepicker1' placeholder="HH:SS" disabled/>
							</td>
							<td> Planned End Schedule <br>(PH Time) <span class="asterisk">*</span> </td>
							<td>
								<input type='text' id='datepicker2' data-language='en' name="ph_edate" placeholder="MM/DD/YYYY" disabled/> &nbsp;
								<input type='text' name='ph_etime' id='sched_timepicker2' placeholder="HH:SS" disabled/>
							</td>
						</tr>

						<tr class="divider_tr">
							<td colspan=4> <hr> </td>
						</tr>

						<tr>
							<td> Reference Mail <span class="asterisk">*</span></td>
							<td>
								<input type="text" name="reference" id="reference">
							</td>
							<td> KMS Document ID </td>
							<td>
								<input type="text" name="kms_id" id="kms_id" placeholder="ex: IM-0001-00001234" onchange="setTimeout(function(){checkKMS()}, 1000)">
							</td>
						</tr>

						<tr>
							<td> Notes <span class="asterisk">*</span></td>
							<td colspan=2>
								<textarea name="notes" id="notes" rows=5></textarea>
							</td>
						</tr> 

						<tr>
							<td> </td>
							<td>
								Is this change <b>Approved</b> and <b>Ready for Implementation</b>?&nbsp;&nbsp;
								<input type='checkbox' name='change_ready' id='change_ready'>
							</td>
							<td colspan=2>
								Has this change been <b>Pre-checked</b> before Execution by the resource?&nbsp;&nbsp;
								<input type='checkbox' name='change_prechecked' id='change_prechecked' disabled>
							</td>
						</tr>

						<tr>
							<td>
								Status <span class="asterisk">*</span>&nbsp;&nbsp;
							</td>
							<td>
									<select name="status" id="form_status_dropdown">
										<option value="Open"> Open </option>
										<option value="In Progress"> In Progress </option>
										<option value="Completed"> Completed </option>
										<option value="Cancelled"> Cancelled </option>
										<option value="Failed"> Failed </option>
									</select>
							</td>
							<td colspan=2></td>
						</tr>

					</table>
					<!-- <input type="submit" class="save_btn" name="save_item"> -->
					<img src="resources/check_mark.png" height=75px width=75px id="save_btn" onclick="return clickSaveBtn()" />
				</form>
			</div>
		</div>
	</div>F
</div>
<!-- END CREATE NEW ITEM MODAL -->

<!-- MY ACCOUNTS MODAL -->
<div class="modal fade" id="my_accounts" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="float: right;width: 2%; background-color: #000; color: white; font-size: 2vw">
			<span aria-hidden="true">&times;</span>
		</button>
		<div class="modal-content my_accounts_modal">
			<!--<div class="modal-header">
				<h3 class="modal-title" id="myModalLabel" style="color: black;"> My Accounts </h3>
			</div>-->
			<div class="modal-body" style="color: black;">
				<table class="table table-hover my_accounts_table">
					<thead><tr>
						<td class='acct_table_th account_col' id='acct_abbrev'> Account </td>
						<td class='acct_table_th' id='total'> Total # of Changes </td>
						<td class='acct_table_th' id='open'> Open Changes</td>
						<td class='acct_table_th' id='inpr'> Changes in Progress </td>
						<td class='acct_table_th' id='comp'> Completed Changes </td>
						<td class='acct_table_th' id='canc'> Cancelled Changes </td>
						<td class='acct_table_th' id='fail'> Failed Changes </td>
						<td class='acct_table_th' id='week'> Changes this Week </td>
						<td class='acct_table_th' id='mont'> Changes this Month</td>
						<td class='acct_table_th' id='norm'> Normal Changes </td>
						<td class='acct_table_th' id='stnd'> Standard Changes </td>
						<td class='acct_table_th' id='tran'> Transports </td>
						<td class='acct_table_th' id='emer'> Emergency Changes </td>
					</tr></thead>
					
					<tbody id='my_accounts_tbody'>
					<?php
						for($a = 0; $a < sizeof($accounts); $a++) {
							echo "<tr>";
							echo "<td class='account_col'> <span id='acct_abbreviation'> <a href=account.php?id=" . $accounts[$a]['acct_abbrev'] . "><b>" . $accounts[$a]['acct_abbrev'] . " </b></a></span><br>";
							echo "<span id='acct_name'><i> " . $accounts[$a]['acct_name'] . " </i></span></td>";
							echo "<td id='acct_table_td1'><span class='acct_table_num' onclick=\"accountModalFilter('" . $accounts[$a]['acct_abbrev'] . "', 'All')\">" . $accounts[$a]['total'] . "</span></td>";
							echo "<td id='acct_table_td2'><span class='acct_table_num' onclick=\"accountModalFilter('" . $accounts[$a]['acct_abbrev'] . "', 'Open')\">" . $accounts[$a]['open'] . "</span></td>";
							echo "<td id='acct_table_td1'><span class='acct_table_num' onclick=\"accountModalFilter('" . $accounts[$a]['acct_abbrev'] . "', 'In Progress')\">" . $accounts[$a]['inpr'] . "</span></td>";
							echo "<td id='acct_table_td2'><span class='acct_table_num' onclick=\"accountModalFilter('" . $accounts[$a]['acct_abbrev'] . "', 'Completed')\">" . $accounts[$a]['comp'] . "</span></td>";
							echo "<td id='acct_table_td1'><span class='acct_table_num' onclick=\"accountModalFilter('" . $accounts[$a]['acct_abbrev'] . "', 'Cancelled')\">" . $accounts[$a]['canc'] . "</span></td>";
							echo "<td id='acct_table_td2'><span class='acct_table_num' onclick=\"accountModalFilter('" . $accounts[$a]['acct_abbrev'] . "', 'Failed')\">" . $accounts[$a]['fail'] . "</span></td>";
							echo "<td id='acct_table_td1'><span class='acct_table_num' onclick=\"accountModalFilter('" . $accounts[$a]['acct_abbrev'] . "', 'Week')\">" . $accounts[$a]['week'] . "</span></td>";
							echo "<td id='acct_table_td2'><span class='acct_table_num' onclick=\"accountModalFilter('" . $accounts[$a]['acct_abbrev'] . "', 'Month')\">" . $accounts[$a]['mont'] . "</span></td>";
							echo "<td id='acct_table_td1'><span class='acct_table_num' onclick=\"accountModalFilter('" . $accounts[$a]['acct_abbrev'] . "', 'Normal')\">" . $accounts[$a]['norm'] . "</span></td>";
							echo "<td id='acct_table_td2'><span class='acct_table_num' onclick=\"accountModalFilter('" . $accounts[$a]['acct_abbrev'] . "', 'Standard')\">" . $accounts[$a]['stnd'] . "</span></td>";
							echo "<td id='acct_table_td1'><span class='acct_table_num' onclick=\"accountModalFilter('" . $accounts[$a]['acct_abbrev'] . "', 'Import Transport')\">" . $accounts[$a]['tran'] . "</span></td>";
							echo "<td id='acct_table_td2'><span class='acct_table_num' onclick=\"accountModalFilter('" . $accounts[$a]['acct_abbrev'] . "', 'Emergency')\">" . $accounts[$a]['emer'] . "</span></td>";
							echo "</tr>";
						}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- END MY ACCOUNTS MODAL -->

<!-- ADD PRE-CHECK NOTES MODAL -->
<div class="modal fade" id="add-precheck-notes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content add_notes_modal"> 
			<div class="modal-body" style="color: black;">
				<h4> Pre-check Details </h4>
				<span><i>Kindly detail what pre-checks have been done prior to executing the change, preferably in paragraph form.</i></span>
				<textarea id='add-precheck-note_textarea'></textarea>
			</div>
      <div class="modal-footer">
        <button type="button" id="add-precheck-note_save" class="btn btn-primary">Add to Notes</button> 
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
		</div>
	</div>
</div>

<!-- END ADD PRE-CHECK NOTES MODAL -->

<form id='download-view_form' method="POST" action='export_view.php'>
	<input id='filter_type' name='filter_type' type='hidden' value=''>
	<input id='filter' name='filter' type='hidden' value=''>
</form>

