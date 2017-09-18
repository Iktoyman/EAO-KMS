function populateTypes() {
	for (var i = 0; i < types.length; i++) {
		var typeoption_name = types[i]['type_name'];
		var typeoption_id = types[i]['type_id'];
		
		var type_element = document.createElement("OPTION");
		type_element.setAttribute("value", typeoption_id);
		var t = document.createTextNode(typeoption_name);
		type_element.appendChild(t);
		document.getElementById("tickettype").appendChild(type_element);
	}
	
	for (var j = 0; j < technologies.length; j++) {
		var tech_name = technologies[j]['tech_name'];
		var tech_id = technologies[j]['tech_id'];
		
		var tech_element = document.createElement("OPTION");
		tech_element.setAttribute("value", tech_id);
		var opt = document.createTextNode(tech_name);
		tech_element.appendChild(opt);
		document.getElementById("techselect").appendChild(tech_element);
	}
	
	for (var l = 0; l < audience.length; l++) {
		var audience_name = audience[l]['audience_name'];
		var audience_id = audience[l]['audience_id'];
		
		var audience_element = document.createElement("OPTION");
		audience_element.setAttribute("value", audience_id);
		var opt = document.createTextNode(audience_name);
		audience_element.appendChild(opt);
		document.getElementById("audienceselect").appendChild(audience_element);
	}
	
	for (var k = 0; k < accounts.length; k++) {
		var account_name = accounts[k]['account_name'];
		var account_id = accounts[k]['account_id'];
		
		var account_element = document.createElement("OPTION");
		account_element.setAttribute("value", account_id);
		var opt = document.createTextNode(account_id + " - " + account_name);
		account_element.appendChild(opt);
		document.getElementById("accountselect").appendChild(account_element);
	}
}


			
