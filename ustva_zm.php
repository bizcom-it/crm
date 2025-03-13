<?php
    require_once("inc/stdLib.php");
    $menu =  $_SESSION['menu'];
    $head = mkHeader();
?>
<html>
    <head><title>ok - Zentrale Meldung</title>
<?php
echo $menu['stylesheets'];
echo $menu['javascripts'];
echo $head['CRMCSS'];
echo $head['THEME'];
echo $head['JQDATE'];
?>    
</head>
<body>
<?php
 echo $menu['pre_content'];
 echo $menu['start_content'];
 echo '<p class="listtop">Zentrale Meldung</p>';
 
if ( $_POST["ok"]=="erzeugen" ) {
    $istsoll = "SELECT accounting_method,co_ustid from defaults";
    $rs      = $GLOBALS['db']->getOne($istsoll);
    $ist     = ($rs['accounting_method'] == 'cash')?true:false;
    if ( $ist ) {
		$bezug = 'datepaid';
    } else {
		$bezug = 'transdate';
    }
    $USTID = $rs['co_ustid'];
	$sqlar = 'SELECT ar.id,invnumber,transdate,datepaid,paid,netamount,name,customer.id as cid,'.
             'country,ustid from ar left join customer on customer.id=customer_id where ';
    $sqlar .= 'ar.taxzone_id=1 and ';

	function schaltjahr($jahr) {
		// Funktion noch verbessern?
		if ($jahr % 4 <> 0) return false;
		if ($jahr % 400 == 0 ) return true;
		if ($jahr % 100 <> 0 ) return false;
		return true;
	}

	$Day      = array(0,"31","28","31","30","31","30","31","31","30","31","30","31");
    $zeitraum = 'Jahr '.$_POST["jahr"];
    $monbis   = ''; $monvon = '';
	if ( $_POST["quartal"]<>"" ) {  
        $zeitraum .= ' Quartal '.$_POST["quartal"];
		$start = array(0,"01","04","07","10");
		$stopM = array(0,"03","06","09","12");
		$stopD = array(0,"31","30","30","31");
		$von   = $start[$_POST["quartal"]]."-01";
		$bis   = $stopM[$_POST["quartal"]]."-".$stopD[$_POST["quartal"]];
		$sqlar.= $bezug." >= '".$_POST["jahr"]."-$von' and ".$bezug." <= '".$_POST["jahr"]."-$bis'";
	} else if ( $_POST["monatbis"]<>"" ) {
		if ( $_POST["monatbis"]=="2" ) {
			if ( schaltjahr($_POST["jahr"]) ) {
				$day = 29;
			} else {
				$day = 28;
			}
		} else { 
			$day = $Day[$_POST["monatbis"]];
		};
		$sqlar  = $bezug." >= '".$_POST["jahr"]."-".$_POST["monatvon"]."-01' and ";
		$sqlar .= $bezug." <= '".$_POST["jahr"]."-".$_POST["monatbis"]."-$day";
        $monvon = ' Monat '.$_POST["monatvon"];
        $monbis = ' bis '.$_POST["monatbis"];
	} else if ( $_POST["monatvon"]<>"" ) {
		if ( $_POST["monatvon"]=="2" ) {
			if ( schaltjahr($_POST["jahr"]) ) {
				$day = 29;
			} else {
				$day = 28;
			}
		} else { 
			$day = $Day[$_POST["monatvon"]];
		};
		$sqlar .= $bezug." between '".$_POST["jahr"]."-".$_POST["monatvon"]."-01' and '".$_POST["jahr"]."-".$_POST["monatvon"]."-".$day."'";
        $monvon = ' Monat ab '.$_POST["monatvon"];
	} else {
		$sqlar .= $bezug." between '".$_POST["jahr"]."-01-01' and '".$_POST["jahr"]."-12-31'";
	}
    $zeitraum .= $monvon.$monbis;
	$rs = $GLOBALS['db']->getAll($sqlar);
    echo '<b>'.$zeitraum.' '.$USTID.'</b> ';
	echo ( $ist )?"Istbesteuerung":"Sollbesteuerung";
	echo '<br><table>';
	echo '<tr><td>TransID</td><td>Re-Nr.</td><td>Re-Datum</td><td>Betrag</td><td>Kunde</td><td>Land</td><td>UST-ID</td></tr>';
	$netto  = 0;
	$brutto = 0;
	$linkR   = '../is.pl?action=edit&id=';
	$linkC   = '../ct.pl?action=edit&db=customer&id=';
	if ( $rs ) foreach ( $rs as $row ) {
		if ( $row["amount"]==$row["netamount"] ) {
			$col1 = "<font color='red'>";
			$col2 = "</font>";
		} else {
			$col1 = "";
			$col2 = "";
		};
?>
	<tr><td><a href="<?php echo $linkR.$row['id'] ?>" target="_blank"><?php echo $row['id'] ?></a> </td>
        <td> <?php echo $row['invnumber'] ?> </td>
		<td><?php echo db2date($row["transdate"]) ?></td>
		<td align="right"><?php echo sprintf("%01.2f",$row["netamount"]) ?></td>
		<td><a href="<?php echo $linkC.$row["cid"] ?>" target="_blank"><?php echo $row["name"] ?></a></td>
		<td align="center"><?php echo $row["country"] ?></td>
		<td><?php echo $row["ustid"] ?></td></tr>
<?
		$netto  += $row["netamount"];
	};
	echo "<tr><td colspan='3'>Summe</td><td align='right'>".sprintf("%01.2f",$netto).
         "</td><td align='right'></td></tr>";
	echo "</table>\n";
} else { 
$JAHR = date('Y');
$jahre = '';
for ( $i = -3; $i<1; $i++) $jahre .= '<option value="'.($JAHR+$i).'"'.(($i==0)?' selected':'').'>'.($JAHR+$i);
?>
<form name="ustva" action="ustva_zm.php" method="post">
<table>
<tr><td>Jahr</td><td><select name="jahr"><?php echo $jahre; ?></select></td></tr>
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
</table>
<input type="submit" name="ok" value="erzeugen">
</form>
<?php }
echo $menu['end_content']; ?>
</body>
</html>
