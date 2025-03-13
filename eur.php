<?php
	require_once("inc/stdLib.php");
    $menu = $_SESSION['menu'];
    $head = mkHeader();
?>
<html>
	<head><title></title>
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
echo '<p class="listtop">Umsatz-Bericht</p>';

if ($_POST["ok"]=="erzeugen") {

    /**
     * getBugru: Buchungsgruppen holen
     * 
     * @return Array
     */
    function getTax() {
            $sql ="SELECT C.id,(T.rate * 100) as rate,TK.startdate,C.accno from chart C ";
            $sql.="left join taxkeys TK  on TK.chart_id=C.id left join tax T on T.id=TK.tax_id ";
            $sql.="where  TK.startdate <= now() and C.datevautomatik='f' and C.pos_bwa is null and (category = 'I' or category = 'E') and datevautomatik = 'f' ";
            $sql.="order by C.id, TK.startdate ";
            $rs=$GLOBALS["db"]->getAll($sql,DB_FETCHMODE_ASSOC);
            if ($rs) foreach ($rs as $row) {
                $tax[$row["id"]]=sprintf("%0.2f",$row["rate"]);
            }
            return $tax;
    }

    function getSteuern($sqlar,$src='ar') {
        $sql = "select trans_id,chart_id,amount from acc_trans acc left join chart c on acc.chart_id=c.id ";
        //$sql.= "where trans_id in (select id from ar) and c.taxkey_id=0 and c.category='I' ";
        $sql.= "where c.datevautomatik='f' and c.pos_bwa is null and (c.category='I' or c.category='E') ";
        $sql.= "and trans_id in (select id from $src where ".$sqlar.") order by trans_id";
        $rs = $GLOBALS["db"]->getAll($sql,DB_FETCHMODE_ASSOC);
        return $rs;
    }

    /**
     * getRechnungen: Rechnungen auslesen
     * 
     * @return Array()
     */
    function getRechnungen($von,$bis,$tz,$istsoll,$wo=1) {
        $tax = getTax();
        $sqlar =  "SELECT ar.id,invnumber,amount,netamount,(amount-netamount) as mwst,ar.taxzone_id,name,customer.id as cid,";
        $sqlar .= "country,ustid,ar.transdate,invoice,'C' as typ ";
        $sqlar .= "from ar left join customer on customer.id=customer_id where " ;
        $sqlap =  "SELECT ar.id,invnumber,amount*-1 as amount,netamount*-1 as netamount,(amount-netamount)*-1 as mwst,";
        $sqlap .= "ar.taxzone_id,name,vendor.id as cid,country,ustid,ar.transdate,invoice,'V' as typ ";
        $sqlap .= "from ap as ar left join vendor on vendor.id=vendor_id where " ;
        if ($tz==1) {
            $sqltz ="ar.taxzone_id=1 and ";
        } else if ($tz==2) {
            $sqlzt ="ar.taxzone_id=2 and ";
        } else if ($tz==3) {
            $sqlzt ="ar.taxzone_id=3 and ";
        } else if ($tz=="0") {
            $sqltz ="ar.taxzone_id=0 and ";
        }      
        if ($istsoll==1) {
            $bezug='datepaid';
        } else {
            $bezug='transdate';
        }
        $sqltz.=$bezug." between '".$von."' and '".$bis."'";
        if ( $wo == '1' ) {
            $steuern = getSteuern($sqltz,'ar');
            $rs = $GLOBALS["db"]->getAll($sqlar.$sqltz." order by transdate",DB_FETCHMODE_ASSOC);
        } else if ( $wo == '2' ) {
            $sqltz ="transdate between '".$von."' and '".$bis."'";
            $steuern = getSteuern($sqltz,'ap');
            //echo $sqlap.$sqltz." order by transdate";
            $rs = $GLOBALS["db"]->getAll($sqlap.$sqltz." order by transdate",DB_FETCHMODE_ASSOC);
        } else {
            $steuern1 = getSteuern($sqltz,'ar');
            $sqltz ="transdate between '".$von."' and '".$bis."'";
            $steuern2 = getSteuern($sqltz,'ap');
            $steuern  = array_merge($steuern1,$steuern2);
            $sql = $sqlar.$sqltz.' UNION '.$sqlap."transdate between '".$von."' and '".$bis."' ORDER BY transdate";
            $rs = $GLOBALS["db"]->getAll($sql,DB_FETCHMODE_ASSOC);
            //$rs = array_merge($rs1,$rs2);
        }
        if ( !$rs ) return false;
        foreach ($rs as $row) {
            $rechng[$row["id"]] = $row;
        }
        $steuersatz = array();
        foreach ($steuern as $row) {
            if ($rechng[$row["trans_id"]]["amount"]<>$rechng[$row["trans_id"]]["netamount"]) {
                $rechng[$row["trans_id"]]['rate'][$tax[$row["chart_id"]]]=$row["amount"];
                if (!in_array($tax[$row["chart_id"]],$steuersatz)) $steuersatz[]=$tax[$row["chart_id"]];
            }
        }
        return array($rechng,$steuersatz);
    }

	function schaltjahr($jahr) {
		// Funktion noch verbessern?
		if ($jahr % 4 <> 0) return false;
		if ($jahr % 400 == 0 ) return true;
		if ($jahr % 100 <> 0 ) return false;
		return true;
	}

	$Day=array(0,"31","28","31","30","31","30","31","31","30","31","30","31");
    $zeitraum = 'Jahr '.$_POST["jahr"];
	if ($_POST["quartal"]<>"") {  
        $zeitraum .= ' Quartal '.$_POST["quartal"];
		$start=array(0,"01","04","07","10");
		$stopM=array(0,"03","06","09","12");
		$stopD=array(0,"31","30","30","31");
		$von = $_POST["jahr"]."-".$start[$_POST["quartal"]]."-01";
		$bis = $_POST["jahr"]."-".$stopM[$_POST["quartal"]]."-".$stopD[$_POST["quartal"]];
	} else if ($_POST["monatbis"]<>"" or $_POST["monatvon"]<>"") {
        if ($_POST["monatvon"]<>"") {
            $zeitraum = ' von Monat '.$_POST["monatvon"];
            $von = $_POST["jahr"]."-".$_POST["monatvon"]."-01";
        } else {
            $zeitraum = ' von Monat '.$_POST["monatvon"];
            $von = $_POST["jahr"]."-".$_POST["monatbis"]."-01";
        }
        if ($_POST["monatbis"]=="" or $_POST["monatbis"]<$_POST["monatvon"]) {
            $_POST["monatbis"] = $_POST["monatvon"];
            $zeitraum .= ' bis Monat '.$_POST["monatbis"];
        }
        if ($_POST["monatbis"]=="2") {
			if (schaltjahr($_POST["jahr"])) {
				$day=29;
			} else {
				$day=28;
			}
		} else {
			$day=$Day[$_POST["monatbis"]];
		} 
        $bis = $_POST["jahr"]."-".$_POST["monatbis"]."-".$day;
    } else {
        $von = $_POST["jahr"]."-01-01";
        $bis = $_POST["jahr"]."-12-31";
    }
    $rechnungen = getRechnungen($von,$bis,$_POST["tz"],$_POST["istsoll"],$_POST['vkek']);
    if ( $rechnungen ) {
        //echo "<pre>";print_r($rechnungen); echo "</pre>";	
        $rechng = array('C'=>"<tr><td><a href='../is",'V'=>"<tr><td><a href='../ir");
        $buchng = array('C'=>"<tr><td><a href='../ar",'V'=>"<tr><td><a href='../ap");
        $zeile  = ".pl?action=edit&id=%s' target='_blank'>%s</a></td>";
        $zeile .= "<td>%s</td>";
        $zeile .= "<td><a href='../ct.pl?action=edit&db=customer&id=%s' target='_blank'>%s</a></td>";
        $zeile .= "<td>%s</td><td align='right'>%s</td><td align='right'>%s</td><td align='right'>%s</td>";
        $tz = "\t";
        $zeilec = "%s$tz%s".$_POST["jahr"]."$tz%s$tz%s$tz%0.2f$tz%0.2f$tz%0.2f$tz%0.2f%s\n";
        $zeilec = "%s$tz%s".$_POST["jahr"]."$tz%s$tz%s$tz%0.2f$tz%0.2f$tz%0.2f%s\n";
        echo "<b>Zeitraum: ".$zeitraum."</b><br />";
        $colsp=7;
        $zone = '';
        $cnt  = 0;
        if ($rechnungen[1]) {
            //taxzone = 0
            foreach ($rechnungen[1] as $row) { $zone .= "<th>".$row." % </th>"; $colsp++; $cnt++;};
            //taxzone = 2
            foreach ($rechnungen[1] as $row) { $zone .= "<th>".$row." % </th>"; $colsp++; };
        }
        echo "<table cellpadding=3px' border='0'>";
        echo '<tr><td colspan="5"></td><th colspan="2">Steuerzone</th><th colspan="'.$cnt.'">Inland</th><th colspan="'.$cnt.'">EU ohne ID</th></tr>';
        echo "<tr><th>Re-Nr</th><th>Datum</th><th>Kunde</th><th>UStID</th><th> Brutto </th><th> Netto </th><th> MwSt </th>".$zone;
        $f = fopen('tmp/deb.csv','w');
        foreach ($rechnungen[0] as $row) {
            $transdate = split("-",$row["transdate"]);
            $transdate = $transdate[2].".".$transdate[1].".";
            $mwst = $row["amount"]- $row["netamount"];
            if ($mwst<>0) { 
                $mwst = sprintf("%0.2f",$mwst) ;
            } else { 
                $mwst = "";
                if ($row["taxzone_id"]==1) {
                    $nettobrutto1 += $row["netamount"];
                } else if ($row["taxzone_id"]==2) {
                    $nettobrutto2 += $row["netamount"];
                } else if ($row["taxzone_id"]==3) {
                    $nettobrutto3 += $row["netamount"];
                } else {
                    $nettobrutto += $row["netamount"];
                }
            }
            if (substr($row["invnumber"],0,6) == "Storno") $row["invnumber"] = substr($row["invnumber"],9)."Sto";
            if ($row['invoice'] == 't') { echo $rechng[$row['typ']]; }
            else { echo $buchng[$row['typ']]; };
            echo sprintf($zeile,$row["id"],$row["invnumber"],$transdate,$row["cid"],$row["name"],$row["ustid"],
                                sprintf("%0.2f",$row["amount"]),sprintf("%0.2f",$row["netamount"]),$mwst);
            $m1 = 0;
            $m2 = '';
            if ( $row['rate'] ) { while ( list($rate,$val) = each ($row['rate']) ) {
                    if ($row["taxzone_id"]==2) { echo "<td colspan='$cnt'></td>"; }; // Inlandspalten überbrücken
                    $tax[$row["taxzone_id"]][$rate]['rate'] += $val;
                    $m1 += $val;
                    if ( $row["taxzone_id"]==1 or $row["taxzone_id"]==3 ) {          // EU-Spalten auch überbrücken
                        $tax[$row["taxzone_id"]][$rate]['amount'] += $row["netamount"];
                        echo "<td colspan='$cnt'></td>";
                    } else {                                                        // Taxzone === 0
                        if ($val) {
                            $tax[$row["taxzone_id"]][$rate]['amount'] += $val/$rate*100; 
                            echo "<td align='right'>".sprintf("%0.2f",$val)."</td>"; 
 	   	                    $m2 = sprintf("$tz%0.2f",$val);
                        } else {
                            echo "<td align='right'>ohne Steuern</td>"; 
                        }
                    }
                };
                    if ( $row['taxzone_id'] === 0 ) echo "<td colspan='$cnt'></td>"; 
            } else {
                if ( $row['taxzone_id'] == '' ) {
                    echo "<td align='right'>???</td>"; 
                } else {
                }
            };
            $brutto += $row["amount"];
            $netto += $row["netamount"];
            //echo "<td>$m1 ".round($mwst-$m1,3)."</td></tr>\n";
            echo "</tr>\n";
            $mwstsum += $m1;
            $line = sprintf($zeilec,$row["invnumber"],$transdate,$row["name"],$row["ustid"],sprintf("%0.2f",$row["amount"]),sprintf("%0.2f",$row["netamount"]),$mwst,$m2); 
            fputs($f,$line);
        }
        echo "<tr><td colspan='".($colsp)."'><hr></td></tr>";
        echo "<tr><td colspan='".($colsp-$cnt-$cnt-3)."'></td><td align='right'>".sprintf("%0.2f",$brutto)."</td><td align='right'>".sprintf("%0.2f",$netto),"</td>";
        echo "<td align='right'>".sprintf("%0.2f",($brutto-$netto))."</td>";
        if ($rechnungen[1]) {
            while (list($key,$val) = each($tax[4])) {
                echo "<td align='right'>".sprintf('%0.2f',$val['rate'])."</td>";
            }
            if ($tax[2]) while (list($key,$val) = each($tax[2])) {
                echo "<td align='right'>".sprintf('%0.2f',$val['rate'])."</td>";
            }
        }
        echo "</tr>";
        echo "</table>";
        echo "<table>";
        if ($tax[0]) foreach ($tax[0] as $key=>$mwst) {
            echo "<tr><td colspan=2>Inland $key%: </td><td align='right'>".sprintf("%0.2f",$mwst['amount'])."</td><td align='right'>".sprintf("%0.2f",$mwst['rate'])."</td></tr>";
        };
        if ($tax[2]) foreach ($tax[2] as $key=>$mwst) {
            echo "<tr><td colspan=2>EU ohne UStID $key%: </td><td align='right'>".sprintf("%0.2f",$mwst['amount'])."</td><td align='right'>".sprintf("%0.2f",$mwst['rate'])."</td></tr>";
        };
        echo "<tr><td colspan=2>EU mit UStID : </td><td align='right'>".sprintf("%0.2f",$nettobrutto1)."</td><td></td></tr>";
        echo "<tr><td colspan=2>Ausland : </td><td align='right'>".sprintf("%0.2f",$nettobrutto3)."</td><td></td></tr>";
        echo "<tr><td colspan=2>Brutto==Netto: (?)</td><td>".$nettobrutto."</td><td></td></tr>";
        echo "</table>";
        fclose($f);
        echo '<a href="tmp/deb.csv">csv</a>';
        //echo "<pre>";print_r($tax);echo "</pre>";
   } else {
        echo "Keine Treffer";
   }
} else {
    $sql = 'SELECT accounting_method FROM defaults';
    $rs  = $GLOBALS['db']->getOne($sql);
    if ($rs['accounting_method'] == 'cash') {
        $ist  = 'checked';
        $soll = '';
    } else {
        $ist  = '';
        $soll = 'checked';
    }
    $aktjahr = date('Y');
    $options = '';
    for ($j = 0; $j<5; $j++) $options .= '<option value="'.($aktjahr-$j).'">'.($aktjahr-$j);
?>
<form name="ustva" action="eur.php" method="post">
<table>
<tr><td>Jahr</td><td><select name="jahr"><?php echo $options; ?></select></td></tr>
<tr><td>Quartal</td><td><select name="quartal">
<option value=""><option value="1">1<option value="2">2<option value="3">3<option value="4">4
</select></td></tr>
<tr><td>Monat</td><td>von:<select name="monatvon">
<option value=""><option value="1">1<option value="2">2<option value="3">3<option value="4">4
<option value="5">5<option value="6">6<option value="7">7<option value="8">8<option value="9">9
<option value="10">10<option value="11">11<option value="12">12
</select>
 bis:<select name="monatbis">
<option value=""><option value="1">1<option value="2">2<option value="3">3<option value="4">4
<option value="5">5<option value="6">6<option value="7">7<option value="8">8<option value="9">9
<option value="10">10<option value="11">11<option value="12">12
</select></td></tr>
<tr><td>Besteuerung</td><td><input type="radio" name="istsoll" value="1" <?php echo $ist; ?>>ist 
                            <input type="radio" name="istsoll" value="0" <?php echo $soll; ?>>soll</td></tr>
<tr><td>Steuerzone</td><td><input type="radio" name="tz" value="0" checked>Inland <input type="radio" name="tz" value="1">EU mit ID <input type="radio" name="tz" value="2">EU ohne ID <input type="radio" name="tz" value="3">Ausland <input type="radio" name="tz" value="-1" checked>Alle</td></tr>
<tr><td>Umsätze</td><td><input type="radio" name="vkek" value="1" checked>Verkäufe <input type="radio" name="vkek" value="2">Einkäufe <input type="radio" name="vkek" value="3">beides</td></tr>
</table>
<input type="submit" name="ok" value="erzeugen">
</form>
<?php };
echo $menu['end_content'];
?>
</body>
</html>
