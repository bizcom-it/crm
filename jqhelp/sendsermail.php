<?php
require_once('../inc/stdLib.php');
include_once('UserLib.php');
require_once('crmLib.php');
include_once('Mail.php');
include_once('Mail/mime.php');
include_once('Debug.php');
 

    //$debug    = new MyDebug('sendsermail','a');
    $msg      = '';
    $rc       = true;
    $dateiID  = false;
    $user     = getUserStamm($_SESSION["loginCRM"]);
    $MailSign = str_replace("\n","<br>",$user["mailsign"]);
    $MailSign = str_replace("\r","",$MailSign);
    $Subject  = preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $_POST["Subject"]);
    $BodyText = preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $_POST["BodyText"]);
    if ( $_POST["CC"]<>"" ) { 
            $CC = preg_replace( "/[^a-z0-9 !?:;,.\/_\-=+@#$&\*\(\)<>]/im", "", $_POST["CC"]);
            $CC = preg_replace( "/(content-type:|bcc:|cc:|to:|from:)/im", "", $CC);
    //$debug->write(array('CC',$CC,'','','','','','','',$CC,'',-1,'','','','','','','','',''),'insertCSV');
            $rc = chkMailAdr($CC); 
    //$debug->write($CC);
            if( $rc<>"ok" ) { 
    //$debug->write('Not insertet');
    //$debug->write($rc);
                $okC=false; $msg.=" CC:".$rc;
                $_SESSION['CC'] = '';
            } else {
                insertCSVData(array('CC',$CC,'','','','','','','',$CC,'',-1,'','','','','','','','',''),-1);
     //$debug->write('insertet');
                $_SESSION['CC'] = $CC;
            }
    };
    mb_internal_encoding($_SESSION['charset']);
    $Name = mb_encode_mimeheader($user["name"], $_SESSION["charset"], 'Q', '');
    if ( $user["email"] != '' ) {
            $abs = $Name.' <'.$user["email"].'>';
            $rc  = chkMailAdr($user["email"]); if($rc<>"ok") { $okA=false; $msg.=" Abs:".$rc; };
    } else {
            $rc  = false;
            $msg = " Kein Absender";
    };
    if ( !$rc ) {
        echo json_encode(array('rc'=>$rc,'msg'=>$msg));
        return false;
    };
    $dateiname = "";
    //if ($_FILES["Datei"]["name"]<>"") {
    if ($_POST["Datei"]<>"") {
        $dateiname    = $_POST['Datei'];
        $dat['Datei'] = array('tmp_name'=> $_SESSION['baseurl'].'crm/dokumente/'.$_SESSION['dbname'].'/'.$_SESSION['login'].'/tmp/'.$dateiname,
                            'name' => $dateiname,
                            'type' => $_POST['Typ'],
                            'size' => $_POST['Size']);
        $ok         = chkdir($_SESSION["login"].'/SerMail');
        //$pfad       = './dokumente/'.$_SESSION['dbname'].'/'.$_SESSION['login'].'/SerMail';
        //$dat["Datei"]["name"]=$_FILES["Datei"]["name"];
        //$dat["Datei"]["tmp_name"]=$_FILES["Datei"]["tmp_name"];
        //$dat["Datei"]["type"]=$_FILES["Datei"]["type"];
        //$dat["Datei"]["size"]=$_FILES["Datei"]["size"];
        $dbfile     = new document();
        $dbfile->setDocData("descript",$Subject);
        $ok         = chkdir($_SESSION["login"].'/SerMail');
        $pfad       = $_SESSION["login"].'/SerMail';
        $rc         = $dbfile->uploadDocument($dat,$pfad);
        if ( $rc ) {
            $dateiID    = $dbfile->id;
        } else {
            echo json_encode(array('rc'=>$rc,'msg'=>"Fehler Upload"));
            return false;
        }
    }
    $SubjectMail    = mb_encode_mimeheader($Subject, $_SESSION["charset"], 'Q', '');
    $headers        = array(
                       "Return-Path"   => $user["email"],
                       "Reply-To"      => $abs,
                       "From"          => $abs,
                       "X-Mailer"      => "PHP/".phpversion(),
                       "Subject"       => $SubjectMail
                   );
    if ( $dateiname == "" ) $headers["Content-Type"] = "text/plain; charset=".$_SESSION["charset"];
    $_SESSION["headers"]   = $headers;
    $_SESSION["Subject"]   = $Subject;
    $_SESSION["bodytxt"]   = $BodyText;
    $_SESSION["dateiname"] = $dateiname;
    $_SESSION["dateiId"]   = ($dateiID)?$dateiID:0;
    $_SESSION["limit"]     = 50;   
    if ( isset ($_POST['Typ']) ) {
        $_SESSION["type"]      = $_POST['Typ'];
    } else {
        $_SESSION["type"]      = '';
    }
    $sendtxt  = "Es &ouml;ffnet sich nun ein extra Fenster.<br>";
    $sendtxt .= "Bitte schlie&szlig;en sie es nur wenn sie dazu aufgefordert werden,<br>";
    $sendtxt .= "da sonst der Mailversand beendet wird.<br><br>";
    $sendtxt .= "Sie k&ouml;nnen aber ganz normal mit anderen Programmteilen arbeiten.";
    echo json_encode(array('rc'=>$rc,'msg'=>$sendtxt));
?>
