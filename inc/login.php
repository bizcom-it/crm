<?php
/* Sollte nur einmal aufgeruffen werden */
//echo "!!!LOGIN!!!";

clearstatcache();
require_once "conf.php";
require_once "version.php";

if ( isset($_SESSION) && is_array($_SESSION) ) while( list($key,$val) = each($_SESSION) ) {       
	     unset($_SESSION[$key]);
};

if ( isset($_SERVER['CONTEXT_DOCUMENT_ROOT']) ) {
    $basepath = $_SERVER['CONTEXT_DOCUMENT_ROOT'];
    $tmp = explode($ERPNAME,$_SERVER['CONTEXT_DOCUMENT_ROOT']);
    $basepath = substr($tmp[0],0,-1);
} else if ( isset($_SERVER['SCRIPT_FILENAME']) ) {
    $tmp = explode($ERPNAME,$_SERVER['SCRIPT_FILENAME']);
    $basepath = substr($tmp[0],0,-1);
} else if ( isset($ERPATH) ) {
    $basepath = $ERPPATH;
} else if ( isset($_SERVER['DOCUMENT_ROOT']) ) {
    $basepath = $_SERVER['DOCUMENT_ROOT'];
} else {
    echo "Basispfad konnte nicht ermittelt werden.<br>";
    echo 'Bitte in "$ERPPATH" in inc/conf.php den absoluten Pfad ohne ERP-Verzeichnis eintragen.';
    exit();
};

$_SESSION['erppath'] = $basepath.'/'.$ERPNAME.'/';;
$_SESSION['ERP_BASE_URL'] = $ERP_BASE_URL;

$inclpath = ini_get('include_path');
ini_set('include_path',$inclpath.":".$_SESSION['erppath'].'crm/inc'.":".$_SESSION['erppath'].'crm/jqhelp');

$conffile = $_SESSION['erppath'].'/config/'.$erpConfigFile.'.conf';
if ( is_file($conffile) ) {
    $tmp = anmelden();
    if ( $tmp ) {
        require ("update_neu.php"); //?VERSION=$VERSION");
    } else {
        echo "Session abgelaufen oder ein anderes Problem beim Anmelden."; 
        $Url  = (empty( $_SERVER['HTTPS'] )) ? 'http://' : 'https://';
        $Url .= $_SERVER['HTTP_HOST'];
        $Url .= preg_replace( "^crm/.*^", "", $_SERVER['REQUEST_URI'] );
        session_unset();
        header('Location: '.$Url.'login.pl?x=1');
        exit();
    };
} else {
    echo "Configfile ($conffile) nicht gefunden<br>$PHPSELF<br>";
    exit();
}

