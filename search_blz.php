<?php
	require_once("inc/stdLib.php");
    if ( $_GET['erp'] == '1' ) { $erp = 'hidden'; $_GET['wo'] = 'F'; } else { $erp='visible'; };
	$ort  = $_GET["ort"];
	$bank = $_GET["bank"];
	//Umlaute wandeln hängt von der Serverumgebung ab!!
	//$loc_de = setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');
	$blz = $_GET["blz"];
	$plz = $_GET["plz"];
	$wo  = $_GET["wo"];
	if ( $_GET['erp'] == '1') { $mitort = true; }
	else { 	$mitort=$_GET["mitort"]; };
	$sql="SELECT * from blz_data where ";
	if ( $blz ) {
	  	$sql.="blz like '$blz%' ";
        if ( $_GET['erp'] == '1' ) $mitort = false;
	}
	if ( $bank ) {
		$bank = strtoupper($_GET["bank"]);
		if ( $blz ) $sql .= "and ";
		$sql .= "UPPER(kurzbez) like '%$bank%' ";
        if ( $_GET['erp'] == '1' ) $mitort = false;
	}
	if ( $ort and $mitort ) {
	  	$ort = strtoupper($ort);
	  	if ( $blz or $bank ) $sql .= "and ";
	  	$sql .= "UPPER(ort) like '%$ort%' ";
	} 
	if ( $plz and $mitort ) {
	  	if ( $bank or $blz or $ort ) $sql .= "and ";
	  	$sql .= "plz like '$plz%' ";
	} 
	$sql .= "order by plz,kurzbez";
	$rs   = $GLOBALS['db']->getAll($sql);
        $menu =  $_SESSION['menu'];
?>
<html>
<head><title></title>
    <?php echo $menu['stylesheets']; ?>
	<script language="JavaScript">
	<!--
	var wo = '<?php echo  $wo ?>';
	function auswahl() {
		nr=document.firmen.Alle.selectedIndex;
		val=document.firmen.Alle.options[nr].value;
		tmp=val.split("--");		
		if (wo=="F") {
			top.document.getElementById("cv_bank_code").value = tmp[0];
			top.document.getElementById("cv_bank").value = tmp[1];
			top.document.getElementById("cv_bic").value  = tmp[2];
		}
	}
	//-->
	</script>
</head>
<body onLoad="self.focus()">
<center>Gefundene - Eintr&auml;ge:<br><br>
<form name="firmen">
<select name="Alle" >
<?php
	if ( $rs ) foreach ( $rs as $zeile ) {
		$ort  = $zeile["ort"]; 
		$kurz = $zeile["kurzbez"]; 
		$bank = $zeile["bezeichnung"]; 
		$blz  = $zeile["blz"];
		$bic  = $zeile["bic"];
		echo "\t<option value='".$blz."--".$kurz.'--'.$bic."'>".$blz." ".$ort." ".$bank."</option>\n";
	}
?>
</select><br>
<br>
<input type="button" name="ok" value="&uuml;bernehmen" onClick="auswahl()"><br>
<input type="button" name="ok" value="Fenster schlie&szlig;en" onClick="self.close();" style="visibility:<?php echo $erp; ?>;">
</form>
</body>
</html>
