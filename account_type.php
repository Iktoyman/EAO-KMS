<?php
	$norm = array();
	$norm_res = mysqli_query($ch_conn, "SELECT i.item_id, CONCAT(u.first_name, ' ', u.last_name) as name, 
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
		AND i.change_type = 'Normal Minor' 
		ORDER BY i.pht_start_datetime");
	$i = 0;
	while ($norm_row = mysqli_fetch_array($norm_res)) {
		$sec_resources_type = array();
		$secondary_res = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE asr.user_id = u.user_id AND asr.item_id = " . $norm_row['item_id']);
		while ($secondary_row = mysqli_fetch_array($secondary_res)) {
			$sec_resources_type[] = $secondary_row['name']; 
		}
		$sr_type = implode('; ', $sec_resources_type);
		$norm[$i]['id'] = $norm_row['item_id'];
		$norm[$i]['uploader'] = $norm_row['name'];
		$norm[$i]['upload_date'] = $norm_row['upload_date'];
		$norm[$i]['chg_id'] = $norm_row['change_ticket_id'];
		$norm[$i]['chg_type'] = $norm_row['change_type'];
		$norm[$i]['desc'] = $norm_row['description'];
		$norm[$i]['sid'] = $norm_row['sys_id'];
		$norm[$i]['server'] = $norm_row['server'];
		$norm[$i]['resources'] = $norm_row['primary_res'];
		$norm[$i]['sec_resources'] = $sr_type;
		if ($norm_row['actions'] == 'Execute Change')
			$add_action = " - " . mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT a.activity_name FROM activity a, items i WHERE i.activity_id = a.activity_id AND i.item_id = " . $norm_row['item_id']))['activity_name'];
		else
			$add_action = "";
		$norm[$i]['actions'] = $norm_row['actions'] . $add_action;
		$norm[$i]['ph_sd'] = $norm_row['pht_start_datetime'];
		$norm[$i]['ph_ed'] = $norm_row['pht_end_datetime'];
		$norm[$i]['reference'] = $norm_row['reference'];
		$i++;
	}

?>
<div role="tabpanel" class="account_details_div_tab tab-pane" id="type">
	<div class="account_details_dropdown_div">
		<select name="sort_by_type" id="sort_by_type" onchange="switchType()">
			<option value=""> -- Sort by Type -- </option>
			<option value="Normal"> All Normal Changes </option>
			<option value="Normal Minor"> Normal Minor </option>
			<option value="Normal Normal"> Normal Normal </option>
			<option value="Normal Major"> Normal Major </option>
			<option value="Normal Urgent"> Normal Urgent </option>
			<option value="Standard"> Standard </option>
			<option value="Emergency"> Emergency </option>
		</select>
	</div>
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
		<tbody id="acct_type_tbody">
		<?php
			if (sizeof($norm) == 0) {
				echo "<tr>";
				echo "<td colspan=15 style='font-size:1vw'>There are no Normal Minor changes</td>";
				echo "</tr>";
			}
			else {
				for ($i = 0; $i < sizeof($norm); $i++) {
					echo "<tr>";
					echo "<td width=5%>" . $norm[$i]['uploader'] . "</td>";
					echo "<td width=5.5%>" . date("M d, Y <\b\\r> h:i A", strtotime($norm[$i]['upload_date'])) . "</td>";
					echo "<td width=6%><a id='show_dets_link' onclick='showDetails(".$norm[$i]['id'].")'>" . $norm[$i]['chg_id'] . "</a></td>";
					echo "<td width=5%>" . $norm[$i]['chg_type'] . "</td>";
					echo "<td width=15%>" . $norm[$i]['desc'] . "</td>";
					echo "<td id='word-wrap-td' width=5%>" . $norm[$i]['sid'] . "</td>";
					echo "<td width=10%>" . $norm[$i]['server'] . "</td>";
					echo "<td width=7.5%>" . $norm[$i]['resources'] . "* <br><i>" . $norm[$i]['sec_resources'] . "</i></td>";
					echo "<td width=7%>" . $norm[$i]['actions'] . "</td>";
					echo "<td width=6.5%>" . date("M d, Y <\b\\r> h:i A", strtotime($norm[$i]['ph_sd'])) . "</td>";
					echo "<td width=6.5%>" . date("M d, Y <\b\\r> h:i A", strtotime($norm[$i]['ph_ed'])) . "</td>";
					echo "<td width=10%>" . $norm[$i]['reference'] . "</td>";
					echo "<td width=5%><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(".$norm[$i]['id'].")'>View Notes</a></td>";
					echo "</tr>";
				}
			}
		?>
		</tbody>
	</table>
</div> 

<script>
	function switchType() {
		var type = document.getElementById('sort_by_type').value;
		var a_id = '<?php echo $a_id; ?>';
		var tbody = document.getElementById('acct_type_tbody');
		document.getElementById('cur_type').innerHTML = "(" + type + ")";
		tbody.innerHTML = '';
		$.ajax({
			type: "POST",
			url: "acct_queries.php",
			data: {
				action: type,
				class_type: 'type',
				a_id: a_id
			},
			dataType: 'json'
		})
		.done(function(data) {
			if (data.length == 0) {
				tbody.innerHTML += "<tr><td colspan=15 style='font-size:1vw'>There are no " + type + " changes</td></tr>"; 
			}
			else {
				for (var x = 0; x < data.length; x++) {
					tbody.innerHTML += "<tr>" 
					+ "<td width=5%>" + data[x]['name'] + "</td>" 
					+ "<td width=5.5%>" + data[x]['up_date'] + "</td>"
					+ "<td width=6%><a id='show_dets_link' onclick='showDetails(" + data[x]['id'] + ")'>" + data[x]['chg_id'] + "</a></td>"
					+ "<td width=5%>" + data[x]['chg_type'] + "</td>"
					+ "<td width=15%>" + data[x]['chg_desc'] + "</td>"
					+ "<td width=5% id='word-wrap-td'>" + data[x]['sid'] + "</td>"
					+ "<td width=10%>" + data[x]['server'] + "</td>"
					+ "<td width=7.5%>" + data[x]['assignees'] + "</td>"
					+ "<td width=7%>" + data[x]['action'] + "</td>"
					+ "<td width=6.5%>" + data[x]['ph_sd'] + "</td>"
					+ "<td width=6.5%>" + data[x]['ph_ed'] + "</td>"
					+ "<td width=10%>" + data[x]['ref'] + "</td>"
					+ "<td width=5%><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(" + data[x]['id'] + ")'>" + data[x]['notes'] + "</a></td>"
					+ "</tr>";
				}
			}
		});
	}
</script>

