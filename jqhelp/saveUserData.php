<?php
include( "../inc/stdLib.php" );
include_once( "UserLib.php" );
if ( isset($_POST['task']) ) {
    $task = $_POST['task'];
} else if ( isset($_GET['task']) ) {
    $task = $_GET['task'];
} else {
    $task = '';
}

function chkAZ($part) {
        $sql = "SELECT count(*) as cnt FROM parts WHERE partnumber = '".$part."'";
        $rs = $GLOBALS['db']->getOne( $sql );
        if ( $rs['cnt'] == 0 ) {
            $rc = array('msg'=>'Artikel nicht gefunden','rc'=>1);
        } elseif ( $rs['cnt'] > 1 ) {
            $rc = array('msg'=>'Artikelnummer nicht eindeutig','rc'=>2);  //kann eigentlich nicht sein
        } else {
            $rc = array('msg'=>'Artikel gefunden','rc'=>0);
        };
        echo json_encode($rc);
}
function getMandant() {
    $sql = "SELECT key,val FROM crmdefaults"; // WHERE"  grp = 'mandant'";
    $rs  = $GLOBALS['db']->getAll( $sql );
    echo json_encode($rs);
}

function saveMandant($indata) {
    $keys = array('ttpart',     'tttime',    'ttround',    'ttclearown',    'GEODB',    'BLZDB',
                  'CallDel',    'CallEdit',  'MailFlag',   'MailDelete',    'logmail',  'dir_group',
                  'dir_mode',   'sep_cust_vendor',          'listLimit',    'logfile',  'streetview_man',
                  'planspace_man', 'errlogfile', 'debug');
    parse_str($indata,$formdata);
    if ( isset( $formdata['MailFlag'] ) ) {
        $mailflag = array_keys($formdata['MailFlag']);
        $json     = json_encode($mailflag);
        $formdata['MailFlag'] = $json;
    };
    $insert = 'INSERT INTO crmdefaults (key,val,grp,employee) VALUES ($1,$2,\'mandant\','.$_SESSION['loginCRM'].")";
    $data = array();
    foreach ( $keys as $key ) {
            if ( isset($formdata[$key]) && !empty($formdata[$key]) )  $data[] = array( $key, $formdata[$key] );
    };
    //Soll wirksam werden, auch wenn sichern nicht funktioniert.
    $_SESSION['logfile']    = ($formdata['logfile'] == 't')?true:false;
    $_SESSION['errlogfile'] = ($formdata['errlogfile'] == 't')?true:false;
    $_SESSION['debug']      = ($formdata['debug'] == 't')?true:false;
    $last   = $GLOBALS['db']->getOne( 'SELECT max(id) as id FROM crmdefaults' );
    $rc = $GLOBALS['db']->begin( );
    if ( $rc ) 
        $rc = $GLOBALS['db']->executeMultiple( $insert, $data);
    if ( $rc ) {
        $msg = 'Sichern erfolgt';
        if ( $last['id'] ) {
            $sql = "DELETE FROM crmdefaults WHERE grp = 'mandant' and id <= ".$last['id'];
            $rc = $GLOBALS['db']->query( $sql );
            $rc = $GLOBALS['db']->commit( );
        }
    } else {
        $rc = $GLOBALS['db']->rollback( );
        $msg = 'Sichern fehlgeschlagen';
    }
    echo $msg;
}

function saveAsterisk($indata) {
    parse_str($indata,$formdata);
    $keys = array('TelcoServer','TelCommand','AuthKey','LocalContext','ExternContext','Vorzeichen');
    $insert = 'INSERT INTO crmdefaults (key,val,grp,employee) VALUES ($1,$2,\'telco\','.$_SESSION['loginCRM'].")";
    $sql = "DELETE FROM crmdefaults WHERE grp = 'telco'";
    $data = array( );
    foreach ( $keys as $key ) {
            if ( isset($formdata[$key]) && !empty($formdata[$key]) )  $data[] = array( $key, $formdata[$key] );
    };
    $GLOBALS['db']->begin();
    $rc = $GLOBALS['db']->query( $sql );
    if ( $rc ) {
        $rc = $GLOBALS['db']->executeMultiple( $insert, $data );
        if ( $rc ) {
            $GLOBALS['db']->commit( );
            $msg = 'Sichern erfolgt';
        } else {
            $GLOBALS['db']->rollback( );
            $msg = 'Sichern fehlgeschlagen';
        }
    } else {
        $GLOBALS['db']->rollback( );
        $msg = 'Sichern fehlgeschlagen';
    }
    echo $msg;
}
function saveUser($indata) {
    parse_str($indata,$formdata);
    //Einstellungen nach dem Sichern gleich übernehmen (ohne neues Login)
    if ( isset($formdata['sql_error']) ) {
          $_SESSION['sql_error']  = true;
    } else {
          $_SESSION['sql_error']  = false;
    };
    if ( isSetVar($_SESSION['theme']) ) $_SESSION['theme']  = ( $formdata['theme'] != 'base' ) ? $formdata['theme'] : '';
    $sessvars = array('zeige_tools','angebot_button','auftrag_button','liefer_button',
                      'rechnung_button','zeige_extra','zeige_karte','zeige_etikett',
                      'zeige_lxcars','zeige_bearbeiter','external_mail','kdviewli',
                      'kdviewre','planspace','streetview','intver','GEODB','pre',
                      'feature_ac_minlength','feature_ac_delay','searchtab');
    foreach ( $sessvars as $var ) { if ( isset($_SESSION[$var]) ) $_SESSION[$var] = $formdata[$var]; };
    //fputs($GLOBALS['f'],print_r($formdata,true));
    $rc = saveUserStamm( $formdata );
    echo $rc;    
}
/*$f = fopen('/tmp/mandant.log','a');
fputs($f,"\n".'TASK:'.$task."\n");
fputs($f,'GET:'.print_r($_GET,true)); 
fputs($f,'POST:'.print_r($_POST,true)); */
switch ($task) {
    case 'usrsave' :  saveUser($_POST['form'] );
                      break;
    case 'getmandant' :  getMandant();
                      break;
    case 'mandant' :  saveMandant($_POST['form']);
                      break;
    case 'asterisk' : saveAsterisk($_POST['form']);
                      break;
    case 'az' :       chkAZ($_GET['part']);
                      fputs($f,$_GET['part']);
                      break;
    default :         fputs($f,'Nicht erlaubt:'.$task."\n");
                      echo "Nicht erlaubt";
}
?>
