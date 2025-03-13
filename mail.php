<?php
    require_once('inc/stdLib.php');
    //include_once('template.inc');
    include('template.inc');
    include_once('crmLib.php');
    include_once('UserLib.php');
    include_once('Mail.php');
    include_once('Mail/mime.php');

    $aktion = false;
    $TO = $CC = $KontaktTO = $KontaktCC = $MID = $QUELLE = $popup = false;
    $Subject = $BodyText = $header = $msg = false;
    if ( isSetVar($_POST) ) foreach($_POST as $key=>$val) ${$key} = $val;
    if ( isSetVar($_GET) )  foreach($_GET  as $key=>$val) ${$key} = $val;

    $USER = getUserEmployee(array('mailsign','mandsig'));
    if ( $QUELLE != '' ) { 
        $referer   = $QUELLE.'?popup='.$popup;
    } else if ( $aktion == 'domail'  && $TO != '') { 
        $referer=getenv('HTTP_REFERER');
        if (preg_match('/.+\.php/',$referer)) {
            if (preg_match('/firma/',$referer)) {
                $btn='<a href="'.$referer.'"><image src="image/firma.png" alt=".:back:." title=".:back:." border="0" ></a>';
                $hide='hidden';
            } else {
                $referer = substr($referer,0,strpos($referer,'?'));
                $btn='<a href="mail.php"><image src="image/new.png" alt=".:new:." title=".:new:." border="0" ></a>';
                $hide='visible';
            }
        } else { 
            $referer = $_SESSION['baseurl'].'/crm/mail.php';
            $btn='<a href="mail.php"><image src="image/new.png" alt=".:new:." title=".:new:." border="0" ></a>';
            $hide='visible';
        };
        $referer .= '&popup='.$popup;
    } else { 
        $referer = ''; 
        $btn='<a href="mail.php"><image src="image/new.png" alt=".:new:." title=".:new:." border="0" ></a>';
        $hide='visible';
    };
    if ( $aktion == 'tplsave') {
        $rc=saveMailVorlage($_POST);
    } else if ( $aktion == 'sendmail') {
        $okT=true; $okC=true; $okA=true; $msg='';
        if ($TO) {
            $TO=preg_replace( '/[^a-z0-9 !?:;,.\/_\-=+@#$&\*\(\)<>]/im', '', $TO);
            $rc=chkMailAdr($TO); if($rc<>'ok') { $okT=false; $msg='TO:'.$rc; }; 
        };
        if ( $CC) { 
            $CC=preg_replace( '/[^a-z0-9 !?:;,.\/_\-=+@#$&\*\(\)<>]/im', '', $CC);
            $rc=chkMailAdr($CC); if($rc<>'ok') { $okC=false; $msg.=' CC:'.$rc; }; 
        };
        if ($TO == '' && $CC == '') {$okT=false; $msg='Kein Empf&auml;nger';};
        $user=getUserStamm($_SESSION['loginCRM']);
        // geht hier nicht ums Konvertieren, sonder ums Quoten!
        mb_internal_encoding($_SESSION['charset']);
        $Name = mb_encode_mimeheader($user['name'], $_SESSION['charset'], 'Q', '');
        $zeichen = 'a-z0-9 ';
        if (preg_match('/[$zeichen]*[^$zeichen]+[$zeichen]*/i',$Name)) $Name = '"'.$Name.'"';
        if ( $user['email'] != '' ) {
            $abs = $Name.' <'.$user['email'].'>';
            $rc = chkMailAdr($user['email']); if($rc<>'ok') { $okA=false; $msg.=' Abs:'.$rc; };
        } else if ( $_SESSION['email'] != '' ) {
            $abs = $Name.' <'.$_SESSION['email'].'>';
            $rc = chkMailAdr($_SESSION['email']); if($rc<>'ok') { $okA=false; $msg.=' Abs:'.$rc; };
        } else {
            $okA=false; $msg.=' Kein Absender';
        }
        if ( $okT && $okC && $okA ) {
            $mime = new Mail_Mime("\n");
            $mail =& Mail::factory('mail');
            $Subject = preg_replace( '/(content-type:|bcc:|cc:|to:|from:)/im', '', $Subject);
            $SubjectMail = mb_encode_mimeheader($Subject, $_SESSION['charset'] , 'Q', '');
            $headers=array( 
                    'Return-Path'    => $abs,
                    'Reply-To'    => $abs,
                    'From'        => $abs,
                    'X-Mailer'    => 'PHP/'.phpversion(),
                    'Subject'    => $SubjectMail);
            $to=($TO)?$TO:$CC;
            if ((strpos($to,',')>0)) {
                $tmp=explode(',',$to);
                $to=array();
                foreach ($tmp as $row) { $to[]=trim($row); }
            }
            if ($TO && $CC) {
                if ( isSetVar($bcc) ) {
                    $headers['Bcc'] = $CC;
                } else {
                    $headers['Cc']=$CC;
                };
            };
            $BodyText = preg_replace( '/(content-type:|bcc:|cc:|to:|from:)/im', '', $BodyText);
            $mime->setTXTBody(strip_tags($BodyText));
            $anz=($_FILES['Datei']['name'][0]<>'')?count($_FILES['Datei']['name']):0;
            $anh=false;
            if ($anz>0) {
                for ($o=0; $o<$anz; $o++) {
                    if ($_FILES['Datei']['name'][$o]<>'') {
                        //move_uploaded_file($_FILES["Datei"]["tmp_name"][$o],$tmpdir.$_FILES["Datei"]["name"][$o]);
                        copy($_FILES['Datei']['tmp_name'][$o],'tmp/'.$_FILES['Datei']['name'][$o]);
                        $mime->addAttachment('tmp/'.$_FILES['Datei']['name'][$o] , $_FILES['Datei']['type'][$o],
                                                $_FILES['Datei']['name'][$o]);
                        unlink ('tmp/'.$_FILES['Datei']['name'][$o]);
                        $anh=true;
                    }
                }
            } else {
                $headers['Content-Type'] = 'text/plain; charset='.$_SESSION['charset'];
            }
            
            $body = $mime->get(array('text_encoding'=>'quoted-printable','text_charset'=>$_SESSION['charset']));
            $hdr = $mime->headers($headers);
            $mail->_params='-f '.$user['email'];
            $rc=$mail->send($to, $hdr, $body);
            if ($_SESSION['logmail']) {
                $f=fopen('log/maillog.txt','a');
                if ($rc) {
                    fputs($f,date('Y-m-d H:i').';ok;'.$TO.';'.$CC.';'.$user['name'].' <'.$user['email'].'>;'.$Subject.";\n");
                } else {
                    fputs($f,date('Y-m-d H:i').';error;'.$TO.';'.$CC.';'.
                                    $user['name'].' <'.$user['email'].'>;'.$Subject.
                                    ';'.PEAR_Error::getMessage()."\n");
                }
            }
            if ($rc) {
                if (!$anh) { $_FILES=false; };
                $data['CRMUSER']=$_SESSION['loginCRM'];
                $data['cause']=$Subject;
                $data['c_cause']=$BodyText."\nAbs: ".$user['name'].' <'.$user['email'].'>';
                if (! isSetVar($KontaktTO) or $KontaktTO == '') {
                    //Aufruf erfolgte nicht aus Kundenmaske
                    //Hoffentlich ist die E-Mail nur einmal vergeben.
                    //Suche erfolgt zuerst in customer, dann vendort und control
                    //Der erste Treffer wird genommen.
                    if ($TO) {
                        $tmp = getSenderMail($_POST['TO']);
                        $KontaktTO = $tmp['kontakttab'].$tmp['kontaktid'];
                    } else {
                        //Wenn kein TO, dann ist aber CC
                        $tmp = getSenderMail($CC);
                        $KontaktTO = $tmp['kontakttab'].$tmp['kontaktid'];
                    }
                }
                $data['Kontakt']='M';
                $data['Bezug']=0;
                $data['Zeit']=date('H:i');
                $data['Datum']=date('d.m.Y');
                $data['DateiID']=0;
                $data['Status']=1;
                $data['inout']='o';
                $data['DCaption']=$Subject;
                $stamm=false;
                if ($KontaktTO!='') {
                    $data['Q']=$KontaktTO[0];
                    if ($data['Q']=='C' || $data['Q']=='V') {
                        include('inc/FirmenLib.php');
                        $empf=getFirmenStamm(substr($KontaktTO,1),true,substr($KontaktTO,0,1));
                        $data['fid']=$empf['id'];
                        $data['CID']=$empf['id'];
                        $data['nummer']=$empf['nummer'];
                    } else {
                        include('inc/persLib.php');
                        $empf=getKontaktStamm(substr($KontaktTO,1));
                        $data['fid']=$empf['cp_cv_id'];
                        $data['CID']==$empf['cp_id'];
                        $data['nummer']=$empf['nummer'];
                    };        
                    // Eintr�ge in den Kontaktverlauf
                    if ($KontaktTO && substr($KontaktTO,0,1)<>'E'){
                        $data['CID']=substr($KontaktTO,1);
                        insCall($data,$_FILES);
                        $stamm=true;
                    }
                    if ($KontaktCC && !substr($KontaktCC,0,1)<>'E'){
                        $data['CID']=substr($KontaktCC,1);
                        insCall($data,$_FILES);
                        $stamm=true;
                    }
                }
                if (!$stamm) {
                    $data['CID']=$_SESSION['loginCRM'];        // Dann halt beim Absender in den Thread eintragen
                    $data['cause']=$Subject.'|'.$TO;
                    insCall($data,$_FILES);
                }
                $TO=''; $CC=''; $msg='Mail versendet';
                $Subject=''; $BodyText='';
                if ($QUELLE) header('Location: '.$referer);
            } else {
                $msg='Fehler beim Versenden '.PEAR_Error::getMessage ();
                //$TO=$_POST['TO']; $CC=$_POST['CC']; $msg='Fehler beim Versenden '.PEAR_Error::getMessage ();
                //$Subject=$_POST['Subject']; $BodyText=$_POST['BodyText'];
            }
        } else {
            $Subject=preg_replace( '/(content-type:|bcc:|cc:|to:|from:)/im', '', $Subject);
            $BodyText=preg_replace( '/(content-type:|bcc:|cc:|to:|from:)/im', '', $BodyText);    
        }
    } else {    
        $user=getUserStamm($_SESSION['loginCRM']);
        $BodyText='';// \n".$MailSign;
    }
    switch ($USER['mandsig']) {
        case '0' :  $MailSign  = $USER['mailsign'];
                    break;
        case '1' :  $MailSign  = $USER['msignature'];
                    break;
        case '2' :  $MailSign  = $USER['msignature'];
                    $MailSign .= "\n".$USER['mailsign'];
                    break;
        case '3' :  $MailSign  = $USER['mailsign'];
                    $MailSign .= "\n".$USER["msignature"];
                    break;
        default  :  $MailSign  = $USER["mailsign"];
    }
    $MailSign=str_replace("\n","<br>",$MailSign);
    $MailSign=str_replace("\r",'',$MailSign);
    $t = new Template($base);
    $menu =  $_SESSION['menu'];
    $head = mkHeader();

    if ( $popup ) {
        $t->set_var(array(
            'STYLESHEETS'   => $menu['stylesheets'],
            'JAVASCRIPTS'   => $menu['javascripts'],
            'CRMCSS'        => $head['CRMCSS'],
            'THEME'         => $head['THEME'],
            'AUTOCOMPLETE'  => $head['AUTOCOMPLETE'],
        ));
        $hide = 'hidden';
    } else {
        $t->set_var(array(
            'STYLESHEETS'   => $menu['stylesheets'],
            'JAVASCRIPTS'   => $menu['javascripts'],
            'PRE_CONTENT'   => $menu['pre_content'],
            'START_CONTENT' => $menu['start_content'],
            'END_CONTENT'   => $menu['end_content'],
            'CRMCSS'        => $head['CRMCSS'],
            'THEME'         => $head['THEME'],
            'AUTOCOMPLETE'  => $head['AUTOCOMPLETE'],
        ));
    }
    $t->set_file(array('mail' => 'mail.tpl'));
    $t->set_block('mail','Betreff','Block');
    $mailvorlagen=getMailVorlage();
    if ($mailvorlagen) foreach ($mailvorlagen as $vorlage) {
        $t->set_var(array(
            'MID'    => $vorlage['id'],
            'CAUSE'  => $vorlage['cause'],
            'C_LONG' => $vorlage['c_long']
        ));
        $t->parse('Block','Betreff',true);
    }
    $tmpdata = getUserEmployee(array('feature_ac_minlength','feature_ac_delay'));
    $t->set_var(array(
            'HEADER'    => $header,
            'acminlen'  => ($tmpdata['feature_ac_minlength']!='')?$tmpdata['feature_ac_minlength']:2,
            'acdelay'   => ($tmpdata['feature_ac_delay']!='')?    $tmpdata['feature_ac_delay']:100,
            'Msg'       => $msg,
            'btn'       => $btn,
            'Subject'   => $Subject,
            'BodyText'  => $BodyText,
            'CC'        => $CC,
            'TO'        => $TO,
            'Sign'      => $MailSign,
            'KontaktCC' => $KontaktCC,
            'KontaktTO' => $KontaktTO,
            'QUELLE'    => $referer,
            'JS'        => '',
            'hide'      => $hide,
            'popup'     => $popup,
            'closelink' => ($popup)?'<a href="JavaScript:self.close()">.:close:.</a>':'',
            'vorlage'   => $MID
            ));
    $t->Lpparse('out',array('mail'),$_SESSION['countrycode'],'work');
?>
