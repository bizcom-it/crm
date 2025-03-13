<?php
    include_once('inc/stdLib.php');
    include_once('inc/UserLib.php');
    include_once("Mail.php");
    include_once("Mail/mime.php");
    define('FPDF_FONTPATH','font/');
    require('fpdf.php');
    $menu =  $_SESSION['menu'];
    $head = mkHeader();    
    $tmpdata = getUserEmployee(array('email'));
    class PDF extends FPDF {
        //Load data
        function LoadData($file)  {
            //Read file lines
            $f = fopen($file,'r');
            $data=array();
            while (($data[] = fgetcsv($f,1000, ";", '"')) !== FALSE ) {};
            fclose($f);
            return $data;
        }

        function BasicTable($data)  {
            //Header
            $this->Cell(20,7,'Menge',1);
            $this->Cell(40,7,'Nummer',1);
            $this->Cell(130,7,'Artikel',1);
            $this->Ln();
            $tmp = array_shift($data);
            $tmp = array_pop($data); // Warum auch immer ein leeres Array am Ende ist.
            //Data
            foreach($data as $row)   {
               if ( substr($row[0],0,3) == '###' ) {
                     $this->MultiCell(190,6,utf8_decode(substr($row[0],3)),0,'L',0);
               } else {
                   $this->Cell(20,6,$row[2],1);
                   $this->Cell(40,6,$row[1],1);
                   $this->Cell(130,6,utf8_decode(substr($row[0],0,70)),1);
               }
               $this->Ln();
           }
        }
        function Titel($txt) {
             $this->Cell(200,10,$txt,0);
             $this->Ln();
        }
    };
    $usr = getAllUser(array(0=>true,1=>"%"));
    if ( $_POST ) {
        $dir = 'tmp/';
        $filecsv = 'packliste.csv';
        $filepdf = 'packliste.pdf';
        $vondatum = $_POST['vondatum'];
        $bisdatum = $_POST['bisdatum'];
        $empfang = $_POST['email'];
        $output = 'Packliste ab: '.$vondatum.' bis '.$bisdatum.'<br>';
        if ( $_POST['read'] != '' ) {
            $tmp  = split('\.',$vondatum);
            $vondate  = $tmp[2].'-'.$tmp[1].'-'.$tmp[0];
            if ( $bisdatum != '' ) {
                $tmp  = split('\.',$bisdatum);
                $bisdate  = "'".$tmp[2].'-'.$tmp[1].'-'.$tmp[0]."'";
            } else {
                 $bisdate = 'now()';
            } 
            $sql  = 'select parts.description,partnumber,sum(qty) as qty ';
            $sql .= 'from invoice left join parts on parts.id=parts_id ';
            //$sql .= 'where trans_id in ';
            $sql .= 'where inventory_accno_id > 0 and  trans_id in '; //Keine Dienstleistungen
            //$sql .= '(SELECT id from ar where  amount > 0 and transdate between \''.$date.'\' and now() ) '; //= \''.$date.'\') ';
            $sql .= '(SELECT id from ar where  transdate between \''.$vondate.'\' and '.$bisdate; // Gutschriften berücksichtigen
            if ( $_POST['user'] > 0 ) {
                $sql .= "AND ".$_POST['srcid']."_id = ".$_POST['user']." ) ";
            } else {
                $sql .= ") ";
            }
            $sql .= 'group by partnumber,parts.description order by qty desc'; //partnumber';
            $rs = $GLOBALS['db']->getAll($sql);
            $line  = "<tr><td><input type='text' size='3'  name='data[qty][]' value='%s'></td>";
            $line .=     "<td><input type='text' size='10' name='data[partnumber][]' value='%s'></td>";
            $line .=     "<td><input type='text' size='70' name='data[description][]' value='%s'></td></tr>\n";
            if ( $rs ) { 
                $output .= '<form name="packliste" action="packliste.php" method="post"><table id="tabelle">'."\n";
                $output .= "<input type='hidden' name='user' value='".$_POST['user']."'>\n";
                $output .= "<input type='hidden' name='datum' value='$datum'>\n";
                $output .= '<tr><th>Menge</th><th>Art-Nr.</th><th>Bezeichnung</th></tr>';
                foreach ( $rs as $row ) {
                    if ( $row['qty']>0 )
                        $output .= sprintf($line,$row['qty'],$row['partnumber'],$row['description']);
                };
                #$output .= sprintf($line,'','','');
                $output .= "</table>\n";
                $output .= '<input type="button" name="zeile" value="Zeilen +" onClick="ZeileEinfuegen();">';
                $output .= ' leere Menge oder 0-Menge werden nicht ausgedruckt<br>'."\n";
                $output .= 'Notizen:<br><textarea name="notiz" cols="80" rows="10" ></textarea><br>';
                $output .= '<input type="submit" name="generate" value="erstelle PDF per: ">';
                $output .= '<input type="radio" name="weg" value="1" checked>download <input type="radio" name="weg" value="2">per E-Mail';
                $output .= '</form>'."\n";
            } else {
                $output .= 'Keine Treffer<br>';
            }
        } else if ( $_POST['generate'] != '' ) {
            $output = '';
            $f = fopen($dir.$filecsv,'w');
            fputs($f,'description;partnumber;qty'."\n");
            $data = $_POST['data'];
            for ( $i = 0; $i < count($data['qty']); $i++) {
                if ( $data['qty'][$i] > 0 )
                    fputs($f,'"'.$data['description'][$i].'";"'.$data['partnumber'][$i].'";'.$data['qty'][$i]."\n");
            };
            if ( $_POST['notiz']    != '' ) { fputs($f,'"###'.$_POST['notiz'].'"'); };
            fclose($f);
            $pdf=new PDF();
            $data=$pdf->LoadData($dir.$filecsv);
            $pdf->SetFont('Arial','',10);
            if ( $_POST['user'] > 0 ) {
                $prtusr = getUserStamm($_POST['user']);
                $usrname = utf8_decode('   Für Fahrzeug: '.( isset($prtusr['name'])?$prtusr['name']:$prtusr['login'] ));
            } else {
                $usrname = '';
            };
            $pdf->AddPage();
            $titelzeile = 'Packliste ab: '.$vondatum;
            if ( $_POST['bisdatum'] == '' ) $titelzeile .= ' bis '.$bisdatum;
            $titelzeile .= $usrname;
            $pdf->SetTitle($titelzeile); //'Packliste ab '.$vondatum.$usrname);
            $pdf->Titel($titelzeile); //'Packliste ab '.$vondatum.$usrname);
            $pdf->SetDrawColor(255, 255, 255);
            $pdf->BasicTable($data);
            $pdf->Output($dir.$filepdf,"F");
            if ( file_exists($dir.$filepdf) ) {
                if ( $_POST['weg'] == 2 ){
                    $headers = array(
                            "From"        => $tmpdata['email'],
                            "X-Mailer"    => "PHP/".phpversion(),
                            "Subject"    => 'Packliste ab '.$datum);
                    $mime = new Mail_Mime(array('eol'=>"\n"));
                    $csv = $mime->addAttachment($dir.$filecsv,mime_content_type($dir.$filecsv),$filecsv);
                    $pdf = $mime->addAttachment($dir.$filepdf,mime_content_type($dir.$filepdf),$filepdf);
                    $mime->setTXTBody('Packliste');
                    $hdrs = $mime->headers($headers);
                    $body = $mime->get();
                    $mail = Mail::factory("mail");
                    $mail->_params = "-f ".$tmpdata['email'];
                    $rc = $mail->send($empfang, $hdrs, $body);
                    $output = 'E-Mail verschickt';
                    #unlink($dir.$filepdf);
                } else {
                    if ( $empfang == '' ) { 
                        $user = getUserStamm($_SESSION["loginCRM"]);
                        $empfang = $user['email'];
                    }
                    header('Content-type: application/pdf');
                    header('Content-Disposition: attachment; filename="packliste.pdf"');
                    readfile($dir.$filepdf);
                    #unlink($dir.$filepdf);
                }
            } else {
                $output .= 'Konnte kein PDF erstellen.<br>';
            };
        } else {
            $output .= 'Keine Treffer<br>';
        };
        $output .= '<br>';
    };
