<?php
	$change_types = ['Normal Minor', 'Normal Normal', 'Normal Major', 'Normal Urgent', 'Standard', 'Emergency'];
	$teams = array();
	$teams_res = mysqli_query($ch_conn, "SELECT team_id, team_name FROM team WHERE team_id NOT IN (98, 99)");
	while ($team_row = mysqli_fetch_array($teams_res))
		$teams[] = $team_row;

	$get_all_accounts = mysqli_query($ch_conn, "SELECT acct_id, acct_abbrev, acct_name FROM account WHERE team_id = " . $_SESSION['ct_team']);
	$all_accounts = array();
	while ($acct_row = mysqli_fetch_array($get_all_accounts)) {
		$all_accounts[] = $acct_row; 
	}

	$resources = array();
	foreach ($teams as $key => $val) {
		$resources[$teams[$key]['team_id']][0]['user_id'] = $_SESSION['ct_uid'];
		$resources[$teams[$key]['team_id']][0]['name'] = $_SESSION['last_name'] . ", " . $_SESSION['first_name'];
		$res_result = mysqli_query($ch_conn, "SELECT user_id, CONCAT(last_name, ', ', first_name) AS name FROM users WHERE team_id = " . $teams[$key]['team_id'] . " AND user_id != " . $_SESSION['ct_uid'] . " ORDER BY name");
		while ($res_row = mysqli_fetch_array($res_result)) {
			$resources[$teams[$key]['team_id']][] = $res_row;
		}
	}
	
?>
<script>
	var change_types = <?php echo json_encode($change_types); ?>;
	var all_accounts = <?php echo json_encode($all_accounts); ?>;
	var resources = <?php echo json_encode($resources); ?>;
	var timezones = <?php echo json_encode($timezones); ?>;
	var my_id = <?php echo json_encode($_SESSION['ct_uid']); ?>;
</script>

<!-- SHOW DETAILS MODAL -->
<div class="modal fade" id="show_ticket_details" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content create_modal" id="show_details_modal">
			<!--<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="float: right;width: 2%; background-color: #eee;"><span aria-hidden="true">&times;</span></button>
				<h3 class="modal-title" id="myModalLabel" style="color: black;"> My Accounts </h3>
			</div>-->
			<div class="modal-body" style="color: black;">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<table class="table show_details">
					<tr class="divider_tr">
						<td colspan=6 class="show_details_table_label" style="text-align: center;"> Ticket Details &nbsp;&nbsp;&nbsp;
						<?php
							if ($_SESSION['ct_uid'] == 25)
								echo "<a id='delete_btn' style='cursor:hand; color: red'> &times; </a>";
						?>
						</td>
					</tr>

					<tr>
						<td class="show_details_table_label"> Change Ticket ID </td>
						<td colspan="2" class="show_details_table" id="det_chg_id_td1"><span id="det_chg_id"></span></td>
						<td class="show_details_table_label"> Change Type </td>
						<td colspan="2" class="show_details_table" id="det_chg_type_td"><span id="det_chg_type"> </span></td>
					</tr>

					<tr>
						<td class="show_details_table_label"> Title / Description </td>
						<td colspan=5 class="show_details_table" id="det_chg_desc_td1"><span id="det_chg_desc"> </span></td>
					</tr>

					<tr class="divider_tr">
						<td colspan=6 class="show_details_table_label" style="text-align: center;"> Server Details </td>
					</tr>

					<tr>
						<td class="show_details_table_label"> Account </td>
						<td colspan=2 class="show_details_table" id="det_account_td"><span id="det_account"> </span></td>
						<td class="show_details_table_label"> SID(s) </td>
						<td colspan=2 class="show_details_table" id="det_sid_td"><span id="det_sid"> </span></td>
					</tr>

					<tr>
						<td class="show_details_table_label"> Server(s) </td>
						<td colspan=5 class="show_details_table" id="det_servers_td"><span id="det_servers"> </span></td>
					</tr>

					<tr class="tech_tr">
						<td class="show_details_table_label"> Technologies </td>
						<td colspan=5 class="show_details_table"><span id="det_os"> </span>	; <span id="det_db"> </span> ; <span id="det_sp"> </span></td>
					</tr>		

					<tr class="divider_tr">
						<td colspan=6 class="show_details_table_label" style="text-align: center;"> Activity Details </td>
					</tr>

					<tr>
						<td class="show_details_table_label"> Resource(s) </td>
						<td colspan=2 class="show_details_table" id="det_resources_td"><span id="det_resources"> </span></td>
						<td></td>
						<td colspan=2 class="show_details_table" id="det_resources_td2"> </td>
					</tr>

					<tr>
						<td class="show_details_table_label"> Action </td>
						<td colspan=3 class="show_details_table" id="det_actions_td"><span id="det_actions"> </span></td>
						<td colspan=2> </td> 
					</tr>

					<tr>
						<td class="show_details_table_label"> Scheduled Date and Time<br>(PH Time) </td>
						<td colspan=2 class="show_details_table" id="det_ph_time_td"><span id="det_ph_time"> </span></td>
						<td colspan=2 class="show_details_table" id="det_ph_time_td2"></td>
						<td></td>
					</tr>

					<tr>
						<td class="show_details_table_label"> Scheduled Date and Time<br>(Customer Time) </td>
						<td colspan=2 class="show_details_table" id="det_cu_time_td"><span id="det_cu_time"> </span></td>
						<td colspan=2 class="show_details_table" id="det_cu_time_td2"></td>
						<td></td>
					</tr>			

					<tr>
						<td class="show_details_table_label"> Reference </td>
						<td colspan=5 class="show_details_table" id="det_reference_td"><span id="det_reference"> </span></td>
					</tr>	

					<tr id="kms_tr">
						<td class="show_details_table_label"> KMS Reference Link </td>
						<td colspan=5 class="show_details_table" id="det_kms_td"><span id="det_kms"> </span></td>
					</tr>							

					<tr class="divider_tr">
						<td colspan=6 class="show_details_table_label" style="text-align: center;"> Progress Details </td>
					</tr>

					<tr>
						<td class="show_details_table_label"> Change Pre-checked? 
							&nbsp;<i class='glyphicon glyphicon-question-sign' data-container="body" data-toggle="popover" data-placement="top" data-title="What is Pre-check?" data-content="Has this change been Pre-checked by the primary resource prior to execution?"></i>
						</td>
						<td colspan=2 class="show_details_table" id='det_prechecked_td'><span id='det_prechecked'> </span></td>
						<td class="show_details_table_label"> Ready for Implementation? 
							&nbsp;<i class='glyphicon glyphicon-question-sign' data-container="body" data-toggle="popover" data-placement="top" data-title="Is it ready for Implementation?" data-content="Has this change been Approved by the Change Owner as Ready for Implementation?"></i>
						</td>
						<td colspan=2 class="show_details_table" id='det_approved_td'><span id='det_approved'> </span></td>
					</tr>

					<tr>
						<td class="show_details_table_label"> Status </td>
						<td colspan=2 class="show_details_table" id='det_status_td'><span id="det_status"> </span></td>
						<td class="show_details_table_label"> Four-eye Checked? 
							&nbsp;<i class='glyphicon glyphicon-question-sign' data-container="body" data-toggle="popover" data-placement="top" data-title="What is Four-eye Check?" data-content="Has the implementation and execution of this change been double-checked by the secondary resource(s)?"></i>
						</td>
						<td colspan=2 class="show_details_table" id='det_foureye_td'><span id='det_foureye'> </span></td>
 					</tr>

					<tr>
						<td class="show_details_table_label"> Most Recent Note </td>
						<td colspan=5 class="show_details_table" id="det_notes_td"><span id="det_notes"> </span>

							<input type="hidden" id="opened_note_chg_id" value=''>
							<input type="hidden" id="opened_note_title" value=''>
							<input type="hidden" id="opened_note_id" value=0>
							<input type="hidden" id="opened_note_acct_id" value=0>
							<input type="hidden" id="opened_note_presource" value=0>
							<input type="hidden" id="opened_note_sresource" value=''>
							<input type="hidden" id="opened_note_ph_sdate" value=''>
							<input type="hidden" id="opened_note_ph_edate" value=''>
							<input type="hidden" id="opened_note_cu_sdate" value=''>
							<input type="hidden" id="opened_note_cu_edate" value=''>
							<input type="hidden" id="opened_note_timezone" value=''>

							<br><br> <a id="show_notes_link" data-toggle="modal" data-target="#show_ticket_notes"> View All Notes </a>
						</td>
					</tr>	

					<tr class="divider_tr">
						<td colspan=6 class="show_details_table_label" style="text-align: center;"> <hr> </td>
					</tr>	

					<tr id="edit_tr" style="text-align:center">
						<td colspan=2 id='ticket_control_td'> <a id='add-note_btn'> Add Note </a></td>
						<td colspan=2 id='ticket_control_td'> <a id='edit-change_btn'> Edit Change Details </a></td>
						<td colspan=2 id='ticket_control_td' style="display: none"> <a id='save-change_btn'> Save Changes </a> </td>
						<td colspan=2 id='ticket_control_td'> <a id='choose-status_btn'> Change Status </a> </td>
						<td colspan=2 id='ticket_control_td' style="display: none"> <a id='cancel-changes_btn' onclick='resetModal()'> Cancel Changes </a> </td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- END SHOW DETAILS MODAL -->

