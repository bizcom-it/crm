<?php
	require_once('inc/stdLib.php');
	include_once('wvLib.php');

	$masch = ( isSetVar($_GET['masch']) )?$_GET['masch']:'%';
?>
<html>
	<script language = "JavaScript">
	<!--
		function auswahl() {
			nr  = document.firmen.Alle.selectedIndex;
			val = document.firmen.Alle.options[nr].value;
			txt = document.firmen.Alle.options[nr].text;
 			opener.document.getElementById("neuname").value = txt;
 			opener.document.getElementById("neuid").value = val; 			
		}
	//-->
	</script>
<body onLoad = "self.focus()">
<center>Gefundene - Maschinen:<br><br>
<form name = "firmen">
<?php
	$daten = getAllMaschinen($masch);
	if ( $daten ) {
        echo '<select name="Alle" >';
        foreach ($daten as $zeile) {
		    echo "\t<option value='".$zeile["id"]."'>".substr($zeile["description"],0,20)." | ".$zeile["serialnumber"]."</option>\n";
	    };
        echo '</select>';
    } else {
        echo 'Keine Maschinen im Bestand';
    };
?>
<br>
<br>
<input type="button" name="ok" value="&uuml;bernehmen" onClick="auswahl()"><br>
<input type="button" name="ok" value="Fenster schlie&szlig;en" onClick="self.close();">
</form>
</body>
</html>
