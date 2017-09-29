var sec_resources = [];
var os = [];
var db = [];
var sp = [];
var check_kms_result = false;
var row_limit = 24;

// Initialize Time Pickers
$(function() {
	$('#sched_timepicker1').timepicker();
	$('#sched_timepicker2').timepicker();
	$('#sched_timepicker3').timepicker();
	$('#sched_timepicker4').timepicker();
});

// Secondary resources dropdown
function checkBoxes_resources() {
	sec_resources = [];
	document.getElementById('sr_dropdown_text').innerHTML = "";
	$('input[name="sec_resources[]"]:checked').each(function() {
		sec_resources.push($(this).val());
		document.getElementById('sr_dropdown_text').innerHTML += $(this).parent().text();
	});
	if ($('input[name="sec_resources[]"]:checked').length == 0)
		document.getElementById('sr_dropdown_text').innerHTML = " -- Select Secondary Resource(s) -- ";
	else if ($('input[name="sec_resources[]"]:checked').length > 1)
		document.getElementById('sr_dropdown_text').innerHTML = sec_resources.length + " selected";
}

// OS dropdown
function checkBoxes_os() {
	os = [];
	document.getElementById('os_dropdown_text').innerHTML = "";
	$('input[name="os[]"]:checked').each(function() {
		os.push($(this).val());
		document.getElementById('os_dropdown_text').innerHTML += $(this).parent().text() + ", ";
	});
	if ($('input[name="os[]"]:checked').length == 0)
		document.getElementById('os_dropdown_text').innerHTML = " -- Select Operating System(s) -- ";
}

// Databases dropdown
function checkBoxes_db() {
	db = [];
	document.getElementById('db_dropdown_text').innerHTML = "";
	$('input[name="db[]"]:checked').each(function() {
		db.push($(this).val());
		document.getElementById('db_dropdown_text').innerHTML += $(this).parent().text() + ", ";
	});
	if ($('input[name="db[]"]:checked').length == 0)
		document.getElementById('db_dropdown_text').innerHTML = " -- Select Database(s) -- ";
}

// SAP Products dropdown
function checkBoxes_sp() {
	sp = [];
	document.getElementById('sp_dropdown_text').innerHTML = "";
	$('input[name="sp[]"]:checked').each(function() {
		sp.push($(this).val());
		document.getElementById('sp_dropdown_text').innerHTML += $(this).parent().text() + ", ";
	});
	if ($('input[name="sp[]"]:checked').length == 0)
		document.getElementById('sp_dropdown_text').innerHTML = " -- Select SAP Product(s) -- ";
}

function checkKMS() {
	var doc_id = document.getElementById('kms_id').value;
	check_kms_result = false;
	if (doc_id != "") {
		$.ajax({
			type: "POST",
			url: "process.php",
			data: {
				action: 'check_kms',
				doc_id: doc_id
			},
			dataType: 'json'
		})
		.done(function(data) {
			if (data) {
				$('#kms_id').css("border", "1px solid #aaa");
				check_kms_result = true;
			}
			else {
				$('#kms_id').css("border", "2px solid red");
				//alert("Invalid KMS document ID! Please check if the document ID is correct and try again.");
				check_kms_result = false;
			}
			console.log(check_kms_result);
		});
	}
}

function removeName() {
	var val = $('#primary_res').val();
	$('.sec_res_chkbox').parent().each(function() {
		$(this).show();
	});
	$('.sec_res_chkbox[value=' + val + ']').parent().hide();
}

