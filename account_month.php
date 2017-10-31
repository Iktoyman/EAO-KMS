<?php
	$month = array();
	$month_res = mysqli_query($ch_conn, "SELECT i.item_id, CONCAT(u.first_name, ' ', u.last_name) as name, 
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
		AND MONTH(i.pht_start_datetime) = MONTH(NOW()) 
		AND YEAR(i.pht_start_datetime) = YEAR(NOW()) 
		ORDER BY i.pht_start_datetime");
	$i = 0;
	while ($month_row = mysqli_fetch_array($month_res)) {
		$sec_resources_mon = array();
		$secondary_res = mysqli_query($ch_conn, "SELECT CONCAT(u.first_name, ' ', u.last_name) AS name FROM activity_sec_resources asr, users u WHERE asr.user_id = u.user_id AND asr.item_id = " . $month_row['item_id']);
		while ($secondary_row = mysqli_fetch_array($secondary_res)) {
			$sec_resources_mon[] = $secondary_row['name']; 
		}
		$sr_mon = implode('; ', $sec_resources_mon);
		$month[$i]['id'] = $month_row['item_id'];
		$month[$i]['uploader'] = $month_row['name'];
		$month[$i]['upload_date'] = $month_row['upload_date'];
		$month[$i]['chg_id'] = $month_row['change_ticket_id'];
		$month[$i]['chg_type'] = $month_row['change_type'];
		$month[$i]['desc'] = $month_row['description'];
		$month[$i]['sid'] = $month_row['sys_id'];
		$month[$i]['server'] = $month_row['server'];
		$month[$i]['resources'] = $month_row['primary_res'];
		$month[$i]['sec_resources'] = $sr_mon;
		if ($month_row['actions'] == 'Execute Change')
			$add_action = " - " . mysqli_fetch_assoc(mysqli_query($ch_conn, "SELECT a.activity_name FROM activity a, items i WHERE i.activity_id = a.activity_id AND i.item_id = " . $month_row['item_id']))['activity_name'];
		else
			$add_action = "";
		$month[$i]['actions'] = $month_row['actions'] . $add_action;
		$month[$i]['ph_sd'] = $month_row['pht_start_datetime'];
		$month[$i]['ph_ed'] = $month_row['pht_end_datetime'];
		$month[$i]['reference'] = $month_row['reference'];
		$i++;
	}

?>
<div role="tabpanel" class="account_details_div_tab tab-pane" id="mont">
	<div class="account_details_dropdown_div">
		<select name="sort_by_month" id="sort_by_month" onchange="switchMonth()">
			<option value=""> -- Sort by Month -- </option>
			<?php
				for ($m = 1; $m <= 12; $m++) {
					echo "<option value=" . $m . "> " . date('F', strtotime('2017-' . $m . '-01')) . "</option>";
				}
			?>
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
		<tbody id="acct_month_tbody">
		<?php
			if (sizeof($month) == 0) {
				echo "<tr>";
				echo "<td colspan=15 style='font-size:1vw'> There are no change items for this month </td>";
				echo "</tr>";
			}
			else {
				for ($i = 0; $i < sizeof($month); $i++) {
					echo "<tr>";
					echo "<td width=5%>" . $month[$i]['uploader'] . "</td>";
					echo "<td width=5.5%>" . date("M d, Y <\b\\r> h:i A", strtotime($month[$i]['upload_date'])) . "</td>";
					echo "<td width=6%><a id='show_dets_link' onclick='showDetails(" . $month[$i]['id'] . ")'>" . $month[$i]['chg_id'] . "</a></td>";
					echo "<td width=5%>" . $month[$i]['chg_type'] . "</td>";
					echo "<td width=15%>" . $month[$i]['desc'] . "</td>";
					echo "<td id='word-wrap-td' width=5%>" . $month[$i]['sid'] . "</td>";
					echo "<td width=10%>" . $month[$i]['server'] . "</td>";
					echo "<td width=7.5%>" . $month[$i]['resources'] . "* <br><i>" . $month[$i]['sec_resources'] . "</i></td>";
					echo "<td width=7%>" . $month[$i]['actions'] . "</td>";
					echo "<td width=6.5%>" . date("M d, Y <\b\\r> h:i A", strtotime($month[$i]['ph_sd'])) . "</td>";
					echo "<td width=6.5%>" . date("M d, Y <\b\\r> h:i A", strtotime($month[$i]['ph_ed'])) . "</td>";
					echo "<td width=10%>" . $month[$i]['reference'] . "</td>";
					echo "<td width=5%><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(".$month[$i]['id'].")'>View Notes</a></td>";
					echo "</tr>";
				}
			}
		?>
		</tbody>
	</table>
</div> 

<script>
	function switchMonth() {
		var month = document.getElementById('sort_by_month').value;
		var a_id = '<?php echo $a_id; ?>';
		var tbody = document.getElementById('acct_month_tbody');
		
		var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

		document.getElementById('cur_month').innerHTML = "(" + months[month - 1] + ")";
		tbody.innerHTML = '';
		$.ajax({
			type: "POST",
			url: "acct_queries.php",
			data: {
				action: month,
				class_type: 'month',
				a_id: a_id
			},
			dataType: 'json'
		})
		.done(function(data) {
			console.log(data.length);
			//console.log(data.length);
			if (data.length == 0) {
				tbody.innerHTML += "<tr><td colspan=15 style='font-size:1vw'>There are no change items for this month</td></tr>";
			}
			else {
				for (var x = 0; x < data.length; x++) {
					//console.log(data);
					tbody.innerHTML += "<tr>" + "<td width=5%>" + data[x]['name'] + "</td>" 
					+ "<td width=5.5%>" + data[x]['up_date'] + "</td>"
					+ "<td width=6%><a id='show_dets_link' onclick='showDetails(" + data[x]['id'] + ")'>" + data[x]['chg_id'] + "</a></td>"
					+ "<td width=5%>" + data[x]['chg_type'] + "</td>"
					+ "<td width=15%>" + data[x]['chg_desc'] + "</td>"
					+ "<td id='word-wrap-td' width=5%>" + data[x]['sid'] + "</td>"
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

