<?php
	$open = array();
	$open_res = mysqli_query($ch_conn, "SELECT i.item_id, CONCAT(u.first_name, ' ', u.last_name) as name, 
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
		i.customer_start_datetime, 
		i.customer_end_datetime, 
		i.customer_timezone, 
		i.reference 
		FROM items i, users u, users p, account a 
		WHERE i.uploader_id = u.user_id
		AND i.account_id = a.acct_id 
		AND i.primary_resource = p.user_id 
		AND a.acct_abbrev = '". $a_id ."' 
		AND a.team_id IN (" . implode(', ', $teams) . ")
		AND i.status = 'Open' 
		ORDER BY i.pht_start_datetime");
	$i = 0;
	while ($open_row = mysqli_fetch_array($open_res)) {
		$sec_resources_stat = array();
		$secondary_res = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE asr.user_id = u.user_id AND asr.item_id = " . $open_row['item_id']);
		while ($secondary_row = mysqli_fetch_array($secondary_res)) {
			$sec_resources_stat[] = $secondary_row['name']; 
		}
		$sr_stat = implode('; ', $sec_resources_stat);
		$open[$i]['id'] = $open_row['item_id'];
		$open[$i]['uploader'] = $open_row['name'];
		$open[$i]['upload_date'] = $open_row['upload_date'];
		$open[$i]['chg_id'] = $open_row['change_ticket_id'];
		$open[$i]['chg_type'] = $open_row['change_type'];
		$open[$i]['desc'] = $open_row['description'];
		$open[$i]['sid'] = $open_row['sys_id'];
		$open[$i]['server'] = $open_row['server'];
		$open[$i]['resources'] = $open_row['primary_res'];
		$open[$i]['sec_resources'] = $sr_stat;
		if ($open_row['actions'] == 'Execute Change')
			$add_action = " - " . mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT a.activity_name FROM activity a, items i WHERE i.activity_id = a.activity_id AND i.item_id = " . $open_row['item_id']))['activity_name'];
		else
			$add_action = "";
		$open[$i]['actions'] = $open_row['actions'] . $add_action;
		$open[$i]['ph_sd'] = $open_row['pht_start_datetime'];
		$open[$i]['ph_ed'] = $open_row['pht_end_datetime'];
		$open[$i]['cu_sd'] = $open_row['customer_start_datetime'];
		$open[$i]['cu_ed'] = $open_row['customer_end_datetime'];
		$open[$i]['cu_tz'] = $open_row['customer_timezone'];
		$open[$i]['reference'] = $open_row['reference'];
		$i++;
	}

?>
<div role="tabpanel" class="account_details_div_tab tab-pane" id="stat">
	<div class="account_details_dropdown_div">
		<select name="sort_by_status" id="sort_by_status" onchange="switchStatus()">
			<option value=""> -- Sort by Status -- </option>
			<option value="Open"> Open </option>
			<option value="In Progress"> In Progress </option>
			<option value="Completed"> Completed </option>
			<option value="Failed"> Failed </option>
			<option value="Cancelled"> Cancelled </option>
			<option value="Overdue"> Overdue </option>
		</select>
	</div>
	<table class="table table-hover account_details_table">
		<thead>	<tr>
			<td width=5%> UPLOADER </td>
			<td width=5.5%1> DATE AND TIME<br>UPLOADED </td>
			<td width=6%> CHANGE TICKET </td>
			<td width=5%> CHANGE TYPE </td>
			<td width=15%> TITLE / DESCRIPTION </td>
			<td width=3%> SID(s) </td>
			<td width=5%> SERVER(s) </td>
			<td width=7.5%> RESOURCE(s) </td>
			<td width=7%> ACTIONS </td>
			<td width=6.5%> SCHED. START<br>DATE AND TIME<br>(PH TIME) </td>
			<td width=6.5%> SCHED. END<br>DATE AND TIME<br>(PH TIME) </td>
			<td width=6.5%> SCHED. START<br>DATE AND TIME<br>(CUST. TIME) </td>
			<td width=6.5%> SCHED. END<br>DATE AND TIME<br>(CUST. TIME) </td>
			<td width=10%> REFERENCE MAIL </td>
			<td width=5%> NOTES </td>
		</tr> </thead>
		<tbody id="acct_status_tbody">
		<?php
			if (sizeof($open) == 0) {
				echo "<tr>";
				echo "<td colspan=15 style='font-size: 1vw'>There are no Open Changes</td>";
				echo "</tr>";
			}
			else {
				for ($i = 0; $i < sizeof($open); $i++) {
					echo "<tr>";
					echo "<td>" . $open[$i]['uploader'] . "</td>";
					echo "<td>" . date("M d, Y <\b\\r> h:i A", strtotime($open[$i]['upload_date'])) . "</td>";
					echo "<td><a id='show_dets_link' onclick='showDetails(".$open[$i]['id'].")'>" . $open[$i]['chg_id'] . "</a></td>";
					echo "<td>" . $open[$i]['chg_type'] . "</td>";
					echo "<td>" . $open[$i]['desc'] . "</td>";
					echo "<td>" . $open[$i]['sid'] . "</td>";
					echo "<td>" . $open[$i]['server'] . "</td>";
					echo "<td>" . $open[$i]['resources'] . "* <br><i>" . $open[$i]['sec_resources'] . "</i></td>";
					echo "<td>" . $open[$i]['actions'] . "</td>";
					echo "<td>" . date("M d, Y <\b\\r> h:i A", strtotime($open[$i]['ph_sd'])) . "</td>";
					echo "<td>" . date("M d, Y <\b\\r> h:i A", strtotime($open[$i]['ph_ed'])) . "</td>";
					echo "<td>" . date("M d, Y <\b\\r> h:i A", strtotime($open[$i]['cu_sd'])) . "</td>";
					echo "<td>" . date("M d, Y <\b\\r> h:i A", strtotime($open[$i]['cu_ed'])) . "</td>";
					echo "<td>" . $open[$i]['reference'] . "</td>";
					echo "<td><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(".$open[$i]['id'].")'>View Notes</a></td>";
					echo "</tr>";
				}
			}
		?>
		</tbody>
	</table>
</div> 

<script>
	function switchStatus() {
		var status = document.getElementById('sort_by_status').value;
		var a_id = '<?php echo $a_id; ?>';
		var tbody = document.getElementById('acct_status_tbody');
		document.getElementById('cur_status').innerHTML = "(" + status + ")";
		tbody.innerHTML = '';
		$.ajax({
			type: "POST",
			url: "acct_queries.php",
			data: {
				action: status,
				class_type: 'status',
				a_id: a_id
			},
			dataType: 'json'
		})
		.done(function(data) {
			if (data.length == 0) {
				tbody.innerHTML += "<tr><td colspan=15 style='font-size: 1vw'>There are no " + status + " changes</td></tr>";
			}
			else {
				for (var x = 0; x < data.length; x++) {
					tbody.innerHTML += "<tr>" + "<td>" + data[x]['name'] + "</td>" 
					+ "<td>" + data[x]['up_date'] + "</td>"
					+ "<td><a id='show_dets_link' onclick='showDetails(" + data[x]['id'] + ")'>" + data[x]['chg_id'] + "</a></td>"
					+ "<td>" + data[x]['chg_type'] + "</td>"
					+ "<td>" + data[x]['chg_desc'] + "</td>"
					+ "<td>" + data[x]['sid'] + "</td>"
					+ "<td>" + data[x]['server'] + "</td>"
					+ "<td>" + data[x]['assignees'] + "</td>"
					+ "<td>" + data[x]['action'] + "</td>"
					+ "<td>" + data[x]['ph_sd'] + "</td>"
					+ "<td>" + data[x]['ph_ed'] + "</td>"
					+ "<td>" + data[x]['cu_sd'] + "</td>"
					+ "<td>" + data[x]['cu_ed'] + "</td>"
					+ "<td>" + data[x]['ref'] + "</td>"
					+ "<td><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(" + data[x]['id'] + ")'>" + data[x]['notes'] + "</a></td>"
					+ "</tr>";
				}
			}
		});
	}
</script>

