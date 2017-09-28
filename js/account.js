var status_buffer = "";
var approved_buffer = false;
var edit_sec_resources = [];
var edit_flag = false;

// Convert time to 24hour format
function convertTo24(time_string) {
	var timestring_length = time_string.length;
	var indexOfColon = time_string.indexOf(":");
	var hr = time_string.substring(0, indexOfColon);
	var min = time_string.substring(indexOfColon + 1, indexOfColon + 3);
	var part = time_string.substring(timestring_length - 2, timestring_length);
	var hr_int = parseInt(hr);
	if (part == "pm") {
		if (hr_int == 12)
			hr = "12";
		else {
			hr_int = hr_int + 12;
			hr = hr_int.toString();
		}
	}
	else {
		if (hr.length == 1)
			hr = "0" + hr;
		else if (hr_int == 12)
			hr = "00";
	}
	var converted_time = hr + ":" + min + ":00";
	return converted_time;
}

function showDetails(id) {
	$('#show_ticket_details').modal('toggle');
	var action = 'show_details';
	$('#opened_note_id').val(id);

	$.ajax({
		type: "POST",
		url: "acct_queries.php",
		data: {
			//a_id: a_id,
			action: action,
			id: id
		},
		dataType: 'json'
	})
	.done(function(data) {
		//console.log(data);
		document.getElementById('det_chg_id').innerHTML = data['change_ticket_id'];
		document.getElementById('det_chg_type').innerHTML = data['change_type'];
		document.getElementById('det_chg_desc').innerHTML = data['description'];
		document.getElementById('det_account').innerHTML = data['account'];
		document.getElementById('det_sid').innerHTML = data['sys_id'];
		//console.log(data['server']);
		if (data['server'] == " ")
			document.getElementById('det_servers').innerHTML = "N/A";
		else {
			if (data['server'].length > 128) {
				var server_start = data['server'].substr(0, 128);
				var server_rest = data['server'].substr(128);
				document.getElementById('det_servers').innerHTML = server_start + "<span id='read-more_span' style='display: none'>" + server_rest + "</span><a id='more-less-btn' onclick='readMoreServers()'>&nbsp; ...Read More</a>";
				//document.getElementById('det_servers').innerHTML += "<br>" + server_rest;
			}
			else 
				document.getElementById('det_servers').innerHTML = data['server'];
		}
		document.getElementById('det_os').innerHTML = data['os'];
		document.getElementById('det_db').innerHTML = data['db'];
		document.getElementById('det_sp').innerHTML = data['sp'];
		document.getElementById('det_resources').innerHTML = data['resources'];
		document.getElementById('det_actions').innerHTML = data['actions'];
		if (data['pht_start_datetime'] == 'Dec 31, 2999 (12:00 AM)' && data['pht_end_datetime'] == 'Dec 31, 2999 (11:59 PM)') {
			document.getElementById('det_ph_time').innerHTML = 'No schedule yet: Tentatively planned for the future';
			document.getElementById('det_cu_time').innerHTML = 'No schedule yet: Tentatively planned for the future';
		}
		else {
			document.getElementById('det_ph_time').innerHTML = data['pht_start_datetime'] + " to " + data['pht_end_datetime'];
			document.getElementById('det_cu_time').innerHTML = data['customer_start_datetime'] + " to " + data['customer_end_datetime'] + " - " + data['customer_timezone'];
		}
		document.getElementById('det_reference').innerHTML = data['reference'];
		document.getElementById('det_status').innerHTML = data['status'];
		var note = _.unescape(data['note']);
		document.getElementById('det_notes').innerHTML = note;
		document.getElementById('det_approved').innerHTML = data['is_approved'];

		$('#opened_note_chg_id').val(data['change_ticket_id']);
		$('#opened_note_title').val(data['description']);
		$('#opened_note_acct_id').val(data['acct_id']);
		$('#opened_note_presource').val(data['primary_resource']);
		$('#opened_note_sresource').val(data['sec_ids']);
		$('#opened_note_timezone').val(data['customer_timezone']);
		$('#opened_note_ph_sdate').val(data['ph_sdate']);
		$('#opened_note_ph_edate').val(data['ph_edate']);
		$('#opened_note_cu_sdate').val(data['cu_sdate']);
		$('#opened_note_cu_edate').val(data['cu_edate']);

		/*
		if (data['can_edit'] == 1) {
			$('#edit_tr').css("visibility", "visible");
		}
		else {
			$('#edit_tr').css("visibility", "hidden");
		}
		*/

		if (data['kms_id'] != "N/A")
			$('#det_kms').html("<a href=http://eao-kms.phl.hp.com:8088/document.php?id='" + data['kms_id'] + "' target='_blank'> http://eao-kms.phl.hp.com:8088/document.php?id='" + data['kms_id'] + "'</a>");
		else
			$('#det_kms').html(data['kms_id']);

		if (data['os'] != '' && data['db'] != '' && data['sp'] != '')
			$('.tech_tr').css("display", "table-row");
		else
			$('.tech_tr').css("display", "none");
	});
}