// Get data
function clickSaveBtn() {
	var kms_id = document.getElementById('kms_id').value;

	if ((kms_id != "" && check_kms_result) || kms_id == '') {
		var chg = new Array();
		chg[0] = document.getElementById('chg_ticket_id').value;
		chg[1] = document.getElementById('chg_type').value;
		chg[2] = document.getElementById('chg_desc').value;
		chg[3] = document.getElementById('account').value;
		chg[4] = document.getElementById('sids').value;
		chg[5] = document.getElementById('servers').value;
		chg[6] = document.getElementById('actions').value;
		chg[7] = document.getElementById('datepicker1').value;
		chg[8] = document.getElementById('sched_timepicker1').value;
		chg[9] = document.getElementById('datepicker2').value;
		chg[10] = document.getElementById('sched_timepicker2').value;
		chg[11] = document.getElementById('datepicker3').value;
		chg[12] = document.getElementById('sched_timepicker3').value;
		chg[13] = document.getElementById('datepicker4').value;
		chg[14] = document.getElementById('sched_timepicker4').value;
		chg[15] = document.getElementById('cust_timezone').value;
		chg[16] = document.getElementById('reference').value;
		chg[17] = document.getElementById('form_status_dropdown').value;
		chg[18] = tinymce.activeEditor.getContent();
		//chg[18] = document.getElementById('notes').value;
		chg[19] = document.getElementById('primary_res').value;
		chg[20] = kms_id;
		if ($('#change_ready').is(":checked"))
			var is_approved = 1;
		else
			var is_approved = 0;

		if (chg[6] == 'Execute Change')
			var chg_act = document.getElementById('activity_dropdown').value;
		else
			var chg_act = 0;

		var chg_defined = false;
		if ((chg[6] == 'Execute Change' && chg_act != "") || chg[6] != 'Execute Change')
			chg_defined = true;

		if (checkDate() && confirmComplete(chg) && chg_defined && confirmSubmit()) {
			var time1 = convertTo24(chg[8]);
			var time2 = convertTo24(chg[10]);
			var time3 = convertTo24(chg[12]);
			var time4 = convertTo24(chg[14]);

			var date1 = chg[7].replace(/(..).(..).(....)/, "$3-$1-$2");
			var date2 = chg[9].replace(/(..).(..).(....)/, "$3-$1-$2");
			var date3 = chg[11].replace(/(..).(..).(....)/, "$3-$1-$2");
			var date4 = chg[13].replace(/(..).(..).(....)/, "$3-$1-$2");

			$.ajax({
				type: "POST",
				url: "process.php",
				data: {
					action: "create_item",
					chg_id: chg[0],
					chg_type: chg[1],
					chg_desc: chg[2],
					acct: chg[3],
					sids: chg[4],
					servers: chg[5],
					chg_action: chg[6],
					chg_act: chg_act,
					date1: date1,
					time1: time1,
					date2: date2,
					time2: time2,
					date3: date3,
					time3: time3,
					date4: date4,
					time4: time4,
					timezone: chg[15],
					reference: chg[16],
					status: chg[17],
					notes: chg[18],
					primary_res: chg[19],
					sec_res: sec_resources,
					os: os,
					db: db,
					sp: sp,
					kms_id: chg[20],
					approved: is_approved
				}
			})
			.done(function(msg) {
				alert("New item added!");
				//window.location.href = "/delta/";
			});
		}
		else if (!chg_defined) {
			alert("You must choose what change activity is being done!");
			$('#activity_dropdown').addClass("required-field");
			setTimeout(function() {$('#activity_dropdown').removeClass("required-field");}, 1500);
		}
	}
	else if (kms_id != '' && !check_kms_result) {
		alert("Invalid KMS document ID! Please check if the document ID is correct and try again.");
	}
}

// Confirm complete
function confirmComplete(chg_ar) {
	var conf = true;
	for (var a = 0; a < chg_ar.length; a++) {
		console.log(chg_ar[a]);
		if (a == 4 || a == 5 || a == 20)
			continue;
		else {
			if (chg_ar[a] == "") {
				alert("Fields marked with a red asterisk are required!");
				conf = false;
				break;	
			}
		}
	}

	var num_os = $('input[name="os[]"]:checked').length;
	var num_db = $('input[name="db[]"]:checked').length;
	var num_sp = $('input[name="sp[]"]:checked').length;
	var num_sr = $('input[name="sec_resources[]"]:checked').length;

	if ((chg_ar[6] == 'Execute Change' && (num_os == 0 || num_db == 0 || num_sp == 0)) || num_sr == 0)
		conf = false;

	return conf;
}

// Confirm submit
function confirmSubmit() {
	return confirm("Save this change item to the tracker?");
}

// Check actions field
function checkAction() {
	var act = document.getElementById('actions').value;
	if (act == 'Execute Change') {
		$('#activity_dropdown').css("visibility", "visible");
		$('.db_os_sp_tr').css("display", "table-row");
	}
	else {
		$('#activity_dropdown').css("visibility", "hidden");
		if ($('#pipeline').is(':checked')) {
			document.getElementById('pipeline').checked = false;
			$('#pipeline').trigger("change");
		}
		$('.db_os_sp_tr').css("display", "none");
	}

	if (act == 'Import Transport') {
		$('.time_options_tr').css("display", "table-row");
	}
	else {
		if ($('#immediate').is(":checked")) {
			document.getElementById('immediate').checked = false;
			$('#immediate').trigger("change");
		}
		$('.time_options_tr').css("display", "none");
	}

}