<!-- SHOW NOTES MODAL -->
<div class="modal fade" id="show_ticket_notes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content notes_modal"> 
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="float: right;width: 2%; background-color: #eee;"><span aria-hidden="true">&times;</span></button>
				<h3 class="modal-title" id="myModalLabel" style="color: black; text-align: center;"> Work Log </h3>
			</div>
			<div class="modal-body" style="color: black;">
				<table class="table table-striped notes_table">
					<thead><tr>
						<td width=15%> <b>Date</b> </td>
						<td width=65%> <b>Note</b> </td>
						<td width=20%> <b>Uploader</b> </td>
					</tr></thead>
					<tbody id="notes_tbody">

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- END SHOW NOTES MODAL -->

<!-- ADD NOTES MODAL -->
<div class="modal fade" id="add_ticket_notes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content add_notes_modal"> 
			<div class="modal-body" style="color: black;">
				<span> Note Details </span><br>
				<textarea id='add-note_textarea'></textarea>
			</div>
      <div class="modal-footer">
        <button type="button" id="add-note_save" class="btn btn-primary">Save changes</button> 
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
		</div>
	</div>
</div>

<!-- END ADD NOTES MODAL -->

<!-- EDIT PRECHECK NOTES MODAL -->
<div class="modal fade" id="edit_precheck_notes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content add_notes_modal"> 
			<div class="modal-body" style="color: black;">
				<h4> Pre-check Details </h4>
				<span><i>Kindly detail what pre-checks have been done prior to executing the change, preferably in paragraph form.</i></span>
				<textarea id='edit-precheck-note_textarea'></textarea>
			</div>
      <div class="modal-footer">
        <button type="button" id="edit-precheck-note_save" class="btn btn-primary">Save Pre-check Details Note</button> 
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
		</div>
	</div>
</div>

<!-- END EDIT PRECHECK NOTES MODAL -->

<form action='set_trigger.php' method='POST' id='trigger_event_form'>
	<input type='hidden' name='e' id='trigger_e' value=''>
</form> 