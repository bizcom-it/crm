<?php
require_once('../inc/stdLib.php');
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <?php echo $menu['stylesheets'].'
    <link type="text/css" REL="stylesheet" HREF="'.$_SESSION["basepath"].'crm/css/'.$_SESSION["stylesheet"].'/main.css">
    <link rel="stylesheet" type="text/css" href="'.$_SESSION['basepath'].'crm/jquery-ui/themes/base/jquery-ui.css"> 
    <script type="text/javascript" src="'.$_SESSION['basepath'].'crm/jquery-ui/jquery.js"></script> 
    <script type="text/javascript" src="'.$_SESSION['basepath'].'crm/jquery-ui/ui/jquery-ui.js"></script>'.
	$menu['javascripts']; ?>
</head>
<body>
<?php 
/*
Warenimport mit Browser nach Lx-Office ERP
Henry Margies <h.margies@maxina.de>
Holger Lindemann <hli@lx-system.de>
*/
require ("import_lib.php");

function ende($nr) {
	echo "Abbruch: $nr<br>";
	echo "Fehlende oder falsche Daten.";
	exit(1);
}

if (!$GLOBALS["db"]) {
    ende(1);
}

/* get DB instance */
$db = $GLOBALS["db"]; 

$dowloand = '';

if ($_POST["liste"]=="Artikelliste erzeugen") {
    $sql  = 'SELECT partnumber,description,listprice,sellprice,lastcost,weight,notes,image,drawing,microfiche,';
    $sql .= 'ean,unit,buchungsgruppen_id,inventory_accno_id,income_accno_id,expense_accno_id,obsolete,shop,bin_id,warehouse_id,onhand,rop,pg.partsgroup ';
    $sql .= 'FROM parts LEFT JOIN partsgroup PG ON PG.id = partsgroup_id';
	$rs   = $db->getAll($sql);
	$file = fopen($_SESSION['erppath'].'crm/tmp/artikel.csv',"w");
    fputs($file,'"partnumber";"description";"listprice";"sellprice";"lastcost";"weight";"notes";"image";"drawing";"microfiche";'.
                 '"ean";"unit";"buchungsgruppen_id";"inventory_accno_id";"income_accno_id";"expense_accno_id";"obsolete";"shop";"bin_id";"warehouse_id";"onhand";"rop";"partsgroup"'."\n");	
	if ($rs) foreach ($rs as $row) {
        $line = join('";"',$row);
		fputs($file,'"'.$line.'"'."\n");	
	}	
	fclose($file);
    $download = '<a href='.$_SESSION['erppath'].'crm/tmp/artikel.csv'>Download Artikelliste</a><br>';
}

/* display help */
if ($_POST["ok"]=="Hilfe") {
    echo "Importfelder:<br>";
	echo "Feldname => Bedeutung<br>";
    foreach($parts as $key=>$val) {
	    echo "$key => $val<br>";
    }
    echo "<br>Die erste Zeile enth&auml;lt die Feldnamen der Daten in ihrer richtigen Reihenfolge<br>";
    echo "Geben Sie das Trennzeichen der Datenspalten ein. Steuerzeichen k&ouml;nnen mit ihrem Dezimalwert gef&uuml;hrt von einem &quot;#&quot; eingegebn werden (#11).<br><br>"; 
    echo "Der &quot;sellprice&quot; kann um den eingegeben Wert  ge&auml;ndert werden.<br><br>";
    echo "Bei vorhandenen Artikelnummern (in der db), kann entweder ein Update auf den Preis durchgef&uuml;hrt werden oder der Artikel mit anderer Artikelnummer eingef&uuml;gt werden.<br><br>";
    echo "Jeder Artikel mu&szlig; einer Buchungsgruppe zugeordnet werden. ";
    echo "Dazu mu&szlig; entweder in der Maske eine Standardbuchungsgruppe gew&auml;hlt werden <br>";
    echo "oder es wird ein g&uuml;ltiges Konto in 'income_accno_id' und 'expense_accno_id' eingegeben. ";
    echo "Das Programm versucht dann eine passende Buchungsgruppe zu finden.";
    exit(0);
};