/****************************************************
* anmelden
* in: name,pwd = String
* out: rs = integer
* prueft ob name und kennwort in db sind und liefer die UserID
*****************************************************/
function anmelden() {
    ini_set("gc_maxlifetime","3600");
    //Konfigurationsfile der ERP einlesen
    if ( file_exists($_SESSION['erppath']."/config/kivitendo.conf") ) {  
	    $lxo = fopen($_SESSION['erppath']."/config/kivitendo.conf","r");  
    } else if ( file_exists($_SESSION['erppath']."/config/kivitendo.conf.default") ) {
	    $lxo = fopen($_SESSION['erppath']."/config/kivitendo.conf.default","r");
    } else {
        return false;
    }
    $dbsec = false; //Nicht in der DB-Sektion
    $tmp   = fgets($lxo,512);
    //Parameter für die Auth-DB in der ERP-Konfiguration finden
    while ( !feof($lxo) ) {
        if ( preg_match("/^[\s]*#/",$tmp) || $tmp == "\n" ) { //Kommentar, ueberlesen
            $tmp = fgets($lxo,512);
	        continue;
        }
        if ( $dbsec && preg_match("!\[.+]!",$tmp) ) $dbsec = false; //DB-Sektion verlassen
        if ( $dbsec ) {
	        if ( preg_match("/db[ ]*=[ ]*(.+)/",$tmp,$hits) )       $dbname = $hits[1];
	        if ( preg_match("/password[ ]*=[ ]*(.+)/",$tmp,$hits) ) $dbpasswd = $hits[1];
	        if ( preg_match("/user[ ]*=[ ]*(.+)/",$tmp,$hits) )     $dbuser = $hits[1];
	        if ( preg_match("/host[ ]*=[ ]*(.+)/",$tmp,$hits) )     $dbhost = ($hits[1])?$hits[1]:"localhost";
	        if ( preg_match("/port[ ]*=[ ]*([0-9]+)/",$tmp,$hits) ) $dbport = ($hits[1])?$hits[1]:"5432";
            if ( preg_match("/\[[a-z]+/",$tmp) ) $dbsec = false;
    	    $tmp = fgets($lxo,512);
	        continue;
        }
        if ( preg_match("/cookie_name[ ]*=[ ]*(.+)/",$tmp,$hits) )     $cookiename = $hits[1];
        if ( preg_match("/session_timeout[ ]*=[ ]*(.+)/",$tmp,$hits) ) $sesstime = $hits[1];
        if ( preg_match("!\[authentication/database\]!",$tmp) )        $dbsec = true;
        $tmp = fgets($lxo,512);
    }
    if ( !$cookiename ) $cookiename = 'kivitendo_session_id';
    if ( !$sesstime )   $sesstime = 480;
    fclose($lxo);
    //setcookie($cookiename,$_SESSION['sessid'],$sesstime);
    //Cookie wird durch ERP gesetzt!
    //echo $HTTP_COOKIE_VARS[$cookiename];
    $cookie = $_COOKIE[$cookiename];
    if ( !$cookie ) header("location: ups.html");
    // Benutzer anmelden
    $dbcon['dbhost']   = $dbhost;
    $dbcon['dbport']   = $dbport;
    $dbcon['dbuser']   = $dbuser;
    $dbcon['dbpasswd'] = $dbpasswd;
    $dbcon['dbname']   = $dbname;
    $auth = authuser($dbcon,$cookie);
    if ( !$auth ) {  return false; };   	   // Anmeldung des Users fehlgeschlagen
    $_SESSION['login']       = $auth['login'];
    $_SESSION['stylesheet']  = $auth['stylesheet'];
    $_SESSION['countrycode'] = $auth['countrycode'];
    $_SESSION['manid']       = $auth['manid'];
    $_SESSION['mandant']     = $auth['mandant'];
    $_SESSION['dbname']      = $auth['dbname'];
    $_SESSION['CRMTL']       = $auth['CRMTL'];
    $_SESSION['sales_edit_all'] = $auth['sales_edit_all'];
    $_SESSION["sessid"]      = $cookie;
    $_SESSION["cookie"]      = $cookiename;
    $_SESSION["sesstime"]    = $sesstime;
    // Mit der Mandanten-DB verbinden
    $dbcon['dbhost']         = $auth['dbhost'];
    $dbcon['dbport']         = $auth['dbport'];
    $dbcon['dbuser']         = $auth['dbuser'];
    $dbcon['dbpasswd']       = $auth['dbpasswd'];
    $dbcon['dbname']         = $auth['dbname'];
    $dbcon["sessid"]         = $cookie;
    $GLOBALS['db']           = new myDB($dbcon);
    if( !$GLOBALS['db'] ) {
        return false;
    } else {
        $_SESSION['connect']   = base64_encode(serialize($dbcon));
        $_SESSION['CRMTL'] = $auth['CRMTL'];
        $charset = ini_get("default_charset");
        if ( $charset == "" ) $charset = 'UTF8';
        $_SESSION["charset"] = $charset;
        $BaseUrl   = (empty( $_SERVER['HTTPS'] )) ? 'http://' : 'https://';
        $BaseUrl  .= $_SERVER['HTTP_HOST'];
        $BaseUrl  .= preg_replace( "^crm/.*^", "", $_SERVER['REQUEST_URI'] );
        $_SESSION["baseurl"] = $BaseUrl;
        if ( chkcrm() != '' ) {
            //$sessvars = array('zeige_tools','angebot_button','auftrag_button','liefer_button','rechnung_button','zeige_extra','zeige_karte','zeige_etikett','zeige_lxcars','zeige_bearbeiter','external_mail','kdviewli','kdviewre','planspace','streetview','intver','GEODB','pre'); //,'feature_ac_minlength','feature_ac_delay','feature_ac','searchtab');
            include_once("UserLib.php");
            $user_data = getUserStamm(0,$_SESSION["login"],false);
            $_SESSION['loginCRM']  = $user_data["id"];
            $_SESSION['theme']     = ($user_data['theme']=='' || $user_data['theme']=='base')?'':$user_data['theme'];
            $_SESSION['pre']       = $user_data["pre"];
            $_SESSION['zeige_tools']       = $user_data['zeige_tools'];
            $_SESSION['php_error'] = $user_data["php_error"];
            $_SESSION['logfile']   = (isset($user_data["logfile"]) && $user_data["logfile"]=='t');
            $_SESSION['debug']     = (isset($user_data["debug"]) && $user_data["debug"]=='t');
            $_SESSION['errlogfile'] = (isset($user_data["errlogfile"]) && $user_data["errlogfile"]=='t');
            //foreach ( $sessvars as $var ) $_SESSION[$var] = $user_data[$var];
            $_SESSION['menu']      = makeMenu($auth["token"]);
            chkdir($_SESSION["login"]);	   // gibt es unter dokumente ein Verzeichnis mit dem Instanznamen
            chkdir('link_dir');			   // gibt es unter dokumente ein Verzeichnis mit dem Instanznamen
            chkdir('link_dir_cust');	   // gibt es unter dokumente ein Verzeichnis mit dem Instanznamen
            chkdir('link_dir_vend');	   // gibt es unter dokumente ein Verzeichnis mit dem Instanznamen
            chkdir('tmp');
        } else {
            writelog('CRM-Version unbekannt');
            echo('CRM-Version unbekannt');
            echo('<a href="../login.pl">Login</a>');
            return false;
        }
        return true;
    }
}

