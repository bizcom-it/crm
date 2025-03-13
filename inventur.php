<?php
    require_once("inc/stdLib.php");
    include_once('inc/katalog.php');
    $link = "";
    $menu =  $_SESSION['menu'];
    $head = mkHeader();
?>
<html>
    <head><title></title>
<?php
echo $menu['stylesheets'];
echo $menu['javascripts'];
echo $head['CRMCSS'];
echo $head['THEME'];
echo $head['JQDATE'];
?>
    <script type='text/javascript' src='inc/help.js'></script>
    <script>
        $(function() {
            $( "#datum" ).datepicker($.datepicker.regional[ "de" ]);
        });
    </script>
</head>
<body>
<?php
 echo $menu['pre_content'];
 echo $menu['start_content'];       
 echo '<p class="listtop">Inventur-/Bestandsliste</p>';
if ( $_POST["erstellen"]=="erstellen" ) {
   $artikel = getLager($_POST);
   $art     = $_POST['art'];  //Inventur oder Bestand
   $vorlage = prepTex($art,false);
   if (file_exists('tmp/'.$art.'.pdf')) unlink('tmp/'.$art.'.pdf');
   if (file_exists('tmp/'.$art.'.tex')) unlink('tmp/'.$art.'.tex');
   if (file_exists('tmp/'.$art.'.aux')) unlink('tmp/'.$art.'.aux');
   if (file_exists('tmp/'.$art.'.log')) unlink('tmp/'.$art.'.log');
   if (file_exists('tmp/tabelle.tex')) unlink('tmp/tabelle.tex');
   $suche   = array('&','_','"','!','#','%');
   $ersetze = array('\&','\_','\"',' : ','\#','\%');
   if ($artikel)  {
        $pg     = $artikel[0]['partsgroup_id'];
        $pgname = $artikel[0]['partsgroup'];
        $qty = 0;
        if ($_POST['wg'] == 1) {
            $fname = $art.'_'.$pg;
        } else {
            $fname = $art;
        }    
        $f   = fopen('tmp/'.$art.'.tex','w');
        $pre = preg_replace("/<%partsgroup%>/i",$artikel[0]['partsgroup'],$vorlage['pre']);
        $pre = preg_replace("/<%datum%>/i",date('d.m.Y'),$pre);
        $rc  = fputs($f,$pre);
        $gesamtsumme = 0;
        $pgsumme     = 0;
        foreach($artikel as $part) {
            //print_r($part); echo "<br>";
            if ($pg != $part['partsgroup_id'] AND $_POST['wg'] == 1) {   // Wechsel der WG und Einzeldruck
                $line = preg_replace("/<%gesamtsumme%>/i",sprintf('%0.2f',$pgsumme),$vorlage['post']);
                $rc   = fputs($f,$line);
                fclose($f);
                if (file_exists('tmp/'.$fname.'.pdf')) unlink('tmp/'.$fname.'.pdf');
                closeinventur($art,$fname);  // Bericht erstellen
                if ( file_exists("tmp/$fname.pdf") )
                    echo "<a href='tmp/$fname.pdf'>WG ".$pgname."</a><br />";
                $pgsumme = 0;
                $pg      = $part['partsgroup_id'];
                $pgname  = $part['partsgroup'];
                $fname   = $art.'_'.$pg;
                $f       = fopen('tmp/'.$art.'.tex','w');  //Neues File beginnen
                $pre     = preg_replace("/<%partsgroup%>/i",$part['partsgroup'],$vorlage['pre']);
                $rc      = fputs($f,$pre);
            }
            $line = $vorlage['artikel'];
            $ep   = 1;
            $qty  = 0;
            foreach ($part as $key=>$val) {
                if ( $key == 'description' ) $val = str_replace($suche,$ersetze,$val);
                if ( $key == 'partnumber' )  $val = str_replace($suche,$ersetze,$val);
                if ( $key == 'ep' )        {
                                              $ep = $val;
                                              $val = sprintf('%0.2f',$val);
                                           }
                if ( $key == 'bestand' )   {
                                              $qty = $val * 1;
                                              if ( floor($qty) == $qty ) {
                                                   $val = sprintf('%7d',$qty);
                                              } else {
                                                   while (substr($val,-1) == '0') { $val = substr($val,0,-1); }
                                                   while (strlen($val) < 7 ) { $val = ' '.$val; };
                                              }
                                              if ($_POST['bestand'] != '1' and $art == 'inventur') $val = '';
                                           };
                $line = preg_replace("/<%$key%>/i",$val,$line);
            };
            $summe        = sprintf('%0.2f',$qty*$ep);
            $gesamtsumme += $qty*$ep;
            $pgsumme     += $qty*$ep;
            $line = preg_replace("/<%summe%>/i",$summe,$line);
            $qty ++;
            $rc = fputs($f,$line);
        }
        $line = preg_replace("/<%gesamtsumme%>/i",sprintf('%0.2f',$gesamtsumme),$vorlage['post']);
        $rc = fputs($f,$line);
        fclose($f); 
        closeinventur($art,$fname);
        if ($_POST['wg'] != 1) {
            if ( file_exists("tmp/$fname.pdf") )
                echo '<a href="tmp/'.$art.'.pdf" target="_blank">Liste</a>';
        }    
   } else {
      echo "Kein Artikel gefunden";
   }
} else {
   $orte    = getLagerOrte();
   $options = "";
   $ort     = "";
   if ($orte) foreach ($orte as $row) {
      if ($row["ort"] != $ort) {
           $options .= '<option value="_'.$row['warehouse_id'].'">'.$row['ort'].' Gesamt';
           $ort = $row['ort'];
      }
      $options .= '<option value="'.$row['id'].'">'.$row['ort'].' '.$row['platz'];
}
?>
<form name="inventur" action="inventur.php" method="post">
<input type='radio' name='art' value='inventur' checked>Inventurliste <br>
<input type='radio' name='art' value='bestand'>Bestandsliste ab: <input type='text' name='datum' size='10' id='datum'><br />
Sortierung nach <input type="radio" name="sort" value="partnumber" checked>Artikelnummer <input type="radio" name="sort" value="description">Artikelname<br />
Jede Warengruppe auf ein neue Seite <input type="checkbox" name="wg" value="1"><br />
Dienstleistungen ausgeben <input type="checkbox" name="dienstl" value="1"><br />
Erzeugnisse ausgeben <input type="checkbox" name="erzeugnis" value="1"><br />
Ist-Bestand ausgeben <input type="checkbox" name="bestand" value="1"><br />
<select name="lager"><option value="0">Gesamtbestand
<?php echo $options; ?>
</select><br />
<input type="submit" name="erstellen" value="erstellen">
</form>
<?php }; echo $menu['end_content']; ?>
</body>
</html>
