<?php
require_once("inc/version.php");
require_once("inc/stdLib.php");
$menu = $_SESSION['menu'];
$head = mkHeader();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head><title><?php echo  translate(".:LxO:.","work"); ?> AccTrans</title>
<?php
echo $menu['stylesheets'];
echo $menu['javascripts'];
echo $head['CRMCSS'];
echo $head['THEME'];
?>
</head>
<body>
<?php
      echo $menu['pre_content'];
      echo $menu['start_content'];

$db = $GLOBALS['db'];
if ($_POST["ok"] == "sichern") {
    //echo "<pre>"; print_r($_POST); echo "</pre>";
    $cnt=count($_POST["chart_id"]);
    $change = False;
    $ok = True;
    $db->begin();
    $sql="update ".$_POST["tab"]." set ";
    if ($_POST["transdate"]<>$_POST["orgtransdate"]) {
        $sql.="transdate='".$_POST["transdate"]."',";
        $change = True;
    }
    if ($_POST["feld1"]<>$_POST["orgfeld1"]) {
        $change = True;
        if ($_POST["tab"]=="gl") {
            $sql.="reference='".$_POST["feld1"]."',";
        } else {
            $sql.="amount=".$_POST["feld1"].",";
        }
    }
    if ($_POST["feld2"]<>$_POST["orgfeld2"]) {
        $change = True;
        if ($_POST["tab"]=="gl") {
            $sql.="description='".$_POST["feld2"]."',";
        } else {
            $sql.="netamount=".$_POST["feld2"].",";
        }
    }
    if ($change) {
        $sql=substr($sql,0,-1)." where id = ".$_POST["trans_id"];
        echo $sql;
        $rc = $db->query($sql);
        if ($rc) { echo " ok<br>";}
        else { echo " fehler<br>"; $change=False;};
    } else {
	$change = True;
    };
    if ($change) {
	for ($i=0; $i<$cnt; $i++) {
            if ($_POST["acc_trans_id"][$i] == '') {
                if ($_POST["chart_id"][$i] == '') continue;
		$sql = "insert into acc_trans (trans_id,transdate,chart_id,taxkey,amount) values (";
                $sql .= $_POST["trans_id"].",'".$_POST["acctransdate"][$i]."',".$_POST["chart_id"][$i].",".$_POST["taxkey"][$i].",".$_POST["amount"][$i].")";
		$rc = $db->query($sql);
			echo $sql;
		if ($rc) { echo " ok<br>";}
		else { echo " fehler<br>"; $ok=False; };
            } else {
	    	$sql="update acc_trans set ";
            $change = False;
	    if ($_POST["chart_id"][$i]<>$_POST["org_chart"][$i]) {
		    $sql .= "chart_id = ".$_POST["chart_id"][$i].",";
		    $change = True;
	    }
	    if ($_POST["taxkey"][$i]<>$_POST["org_taxkey"][$i]) {
		    $sql .= "taxkey=".$_POST["taxkey"][$i].",";
		    $change = True;
	    }
	    if ($_POST["amount"][$i]<>$_POST["org_amount"][$i]) {
		    $sql .= "amount=".$_POST["amount"][$i].",";
		    $change = True;
	    }
	    if ($_POST["acctransdate"][$i]<>$_POST["org_acctransdate"][$i]) {
		    $sql .= "transdate='".$_POST["acctransdate"][$i]."',";
		    $change = True;
	    }
	    if ($change) {
		    $sql = substr($sql,0,-1)." where acc_trans_id = ".$_POST["acc_trans_id"][$i]; 
		    $rc = $db->query($sql);
				echo $sql;
		    if ($rc) { echo " ok<br>";}
		    else { echo " fehler<br>"; $ok=False; };
	    }
            }
	}
    }
    if ($ok) { $db->commit(); }
    else { $db->rollback(); };
} else if ($_POST["del"] == "del") {
	echo "Transaktion ".$_POST["trans_id"]." von ".$_POST["tab"]." löschen ";
	$rc = $db->begin();
	$sql = "delete from acc_trans where trans_id = ".$_POST["trans_id"];
        $rc = $db->query($sql);
	if ($_POST["tab"]=="gl") {
		$sql = "delete from gl where  id = ".$_POST["trans_id"];
           	$rc = $db->query($sql);
        	if ($rc) { $rc=$db->commit(); echo " ok<br>";}
        	else { $rc=$db->rollback(); echo " fehler<br>"; };
	} else if ($_POST["tab"]=="ar") {
		    $sql = "delete from ar where  id = ".$_POST["trans_id"];
           	$rc = $db->query($sql);
        	if ($rc) { 
			    $sql = "delete from invoice where  trans_id = ".$_POST["trans_id"];
            	$rc = $db->query($sql);
        		if ($rc) { $rc=$db->commit(); echo " ok<br>";
        		} else { $rc=$db->rollback(); echo " fehler ($sql)<br>"; };
        	} else { $rc=$db->rollback(); echo " fehler ($sql)<br>"; };
        } else if ($_POST["tab"]=="ap") {
		$sql = "delete from ap where  id = ".$_POST["trans_id"];
            	$rc = $db->query($sql);
        	if ($rc) { 
			$sql = "delete from invoice where  trans_id = ".$_POST["trans_id"];
            		$rc = $db->query($sql);
        		if ($rc) { $rc=$db->commit(); echo " ok<br>";
        		} else { $rc=$db->rollback(); echo " fehler<br>"; };
        	} else { $rc=$db->rollback(); echo " fehler<br>"; };
        }
} else if ($_POST["get"] == "get") {
    $rs = $db->getAll("select * from ar,customer where customer_id=customer.id and ar.id =".$_POST["trans_id"]);
    if (!$rs) {
        $rs = $db->getAll("select * from ap,vendor where vendor.id=vendor_id and ap.id =".$_POST["trans_id"]);
        if (!$rs) {
            $rs = $db->getAll("select * from gl where id =".$_POST["trans_id"]);
            $tabstr = "GL <br />";
            $tab = "gl"; 
            $transdate = $rs[0]["transdate"];
	    $name1 = "Referenz";
	    $name2 = "Beschr.";
            $feld1 = $rs[0]["reference"];
            $feld2 = $rs[0]["description"];
        } else {
            $tabstr = "AP<br />";
            $tab = "ap";
	    $name1 = "Brutto";
	    $name2 = "Netto";
            $transdate = $rs[0]["transdate"];
            $feld1 = $rs[0]["amount"];
            $feld2 = $rs[0]["netamount"];
        }
    } else {
        $tabstr = "AR<br />";
        $tab = "ar";
        $transdate = $rs[0]["transdate"];
	$name1 = "Brutto";
	$name2 = "Netto";
        $feld1 = $rs[0]["amount"];
        $feld2 = $rs[0]["netamount"];
    }

    echo "Tabelle: $tabstr<br />";
    $rs=$db->getAll("select * from acc_trans where trans_id=".$_POST["trans_id"]); //,DB_FETCHMODE_ASSOC);
    if ($rs) {
        echo "<form name='acc' action='acctrans.php' method='post'>";
        echo "<input type='hidden' name='tab' value='$tab'>";
        echo "<input type='hidden' name='orgtransdate' value='$transdate'>";
        echo "<input type='hidden' name='orgfeld1' value='$feld1'>";
        echo "<input type='hidden' name='orgfeld2' value='$feld2'>";
        echo "<input type='hidden' name='trans_id' value='".$_POST["trans_id"]."'> TransID:".$_POST["trans_id"]."<br>";
        echo "Datum <input type='text' name='transdate' value='".$transdate."'> ";
        echo "$name1: <input type='text' name='feld1' value='".$feld1."'> ";
        echo "$name2: <input type='text' name='feld2' value='".$feld2."'> <br />";
        foreach ($rs as $row) {
            echo "Chart ID<input type='text' name='chart_id[]' value='".$row["chart_id"]."'>";
            echo "Taxkey<input type='text' name='taxkey[]' value='".$row["taxkey"]."'>";
            echo "Betrag<input type='text' name='amount[]' value='".$row["amount"]."'>";
            echo "Transdate<input type='text' name='acctransdate[]' value='".$row["transdate"]."'><br>";
            echo "<input type='hidden' name='org_chart[]' value='".$row["chart_id"]."'>";
            echo "<input type='hidden' name='org_taxkey[]' value='".$row["taxkey"]."'>";
            echo "<input type='hidden' name='org_amount[]' value='".$row["amount"]."'>";
            echo "<input type='hidden' name='org_acctransdate[]' value='".$row["transdate"]."'>";
            echo "<input type='hidden' name='acc_trans_id[]' value='".$row["acc_trans_id"]."'>";
        }

        echo "Chart ID<input type='text' name='chart_id[]' value=''>";
        echo "Taxkey<input type='text' name='taxkey[]' value=''>";
        echo "Betrag<input type='text' name='amount[]' value=''>";
        echo "Transdate<input type='text' name='acctransdate[]' value='".$row["transdate"]."'><br>";
        echo "<input type='submit' name='ok' value='sichern'>";
        echo "<input type='submit' name='del' value='del'>";
        echo "</form>";
    } else {
        echo "nicht gefunden";
    }
}


?>
<form name='acc' action='acctrans.php' method='post'>
Trans-ID <input type="text" name="trans_id"> <input type="submit" name="get" value="get">
<?php echo $menu['end_content']; ?>
</body>
</html>