function checkDate() {
	var start_d = $('#datepicker3').val().replace(/(..).(..).(....)/, "$3-$1-$2");
	var start_t = convertTo24($('#sched_timepicker3').val());
	var startstr = start_d + " " + start_t;
	var end_d = $('#datepicker4').val().replace(/(..).(..).(....)/, "$3-$1-$2");
	var end_t = convertTo24($('#sched_timepicker4').val());
	var endstr = end_d + " " + end_t;
	
	var conf = moment(startstr).isBefore(endstr);

	if (!conf) {
		alert("Invalid date! End date and time must be after Start date and time!");
		$('#datepicker4').addClass("required-field");
		$('#sched_timepicker4').addClass("required-field");
		setTimeout(function() {$('#datepicker4').removeClass("required-field");}, 1500);
		setTimeout(function() {$('#sched_timepicker4').removeClass("required-field");}, 1500);
	}

	return conf;
}

// Dropdown behavior
$(document).ready(function() {
	if (trigger_event == 'new_item') {
		setTimeout(function() {
			$('#new_item').modal('toggle');
		}, 500);
	}
	else if (trigger_event == 'my_accounts') {
		setTimeout(function() {
			$('#my_accounts').modal('toggle');
		}, 500);
	}
	else if (trigger_event == 'my_uploads') {
		setTimeout(function() {
			filterColumn(0);
		}, 500);
	}


	$('#datepicker3').datepicker({
		onSelect: function(formattedDate, date, inst) {
			convertTimezone();
		}
	});

	$('#datepicker4').datepicker({
		onSelect: function(formattedDate, date, inst) {
			convertTimezone();
		}
	});

	$('.sec_res_dropdown').hide();
	$('.os_dropdown').hide();
	$('.db_dropdown').hide();
	$('.sp_dropdown').hide();

	$('.modal-dialog').on('click', function() {
		$('.sec_res_dropdown').hide();
		$('.os_dropdown').hide();
			$('.db_dropdown').hide();
			$('.sp_dropdown').hide();
	});

	$('#secondary_res').on('click', function() {
		$('.sec_res_dropdown').slideToggle('fast');
		$('.os_dropdown').hide();
			$('.db_dropdown').hide();
			$('.sp_dropdown').hide();
		return false;
	});

	$('#os_dd').on('click', function() {
		$('.os_dropdown').slideToggle('fast');
		$('.sec_res_dropdown').hide();
			$('.db_dropdown').hide();
			$('.sp_dropdown').hide();
		return false;
	});

	$('#db_dd').on('click', function() {
		$('.db_dropdown').slideToggle('fast');
		$('.sec_res_dropdown').hide();
		$('.os_dropdown').hide();
			$('.sp_dropdown').hide();
		return false;
	});

	$('#sp_dd').on('click', function() {
		$('.sp_dropdown').slideToggle('fast');
		$('.sec_res_dropdown').hide();
		$('.os_dropdown').hide();
			$('.db_dropdown').hide();
		return false;
	});

	$('.sec_res_dropdown').on('click', function(event) {
		event.stopPropagation();
	});

	$('.os_dropdown').on('click', function(event) {
		event.stopPropagation();
	});

	$('.db_dropdown').on('click', function(event) {
		event.stopPropagation();
	});

	$('.sp_dropdown').on('click', function(event) {
		event.stopPropagation();
	});

	$('#my-uploads_link').on('click', function() {
		$('.loading').css("display", "block");
		filterColumn(0);
		$('#header_user-dropdown-div').hide();
	});

	tinymce.init({
		selector: '#notes',
		toolbar: false,
		menubar: false,
		statusbar: false,
		width: 600,
		content_style: 'p {line-height: 75%;}'
	});

	$('.change-list-th .filter-btn').on('click', function(event) {
		var id = $(this).parent().attr('id');
		if ($('#chg-list-th-' + id).css("display") != "none") {
			$('#chg-list-th-' + id).val('');
			$('.loading').css("display", "block");
			filterColumn(id);/*
			if (id == 1)
				$('#' + id + '-label').html("Change ID");
			else if (id == 2)
				$('#' + id + '-label').html("Account");
			else if (id == 4)
				$('#' + id + '-label').html("Title");
			else if (id == 7)*/
				$('#' + id + '-label').css("display", "inline");
			$('#chg-list-th-' + id).css("display", "none");
		}
		else {
			//$('#' + id + '-label').html("<input type='text' id='chg-list-th-" + id + "' onkeyup='filterColumn(" + id + ")' >");
			$('#' + id + "-label").css("display", "none");
			$('#chg-list-th-' + id).css("display", "inline");
			$('#chg-list-th-' + id).focus();
		}
	});

	$('.change-list-th .sort-btn').on('click', function() {
		var id = $(this).parent().attr('id');
		$('th', '#change-list-thead-tr').each(function() {
			if ($(this).attr('id') != id) {
				$(this).removeClass("sorted");
				$(this).removeClass("rev-sorted");
			}
		});

		if (!$(this).parent().hasClass("sorted") && !$(this).parent().hasClass("rev-sorted")) {
			$(this).parent().addClass("sorted");
		}
		else if ($(this).parent().hasClass("sorted") && !$(this).parent().hasClass("rev-sorted")) {
			$(this).parent().removeClass("sorted");
			$(this).parent().addClass("rev-sorted");
		}
		else {
			$(this).parent().removeClass("rev-sorted");
			$(this).parent().addClass("sorted");
		}

		sortColumn(id);

		if ($('#' + id + " .sort-btn").hasClass("glyphicon-sort-by-alphabet") && $(this).parent().hasClass("rev-sorted")) {
			$('#' + id + " .sort-btn").removeClass("glyphicon-sort-by-alphabet");
			$('#' + id + " .sort-btn").addClass("glyphicon-sort-by-alphabet-alt");
		}
		else if ($('#' + id + " .sort-btn").hasClass("glyphicon-sort-by-alphabet-alt")) {
			$('#' + id + " .sort-btn").removeClass("glyphicon-sort-by-alphabet-alt");
			$('#' + id + " .sort-btn").addClass("glyphicon-sort-by-alphabet");
		}

		if ($('#' + id + " .sort-btn").hasClass("glyphicon-sort-by-attributes") && $(this).parent().hasClass("rev-sorted")) {
			$('#' + id + " .sort-btn").removeClass("glyphicon-sort-by-attributes");
			$('#' + id + " .sort-btn").addClass("glyphicon-sort-by-attributes-alt");
		}
		else if ($('#' + id + " .sort-btn").hasClass("glyphicon-sort-by-attributes-alt")) {
			$('#' + id + " .sort-btn").removeClass("glyphicon-sort-by-attributes-alt");
			$('#' + id + " .sort-btn").addClass("glyphicon-sort-by-attributes");
		}
	});

	$('#immediate').change(function() {
		if (this.checked) {
			$('#cust_timezone').val("UTC + 08:00 - Philippine Time (PHT)");
			$('#cust_timezone').attr("disabled", true);
			$('#datepicker3').attr("disabled", true);
			$('#sched_timepicker3').attr("disabled", true);
			$('#datepicker4').attr("disabled", true);
			$('#sched_timepicker4').attr("disabled", true);
			var start_d = moment().utcOffset('+0800').format('MM/DD/YYYY');
			var start_t = moment().utcOffset('+0800').format('hh:mma');
			var end_d = moment().utcOffset('+0830').format('MM/DD/YYYY');
			var end_t = moment().utcOffset('+0830').format('hh:mma');

			$('#datepicker1').val(start_d);
			$('#sched_timepicker1').val(start_t);
			$('#datepicker3').val(start_d);
			$('#sched_timepicker3').val(start_t);
			$('#datepicker2').val(end_d);
			$('#sched_timepicker2').val(end_t);
			$('#datepicker4').val(end_d);
			$('#sched_timepicker4').val(end_t);

			$('.ph_sched_tr').css("display", "none");
		}
		else {
			$('#cust_timezone').val("");
			$('#datepicker1').val('');
			$('#sched_timepicker1').val('');
			$('#datepicker3').val('');
			$('#sched_timepicker3').val('');
			$('#datepicker2').val('');
			$('#sched_timepicker2').val('');
			$('#datepicker4').val('');
			$('#sched_timepicker4').val('');

			$('#cust_timezone').attr("disabled", false);
			$('#datepicker3').attr("disabled", false);
			$('#sched_timepicker3').attr("disabled", false);
			$('#datepicker4').attr("disabled", false);
			$('#sched_timepicker4').attr("disabled", false);

			$('.ph_sched_tr').css("display", "table-row");
		}
	});

	$('#pipeline').change(function() {
		if (this.checked) {
			$('#cust_timezone').val("UTC + 08:00 - Philippine Time (PHT)");
			$('#cust_timezone').attr("disabled", true);
			$('#datepicker3').attr("disabled", true);
			$('#sched_timepicker3').attr("disabled", true);
			$('#datepicker4').attr("disabled", true);
			$('#sched_timepicker4').attr("disabled", true);

			var start_d = '12/31/2999';
			var start_t = '12:00am';
			var end_d = '12/31/2999';
			var end_t = '11:59pm';

			$('#datepicker1').val(start_d);
			$('#sched_timepicker1').val(start_t);
			$('#datepicker3').val(start_d);
			$('#sched_timepicker3').val(start_t);
			$('#datepicker2').val(end_d);
			$('#sched_timepicker2').val(end_t);
			$('#datepicker4').val(end_d);
			$('#sched_timepicker4').val(end_t);

			$('.ph_sched_tr').css("display", "none");
			$('.cust_sched_tr').css("display", "none");
		}
		else {
			$('#cust_timezone').val("");
			$('#datepicker1').val('');
			$('#sched_timepicker1').val('');
			$('#datepicker3').val('');
			$('#sched_timepicker3').val('');
			$('#datepicker2').val('');
			$('#sched_timepicker2').val('');
			$('#datepicker4').val('');
			$('#sched_timepicker4').val('');

			$('#cust_timezone').attr("disabled", false);
			$('#datepicker3').attr("disabled", false);
			$('#sched_timepicker3').attr("disabled", false);
			$('#datepicker4').attr("disabled", false);
			$('#sched_timepicker4').attr("disabled", false);

			$('.ph_sched_tr').css("display", "table-row");
			$('.cust_sched_tr').css("display", "table-row");
		}
	});

	$('.change-list-view-status-btn').on('click', function() {
		var id = $(this).attr('id');
		if (id == 'chg-view-all')
			var stat = '';
		else if (id == 'chg-view-inpr')
			var stat = 'In Progress';
		else if (id == 'chg-view-comp')
			var stat = 'Completed';
		else if (id == 'chg-view-overdue')
			var stat = 'Overdue';

		$('#chg-list-th-9').val(stat);

		$('.loading').css("display", "block");
		filterColumn(9);
	});

	$('.change-list-view-date-btn').on('click', function() {
		var id = $(this).attr('id');
		row_limit = 24;
		if (id == 'chg-view-month')
			var date_filter = 'filter_month';
		else if (id == 'chg-view-week')
			var date_filter = 'filter_week';
		else if (id == 'chg-view-day')
			var date_filter = 'filter_day';
		else if (id == 'chg-view-pipeline')
			var date_filter = 'filter_pipeline';

		$('.loading').css("display", "block");

		setTimeout(function() {
			$.ajax({
				type: "POST",
				url: "process.php",
				data: {
					action: date_filter
				},
				dataType: 'json'
			})
			.done(function(data) {
				changes = data;
				$('#change-list-tbody').html("");
				if (changes.length == 0) {
					$('#change-list_showlabel').html("");
					document.getElementById('change-list-tbody').innerHTML += "<tr><td colspan=8> No change items found </td></tr>";
				}
				else {
					for (var x = 0; x < changes.length; x++) {
						$('#change-list_showlabel').html("Showing " + (x + 1) + " of " + changes.length);
						if (changes[x]['status'] == 'In Progress')
							var stat_hl = "status-inprogress";
						else if (changes[x]['status'] == 'Completed')
							var stat_hl = "status-completed";
						else if (changes[x]['status'] == 'Failed')
							var stat_hl = "status-failed";
						else if (changes[x]['status'] == 'Overdue')
							var stat_hl = "status-overdue";
						else 
							var stat_hl = "";
						document.getElementById('change-list-tbody').innerHTML += "<tr>"
						+ "<td width=8.5% id='chg_list-id'><a onclick='showDetails(" + changes[x]['item_id'] + ")'>" + changes[x]['change_ticket_id'] + "</a></td>"
						+ "<td width=6.5%>" + changes[x]['team_name'] + "</td>"
						+ "<td width=6% id='chg_list-aa'>" + changes[x]['acct_abbrev'] + "</td>"
						+ "<td width=8% id='chg_list-an'>" + changes[x]['acct_name'] + "</td>"
								//echo "<td width=25%>" . $changes[$x]['actions'] . "</td>";
						+ "<td width=24.75% id='chg_list-cd'>" + changes[x]['description'] + "</td>"
						+ "<td width=13.5% id='chg_list-res'>" + changes[x]['name'] + "</td>"
						+ "<td width=10.25% id='chg_list-st'>" + changes[x]['pht_start_datetime'] + "</td>"
						+ "<td width=10.25% id='chg_list-et'>" + changes[x]['pht_end_datetime'] + "</td>"
						+ "<td class='" + stat_hl + "' id='chg_list-status'>" + changes[x]['status'] 
						+ "<br><br><i><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(" + changes[x]['item_id'] + ")'>View Notes</a></i></td>"
						+ "</tr>";
						if (x == row_limit) {
							var next_row = x + 1;
							document.getElementById('change-list-tbody').innerHTML += "<tr>"
							+ "<td colspan=9 id='show-more_row'> <a onclick='showMoreChanges(" + next_row + ")'>Show more</a></td>"
							+ "</tr>";
							break;
						}
					}
				}
				$('.loading').css("display", "none");
			});
		}, 750);
	});

	$('#new_item').on('show.bs.modal', function() {

	});
});