$(document).ready(function() {
	$('#show_notes_link').on('click', function() {
		var id = $('#opened_note_id').val();
		var action = 'show_notes';
		var note_tbody = document.getElementById('notes_tbody');
		note_tbody.innerHTML = "";
		$.ajax({
			type: "POST",
			url: "acct_queries.php",
			data: {
				id: id,
				action: action
			},
			dataType: 'json'
		})
		.done(function(data) {
			for (var a = 0; a < data.length; a++) {
				note_tbody.innerHTML += "<tr>"
				+ "<td>" + data[a]['note_date'] + "</td>"
				+ "<td>" + _.unescape(data[a]['note_details']) + "</td>"
				+ "<td>" + data[a]['name'] + "</td>"
				+ "</tr>";
			} 
		});
	});

	$('#add-note_btn').on('click', function() {
		$('#add_ticket_notes').modal('toggle');
	});
});

function showNotes(id) {
	var note_tbody = document.getElementById('notes_tbody');
	note_tbody.innerHTML = "";
	$.ajax({
		type: "POST",
		url: "acct_queries.php",
		data: {
			id: id,
			action: 'show_notes'
		},
		dataType: 'json'
	})
	.done(function(data) {
		for (var a = 0; a < data.length; a++) {
			var note = _.unescape(data[a]['note_details']);
			note_tbody.innerHTML += "<tr>"
			+ "<td>" + data[a]['note_date'] + "</td>"
			+ "<td>" + note + "</td>"
			+ "<td>" + data[a]['name'] + "</td>"
			+ "</tr>";
		} 
	});
}

