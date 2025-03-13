<?php
//mb_internal_encoding('UTF-8');
require_once('inc/stdLib.php');
include_once('inc/UserLib.php');
require_once('inc/crmLib.php');
include_once('Mail.php');
include_once('Mail/mime.php');
mb_internal_encoding($_SESSION['charset']);
$offset  = ($_GET['offset'])?$_GET['offset']:1;
$mime    = new Mail_Mime("\n");
$mail    = & Mail::factory('mail');
$headers = $_SESSION['headers'];
$user    = getUserStamm($_SESSION['loginCRM']);
$mail->_params='-f '.$user['email'];
$subject = $headers['Subject'];
$betreff = $_SESSION['Subject'];
$bodytxt = $_SESSION['bodytxt'];
$limit   = ($_SESSION['limit'])?$_SESSION['limit']:50;
$abs     = $headers['Return-Path'];
if (isset($_SESSION['logmail']) && $_SESSION['logmail']) $f = fopen('log/maillog.txt','a');
$dateiname = $_SESSION['dateiname'];
if ($dateiname) {
	$ftmp     = fopen('./dokumente/'.$_SESSION['dbname'].'/'.$_SESSION['login'].'/SerMail/'.$dateiname,'rb');
	$filedata = fread($ftmp,filesize('./dokumente/'.$_SESSION['dbname'].'/'.$_SESSION['login'].'/SerMail/'.$dateiname));
	fclose($ftmp);
	$mime->addAttachment($filedata, $_SESSION['type'],$_SESSION['dateiname'], false );
}
$sql    = "select * from tempcsvdata where uid = '".$_SESSION['loginCRM']."' and id < -1 order by id asc";
$data   = $GLOBALS['db']->getAll($sql);
$felder = explode(":",$data[0]['csvdaten']);
$pemail = array_search("EMAIL",$felder);
$cid    = array_search("ID",$felder);
$pkont  = array_search("KONTAKT",$felder);
$sql    = "select * from tempcsvdata where uid = '".$_SESSION['loginCRM']."' order by id offset ".$offset." limit ".$limit;
$csv    = $GLOBALS['db']->getAll($sql);
preg_match_all('/%([A-Z0-9_]+)%/U',$bodytxt,$ph, PREG_PATTERN_ORDER);
$ph     = array_slice($ph,1);

if ($csv) {
	$bodytxt  = strip_tags($bodytxt);
	if ( $_GET['offset']==1 ) { // Einmal beim Absender hinterlegen
        $data['CRMUSER'] = $_SESSION['loginCRM'];
		$data['CID']     = $_SESSION['loginCRM'];		
		$data['cause']   = $betreff.'|Serienmail';
		$data['c_long']  = $bodytxt."\n$dateiname";
        if ( $_SESSION['CC'] <> '' ) $data['c_long'] .= "\nCC: ".$_SESSION['CC'];
		$data['kontakt'] = 'M';
		$data['bezug']   = 0;
        $data['inout']   = 'o';
        $data['DateiID'] = $_SESSION['dateiId'];
		insCall($data,false);
		$_GET['first']   = 0; //???
    };
	$insdata['CRMUSER'] = $_SESSION['loginCRM'];
	$insdata['cause']   = 'Sermail: '.$betreff;
	$insdata['kontakt'] = 'm';
	$insdata['bezug']   = 0;
	$insdata['DateiID'] = $_SESSION['dateiId'];
	$insdata['status']  = 1;
	$insdata['inout']   = 'o';
	$insdata['DCaption']= $betreff;
	foreach ($csv as $row) {
		$to   = '';
		$tmp  = explode(':',$row['csvdaten']);
		$text = $bodytxt;
		if ( $tmp[$pemail]=='' ) continue;
		if ( $tmp[$pkont]<>'' and $tmp[$pemail]<>'' ) {
            $Name = mb_encode_mimeheader($tmp[$pkont], $_SESSION['charset'], 'Q', '');
			$to   = $Name.' <'.$tmp[$pemail].'>';
		} else {
			$to   = $tmp[$pemail];
		}
		if ( $to<>'' ) {
			//if ( $ph ) {
				if ( $ph[0] ) { foreach ( $ph as $x ) {
					foreach ( $x as $u ) {
						$p = array_search($u,$felder);
						if ( $p!==false ) {
							$y    = $tmp[$p];
							$text = str_replace('%'.$u.'%',$y,$text);
						} else {
							$text = str_replace('%'.$u.'%','',$text);
						}
					}
				}};
			//};
			$mime->setTXTBody($text);
            $body = $mime->get(array('text_encoding'=>'quoted-printable','text_charset'=>$_SESSION['charset']));
			$hdr  = $mime->headers($headers);
			$rc   = $mail->send($to, $hdr, $body);
			if ( $rc && $row['id']>0 ) {
				$insdata['c_long'] = $text."\nAbs: ".$abs;
				//if ($dateiname) $data['c_cause'].="\nDatei: ".$_SESSION['loginCRM'].'/'.$dateiname;
				$insdata['CID']     = $tmp[$cid];
				$insdata['Zeit']    = date('H:i');
				$insdata['Datum']   = date('d.m.Y');
				$stamm=false;
				// Einträge in den Kontaktverlauf
				if ( isset($insdata['CID']) && ($insdata['CID'] > 0) ) $ri = insCall($insdata,false);
				if ( isset($_SESSION['logmail']) && $_SESSION['logmail'] ) fputs($f,date('Y-m-d H:i').";ok;$to;$abs;S:$betreff\n");
			} else {
				if ( isset($_SESSION['logmail']) && $_SESSION['logmail'] ) fputs($f,date('Y-m-d H:i').";error;$to;$abs;S:$betreff\n");
			} // if $rc
		} //if to
	} // foreach
	header('location: sendsermail.php?offset='.($offset+$limit));
} else {
    /* Was soll das??
	if ($dateiname) {
		$ok=chkdir($_SESSION['loginCRM']);
       	copy('./dokumente/'.$_SESSION['dbname'].'/'.$_SESSION['loginCRM'].'/SerMail/$dateiname','./dokumente/'.$_SESSION['dbname'].'/'.$_SESSION['loginCRM'].'/'.$dateiname);
	}; */
?>
	<center>
	Keine weiteren Mails.<br>
	<a href='javascript:self.close()'>Sie k&ouml;nnen das Fenster 
	jetzt schie&szlig;en</a>
	</center>
	
<?php } ?>