function sortColumn(id) {
	$('.loading').css("display", "block");
	$('#change-list-tbody').html("");
	if (id == 7 || id == 8) {
		changes.sort(function(a,b) {
			var date_a = moment(a[id], 'MMM DD, YYYY - hh:mmA');
			var date_b = moment(b[id], 'MMM DD, YYYY - hh:mmA');
			
			if (moment(date_a).isSame(date_b))
				return 0;
			if (moment(date_a).isAfter(date_b))
				return 1;
			if (moment(date_a).isBefore(date_b))
				return -1;
		});
	}
	else {
		changes.sort(function(a,b) {
			if (a[id] == b[id])
				return 0;
			if (a[id] > b[id])
				return 1;
			if (a[id] < b[id])
				return -1;
		});
	}
	if ($('#' + id).hasClass("rev-sorted")) {
		changes.reverse();
	}
	
	for (var x = 0; x < changes.length; x++) {
		$('#change-list_showlabel').html("Showing " + (x + 1) + " of " + changes.length);
		if (changes[x]['status'] == 'In Progress')
			var stat_hl = "status-inprogress";
		else if (changes[x]['status'] == 'Completed')
			var stat_hl = "status-completed";
		else if (changes[x]['status'] == 'Failed')
			var stat_hl = "status-failed";
		else if (changes[x]['status'] == 'Overdue')
			var stat_hl = "status-overdue";
		else 
			var stat_hl = "";
		document.getElementById('change-list-tbody').innerHTML += "<tr>"
		+ "<td width=8.5% id='chg_list-id'><a onclick='showDetails(" + changes[x]['item_id'] + ")'>" + changes[x]['change_ticket_id'] + "</a></td>"
		+ "<td width=6.5%>" + changes[x]['team_name'] + "</td>"
		+ "<td width=6% id='chg_list-aa'>" + changes[x]['acct_abbrev'] + "</td>"
		+ "<td width=8% id='chg_list-an'>" + changes[x]['acct_name'] + "</td>"
				//echo "<td width=25%>" . $changes[$x]['actions'] . "</td>";
		+ "<td width=24.75% id='chg_list-cd'>" + changes[x]['description'] + "</td>"
		+ "<td width=13.5% id='chg_list-res'>" + changes[x]['name'] + "</td>"
		+ "<td width=10.25% id='chg_list-st'>" + changes[x]['pht_start_datetime'] + "</td>"
		+ "<td width=10.25% id='chg_list-et'>" + changes[x]['pht_end_datetime'] + "</td>"
		+ "<td class='" + stat_hl + "' id='chg_list-status'>" + changes[x]['status'] 
		+ "<br><br><i><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(" + changes[x]['item_id'] + ")'>View Notes</a></i></td>"
		+ "</tr>";
		if (x == row_limit) {
			var next_row = x + 1;
			document.getElementById('change-list-tbody').innerHTML += "<tr>"
			+ "<td colspan=9 id='show-more_row'> <a onclick='showMoreChanges(" + next_row + ")'>Show more</a></td>"
			+ "</tr>";
			break;
		}
	}
	$('.loading').css("display", "none");
}