$(document).ready(function() {
	$('#header_acct-dropdown-div').hide();

	$('#header_acct-dropdown').on('click', function() {
		//if (!$('#header_acct-dropdown-div').is(":visible"))
			$('#header_acct-dropdown-div').slideToggle("fast");
	});

	$('.sec_res_dropdown2').on('click', function(event) {
		event.stopPropagation();
	});
	//$('.header_navbar_div').on('mouseleave', function() {
	//	if ($('#header_acct-dropdown-div').is(":visible"))
	//		setTimeout(function() {$('#header_acct-dropdown-div').slideToggle("fast")}, 200);
	//});

	$('#add-note_save').on('click', function() {
		var note = $('#add-note_textarea').val();
		var id = $('#opened_note_id').val();

		if (confirm("Save this note?")) {
			$.ajax({
				type: "POST",
				url: "acct_queries.php",
				data: {
					id: id,
					note: note,
					action: 'add_note'
				},
				dataType: 'json'
			})
			.done(function(data) {
				console.log(data);
				manual_close = false;
				if (data) {
					$('#add_ticket_notes').modal('toggle');
					$('#show_ticket_details').modal('toggle');

					if ($('#add_ticket_notes').hasClass('in') || $('#show_ticket_details').hasClass('in'))
						console.log("Still open");
					else {
						console.log("Closed");
						console.log($('#show_ticket_details'));
						$('#show_ticket_details').on('hidden.bs.modal', function() {
							if (!manual_close) {
								showDetails(id);
								manual_close = true;
							}
						});
					}
				}
			});
		}
	});

	$('#change-status_btn').on('click', function(event) {
		var status = $('#det_status').html();
		var id = $('#opened_note_id').val();

		if (status == 'Completed')
			event.preventDefault();
		else if (confirm($('#change-status_btn').html() + "?")) {
			$.ajax({
				type: "POST",
				url: "acct_queries.php",
				data: {
					action: "quick_status_change",
					status: status,
					id: id
				},
				dataType: 'json'
			})
			.done(function(data) {
				$('#show_ticket_details').modal('toggle');
				manual_close = false;

				$('#show_ticket_details').on('hidden.bs.modal', function() {
					if (!manual_close) {
						showDetails(id);
						manual_close = true;
					}
				});
			});
		}
	});

	$('#edit-change_btn').on('click', function() {
		edit_flag = true;
		// Text fields
		$('.show_details_table').each(function() {
			var id = $(this).children().attr('id');
			var value = $('#' + id).html();
			$('#' + id + '_td1').html('<input type="text" class="show_details_table_input" name="' + id + '_input" id="' + id + '_input" value="' + value + '" >');
		});

		var status = $('#det_status').html();
		status_buffer = status;
		document.getElementById('det_status_td').innerHTML = "<select name='edit-status_select' id='edit-status_select'>"
			+ "<option value='Open'> Open </option>"
			+ "<option value='In Progress'> In Progress </option>"
			+ "<option value='Completed'> Completed </option>"
			+ "<option value='Cancelled'> Cancelled </option>"
			+ "<option value='Failed'> Failed </option>"
			+ "<option value='Overdue'> Overdue </option>"
			+ "</select>";
		$('#edit-status_select').val(status);

		var approved = $('#det_approved').html();
		$('#det_approved').html("Approved and Ready for Implementation?&nbsp;&nbsp;");
		$('#det_approved').append($('<input>', {
			type: 'checkbox',
			id: 'edit-change_ready',
			name: 'edit-change_ready'
		}));
		if (approved == 'Yes') {
			$('#edit-change_ready').prop("checked", true);
			approved_buffer = true;
		}

		// Dropdowns
		/*
		var chg_type = $('#det_chg_type').html();
		$('#det_chg_type_td').html("<select class='show_details_table_input' name='det_chg_type_select' id='det_chg_type_select'></select>");
		for (var a = 0; a < change_types.length; a++) {
			$('#det_chg_type_select').append($('<option>', {
				value: change_types[a],
				text: change_types[a]
			}));
		}
		$('#det_chg_type_select').val(chg_type);

		$('#det_account_td').html("<select class='show_details_table_input' name='det_account_select' id='det_account_select'></select>");
		for (var b = 0; b < all_accounts.length; b++) {
			$('#det_account_select').append($('<option>', {
				value: all_accounts[b]['acct_id'],
				text: all_accounts[b]['acct_abbrev'] + " - " + all_accounts[b]['acct_name']
			}));
		}
		$('#det_account_select').val($('#opened_note_acct_id').val());
		*/
		$.ajax({
			type: "POST",
			url: "acct_queries.php",
			data: {
				id: $('#opened_note_id').val(),
				action: 'retrieve_team'
			},
			dataType: 'json'
		})
		.done(function(data) {
			$('#det_resources_td').html("<select class='show_details_table_input' name='det_presource_select' id='det_presource_select'></select>");
			for (var c = 0; c < resources[data].length; c++) {
				$('#det_presource_select').append($('<option>', {
					value: resources[data][c]['user_id'],
					text: resources[data][c]['name']
				}));
			} 
			$('#det_presource_select').val($('#opened_note_presource').val());

			$('#det_resources_td2').html('<button name="secondary_res2" id="secondary_res2" onclick="toggleSecResDropdown()"> <span class="create_modal_dropdown_text" id="edit-sr_dropdown_text"> -- Select Secondary Resource(s) -- </span><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span></button>');
			$('#det_resources_td2').append('<div class="sec_res_dropdown2"><ul></ul></div>');
			$('.sec_res_dropdown2').hide();
			var secondary_resource_ids = $('#opened_note_sresource').val().split(';');
			edit_sec_resources = secondary_resource_ids;
			for (var c = 0; c < resources[data].length; c++) {
				if (resources[data][c]['user_id'] == $('#det_presource_select').val())
					continue;

				$('.sec_res_dropdown2 ul').append("<li><input type='checkbox' class='sec_res_chkbox' id='edit-sec_res_chkbox' value=" + resources[data][c]['user_id'] + " onchange='edit_checkBoxes_resources()'>"  + resources[data][c]['name'] + " </li>");
				if (secondary_resource_ids.indexOf(resources[data][c]['user_id']) > -1)
					$('.sec_res_chkbox[value=' + resources[data][c]['user_id'] + ']').prop("checked", true);
					edit_checkBoxes_resources();
			}
		});


		$('#det_ph_time_td').html("<input type='text' class='datepicker-here' id='edit-datepicker1' data-language='en' name='ph_sdate' placeholder='MM/DD/YYYY' disabled/> <input type='text' name='ph_stime' id='edit-timepicker1' placeholder='HH:SS' disabled /> &nbsp;");
		$('#det_ph_time_td2').html("<input type='text' class='datepicker-here' id='edit-datepicker2' data-language='en' name='ph_edate' placeholder='MM/DD/YYYY' disabled/> <input type='text' name='ph_etime' id='edit-timepicker2' placeholder='HH:SS' disabled />");
		//$('#edit-datepicker1').datepicker();
		$('#edit-timepicker1').timepicker();
		//$('#edit-datepicker2').datepicker();
		$('#edit-timepicker2').timepicker();

		$('#det_cu_time_td').html("<input type='text' class='datepicker-here' id='edit-datepicker3' data-language='en' name='cu_sdate' placeholder='Start Date' onkeyup='editTime()'/> <input type='text' name='cu_stime' id='edit-timepicker3' placeholder='Start Time' onchange='editTime()' /> &nbsp;");
		$('#det_cu_time_td2').html("<input type='text' class='datepicker-here' id='edit-datepicker4' data-language='en' name='cu_edate' placeholder='End Date' onchange='editTime()'/> <input type='text' name='cu_etime' id='edit-timepicker4' placeholder='End Time' onchange='editTime()' />");
		$('#det_cu_time_td').append("<br><br><select name='edit-cust_timezone' id='edit-cust_timezone' onchange='editTime()'></select>");
		$('#det_cu_time_td2').append("<br><br>Pipeline (No schedule yet) &nbsp; <input type='checkbox' id='edit-pipeline' onchange='editPipeline()'>");
		for (var d = 0; d < timezones.length; d++) {
			$('#edit-cust_timezone').append($('<option>', {
				value: timezones[d]['tz_name'],
				id: timezones[d]['tz-offset'],
				text: timezones[d]['tz_name']
			}));
		}
		$('#edit-cust_timezone').val($('#opened_note_timezone').val());
		$('#edit-datepicker1').val(moment($('#opened_note_ph_sdate').val()).utcOffset('+0800').format('MM/DD/YYYY'));
		$('#edit-timepicker1').val(moment($('#opened_note_ph_sdate').val()).utcOffset('+0800').format('hh:mma'));
		$('#edit-datepicker2').val(moment($('#opened_note_ph_edate').val()).utcOffset('+0800').format('MM/DD/YYYY'));
		$('#edit-timepicker2').val(moment($('#opened_note_ph_edate').val()).utcOffset('+0800').format('hh:mma'));
		$('#edit-datepicker3').val(moment($('#opened_note_cu_sdate').val()).utcOffset('+0800').format('MM/DD/YYYY'));
		$('#edit-timepicker3').val(moment($('#opened_note_cu_sdate').val()).utcOffset('+0800').format('hh:mma'));
		$('#edit-datepicker4').val(moment($('#opened_note_cu_edate').val()).utcOffset('+0800').format('MM/DD/YYYY'));
		$('#edit-timepicker4').val(moment($('#opened_note_cu_edate').val()).utcOffset('+0800').format('hh:mma'));

		$('#edit-datepicker3').datepicker({
			onSelect: function(formattedDate, date, inst) {
				editTime();
			}
		});
		$('#edit-timepicker3').timepicker();
		$('#edit-datepicker4').datepicker({
			onSelect: function(formattedDate, date, inst) {
				editTime();
			}
		});
		$('#edit-timepicker4').timepicker();

		if ($('#edit-datepicker3').val() == '12/31/2999') {
			$('#edit-pipeline').prop("checked", true);
			$('#edit-datepicker3').prop("disabled", true);
			$('#edit-timepicker3').prop("disabled", true);
			$('#edit-datepicker4').prop("disabled", true);
			$('#edit-timepicker4').prop("disabled", true);
			$('#edit-cust_timezone').prop('disabled', true);
		}
		$('#edit-change_btn').parent().css("display", "none");
		$('#save-change_btn').parent().css("display", "table-cell");
		$('#choose-status_btn').parent().css("display", "none");
		$('#cancel-changes_btn').parent().css("display", "table-cell");
	});

	$('#save-change_btn').on('click', function() {
		var id = $('#opened_note_id').val();
		var chg_id = $('#det_chg_id_input').val();
		var title = $('#det_chg_desc_input').val();
		var primary_res = $('#det_presource_select').val();
		var status = $('#edit-status_select').val();
		if ($('#edit-change_ready').is(":checked"))
			var is_approved = 1;
		else
			var is_approved = 0;

		var time1 = convertTo24($('#edit-timepicker1').val());
		var time2 = convertTo24($('#edit-timepicker2').val());
		var time3 = convertTo24($('#edit-timepicker3').val());
		var time4 = convertTo24($('#edit-timepicker4').val());

		var date1 = $('#edit-datepicker1').val().replace(/(..).(..).(....)/, "$3-$1-$2");
		var date2 = $('#edit-datepicker2').val().replace(/(..).(..).(....)/, "$3-$1-$2");
		var date3 = $('#edit-datepicker3').val().replace(/(..).(..).(....)/, "$3-$1-$2");
		var date4 = $('#edit-datepicker4').val().replace(/(..).(..).(....)/, "$3-$1-$2");

		var ph_start = date1 + ' ' + time1;
		var ph_end = date2 + ' ' + time2;
		var cu_start = date3 + ' ' + time3;
		var cu_end = date4 + ' ' + time4;
		var timezone = $('#edit-cust_timezone').val();

		if (chg_id != '' && title != '' && moment(ph_start).isBefore(ph_end) && confirm("Save these changes? Double-check if all details are correct.")) {
			$.ajax({
				type: "POST",
				url: "acct_queries.php",
				data: {
					action: 'save_changes',
					id: id,
					chg_id: chg_id,
					title: title,
					primary_res: primary_res,
					sec_res: edit_sec_resources,
					date1: ph_start,
					date2: ph_end,
					date3: cu_start,
					date4: cu_end,
					timezone: timezone,
					status: status,
					is_approved: is_approved
				}
			})
			.done(function() {
				alert("Changes saved!");
				window.location.href = window.location.href;
			}); 
		}
		else if (chg_id == '' || title == '') {
			alert("Neither Change ID nor Title/Description fields may be blank!");
		}
		else if (!moment(ph_start).isBefore(ph_end)) {
			alert("Invalid date and time! Start date and time MUST BE BEFORE end date and time!");
		}

	});

	$('#choose-status_btn').on('click', function() {
		status_buffer = $('#det_status').html();
		document.getElementById('det_status').innerHTML = "<select name='choose-status_select' id='choose-status_select' onchange='chooseStatus()'>"
			+ "<option value='Open'> Open </option>"
			+ "<option value='In Progress'> In Progress </option>"
			+ "<option value='Completed'> Completed </option>"
			+ "<option value='Cancelled'> Cancelled </option>"
			+ "<option value='Failed'> Failed </option>"
			+ "</select>";
		$('#choose-status_select').val(status_buffer);
		$('#choose-status_btn').parent().css("display", "none");
		$('#cancel-changes_btn').parent().css("display", "table-cell");
	});

	$('#cancel-changes_btn').on('click', function() {
		if (confirm("Are you sure you wish to discard edited changes?"))
			resetModal();
	});

	$('#delete_btn').on('click', function() {
		var id = $('#opened_note_id').val();

		if (confirm("Delete this item?")) {
			$.ajax({
				type: "POST",
				url: "acct_queries.php",
				data: {
					action: 'delete_item',
					id: id
				}
			})
			.done(function() {
				alert("Deleted");
				window.location.href = '../delta-test/';
			});
		}
		else console.log(id);
	});

	$('#show_ticket_details').on('hide.bs.modal', function(event) {
		if (edit_flag)
			if (!confirm("Are you sure you wish to discard edited changes?")) {
				event.preventDefault();
				event.stopImmediatePropagation();
				return false;
			}
			else {
				resetModal();
			}
	});

	$('body').on('click', function() {
		$('#header_user-dropdown-div').hide();
		$('#menu-dropdown-div').hide();
	});

	$('#header_user-dropdown-div').hide();
	$('#menu-dropdown-div').hide();

	$('#user-dropdown').on('click', function(event) {
		$('#header_user-dropdown-div').toggle();
		$('#menu-dropdown-div').hide();
		event.stopPropagation();
	});

	$('#menu-dropdown').on('click', function(event) {
		$('#menu-dropdown-div').toggle();
		$('#header_user-dropdown-div').hide();
		event.stopPropagation();
	});

	$('#header_user-dropdown-div').on('click', function(event) {
		event.stopPropagation();
	});

	$('#menu-dropdown-div').on('click', function(event) {
		event.stopPropagation();
	});

	$('#header-sidebar-btnlink').on('click', function() {
		$('.sidebar-div-container').css("display", "block");
		$('.sidebar-div').animate({"margin-left": '+=22.5%'});
	});

	$('#header-sidebar-btnlink-open').on('click', function() {
		$('.sidebar-div').animate({"margin-left": '-=22.5%'});
		setTimeout(function(){
			$('.sidebar-div-container').css("display", "none");
		}, 500);
	});

	$('#new-item_link').on('click', function() {
		$('.sidebar-div').animate({"margin-left": '-=22.5%'});
		setTimeout(function(){
			$('.sidebar-div-container').css("display", "none");
		}, 500);
		$('#new_item').modal('toggle');
	});

	$('#my-accounts_link').on('click', function() {
		$('.sidebar-div').animate({"margin-left": '-=22.5%'});
		setTimeout(function(){
			$('.sidebar-div-container').css("display", "none");
		}, 500);
		$('#my_accounts').modal('toggle');
	});
});

