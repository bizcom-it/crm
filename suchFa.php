<?php
// $Id$
	require_once('inc/stdLib.php');
	include_once('FirmenLib.php');
	include_once('persLib.php');
    $id   = false;
    $tab  = false;
    $txt  = false;
    $pers = false;
    $datenC = false;
    $datenL = false;
    $datenP = false;
    if ( isSetVar($_GET) ) {
        foreach ($_GET as $key=>$val) {
            ${$key} = $val;
        };
    };
    if ( $id > 0 ) {
        $txt = '';
        if ( $tab == 'P' ) {
            $rs = getKontaktStamm($id);
            if ( $rs ) {
                $txt = $rs['cp_givenname'].' '.$rs['cp_name'].' '.$rs['name'];
            }
        } else {
            $rs = getFirmenStamm($id,false,$tab,false);
            if ( $rs ) {
                $txt = $rs['name'].' - '.$rs['city'];
            }
        }
        if ( $txt != '' ) {
            $txt .= ' (zugeordnet)';
            echo "<html><script language='JavaScript'>";
            echo "opener.document.formular.cp_cv_id.value='$tab$id';";
            echo "opener.document.formular.name.value='$txt';";
            echo "self.close();";
            echo "</script></html>";
            exit(1);
        }
    }
	if ( $tab ) {  //Nur Lieferant oder Kunde
		$datenC = getAllFirmen(array(1,$name),true,$tab);
	} else {      //sowohl, als auch
		$datenC = getAllFirmen(array(1,$name),true,"C");
		$datenL = getAllFirmen(array(1,$name),true,"V");
	}
	if ( $pers ) {
		$datenP = getAllPerson(array(1,$name));
    };
?>
<html>
	<script language="JavaScript">
	<!--
		function auswahl() {
			nr=document.firmen.Alle.selectedIndex;
			val=document.firmen.Alle.options[nr].value;
			tmp=document.firmen.Alle.options[nr].text;
			txt=tmp.substr(0,(tmp.length - 2));
			fid=val.substr(1,val.length);
<?php if ($pers==1) { ?>
			opener.document.formular.cp_cv_id.value=val;
			opener.document.formular.name.value=txt;
<?php } else if ( isSetVar($op) ) { ?>
			opener.document.formular.fid.value=fid;
			opener.document.formular.name.value=txt;
			opener.document.formular.tab.value=val.substr(0,1);
<?php } else if ( isSetVar($konzernname) ) {?>
			opener.document.neueintrag.konzern.value=fid;
			opener.document.neueintrag.konzernname.value=txt;
<?php } else {?>
			opener.document.formular.cp_cv_id.value=fid;
			opener.document.formular.name.value=txt;
<?php } 
 if ($nq==1) { ?>
			opener.document.formular.Quelle.value=val.substr(0,1);
<?php } ?>
		}
	//-->
	</script>
<body onLoad="self.focus()">
<center>Gefundene - Eintr&auml;ge:<br><br>
<form name="firmen">
<?php 
    if ( $datenP or $datenC or $datenL ) {
?>
<select name="Alle" >
	<option value=''>nichts markiert</option>
<?php
	if ( $konzernname != '' ) $name = $konzernname;
	if ( $name=="EINZELPERSON" ) $name="";
	if ( $datenP ) foreach ( $datenP as $zeile ) {
		echo "\t<option value='P".$zeile["cp_id"]."'>".$zeile["cp_name"].", ".$zeile["cp_givenname"].", ".$zeile["cp_city"]." P</option>\n";
	}
	if ( isSetVar($datenC ) ) {
        if ( $tab == "V" )
            $tab = "L";
        else
            $tab = "K";
        foreach ( $datenC as $zeile ) {
	    	echo "\t<option value='C".$zeile["id"]."'>".$zeile["customernumber"]." ".$zeile["name"].", ".$zeile["city"]." $tab</option>\n";
        }
	}
	if ( isSetVar($datenL) ) foreach ( $datenL as $zeile ) {
		echo "\t<option value='V".$zeile["id"]."'>".$zeile["vendornumber"]." ".$zeile["name"].", ".$zeile["city"]." L</option>\n";
	}

?>
</select>
<br>
<input type="button" name="ok" value="&uuml;bernehmen" onClick="auswahl()"><br>
<?php
    } else {
        echo "Keinen Treffer<br>";
    }
?>
<br>
<input type="button" name="ok" value="Fenster schlie&szlig;en" onClick="self.close();">
</form>
</body>
</html>