function filterColumn(id) {
	var text = $('#chg-list-th-' + id).val();
	row_limit = 24;

	if (id == 0)
		var action = 'filter_uploader';
	else if (id == 1)
		var action = 'filter_id';
	else if (id == 3)
		var action = 'filter_acct';
	else if (id == 5)
		var action = 'filter_title';
	else if (id == 9)
		var action = 'filter_status';

	setTimeout(function() {
		$.ajax({
			type: "POST",
			url: "process.php",
			data: {
				text: text,
				action: action
			},
			dataType: 'json'
		})
		.done(function(data) {
			changes = data;
			$('#change-list-tbody').html("");
			if (changes.length == 0) {
				$('#change-list_showlabel').html("");
				document.getElementById('change-list-tbody').innerHTML += "<tr><td colspan=8> No change items found </td></tr>";
			}
			else {
				for (var x = 0; x < changes.length; x++) {
					$('#change-list_showlabel').html("Showing " + (x + 1) + " of " + changes.length);
					if (changes[x]['status'] == 'In Progress')
						var stat_hl = "status-inprogress";
					else if (changes[x]['status'] == 'Completed')
						var stat_hl = "status-completed";
					else if (changes[x]['status'] == 'Failed')
						var stat_hl = "status-failed";
					else if (changes[x]['status'] == 'Overdue')
						var stat_hl = "status-overdue";
					else 
						var stat_hl = "";
					document.getElementById('change-list-tbody').innerHTML += "<tr>"
					+ "<td width=8.5% id='chg_list-id'><a onclick='showDetails(" + changes[x]['item_id'] + ")'>" + changes[x]['change_ticket_id'] + "</a></td>"
					+ "<td width=6.5%>" + changes[x]['team_name'] + "</td>"
					+ "<td width=6% id='chg_list-aa'>" + changes[x]['acct_abbrev'] + "</td>"
					+ "<td width=8% id='chg_list-an'>" + changes[x]['acct_name'] + "</td>"
							//echo "<td width=25%>" . $changes[$x]['actions'] . "</td>";
					+ "<td width=24.75% id='chg_list-cd'>" + changes[x]['description'] + "</td>"
					+ "<td width=13.5% id='chg_list-res'>" + changes[x]['name'] + "</td>"
					+ "<td width=10.25% id='chg_list-st'>" + changes[x]['pht_start_datetime'] + "</td>"
					+ "<td width=10.25% id='chg_list-et'>" + changes[x]['pht_end_datetime'] + "</td>"
					+ "<td class='" + stat_hl + "' id='chg_list-status'>" + changes[x]['status'] 
					+ "<br><br><i><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(" + changes[x]['item_id'] + ")'>View Notes</a></i></td>"
					+ "</tr>";
					if (x == row_limit) {
						var next_row = x + 1;
						document.getElementById('change-list-tbody').innerHTML += "<tr>"
						+ "<td colspan=9 id='show-more_row'> <a onclick='showMoreChanges(" + next_row + ")'>Show more</a></td>"
						+ "</tr>";
						break;
					}
				}
			}
			$('.loading').css("display", "none");
		});
	}, 1000);
}