function authuser($dbcon,$cookie) {
    //$db   = new myDB($dbcon);
    $dbcon['sessid'] = $cookie;
    $db   = new myDB($dbcon);
    //Hat sich ein User angemeldet
    $sql  = "select sc.session_id,u.id,u.login from auth.session_content sc left join auth.\"user\" u on ";
    $sql .= "(E'--- ' || u.login || chr(10) )=sc.sess_value left join auth.session s on s.id=sc.session_id ";
    $sql .= "where session_id = '$cookie' and sc.sess_key='login'";
    $rs   = $db->getAll($sql);
    if ( count($rs) != 1 ) { // Garnicht mit ERP angemeldet oder zu viele Sessions, sollte die ERP drauf achten
        while( list($key,$val) = each($_SESSION) ) {  unset($_SESSION[$key]); };
        $Url = preg_replace( "^crm/.*^", "", $_SERVER['REQUEST_URI'] );
        header( "location:".$Url."controller.pl?action=LoginScreen/user_login" );        
    }
    $auth = array();
    $uid  = $rs[0]["id"];
    $auth["login"]      = $rs[0]["login"];
    $sql = "select * from auth.user_config where user_id=".$uid;
    $rs  = $db->getAll($sql);
    $keys = array("countrycode","stylesheet","vclimit","signature","email","tel","fax","name");
    foreach ( $rs as $row ) {
        if ( in_array($row["cfg_key"],$keys) ) {
            $auth[$row["cfg_key"]] = $row["cfg_value"];
        }
    }
    $auth["stylesheet"] = substr($auth["stylesheet"],0,-4);
    //Welcer Mandant ist verbunden
    $sql  = "SELECT sess_value FROM auth.session_content WHERE session_id = '$cookie' and sess_key='client_id'";
    $rs   = $db->getOne($sql);
    $mandant = substr($rs['sess_value'],4);
    $sql  = 'SELECT id as manid,name as mandant,dbhost,dbport,dbname,dbuser,dbpasswd FROM auth.clients WHERE id = '.$mandant;
    $rs   = $db->getOne($sql);
    if ( $rs ) {
        $auth = array_merge($auth,$rs);
    } else {
        return false;
    }
    //Eine der Gruppen des Users darf sales_all_edit
    $sql  = "SELECT granted from auth.group_rights G where G.right = 'sales_all_edit' ";
    $sql .= "and G.group_id in (select group_id from auth.user_group where user_id = ".$uid.")";
    $rs3  = $db->getAll($sql);
    $auth["sales_edit_all"] = 'f';
    if ( $rs3 ) {
        foreach ( $rs3 as $row ) {
             if ( $row["granted"] == 't' ) {
                   $auth["sales_edit_all"] = 't';
                   break;
              }
         }
    }
    // Ist der User ein CRM-Supervisor?
    $sql = "SELECT count(*) as cnt from auth.user_group left join auth.group on id=group_id where name = 'CRMTL' and user_id = ".$uid;
    $rs  = $db->getOne($sql);
    $auth['CRMTL'] = $rs['cnt'];
    //Session update
    $sql = "update auth.session set mtime = '".date("Y-M-d H:i:s.100001")."' where id = '".$cookie."'"; 
    $db->query($sql,"authuser_3");
    //Token lesen
    $sql = "SELECT * FROM auth.session WHERE id = '".$cookie."'";
    $rsa =  $db->getOne($sql);
    $auth['token'] = $rsa['api_token'];
    unset( $db );
    return $auth;
}

function chksesstime($dbcon) {
    //$db   = new myDB($dbcon);
    $dbcon['sessid'] = $session;
    $db   = new myDB($dbcon);
    $sql  = "SELECT * FROM auth.session WHERE = '$session'";
    $rs   = $db->getOne($sql);
    return true;
}

