<?php
	$transports = array();
	$transports_res = mysqli_query($ch_conn, "SELECT i.item_id, CONCAT(u.first_name, ' ', u.last_name) as name, 
		i.upload_date, 
		i.change_ticket_id, 
		i.change_type, 
		i.description, 
		i.sys_id, 
		i.server, 
		CONCAT(p.first_name, ' ', p.last_name) AS primary_res, 
		i.actions, 
		i.pht_start_datetime, 
		i.pht_end_datetime,
		i.reference 
		FROM items i, users u, users p, account a 
		WHERE i.uploader_id = u.user_id
		AND i.account_id = a.acct_id 
		AND i.primary_resource = p.user_id 
		AND i.actions = 'Import Transport'
		AND a.acct_abbrev = '". $a_id ."' 
		AND a.team_id IN (" . implode(', ', $teams) . ")
		ORDER BY i.pht_start_datetime");
	$i = 0;
	while ($transports_row = mysqli_fetch_array($transports_res)) {
		$sec_resources_stat = array();
		$secondary_res = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE asr.user_id = u.user_id AND asr.item_id = " . $transports_row['item_id']);
		while ($secondary_row = mysqli_fetch_array($secondary_res)) {
			$sec_resources_stat[] = $secondary_row['name']; 
		}
		$sr_stat = implode('; ', $sec_resources_stat);
		$transports[$i]['id'] = $transports_row['item_id'];
		$transports[$i]['uploader'] = $transports_row['name'];
		$transports[$i]['upload_date'] = $transports_row['upload_date'];
		$transports[$i]['chg_id'] = $transports_row['change_ticket_id'];
		$transports[$i]['chg_type'] = $transports_row['change_type'];
		$transports[$i]['desc'] = $transports_row['description'];
		$transports[$i]['sid'] = $transports_row['sys_id'];
		$transports[$i]['server'] = $transports_row['server'];
		$transports[$i]['resources'] = $transports_row['primary_res'];
		$transports[$i]['sec_resources'] = $sr_stat;
		if ($transports_row['actions'] == 'Execute Change')
			$add_action = " - " . mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT a.activity_name FROM activity a, items i WHERE i.activity_id = a.activity_id AND i.item_id = " . $transports_row['item_id']))['activity_name'];
		else
			$add_action = "";
		$transports[$i]['actions'] = $transports_row['actions'] . $add_action;
		$transports[$i]['ph_sd'] = $transports_row['pht_start_datetime'];
		$transports[$i]['ph_ed'] = $transports_row['pht_end_datetime'];
		$transports[$i]['reference'] = $transports_row['reference'];
		$i++;
	}

?>
<div role="tabpanel" class="account_details_div_tab tab-pane" id="trans">
	<table class="table table-hover account_details_table">
		<thead>	<tr>
			<td width=5%> UPLOADER </td>
			<td width=5.5%> DATE AND TIME<br>UPLOADED </td>
			<td width=6%> CHANGE TICKET </td>
			<td width=5%> CHANGE TYPE </td>
			<td width=15%> TITLE / DESCRIPTION </td>
			<td width=5%> SID(s) </td>
			<td width=10%> SERVER(s) </td>
			<td width=7.5%> RESOURCE(s) </td>
			<td width=7%> ACTIONS </td>
			<td width=6.5%> SCHED. START<br>DATE AND TIME<br>(PH TIME) </td>
			<td width=6.5%> SCHED. END<br>DATE AND TIME<br>(PH TIME) </td>
			<td width=10%> REFERENCE MAIL </td>
			<td width=5%> NOTES </td>
		</tr> </thead>
		<tbody id="acct_custom_tbody">
		<?php
			if (sizeof($transports) == 0) {
				echo "<tr>";
				echo "<td colspan=15 style='font-size: 1vw'>There are no Changes</td>";
				echo "</tr>";
			}
			else {
				for ($i = 0; $i < sizeof($transports); $i++) {
					echo "<tr>";
					echo "<td width=5%>" . $transports[$i]['uploader'] . "</td>";
					echo "<td width=5.5%>" . date("M d, Y <\b\\r> h:i A", strtotime($transports[$i]['upload_date'])) . "</td>";
					echo "<td width=6%><a id='show_dets_link' onclick='showDetails(".$transports[$i]['id'].")'>" . $transports[$i]['chg_id'] . "</a></td>";
					echo "<td width=5%>" . $transports[$i]['chg_type'] . "</td>";
					echo "<td width=15%>" . $transports[$i]['desc'] . "</td>";
					echo "<td id='word-wrap-td' width=5%>" . $transports[$i]['sid'] . "</td>";
					echo "<td width=10%>" . $transports[$i]['server'] . "</td>";
					echo "<td width=7.5%>" . $transports[$i]['resources'] . "* <br><i>" . $transports[$i]['sec_resources'] . "</i></td>";
					echo "<td width=7%>" . $transports[$i]['actions'] . "</td>";
					echo "<td width=6.5%>" . date("M d, Y <\b\\r> h:i A", strtotime($transports[$i]['ph_sd'])) . "</td>";
					echo "<td width=6.5%>" . date("M d, Y <\b\\r> h:i A", strtotime($transports[$i]['ph_ed'])) . "</td>";
					echo "<td width=10%>" . $transports[$i]['reference'] . "</td>";
					echo "<td width=5%><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(".$transports[$i]['id'].")'>View Notes</a></td>";
					echo "</tr>";
				}
			}
		?>
		</tbody>
	</table>
</div> 

<script>
</script>

