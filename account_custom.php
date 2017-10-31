<?php
	$custom = array();
	$custom_res = mysqli_query($ch_conn, "SELECT i.item_id, CONCAT(u.first_name, ' ', u.last_name) as name, 
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
		AND a.acct_abbrev = '". $a_id ."' 
		AND a.team_id IN (" . implode(', ', $teams) . ")
		ORDER BY i.pht_start_datetime");
	$i = 0;
	while ($custom_row = mysqli_fetch_array($custom_res)) {
		$sec_resources_stat = array();
		$secondary_res = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE asr.user_id = u.user_id AND asr.item_id = " . $custom_row['item_id']);
		while ($secondary_row = mysqli_fetch_array($secondary_res)) {
			$sec_resources_stat[] = $secondary_row['name']; 
		}
		$sr_stat = implode('; ', $sec_resources_stat);
		$custom[$i]['id'] = $custom_row['item_id'];
		$custom[$i]['uploader'] = $custom_row['name'];
		$custom[$i]['upload_date'] = $custom_row['upload_date'];
		$custom[$i]['chg_id'] = $custom_row['change_ticket_id'];
		$custom[$i]['chg_type'] = $custom_row['change_type'];
		$custom[$i]['desc'] = $custom_row['description'];
		$custom[$i]['sid'] = $custom_row['sys_id'];
		$custom[$i]['server'] = $custom_row['server'];
		$custom[$i]['resources'] = $custom_row['primary_res'];
		$custom[$i]['sec_resources'] = $sr_stat;
		if ($custom_row['actions'] == 'Execute Change')
			$add_action = " - " . mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT a.activity_name FROM activity a, items i WHERE i.activity_id = a.activity_id AND i.item_id = " . $custom_row['item_id']))['activity_name'];
		else
			$add_action = "";
		$custom[$i]['actions'] = $custom_row['actions'] . $add_action;
		$custom[$i]['ph_sd'] = $custom_row['pht_start_datetime'];
		$custom[$i]['ph_ed'] = $custom_row['pht_end_datetime'];
		$custom[$i]['reference'] = $custom_row['reference'];
		$i++;
	}

?>
<div role="tabpanel" class="account_details_div_tab tab-pane" id="custom">
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
			if (sizeof($custom) == 0) {
				echo "<tr>";
				echo "<td colspan=15 style='font-size: 1vw'>There are no Changes</td>";
				echo "</tr>";
			}
			else {
				for ($i = 0; $i < sizeof($custom); $i++) {
					echo "<tr>";
					echo "<td width=5%>" . $custom[$i]['uploader'] . "</td>";
					echo "<td width=5.5%>" . date("M d, Y <\b\\r> h:i A", strtotime($custom[$i]['upload_date'])) . "</td>";
					echo "<td width=6%><a id='show_dets_link' onclick='showDetails(".$custom[$i]['id'].")'>" . $custom[$i]['chg_id'] . "</a></td>";
					echo "<td width=5%>" . $custom[$i]['chg_type'] . "</td>";
					echo "<td width=15%>" . $custom[$i]['desc'] . "</td>";
					echo "<td id='word-wrap-td' width=5%>" . $custom[$i]['sid'] . "</td>";
					echo "<td width=10%>" . $custom[$i]['server'] . "</td>";
					echo "<td width=7.5%>" . $custom[$i]['resources'] . "* <br><i>" . $custom[$i]['sec_resources'] . "</i></td>";
					echo "<td width=7%>" . $custom[$i]['actions'] . "</td>";
					echo "<td width=6.5%>" . date("M d, Y <\b\\r> h:i A", strtotime($custom[$i]['ph_sd'])) . "</td>";
					echo "<td width=6.5%>" . date("M d, Y <\b\\r> h:i A", strtotime($custom[$i]['ph_ed'])) . "</td>";
					echo "<td width=10%>" . $custom[$i]['reference'] . "</td>";
					echo "<td width=5%><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(".$custom[$i]['id'].")'>View Notes</a></td>";
					echo "</tr>";
				}
			}
		?>
		</tbody>
	</table>
</div> 

<script>
</script>