function toggleSecResDropdown() {
	$('.sec_res_dropdown2').slideToggle('fast');
	//return false;
}

function chooseStatus() {
	var status = $('#choose-status_select').val();
	var id = $('#opened_note_id').val();

	if (status != status_buffer && confirm("Set change status to " + status + "?")) {
		$.ajax({
			type: "POST",
			url: "acct_queries.php",
			data: {
				action: "change_status",
				id: id,
				status: status
			},
			dataType: 'json'
		})
		.done(function(data) {
			if (data == 1) {
				alert("Status successfully changed!");
				$('#show_ticket_details').modal('toggle');
				manual_close = false;

				$('#show_ticket_details').on('hidden.bs.modal', function() {
					if (!manual_close) {
						showDetails(id);
						manual_close = true;
					}
				});
			}
			else {
				alert("Error encountered. Status not changed.");
				document.getElementById('det_status').innerHTML = status_buffer;
			}
		});
	}
	else 
		document.getElementById('det_status').innerHTML = status_buffer;

}

function editPipeline() {
	if ($('#edit-pipeline').prop("checked")) {
		$('#edit-datepicker3').prop("disabled", true);
		$('#edit-timepicker3').prop("disabled", true);
		$('#edit-datepicker4').prop("disabled", true);
		$('#edit-timepicker4').prop("disabled", true);
		$('#edit-cust_timezone').prop('disabled', true);

		$('#edit-datepicker1').val('12/31/2999');
		$('#edit-timepicker1').val('12:00am');
		$('#edit-datepicker2').val('12/31/2999');
		$('#edit-timepicker2').val('11:59pm');
		$('#edit-datepicker3').val('12/31/2999');
		$('#edit-timepicker3').val('12:00am');
		$('#edit-datepicker4').val('12/31/2999');
		$('#edit-timepicker4').val('11:59pm');
		$('#edit-cust_timezone').val('UTC + 08:00 - Philippine Time (PHT)'); 	
	}
	else {
		$('#edit-datepicker3').prop("disabled", false);
		$('#edit-timepicker3').prop("disabled", false);
		$('#edit-datepicker4').prop("disabled", false);
		$('#edit-timepicker4').prop("disabled", false);
		$('#edit-cust_timezone').prop('disabled', false);

		$('#edit-cust_timezone').val($('#opened_note_timezone').val());
		$('#edit-datepicker1').val(moment($('#opened_note_ph_sdate').val()).utcOffset('+0800').format('MM/DD/YYYY'));
		$('#edit-timepicker1').val(moment($('#opened_note_ph_sdate').val()).utcOffset('+0800').format('hh:mma'));
		$('#edit-datepicker2').val(moment($('#opened_note_ph_edate').val()).utcOffset('+0800').format('MM/DD/YYYY'));
		$('#edit-timepicker2').val(moment($('#opened_note_ph_edate').val()).utcOffset('+0800').format('hh:mma'));
		$('#edit-datepicker3').val(moment($('#opened_note_cu_sdate').val()).utcOffset('+0800').format('MM/DD/YYYY'));
		$('#edit-timepicker3').val(moment($('#opened_note_cu_sdate').val()).utcOffset('+0800').format('hh:mma'));
		$('#edit-datepicker4').val(moment($('#opened_note_cu_edate').val()).utcOffset('+0800').format('MM/DD/YYYY'));
		$('#edit-timepicker4').val(moment($('#opened_note_cu_edate').val()).utcOffset('+0800').format('hh:mma'));
	}
}