function makeMenu($token){
    if( !function_exists( 'curl_init' ) ){
        die( 'Curl (php5-curl) ist nicht installiert!' );
    }
    $Url = $_SESSION['baseurl'].'controller.pl?action=Layout/empty&format=json';
    try {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $Url );
        curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 1 );
        curl_setopt( $ch, CURLOPT_ENCODING, 'gzip,deflate' );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array (
                    "Connection: keep-alive",
                    "Cookie: ".$_SESSION["cookie"]."=".$_SESSION["sessid"]."; ".$_SESSION["cookie"]."_api_token=".$token
                    ));
        $result = curl_exec( $ch );
        if( $result === false || curl_errno( $ch )){
            header( "location:".$_SESSION['baseurl']."controller.pl?action=LoginScreen/user_login" );        
            die( 'Curl-Error: ' .curl_error($ch).' </br> $ERP_BASE_URL in "inc/conf.php" richtig gesetzt??' );
        };
        curl_close( $ch );
    } catch ( Exception $e ) {
        while( list($key,$val) = each($_SESSION) ) {
            unset($_SESSION[$key]);
        };
        anmelden();
    }
    $objResult = json_decode( $result );
    if (!is_object($objResult)) anmelden();
    $_arr = get_object_vars($objResult);
    $rs['javascripts']   = '';
    $rs['stylesheets']   = '';
    $rs['pre_content']   = '';
    $rs['start_content'] = '';
    $rs['end_content']   = '';
    if ( $_SESSION['zeige_tools'] == 't' || $_SESSION['zeige_tools'] == '1' ) {
        $SV = '<script type="text/javascript" src="';
        $SN = '"></script>'."\n";
        $LV = '<link rel="stylesheet" type="text/css" href="';
        $LN = '">'."\n";
        $tools           = $LV.$_SESSION['baseurl'].'crm/jquery/plugin/jquery-calculator/jquery.calculator.css'.$LN.
                           $SV.$_SESSION['baseurl'].'crm/jquery/plugin/jquery-calculator/jquery.plugin.js'.$SN.
                           $SV.$_SESSION['baseurl'].'crm/jquery/plugin/jquery-calculator/jquery.calculator.js'.$SN.
                           $SV.$_SESSION['baseurl'].'crm/jquery/plugin/jquery-calculator/jquery.calculator-'.$_SESSION['countrycode'].'.js'.$SN.
                           $SV.$_SESSION['baseurl'].'crm/js/tools.js'.$SN;
    } else {
       $tools           = '<!-- tools -->';
    };    
    if ($objResult) {
        //jQuery und UI der ERP benützen
        foreach($objResult->{'javascripts'} as $js) {
            //jQuery und UI der ERP benützen
            //$rs['javascripts'] .= '<script type="text/javascript" src="'.$BaseUrl.$js.'"></script>'."\n".'   ';
            //Da die ERP eine veraltete JUI benützt, aktuelle JUI aus CRM laden
            //ToDo: JUI aus ERP laden wenn diese >= Version 11.4 wird
            //Achtung!: JUI wird von der ERP nur geliefert wenn fast alle Module aktiviert sind (Menü)
            if( strpos( $js, "jquery-ui")  === false ) $rs['javascripts'] .= '<script type="text/javascript" src="'.$_SESSION['baseurl'].$js.'"></script>'."\n".'   ';
            $rs['javascripts'] .= '<script type="text/javascript" src="'.$_SESSION['baseurl'].'crm/jquery/jquery-ui.min.js"></script>'."\n".'   ';;
        }
        foreach($objResult->{'stylesheets'} as $style) {
            if ($style) $rs['stylesheets'] .= '<link rel="stylesheet" href="'.$_SESSION['baseurl'].$style.'" type="text/css">'."\n".'   ';
        }
        foreach($objResult->{'stylesheets_inline'} as $style) {
            if ($style) $rs['stylesheets'] .= '<link rel="stylesheet" href="'.$_SESSION['baseurl'].$style.'" type="text/css">'."\n".'   ';
        }
        $suche = '^([/a-zA-Z_0-9]+)\.(pl|php|phtml)^';
        $ersetze = $_SESSION['baseurl'].'${1}.${2}';
        $tmp = preg_replace($suche, $ersetze, $objResult->{'pre_content'} );
        $tmp = str_replace( 'itemIcon="', 'itemIcon="'.$_SESSION['baseurl'], $tmp );
        $rs['pre_content']   = str_replace( 'src="', 'src="'.$_SESSION['baseurl'], $tmp );
        $rs['start_content'] = $objResult->{'start_content'};
        $rs['start_content_ui'] = '<div class="ui-widget-content">';//Begin UI-Look
        $rs['end_content']   = $objResult->{'end_content'};
        $rs['end_content']  .= '<script type="text/javascript">'."\n";
        //Inline-JS der ERP in den Footer (nach end_content)
        foreach($objResult->{'javascripts_inline'} as $js) {
            $js = preg_replace($suche, $ersetze,$js);
            $rs['end_content'] .= $js; //'<script type="text/javascript" src="'.$BaseUrl.$js.'"></script>'."\n".'   ';
        }
        $rs['end_content'] .= '</script>'."\n".$tools;
        $rs['end_content_ui']   = '</div>'; //End UI-Look
    }
    return $rs;
}

?>
