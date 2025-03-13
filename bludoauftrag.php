<html>
<body>
Start<br>
<?php
    function ende($ausgabe,$close=false) {
        echo "<h2>$ausgabe</h2>";
        if ( !$close ) {
            echo "<a href='javascript:self.close()'>Schließen</a>";
        } else {
            echo "<script language='JavaScript'>setTimeout(function(){ self.close(); },10000);</script>";
        }
    }
    require_once("inc/stdLib.php");
    require_once("UserLib.php");
    include_once('Mail.php');
    include_once('Mail/mime.php');
    $user  = getUserStamm($_SESSION['loginCRM']);
    $abs   = $user['email'];
    if ( $abs == '' ) {
       ende('Keine Absender-EMail eingetragen');
       exit(1);
    };
    $dateiname = '';
    $to    = '';
    $ID    = $_GET['id'];
    $sqlA  = "SELECT * FROM oe WHERE id = ".$ID;
    $sqlP  = "SELECT partnumber,o.description,qty,o.unit,o.sellprice FROM orderitems o left join parts p on p.id=o.parts_id WHERE trans_id = ".$ID." ORDER BY position";
    $oe    = $GLOBALS['db']->getOne($sqlA);
    if ( $oe ) {
        $lieferant = $oe['shipvia'];
        $sqlV      = "SELECT * FROM vendor WHERE name ILIKE '".$lieferant."'";
        $auftrag   = $oe['ordnumber'];
        $adatum    = $oe['transdate'];
        $cid       = $oe['customer_id'];
        $vendor    = $GLOBALS['db']->getOne($sqlV);
        if ( $vendor['cc'] != '' ) { $to  = $vendor['cc']; }
        else { ende("Keine E-Mail Adresse beim Lieferanten: $lieferant hinterlegt"); exit(1); };
        $parts     = $GLOBALS['db']->getAll($sqlP);
        $dateiname = "tmp/$auftrag.txt";
        $f = fopen($dateiname,'w');
	foreach ( $parts as $part ) {
	    $description = explode("\n",$part['description']);
	    fputs($f,sprintf("%-10s%10s%-10s%'010d%-10s%10s%-50s\r\n",$auftrag,$adatum,$part['partnumber'],$part['qty'],$part['unit'],$part['sellprice'],$description[0]));
	    if ( count($description) > 1 ) {
               for ( $i=1; $i<=count($description); $i++) {
                   echo "!".$description[$i]."!";
                   if ( trim($description[$i]) != '' ) { 
                       fputs($f,sprintf("%-10s%10s          0000000000                    %-50s\r\n",$auftrag,$adatum,$description[$i]));
                   }
               }
            };
        };
        fclose($f);
        $rc = true;
    }
    if ( $rc and file_exists($dateiname) ) {
        echo "erstelle E-Mail<br>";
        $mime = new Mail_Mime("\n"); 
        $mail =& Mail::factory('mail');
        $Subject = "Auftragsdaten $auftrag";
        $SubjectMail = mb_encode_mimeheader($Subject, $_SESSION['charset'] , 'Q', '');
        $headers=array(
                    'Return-Path' => $abs,
                    'Reply-To'    => $abs,
                    'From'        => $abs,
                    'X-Mailer'    => 'PHP/'.phpversion(),
                    'Subject'     => $SubjectMail);
        $BodyText = 'Im Anhang finden sie die Auftragsdaten als Textdatei';
        $mime->setTXTBody(strip_tags($BodyText));
        $mime->addAttachment($dateiname,'application/text',"$auftrag.txt");
        $body = $mime->get(array('text_encoding'=>'quoted-printable','text_charset'=>$_SESSION['charset']));
        $hdr  = $mime->headers($headers);
        $mail->_params='-f '.$abs;
        $rc   = $mail->send($to, $hdr, $body);
        if ( $rc ) {
            ende('E-Mail versendet',true);
        } else {
            ende('E-Mail konnte nicht versendet werden');
        }
        /*header("Location: $dateiname");
        header('Content-type: application/text');
        header('Content-Disposition: attachment; filename="'.$auftrag.'.txt"');
        readfile($dateiname);*/
    } else {
        ende('Es ist ein Fehler aufgetreten');
        exit(1);
    };
?>
</body>
</html>