function edit_checkBoxes_resources() {
	edit_sec_resources = [];
	document.getElementById('edit-sr_dropdown_text').innerHTML = "";
	$('#edit-sec_res_chkbox:checked').each(function() {
		edit_sec_resources.push($(this).val());
		document.getElementById('edit-sr_dropdown_text').innerHTML += $(this).parent().text();
	});
	if ($('#edit-sec_res_chkbox:checked').length == 0)
		document.getElementById('edit-sr_dropdown_text').innerHTML = " -- Select Secondary Resource(s) -- ";
	else if ($('#edit-sec_res_chkbox:checked').length > 1)
		document.getElementById('edit-sr_dropdown_text').innerHTML = edit_sec_resources.length + " selected";
}

function editTime() {
	var cust_date1 = $('#edit-datepicker3').val().replace(/(..).(..).(....)/, "$3-$1-$2");
	var cust_time1 = convertTo24($('#edit-timepicker3').val());
	var cust_date2 = $('#edit-datepicker4').val().replace(/(..).(..).(....)/, "$3-$1-$2");
	var cust_time2 = convertTo24($('#edit-timepicker4').val());

	var offset = $('#edit-cust_timezone').children(":selected").attr('id');
	var sign = offset.substring(0,1);
	var hrs = offset.substring(1,3);
	var min = offset.substring(3);
	var hrs_int = parseInt(hrs);
	if (min == '30')
		hrs_int += 1;
	if (sign == '+') {
		var pht_offset = 8 + (8 - hrs_int);
	}
	else {
		var pht_offset = 8 + (8 + hrs_int);
	}

	if (pht_offset < 10)
		var pht_offset_str = '0' + pht_offset.toString() + min;
	else
		var pht_offset_str = pht_offset.toString() + min;

	$('#edit-datepicker1').val(moment(cust_date1 + " " + cust_time1).utcOffset('+' + pht_offset_str).format('MM/DD/YYYY'));
	$('#edit-timepicker1').val(moment(cust_date1 + " " + cust_time1).utcOffset('+' + pht_offset_str).format('hh:mma'));
	$('#edit-datepicker2').val(moment(cust_date2 + " " + cust_time2).utcOffset('+' + pht_offset_str).format('MM/DD/YYYY'));
	$('#edit-timepicker2').val(moment(cust_date2 + " " + cust_time2).utcOffset('+' + pht_offset_str).format('hh:mma'));
}

