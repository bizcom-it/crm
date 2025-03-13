function blzSearch(wo) {
	mitort=false;
	mitplz=false;
	ort=""
	plz=""
	if (document.neueintrag.mitort.checked==true) mitort=true
	if (document.neueintrag.mitplz.checked==true) mitplz=true
	if (wo=="F") {
		var bank = document.neueintrag.bank.value;
		var blz = document.neueintrag.bank_code.value;
		if (mitort) var ort = document.neueintrag.city.value;
		if (mitplz) var plz = document.neueintrag.zipcode.value;
	} else {
		return false;
	}
	if (blz=="" && bank=="" && ort=="" && plz=="") {
		alert("Bitte Ort,Bank oder BLZ (teilweise) eingeben");
		return false;
	}
	var f = open("search_blz.php?blz="+blz+"&ort="+ort+"&plz="+plz+"&bank="+bank+"&wo="+wo+"&mitort="+mitort,"win","width=450,height=200,left=100,top=100");
}
txt  = "<input type='button' value='suche Bank' onClick='blzSearch(\"F\")'> <input type='checkbox' name='mitort' value='1'>mit Ort";
txt += "<input type='checkbox' name='mitplz' value='1'>mit PLZ";
document.getElementById("blzsearch").innerHTML= txt;