?>
<html>
<head>
<?php 
      echo $menu['stylesheets']; 
      echo $menu['javascripts'];
      echo $head['CRMCSS'];
      //echo $head['JQDATE'];
      echo $head['DATEPICKER'];
      echo $head['THEME'];
?> 
<script language="JavaScript">
        $(function() {
            //$.datepicker.setDefaults(
            //                {monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez'],
            //                 dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
            //                 closeText: 'schließen',
            //                 currentText: 'heute',
            //                 firstDay:1},
            //                $.datepicker.regional["de"]);
            $( "#vondatum" ).datepicker();
            $( "#bisdatum" ).datepicker();
        });
</script>
<script type="text/javascript" src="inc/packliste.js"></script>
<body>
<?php
 echo $menu['pre_content'];
 echo $menu['start_content'];
 echo '<p class="listtop">Packliste</p>';
 echo $output; ?>
<form name="pl" method='post' action='packliste.php'>
Packliste an Hand von VK-Rechnungen erstellen.<br>
Für Fahrzeug von User <select name="user">
    <option value=''>Alle
<?php if ( $usr ) foreach($usr as $tmp) {
    echo "\t<option value='".$tmp['id']."'>".(isset($tmp['name'])?$tmp['name']:$tmp['login'])."\n";
}?></select> <input type='radio' name='srcid' value='employee' checked>ok-Benutzer <input type='radio' name='srcid' value='salesman'>Verkäufer<br>
Ab Datum (Pflichtfeld) :<br>
<input type="text" size="10" name="vondatum" id="vondatum" value="" > tt.mm.jjjj <br>
Bis Datum:<br>
<input type="text" size="10" name="bisdatum" id="bisdatum" value="" > tt.mm.jjjj <br>
abweichender E-Mail Empfänger:
<input type='text' size='30' name='email' id='email' value='<?php echo $tmpdata['email']; ?>'><br>
<input type="submit" name="read" value="erstellen">
</form>
<?php  echo $menu['end_content']; ?>
</body>
</html>
