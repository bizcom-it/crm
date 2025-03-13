<?php
    require_once("inc/stdLib.php");
    include_once('inc/katalog.php');
    $menu = $_SESSION['menu'];
    $head = mkHeader();
    $sql = "select count(*) from  information_schema.tables WHERE table_name = 'inventurdata'";
    $rs  = $GLOBALS['db']->getOne($sql);
?>
<html>
<head><title></title>
<?php echo $menu['stylesheets']; 
      echo $menu['javascripts']; 
      echo $head['CRMCSS']; 
      echo $head['THEME']; 
      echo $head['JQDATE']; ?>
    <script>
        $(function() {
            $( "#budatum" ).datepicker($.datepicker.regional[ "de" ]);
        });
    </script>

<body>
<?php
echo $menu['pre_content'];
echo $menu['start_content'];
echo '<p class="listtop">Inventurdaten Erfassungsprotokoll</p>';
if ($_POST['ok'] == 'suchen' && $rs['count']>0 ) {
    $where = 'WHERE 1=1 ';
    if ( $_POST['datum'] != '' ) {
        $tmp = split('\.',$_POST['datum']);
        if ( count($tmp) == 3 ){
            $date = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];
            $where .= " AND to_char(i.itime,'YYYY-MM-DD') like '$date' ";
        }
    };
    if ( $_POST['lager'] != '0' ) {
        $tmp = explode(':',$_POST['lager']);
        $wh = $tmp[0];
        $bin = $tmp[1];
        echo getLagername($wh,$bin);
        echo " Lagerort: $wh Lagerplatz: $bin";
        $ort = false;
        $where .= ' AND i.bin_id='.$bin;
    } else { 
        $ort = '<td>Lager-Platz</td>';
    };
    echo " Datum: $date";
    $sql  = "SELECT p.partnumber,p.description,e.login,i.* ";
    $sql .= "FROM inventurdata i LEFT JOIN parts p ON p.id=i.parts_id ";
    $sql .= "LEFT JOIN employee e ON e.id=i.employee $where";
    $artikel = $GLOBALS['db']->getAll($sql);
    if ($artikel) {
?>
      <form name="inventur" action="inventurdata.php" method="post">
      <br />
      <table>
      <tr><td>Artikelnummer</td><td>Beschreibung</td><td>Benutzer</td><?php echo $ort; ?><td>Altbestand</td><td>Neubestand</td><td>Datum</td></tr>
<?php $last = 0; foreach ($artikel as $part) { 
         echo "<tr>";
         echo "<td>".$part['partnumber']."</td><td>".$part['description']."</td><td>".$part['login']."</td>"; 
         if ( $ort )  echo "<td>".$part['bin_id']."</td>";
         echo "<td>".$part['alt']."</td><td>".$part['neu']."</td><td>".$part['itime']."</td>"; 
         echo "</tr>";
      };
?>
      </table>
<?php   
   } else {
      echo "Keine Daten für das Datum gefunden: ".$_POST['datum']; 
   }
}  // endif suche 
if ( $rs['count']>0 ) {
   $orte = getLagerOrte();
   $Ooptions = "";
   if ($orte) foreach ($orte as $row) {
      $Ooptions .= '<option value="'.$row['warehouse_id'].":".$row['id'].'">'.$row['ort'].' '.$row['platz'].":".$row['id'];
   };
?>
<br />
<form name="inventurs" action="inventurdata.php" method="post" <?php echo $js ?>>
    <table>
    <tr><td>Datum:</td><td><input type="text" size="10" name="datum"> Immer TT.MM.JJJJ, Platzhalter % und _ erlaubt: %.01.201_</td></tr>
    <tr><td>Lager:</td><td>
               <select name="lager">
               <option value='0'>alle
<?php echo $Ooptions; ?>
               </select></td></tr>
    </table>
    <input type="submit" name="ok" value="suchen">
</form>
<?php 
} else {
    echo "Keine Protokolldaten-Tabelle installiert";
}
echo $menu['end_content'];
 ?>
</body>
</html>
