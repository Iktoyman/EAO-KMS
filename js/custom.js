

function item_class_mo(id) {
	document.getElementById(id).src = 'img/' + id + 'Hover.png';
}

function item_class_moh(id) {
	document.getElementById(id).src = 'img/' + id + '.png';
}

/*

function GO() {
	document.getElementById("genOnboard").src = "img/genOnboardHover.png";
}

function GOH(){
	document.getElementById("genOnboard").src = "img/genOnboard.png";
}

function EAOA() {
	document.getElementById("eaoAccli").src = "img/eaoAccliHover.png";
}

function EAOAH(){
	document.getElementById("eaoAccli").src = "img/eaoAccli.png";
}

function NO() {
	document.getElementById("nestleOnboard").src = "img/nestleOnboardHover.png";
}

function NOH(){
	document.getElementById("nestleOnboard").src = "img/nestleOnboard.png";
}

function TT() {
	document.getElementById("techTraining").src = "img/techTrainingHover.png";
}

function TTH(){
	document.getElementById("techTraining").src = "img/techTraining.png";
}

function NE() {
	document.getElementById("nestleExams").src = "img/nestleExamsHover.png";
}

function NEH(){
	document.getElementById("nestleExams").src = "img/nestleExams.png";
}

function NST() {
	document.getElementById("nestleSpecTrain").src = "img/nestleSpecTrainHover.png";
}

function NSTH(){
	document.getElementById("nestleSpecTrain").src = "img/nestleSpecTrain.png";
}

function AU() {
	document.getElementById("acceleratingU").src = "img/acceleratingUHover.png";
}

function AUH(){
	document.getElementById("acceleratingU").src = "img/acceleratingU.png";
}

function C() {
	document.getElementById("certifications").src = "img/certificationsHover.png";
}

function CH(){
	document.getElementById("certifications").src = "img/certifications.png";
}

*/

function show_class(id) {
	var item_class_num = item_classes.length;
	for (var a = 0; a < item_class_num; a++) {
		if (item_classes[a]['element_id'] != id)
			document.getElementById(item_classes[a]['element_id'] + 'Div').style.display = 'none';

		if (item_classes[a]['element_id'] + 'Div' == id) {
			var name = item_classes[a]['name'];
			var completion = item_classes[a]['completion'];
		}
	}
	document.getElementById(id).style.display = 'block';
	document.getElementById('progressHeader').innerHTML = name;
	$('#percentageRing').removeClass();
	$('#percentageRing').addClass('c100');
	$('#percentageRing').addClass('p' + completion);
	$('#percentageRing').addClass('big');
	document.getElementById('percentageRing_val').innerHTML = completion + '%';
}