function resetModal() {
	edit_flag = false;

	var chg_id = $('#opened_note_chg_id').val();
	var title = $('#opened_note_title').val();
	var sr = [];
	var secondary_resource_ids = $('#opened_note_sresource').val().split(';');

	//if (confirm("Are you sure you wish to discard changes?")) {
		$('#det_chg_id_td1').html("<span id='det_chg_id'>" + chg_id + "</span>");
		$('#det_chg_desc_td1').html("<span id='det_chg_desc'>" + title + "</span>");
		$('#det_resources_td').html("<span id='det_resources'></span>");
		$('#det_resources_td2').html("");

		$.ajax({
			type: "POST",
			url: "acct_queries.php",
			data: {
				id: $('#opened_note_id').val(),
				action: 'retrieve_team'
			},
			dataType: 'json'
		})
		.done(function(data) {
			for (var a = 0; a < resources[data].length; a++) {
				if (resources[data][a]['user_id'] == $('#opened_note_presource').val()) {
					var primary_res = resources[data][a]['name'];
				}
				if (secondary_resource_ids.indexOf(resources[data][a]['user_id']) > -1) {
					sr.push(resources[data][a]['name']);
				}
			}
			$('#det_resources').html(primary_res + "(Primary)<br>" + sr.join('; '));
		});

		if ($('#opened_note_ph_sdate').val() == '2999-12-31 00:00:00') {
			$('#det_ph_time_td').html("<span id='det_ph_time'>No schedule yet: Tentatively planned for the future</span>");
			$('#det_ph_time_td2').html("");
			$('#det_cu_time_td').html("<span id='det_cu_time'>No schedule yet: Tentatively planned for the future</span>");
			$('#det_cu_time_td2').html("");
		}
		else {
			var ph_sched = moment($('#opened_note_ph_sdate').val()).utcOffset('+0800').format('MMM DD, YYYY (hh:mmA)') + ' to ' + moment($('#opened_note_ph_edate').val()).utcOffset('+0800').format('MMM DD, YYYY (hh:mmA)');
			var cu_sched = moment($('#opened_note_cu_sdate').val()).utcOffset('+0800').format('MMM DD, YYYY (hh:mmA)') + ' to ' + moment($('#opened_note_cu_edate').val()).utcOffset('+0800').format('MMM DD, YYYY (hh:mmA)');
			cu_sched += ' - ' + $('#opened_note_timezone').val();
			$('#det_ph_time_td').html("<span id='det_ph_time'>" + ph_sched + "</span>");
			$('#det_ph_time_td2').html('');
			$('#det_cu_time_td').html("<span id='det_cu_time'>" + cu_sched + "</span>");
			$('#det_cu_time_td2').html('');
		}

		$('#det_status_td').html("<span id='det_status'>" + status_buffer + "</span>");
		if (approved_buffer)
			$('#det_approved').html("Yes");
		else
			$('#det_approved').html("No");

		$('#save-change_btn').parent().css('display', 'none');
		$('#edit-change_btn').parent().css('display', 'table-cell');
		$('#cancel-changes_btn').parent().css('display', 'none');
		$('#choose-status_btn').parent().css('display', 'table-cell');
	//}
}

function readMoreServers() {
	if ($('#read-more_span').css("display") == 'inline') {
		$('#read-more_span').css("display", "none");
		$('#more-less-btn').html('&nbsp; ...Read More');
	}
	else {
		$('#read-more_span').css("display", "inline");
		$('#more-less-btn').html('&nbsp; Read Less');
	}
}

function triggerHomeEvent(event) {
	$('#trigger_e').val(event);
	$('#trigger_event_form').submit();
}