function convertTimezone() {
	var cust_date1 = $('#datepicker3').val().replace(/(..).(..).(....)/, "$3-$1-$2");
	var cust_time1 = convertTo24($('#sched_timepicker3').val());
	var cust_date2 = $('#datepicker4').val().replace(/(..).(..).(....)/, "$3-$1-$2");
	var cust_time2 = convertTo24($('#sched_timepicker4').val());

	var offset = $('#cust_timezone').children(":selected").attr('id');
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

	$('#datepicker1').val(moment(cust_date1 + " " + cust_time1).utcOffset('+' + pht_offset_str).format('MM/DD/YYYY'));
	$('#sched_timepicker1').val(moment(cust_date1 + " " + cust_time1).utcOffset('+' + pht_offset_str).format('hh:mma'));
	$('#datepicker2').val(moment(cust_date2 + " " + cust_time2).utcOffset('+' + pht_offset_str).format('MM/DD/YYYY'));
	$('#sched_timepicker2').val(moment(cust_date2 + " " + cust_time2).utcOffset('+' + pht_offset_str).format('hh:mma'));
}

function showMoreChanges(row_num) {
	var row_limit = row_num + 24;
	if (row_limit - changes.length <= 0)
		var loop_limit = row_limit;
	else 
		var loop_limit = changes.length - 1;

	$('#show-more_row').remove();
	for (var x = row_num; x <= loop_limit; x++) {
		$('#change-list_showlabel').html("Showing " + (x + 1) + " of " + changes.length);
		console.log(x + " " + changes[x]);
		if (changes[x]['status'] == 'In Progress')
			var stat_hl = "status-inprogress";
		else if (changes[x]['status'] == 'Completed')
			var stat_hl = "status-completed";
		else if (changes[x]['status'] == 'Failed')
			var stat_hl = "status-failed";
		else if (changes[x]['status'] == 'Overdue')
			var stat_hl = "status-overdue";
		else 
			var stat_hl = "";
		document.getElementById('change-list-tbody').innerHTML += "<tr>"
		+ "<td width=8.25% id='chg_list-id'><a onclick='showDetails(" + changes[x]['item_id'] + ")'>" + changes[x]['change_ticket_id'] + "</a></td>"
		+ "<td width=6.5%>" + changes[x]['team_name'] + "</td>"
		+ "<td width=6% id='chg_list-aa'>" + changes[x]['acct_abbrev'] + "</td>"
		+ "<td width=8% id='chg_list-an'>" + changes[x]['acct_name'] + "</td>"
				//echo "<td width=25%>" . $changes[$x]['actions'] . "</td>";
		+ "<td width=24.75% id='chg_list-cd'>" + changes[x]['description'] + "</td>"
		+ "<td width=13.5% id='chg_list-res'>" + changes[x]['name'] + "</td>"
		+ "<td width=10.25% id='chg_list-st'>" + changes[x]['pht_start_datetime'] + "</td>"
		+ "<td width=10.25% id='chg_list-et'>" + changes[x]['pht_end_datetime'] + "</td>"
		+ "<td class='" + stat_hl + "' id='chg_list-status'>" + changes[x]['status'] 
		+ "<br><br><i><a data-toggle='modal' data-target='#show_ticket_notes' onclick='showNotes(" + changes[x]['item_id'] + ")'>View Notes</a></i></td>"
		+ "</tr>";
		if (x == row_limit) {
			var next_row = x + 1;
			document.getElementById('change-list-tbody').innerHTML += "<tr id='show-more_row'>"
			+ "<td colspan=9> <a onclick='showMoreChanges(" + next_row + ")'>Show more</a></td>"
			+ "</tr>";
			break;
		}
	}
}