/*
function showGO(){
	var x = document.getElementById('genOnboardDiv');
	var a = document.getElementById('eaoAccliDiv');
	var b = document.getElementById('nestleOnboardDiv');
	var c = document.getElementById('techTrainingDiv');
	var d = document.getElementById('nestleExamsDiv');
	var e = document.getElementById('nestleSpecTrainDiv');
	var f = document.getElementById('acceleratingUDiv');
	var g = document.getElementById('certificationsDiv');

	if ( x.style.display == 'none'){
		document.getElementById("genOnboardDiv").style.display= 'block';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	} else{
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	}
}

function showEAO(){
	var x = document.getElementById('eaoAccliDiv');
	var a = document.getElementById('genOnboardDiv');
	var b = document.getElementById('nestleOnboardDiv');
	var c = document.getElementById('techTrainingDiv');
	var d = document.getElementById('nestleExamsDiv');
	var e = document.getElementById('nestleSpecTrainDiv');
	var f = document.getElementById('acceleratingUDiv');
	var g = document.getElementById('certificationsDiv');
	if ( x.style.display == 'none'){
		document.getElementById("eaoAccliDiv").style.display= 'block';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	} else{
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	}
}

function showNO(){
	var x = document.getElementById('nestleOnboardDiv');
	var a = document.getElementById('genOnboardDiv');
	var b = document.getElementById('eaoAccliDiv');
	var c = document.getElementById('techTrainingDiv');
	var d = document.getElementById('nestleExamsDiv');
	var e = document.getElementById('nestleSpecTrainDiv');
	var f = document.getElementById('acceleratingUDiv');
	var g = document.getElementById('certificationsDiv');
	if ( x.style.display == 'none'){
		document.getElementById("nestleOnboardDiv").style.display= 'block';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	} else{
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	}
}

function showTT(){
	var x = document.getElementById('techTrainingDiv');
	var a = document.getElementById('genOnboardDiv');
	var b = document.getElementById('eaoAccliDiv');
	var c = document.getElementById('nestleOnboardDiv');
	var d = document.getElementById('nestleExamsDiv');
	var e = document.getElementById('nestleSpecTrainDiv');
	var f = document.getElementById('acceleratingUDiv');
	var g = document.getElementById('certificationsDiv');
	if ( x.style.display == 'none'){
		document.getElementById("techTrainingDiv").style.display= 'block';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	} else{
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	}
}

function showNE(){
	var x = document.getElementById('nestleExamsDiv');
	var a = document.getElementById('genOnboardDiv');
	var b = document.getElementById('eaoAccliDiv');
	var c = document.getElementById('nestleOnboardDiv');
	var d = document.getElementById('techTrainingDiv');
	var e = document.getElementById('nestleSpecTrainDiv');
	var f = document.getElementById('acceleratingUDiv');
	var g = document.getElementById('certificationsDiv');
	if ( x.style.display == 'none'){
		document.getElementById("nestleExamsDiv").style.display= 'block';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	} else{
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	}
}

function showNST(){
	var x = document.getElementById('nestleSpecTrainDiv');
	var a = document.getElementById('genOnboardDiv');
	var b = document.getElementById('eaoAccliDiv');
	var c = document.getElementById('nestleOnboardDiv');
	var d = document.getElementById('techTrainingDiv');
	var e = document.getElementById('nestleExamsDiv');
	var f = document.getElementById('acceleratingUDiv');
	var g = document.getElementById('certificationsDiv');
	if ( x.style.display == 'none'){
		document.getElementById("nestleSpecTrainDiv").style.display= 'block';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	} else{
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	}
}

function showAU(){
	var x = document.getElementById('acceleratingUDiv');
	var a = document.getElementById('genOnboardDiv');
	var b = document.getElementById('eaoAccliDiv');
	var c = document.getElementById('nestleOnboardDiv');
	var d = document.getElementById('techTrainingDiv');
	var e = document.getElementById('nestleExamsDiv');
	var f = document.getElementById('nestleSpecTrainDiv');
	var g = document.getElementById('certificationsDiv');
	if ( x.style.display == 'none'){
		document.getElementById("acceleratingUDiv").style.display= 'block';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	} else{
		document.getElementById("acceleratingUDiv").style.display= 'none';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("certificationsDiv").style.display= 'none';
	}
}

function showC(){
	var x = document.getElementById('certificationsDiv');
	var a = document.getElementById('genOnboardDiv');
	var b = document.getElementById('eaoAccliDiv');
	var c = document.getElementById('nestleOnboardDiv');
	var d = document.getElementById('techTrainingDiv');
	var e = document.getElementById('nestleExamsDiv');
	var f = document.getElementById('nestleSpecTrainDiv');
	var g = document.getElementById('acceleratingUDiv');
	if ( x.style.display == 'none'){
		document.getElementById("certificationsDiv").style.display= 'block';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
	} else{
		document.getElementById("certificationsDiv").style.display= 'none';
		document.getElementById("genOnboardDiv").style.display= 'none';
		document.getElementById("eaoAccliDiv").style.display= 'none';
		document.getElementById("nestleOnboardDiv").style.display= 'none';
		document.getElementById("techTrainingDiv").style.display= 'none';
		document.getElementById("nestleExamsDiv").style.display= 'none';
		document.getElementById("nestleSpecTrainDiv").style.display= 'none';
		document.getElementById("acceleratingUDiv").style.display= 'none';
	}
}
*/

function showIRM(){
	var x = document.getElementById('IRM');
	if(x.style.display == 'none'){
		document.getElementById('IRM').style.display = 'block';
	} else{
		document.getElementById('IRM').style.display = 'none';
	}
}

function showPM(){
	var x = document.getElementById('PM');
	if(x.style.display == 'none'){
		document.getElementById('PM').style.display = 'block';
	} else{
		document.getElementById('PM').style.display = 'none';
	}
}

function showEM(){
	var x = document.getElementById('EM');
	if(x.style.display == 'none'){
		document.getElementById('EM').style.display = 'block';
	} else{
		document.getElementById('EM').style.display = 'none';
	}
}

function showADKB(){
	var x = document.getElementById('ADKB');
	if(x.style.display == 'none'){
		document.getElementById('ADKB').style.display = 'block';
	} else{
		document.getElementById('ADKB').style.display = 'none';
	}
}

function showCM(){
	var x = document.getElementById('CM');
	if(x.style.display == 'none'){
		document.getElementById('CM').style.display = 'block';
	} else{
		document.getElementById('CM').style.display = 'none';
	}
}

function showST(){
	var x = document.getElementById('ST');
	if(x.style.display == 'none'){
		document.getElementById('ST').style.display = 'block';
	} else{
		document.getElementById('ST').style.display = 'none';
	}
}

function showKPM(){
	var x = document.getElementById('KPM');
	if(x.style.display == 'none'){
		document.getElementById('KPM').style.display = 'block';
	} else{
		document.getElementById('KPM').style.display = 'none';
	}
}

function showBJO(){
	var x = document.getElementById('BJO');
	if(x.style.display == 'none'){
		document.getElementById('BJO').style.display = 'block';
	} else{
		document.getElementById('BJO').style.display = 'none';
	}
}

function showCRR(){
	var x = document.getElementById('CRR');
	if(x.style.display == 'none'){
		document.getElementById('CRR').style.display = 'block';
	} else{
		document.getElementById('CRR').style.display = 'none';
	}
}

function showTSP(){
	var x = document.getElementById('TSP');
	if(x.style.display == 'none'){
		document.getElementById('TSP').style.display = 'block';
	} else{
		document.getElementById('TSP').style.display = 'none';
	}
}