/* just display page or do real import? */
if ($_POST["ok"]) {

    require ("parts_import.php");

    clearstatcache ();

    $test    = $_POST["test"];
    $TextUpd = $_POST["TextUpd"];
    $trenner = ($_POST["trenner"])?$_POST["trenner"]:",";
    $trennzeichen = ($_POST["trennzeichen"])?$_POST["trennzeichen"]:"";
    $precision = $_POST["precision"];
    $quotation = $_POST["quotation"];
    $quottype = $_POST["quottype"];
    $file    = "parts";

    /* no data? */
    if (empty($_FILES["Datei"]["name"]))
	    ende (2);

    /* copy file */
    if (!move_uploaded_file($_FILES["Datei"]["tmp_name"],$_SESSION['erppath'].'crm/tmp/'.$file.".csv")) {
	    echo "Upload von Datei fehlerhaft.";
    	echo $_FILES["Datei"]["error"], "<br>";
	    ende (2);
    } 

    /* check if file is really there */
    if (!file_exists("$file.csv")) 
	    ende(5);

    /* first check all elements */
    //echo "Checking data:<br>";
    //$_test=$_POST;
    //$_test["precision"]=-1;
    //$_test["quotation"]=0;
    //$err = import_parts($db, $file, $trenner, $trennzeichen, $parts, TRUE, FALSE, FALSE,$_test);
    //echo "$err Errors found\n";
    //if ($err!=0)	exit(0);

    /* just print data or insert it, if test is false */
    import_parts($file, $trenner, $trennzeichen, $parts, FALSE, !$test, TRUE,$_POST);

} else {
	$bugrus=getAllBG($db);
?>

<p class="listtop">Artikelimport f&uuml;r die ERP<p>
<br>
<?php echo $download; ?>
<form name="import" method="post" enctype="multipart/form-data" action="partsB.php">
<input type="hidden" name="MAX_FILE_SIZE" value="150000000">
<input type="hidden" name="login" value="<?= $login ?>">
<table>
<tr><td><input type="submit" name="ok" value="Hilfe"></td><td>
		<input type="submit" name="liste" value="Artikelliste erzeugen"> </td></tr>
<tr><td>Trennzeichen</td><td>
		<input type="radio" name="trenner" value=";" checked>Semikolon 
		<input type="radio" name="trenner" value=",">Komma 
		<input type="radio" name="trenner" value="#9">Tabulator
		<input type="radio" name="trenner" value=" ">Leerzeichen
		<input type="radio" name="trenner" value="other"> 
		<input type="text" size="2" name="trennzeichen" value=""> 
</td></tr>
<tr><td>VK-Preis<br>Nachkomma:</td><td><input type="Radio" name="precision" value="0">0  
			<input type="Radio" name="precision" value="1">1 
			<input type="Radio" name="precision" value="2" checked>2 
			<input type="Radio" name="precision" value="3">3 
			<input type="Radio" name="precision" value="4">4 
			<input type="Radio" name="precision" value="5">5 
	</td></tr>
<tr><td>VK-Preis<br>Aufschlag:</td><td><input type="text" name="quotation" size="5" value="0"> 
			<input type="radio" name="quottype" value="P" checked>% 
			<input type="radio" name="quottype" value="A">Absolut</td></tr>
<tr><td>Shopartikel:</td><td><input type="radio" name="shop" value="t" checked>ja <input type="radio" name="shop" value="f">nein</td></tr>
<tr><td>Vorhandene<br>Artikelnummer:</td><td><input type="radio" name="update" value="U" checked>Preis update durchf&uuml;hren<br>
					<input type="radio" name="update" value="I">mit neuer Nummer einf&uuml;gen</td></tr>

<tr><td>Lieferanten</td><td><input type="radio" name="newvendor" value="1">ersten Eintrag ersezten<input type='radio' name='newvendor' value='2' checked>als ersten Eintrag einfügen</td></tr>

<tr><td>Test</td><td><input type="checkbox" name="test" value="1">ja</td></tr>
<tr><td>Textupdate</td><td><input type="checkbox" name="TextUpd" value="1">ja</td></tr>
<tr><td>Art</td><td><input type="Radio" name="ware" value="W" checked>Ware &nbsp; 
		    <input type="Radio" name="ware" value="D">Dienstleistung
		    <input type="Radio" name="ware" value="G">gemischt (Spalte 'art' vorhanden)</td></tr>
<tr><td>Default Bugru<br></td><td><select name="bugru">
<?	if ($bugrus) foreach ($bugrus as $bg) { 
                        if (strpos($bg['description'],'19')) { $sel=$bg['id'].'" selected'; }
                        else { $sel = $bg['id'].'"'; };
?>
			<option value="<? echo $sel; ?>><?= $bg["description"] ?>
<?	} ?>
	</select><br>
	<input type="radio" name="bugrufix" value="0">nie
	<input type="radio" name="bugrufix" value="1">f&uuml;r alle Artikel verwenden
	<input type="radio" name="bugrufix" value="2" checked>f&uuml;r Artikel ohne passende Bugru
	</td></tr>
<tr><td>Daten</td><td><input type="file" name="Datei"></td></tr>
<tr><td></td><td><input type="submit" name="ok" value="Import"></td></tr>
</table>
</form>
<?php };  ?>
</body>
</html>