function selectTeam() {
	var team = $('#team').val();
	$('#account').find('option').remove().end().append('<option value=""> -- Select Account -- </option>').val('');
	$('#primary_res').find('option').remove().end().append('<option value=""> -- Select Primary Resource -- </option>').val('');
	$('.sec_res_dropdown ul').html("");
	$('#sr_dropdown_text').html(" -- Select Secondary Resource(s) -- ");

	$.ajax({
		type: "POST",
		url: "process.php",
		data: {
			action: 'get_accounts_by_team',
			team: team
		},
		dataType: 'json'
	})
	.done(function(data) {
		for (var x = 0; x < data.length; x++) {
			$('#account').append($('<option>', {
				value: data[x][0],
				text: data[x][1] + " - " + data[x][2]
			}));
		}
	});

	$.ajax({
		type: "POST",
		url: "process.php",
		data: {
			action: 'get_resources_by_team',
			team: team
		},
		dataType: 'json'
	})
	.done(function(data) {
		for (var x = 0; x < data.length; x++) {
			$('#primary_res').append($('<option>', {
				value: data[x][0],
				text: data[x][1]
			}));

			$('.sec_res_dropdown ul').append("<li><input type='checkbox' class='sec_res_chkbox' id='sec_res_chkbox' name='sec_resources[]' value=" + data[x][0] + " onchange='checkBoxes_resources()'>" + data[x][1] + " </li>");
		}
	});
}
