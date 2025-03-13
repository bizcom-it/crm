<?php
unset($GLOBALS['php_errormsg']);
require_once("../inc/stdLib.php");

$task = '';
$f = fopen('/tmp/ana.log','w');
if ( isset($_POST) AND isset($_POST['task']) ) {
    $task  = $_POST['task'];
    fputs($f,"POST\n");
    fputs($f,print_r($_POST,true));
} else if ( isset($_GET) AND isset($_GET['task']) ) {
    $task = $_GET['task'];
    fputs($f,"GET\n");
    fputs($f,print_r($_GET,true));
};

function saveTelco( $save ) {
    fputs($GLOBALS['f'],print_r($data,true));
    $keys = array('TelcoServer','TelCommand','AuthKey','LocalContext','ExternContext','Vorzeichen');
    $last = $GLOBALS['db']->getOne( 'SELECT max(id) as id FROM crmdefaults' );
    $insert = 'INSERT INTO crmdefaults (key,val,grp,employee) VALUES (?,?,\'telco\','.$_SESSION['loginCRM'].")";
    $data = array( );
    foreach ( $keys as $row ) {
        $data[] = array(
            $row,
            $save[$row],
        );
    };
    $rc = $GLOBALS['db']->executeMultiple( $insert, $data );
    if ( $rc ) {
        $msg = 'Sichern erfolgt';
        if ( $last['id'] ) {
            $sql = "DELETE FROM crmdefaults WHERE grp = 'telco' and id <= ".$last['id'];
            $rc = $GLOBALS['db']->query( $sql );
        }
        $GLOBALS['db']->commit( );
    } else {
        $GLOBALS['db']->rollback( );
        $msg = 'Sichern fehlgeschlagen';
    };
    fputs($GLOBALS['f'],$msg);
    return $msg;
}

function doCall($data) {
    $srv = getTelco(true);
    fputs($GLOBALS['f'],print_r($srv,true));
    fputs($GLOBALS['f'],print_r($data,true));
    if ( !isSetVar($srv['TelcoServer']) && !isSetVar($srv['TelCommand']) ) {
        echo 'Asterisk nicht konfiguriert';
        return;
    }
    $auth = md5($srv['AuthKey']);
    $len  = strlen($srv['Vorzeichen']);
    fputs($GLOBALS['f'],"!$len!\n");
    fputs($GLOBALS['f'],substr($data['To'],0,$len)."\n");
    if ( substr($data['To'],0,$len) == $srv['Vorzeichen'] ) {
        $to   = substr($data['To'],$len).'@'.$srv['ExternContext'];
    } else {
        $to   = $data['To'].'@'.$srv['LocalContext'];
    }
    $from = $data['From'].'@'.$srv['LocalContext'];
    if ( $srv['TelcoServer'] == 'localhost' or $srv['TelcoServer'] == '' ) {
         $key = trim(file_get_contents('/etc/asterisk/okauth'));
         if ( md5($key) != $auth ) {
               echo 'Keine Erlaubniss';
               return;
         }; 
         fputs($GLOBALS['f'],$srv['TelCommand']." -rx 'Originate Local/$from extension $to'");
         $rc = shell_exec($srv['TelCommand']." -rx 'Originate Local/$from extension $to'");
         if ( !$rc ) $rc = 'Befehl local abgesetzt';
    } else {
         fputs($GLOBALS['f'],$srv['TelcoServer'].'asterisk.php?action=call&auth='.$auth.'&from='.$from.'&to='.$to);
         $rc = file_get_contents($srv['TelcoServer'].'asterisk.php?action=call&auth='.$auth.'&from='.$from.'&to='.$to);
         if ( !$rc ) $rc = 'Befehl remote abgesetzt';
    };
    echo $rc;
}

switch( $task ){
        case "call" :      doCall($_POST);
                           break;
        case "isManager":  echo isManager();
                           break;
        case "initTel":    $usr = getUserEmployee(array('countrycode'));
                           $usr['loginCRM'] = $_SESSION['loginCRM'];
                           $usr['countrycode'] = $_SESSION['countrycode'];
                           $sql  = "SELECT count(*) as cnt FROM gruppenname n LEFT JOIN grpusr u ON n.grpid=u.grpid ";
                           $sql .= "WHERE n.rechte = 'r' AND u.usrid = ".$_SESSION['loginCRM'];
                           $rs = $GLOBALS['db']->getOne($sql);
                           $usr['manager'] = $rs['cnt'] > 0;
                           echo json_encode( $usr );
                           break;
        case "test":       $auth = md5($_POST['AuthKey']);
                           if ( $_POST['TelcoServer'] == 'localhost' or $_POST['TelcoServer'] == '' ) {
                               $key = trim(file_get_contents('/etc/asterisk/okauth'));
                               if ( md5($key) != $auth ) {
                                     echo 'Keine Erlaubniss';
                                     return;
                               }; 
                               $rc = shell_exec($_POST['TelCommand']." -rx 'core show sysinfo'");
                           } else { 
                               $rc = file_get_contents($_POST['TelcoServer'].'asterisk.php?action=test&auth='.$auth);
                               if ( !$rc ) $rc = 'Keine gültige Verbindung';
                           };
                           echo '<pre>'.$rc.'</pre>';
                           break;
        case "save":       echo saveTelco($_POST);
                           break;
};

?>
