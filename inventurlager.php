<?php
    require_once("inc/stdLib.php");
    include_once('inc/katalog.php');
    $menu = $_SESSION['menu'];
    $head = mkHeader();
?>
<html>
<head><title></title>
<?php echo $menu['stylesheets']; 
      echo $menu['javascripts']; 
      echo $head['CRMCSS']; 
      echo $head['THEME']; 
      echo $head['JQDATE']; 
?>
    <script type='text/javascript'>
         function getData() {
             document.inventurs.comment.value = document.inventur.comment.value;
             document.inventurs.budatum.value = document.inventur.budatum.value;
             return true;
         }
    </script>
    <script>
        $(function() {
            $( "#budatum" ).datepicker($.datepicker.regional[ "de" ]);
        });
    </script>

<body>
<?php
echo $menu['pre_content'];
echo $menu['start_content'];
echo '<p class="listtop">Lagerumbuchung / Lagerkorrektur</p>';
if ($_POST) {
    $comment = $_POST["comment"];
    if ($_POST['budatum'] != '') {
        $now = $_POST['budatum'];
    } else {
        $now = date('d.m.Y');
    }
    $js = 'onSubmit="return getData();"';
}
if ($_POST['ok'] == 'sichern') {
    $rc = updatePartBin($_POST);
    echo $rc;
} else if ($_POST['ok'] == 'suchen') {
    $tmp = explode(':',$_POST['lager']);
    $wh = $tmp[0];
    $bin = $tmp[1];
    $artikel = getPartBin($_POST['pg'],$_POST['partnumber'],$_POST['obsolete'],$bin);
    echo getLagername($wh,$bin);
    if ($artikel) {
?>
      <form name="inventur" action="inventurlager.php" method="post" onSubmit="return getData();">
      <input type="hidden" name="warehouse" value="<?php echo $wh; ?>">
      <input type="hidden" name="bin"       value="<?php echo $bin; ?>">
      Kommentar: <input type="text"   name="comment" value="<?php echo $comment ?>">
      Datum der Buchung: <input type="text"   name="budatum" id="budatum" value="<?php echo $now; ?>" size="10" >
      <br />
      Transfertype: 
      <input type="radio" name="transtype" value="1" checked>Korrektur
      <input type="radio" name="transtype" value="2">Einlagern / Entnahme
      <input type="radio" name="transtype" value="3">Gefunden / Fehlbestand
      <table>
      <tr><td>Nummer</td><td>Artikel</td><td>Menge</td><td>Chargenumber</td><td>Bestbefore JJJJ-MM-DD</td></tr>
<?php $last = 0; foreach ($artikel as $part) { 
         if ( $last == $part['parts_id'] ) {
             continue;
         }
         $last = $part['parts_id'];
         echo "<tr><td>".$part['partnumber']."</td><td>".$part['partdescription']."</td><td nowrap>"; 
         if ($part['qty'] == '') {
             $qty = '';
         } else {
             $qty = abs($part['qty']);
             if ($part['qty']<0) $qty *= -1;
         };
         $onhand = (abs($part['onhand']<0))?abs($part['onhand'])*-1:abs($part['onhand']);
         $lager = '';
         if ($part['bin_id']) $lager=' *';
         echo '<input type="hidden" name="parts_id[]"     value="'.$part['parts_id'].'">';
         echo '<input type="hidden" name="oldqty[]"       value="'.$part['qty'].'">';
         echo '<input type="text"   name="qty[]"          value="'.$qty.'" size="5">'.$part['partunit'].' ('.$onhand.')</td><td>';
         if ($lager=='') {
            echo '<input type="type" name="chargenumber[]" value=""></td><td>';
         } else { 
            echo '<input type="hidden" name="chargenumber[]" value="'.$part['chargenumber'].'">'.$part['chargenumber'].'</td><td>';
         }
         if ($lager=='') {
             echo '<input type="text" name="bestfefore[]"   value=""></td>';
         } else { 
             echo '<input type="hidden" name="bestfefore[]"   value="'.$part['bestbefore'].'">'.$part['bestbefore'].'</td>';
         };
         echo "<tr>";
      };
?>
      </table>
      <input type="submit" name="ok" value="sichern">
      </form>
<?php   
   } else {
      echo "Artikel nicht gefunden: ".$_POST['partnumber']; 
   }
}  // endif suche 
   $orte = getLagerOrte();
   $pg   = getPartsGroup();
   $Ooptions = "";
   if ($orte) foreach ($orte as $row) {
      $Ooptions .= '<option value="'.$row['warehouse_id'].":".$row['id'].'">'.$row['ort'].' '.$row['platz'];
   };
   $Poptions = "";
   if ($pg) foreach ($pg as $row) {
      $Poptions .= '<option value="'.$row['id'].'">'.$row['partsgroup'];
   };
?>
<br />
<form name="inventurs" action="inventurlager.php" method="post" <?php echo $js ?>>
    <input type="hidden" name="comment" value="<?php echo $comment ?>">
    <input type="hidden" name="budatum" value="<?php echo $now ?>">
    <table>
    <tr><td>Artikelnummer:</td><td><input type="text" size="20" name="partnumber"></td>
        <td rowspan='4' valign='top'>Es können Platzhalter verwendet werden * oder % für beliebig viele Zeichen,<br>
                                     für ein einzelnes Zeichen ? oder _<br>
                                     Es können auch mehrere Artikelnummer (ohne Platzhalter) durch Komma getrennt, angegeben werden.</td></tr>
    <tr><td>Warengruppe:</td><td>
                <select name="pg">
                <option value="">Artikel ohne Warengruppe
<?php echo $Poptions; ?>
                </select></td></tr>
    <tr><td>Lager:</td><td>
               <select name="lager">
<?php echo $Ooptions; ?>
               </select></td></tr>
    <tr><td></td><td>
    <input type='radio' name='obsolete' value='f' checked>Nur gültige <input type='radio' name='obsolete' value='t'>Nur ungültige <input type='radio' name='obsolete' value=''>Alle Artikel</td></tr>
    </table>
    <input type="submit" name="ok" value="suchen">
</form>
<?php 
echo $menu['end_content'];
 ?>
</body>
</html>
