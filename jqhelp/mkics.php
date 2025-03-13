<?php
    require_once("../inc/stdLib.php");
    include_once("crmLib.php");
    include_once("UserLib.php");
    require_once( 'iCalcreator.class.php' );

    $user  = getUserStamm($_SESSION["loginCRM"]);
    $start = ($_GET["start"]<>"")?$_GET["start"]:date('d.m.Y');
    $stop  = ($_GET["stop"]<>"")?$_GET["stop"]:'';
    $termine = searchTermin('%',0,$start,$stop,$_SESSION["loginCRM"]);
    $v = new vcalendar(); // create a new calendar instance
    $v->setConfig( 'unique_id', strtr($user["name"],' ','_')); // set Your unique id
    $v->setProperty( 'method', 'PUBLISH' ); // required of some calendar software
    if ( !$termine ) {
        $rc = array('rc'=>0, 'cnt'=>0, 'msg'=>'Keine Termine');
    } else {
        $ts = "";
        foreach ( $termine as $t ) {
            $ts .= $t["id"].",";
        }
        $data = getTerminList($ts."0");
        $cnt  = 0;
        foreach ( $data as $term ) {
            $cnt++;
            $vevent = new vevent(); // create an event calendar component
            $vevent->setProperty( 'dtstart', array( 'year'=>substr($term["starttag"],0,4), 
                                                    'month'=>substr($term["starttag"],5,2), 
                                                    'day'=>substr($term["starttag"],8,2), 
                                                    'hour'=>substr($term["startzeit"],0,2), 
                                                    'min'=>substr($term["startzeit"],3,2),
                                                    'sec'=>0 ));
            $vevent->setProperty( 'dtend', array(   'year'=>substr($term["stoptag"],0,4), 
                                                    'month'=>substr($term["stoptag"],5,2), 
                                                    'day'=>substr($term["stoptag"],8,2), 
                                                    'hour'=>substr($term["stopzeit"],0,2), 
                                                    'min'=>substr($term["stopzeit"],3,2),
                                                    'sec'=>0 ));
            $vevent->setProperty( 'LOCATION', $term["location"]  ); // property name - case independent
            $vevent->setProperty( 'categories', $term["catname"] );
            $vevent->setProperty( 'summary', $term["cause"] );
            $vevent->setProperty( 'description', $term["c_cause"] );
            $vevent->setProperty( 'attendee', $user["email"] );
            $v->setComponent ( $vevent ); // add event to calendar
        }
        $v->setConfig( 'filename', date('Ymd').'_calendar.'.$_GET["icalext"] ); // set file name
        $filename = date('Ymd').'_calendar.'.$_GET["icalext"]; // set file name
        if ( $_GET["icalart"] == "client" ) {
            $v->setConfig( 'directory', "../tmp/" ); // identify directory
            $v->saveCalendar(); // save calendar to file
            $file = "tmp/".$filename;
            $rc = array('rc'=>1, 'cnt'=>$cnt, 'msg'=>'Termine exportiert', 'file'=>$file);
        } else if ( $_GET["icalart"] == "mail" ) {
            $user    = getUserStamm($_SESSION["loginCRM"]);
            $abs     = sprintf("%s <%s>",$user["name"],$user["email"]);        
            $Subject = "ok-Kalender";
            $v->setConfig( 'directory', "../tmp/" ); // identify directory
            $v->saveCalendar(); // save calendar to file
            include_once("Mail.php");
            include_once("Mail/mime.php");
            $headers = array(
                    "Return-Path"   => $abs,
                    "Reply-To"  => $abs,
                    "From"      => $abs,
                    "X-Mailer"  => "PHP/".phpversion(),
                    "Subject"   => $Subject);
            $mime = new Mail_Mime("\n");
            $mime->setTXTBody("");
            $mime->addAttachment($v->getConfig('directory')."/".$v->getConfig('filename'),"text/plain",$v->getConfig('filename'));
            $body = $mime->get(array("text_encoding"=>"quoted-printable","text_charset"=>$_SESSION["charset"]));
            $hdr  = $mime->headers($headers);
            $mail = & Mail::factory("mail");
            $mail->_params = "-f ".$user["email"];
            $to = ( $_GET["icaldest"] != '' )?$_GET["icaldest"]:$user["email"];
            $rc = $mail->send($to, $hdr, $body);                
            $rc = array('rc'=>2, 'cnt'=>$cnt, 'msg'=>($rc==1)?'Mail versendet an: '.$to:'Mail nicht versendet!');
        } else if ( $_GET["icalart"] == "server" ) {
            $directory = "../dokumente/".$_SESSION["dbname"]."/".$_SESSION["login"]."/" ;
            $ok = $v->setConfig( 'directory', $directory ); // identify directory
            if ( $ok ) $ok = $v->saveCalendar(); // save calendar to file
            $rc = array('rc'=>3, 'cnt'=>($ok)?$cnt:0, 'msg'=>($ok)?'Termine exportiert':'Termine nicht exportiert');
        } else if ( $_GET["icalart"] == "webdav" ) {
            $v->setConfig( 'directory', "../tmp/" ); // identify directory
            $v->saveCalendar(); // save calendar to file
            // Hier noch anpassen. Protokolle ftp, webdav, webdavs, ....
            include_once('HTTP/WebDAV/Client.php');
            @$client = new HTTP_WebDAV_Client_Stream();
            if ( substr($_SESSION['putadrcard'],0,5) == 'https' ) { $prot = 'webdavs'; $server = substr($_SESSION['putadrcard'],8).'/'; }
            else if ( substr($_SESSION['putadrcard'],0,4) == 'http' ) { $prot = 'webdav'; $server = substr($_SESSION['putadrcard'],7).'/'; }
            else { $prot = 'webdav'; };
            $dir = '';
            $link = $prot.'://'.$_SESSION['syncname'].':'.$_SESSION['syncpwd'].'@'.$server;
            @$client->stream_open($link.'/'.$filename,'w',null,$dir);
            @$client->stream_write( file_get_contents('../tmp/'.$filename) );
            @$client->stream_close();
            @$client->dir_opendir($link,array());
            if ( in_array($filename,$client->dirfiles) ) {
                $rc = array('rc'=>4, 'cnt'=>$cnt, 'msg'=>"$filename übertragen");
            } else {
                $rc = array('rc'=>4, 'cnt'=>$cnt, 'msg'=>"$filename nicht übertragen.");
            }
        } else {
            $rc = array('rc'=>0, 'cnt'=>$cnt, 'msg'=>'unbekannter Parameter: '.$_GET["icaldest"]);
        }
    };
    echo json_encode( $rc );
?>
