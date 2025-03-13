<?php 
ini_set('session.bug_compat_warn', 0);// Warnung für Sessionbug in neueren Php-Versionen abschalten. 
ini_set('session.bug_compat_42', 0);  // Das ist natürlich lediglich eine Provirorische Lösung.
//Warning: Unknown: Your script possibly relies on a session side-effect which existed until PHP 4.2.3. ....
session_set_cookie_params(1800); // 30 minuten.
if(!isset($_SESSION)) session_start();

define('lf',"\n");

require 'php_error.php';
/*
Fehlermeldungen provozieren:
trigger_error('Ein Datendump: '.print_r($data,true));
*/

require 'PgSQL.php';
//require 'mdb.php';
//require_once 'phpDataObjects.php';
function isSetVar( &$var, $default = false ) {
    if ( isset($var) ) { 
        return $var; 
    } else { 
        $var = $default; return $default; 
    };
}

function isSetVarNotEmpty( &$var ) {
    if ( isset($var) && !empty($var) ) { 
        return $var; 
    } else { 
        return false;
    };
}
function isSetVarTrue( &$var, $op, $comp  ) {
    $rc = false;
    if ( isset($var) ) { 
        $rc = eval("return \"$var\" $op \"$comp\";"); 
    } else { 
        return false;
    }
    return $rc;
}
//if ( isset($_SESSION['php_error']) && $_SESSION['php_error'] ) {
if ( isSetVar($_SESSION['php_error']) ) {
    error_reporting (E_ALL & ~E_DEPRECATED);
    ini_set ('display_errors',1);
} 
//if ( isset($_SESSION['connect']) && $_SESSION['connect'] != '' ) {
if ( isSetVar($_SESSION['connect']) ) {
    $db = new myDB(unserialize(base64_decode($_SESSION['connect'])));
    if ( !$db ) require_once "login.php";
} else {
    require_once "login.php";
}
if ( !isset($_SESSION["cookie"]) || !isset($_SESSION["sessid"]) || 
     ( $_SESSION["sessid"] != $_COOKIE[$_SESSION["cookie"]] ) ) {   //Irgend etwas ist nicht in Ordnung
                require_once "login.php";
};

$inclpath = ini_get('include_path');
ini_set('include_path',$inclpath.":".$_SESSION['erppath'].'crm/inc'.":".$_SESSION['erppath'].'crm/jqhelp');

/****************************************************
* db2date
* in: Datum = String
* out: Datum = String
* wandelt ein db-Datum in ein "normales" Datum um
*****************************************************/
function db2date($datum) {
   if ( strpos($datum,"-") ) {
       $D = explode("-",$datum);
       $datum = sprintf ("%02d.%02d.%04d",$D[2],$D[1],$D[0]);
   }
   return $datum;
}

function writeLog( $log ){
    if ( is_array($log) ) { file_put_contents( $_SESSION['erppath'].'/crm/tmp/log.txt', date("Y-m-d H:i:s -> " ).print_r( $log, TRUE )."\n", FILE_APPEND ); }
    else { file_put_contents( $_SESSION['erppath'].'/crm/tmp/log.txt', date("Y-m-d H:i:s -> " ).$log."\n", FILE_APPEND ); };
}

function printArray( $array ){
    echo '<pre>';
    print_r( $array );
    echo '</pre>';
}

/****************************************************
* date2db
* in: Datum = String
* out: Datum = String
* wandelt ein "normales" Datum in ein db-Datum um
*****************************************************/
function date2db($Datum = '') {
   if ( empty($Datum) ) return date("Y-m-d");
   $repl = array ("/","-",","," ");
   $date = str_replace($repl,".",$Datum);
   $t = explode(".",$date);
   if ( checkdate($t[1],$t[0],$t[2]) ) {
      if ( $t[2] <= date('y') + 20 ) {
          $Datum = sprintf('20%02d-%02d-%02d',$t[2],$t[1],$t[0]);
      } else if ( $t[2] > 100 ) {
          $Datum = sprintf('%04d-%02d-%02d',$t[2],$t[1],$t[0]);
      } else {
          $Datum = sprintf('19%02d-%02d-%02d',$t[2],$t[1],$t[0]);
      }
   }
   return $Datum;
}

function translate($word,$file ) {
    include("locale/$file.".$_SESSION['countrycode']);
    if ( $texts[$word] ) {
            return $texts[$word];
    } else {
        return $word;
    }
}

/****************************************************
* chkdir
* in: dir = String
* out: boolean
* prueft, ob Verzeichnis besteht und legt es bei Bedarf an
* $p == true, prüft ab ERP-Verzeichnis, z.B. für Bilderupload
*****************************************************/
function chkdir($dir,$p=false) {
    $tmpdata = getUserEmployee(array('dir_mode','dir_group'));
    if ( !isset($tmpdata['dir_mode']) ) {
	    $dir_mode = '0755';
    } else {
        $dir_mode = $tmpdata['dir_mode'];
    };
    if ( ! isset($_SESSION["dbname"]) ) return false; // sollte nie vorkommen
    if ( !file_exists($_SESSION['erppath']."crm/dokumente/".$_SESSION["dbname"] ) ) {
        writeLog("Erstelle: ".$_SESSION['erppath']."crm/dokumente/".$_SESSION["dbname"]);
        @mkdir($_SESSION['erppath']."crm/dokumente/".$_SESSION["dbname"],octdec($dir_mode));
    };
    if ( !$p and isset($_SESSION['erppath']) && file_exists($_SESSION['erppath']."crm/dokumente/".$_SESSION["dbname"]."/".$dir) ) { 
        return $_SESSION['erppath']."crm/dokumente/".$_SESSION["dbname"]."/".$dir;
    } else if (file_exists($_SESSION['erppath'].$dir)) {
        return $_SESSION['erppath'].$dir;
    } else {
        if ( substr($dir,-1) == '/' ) $dir = substr($dir,0,-1);
        $dirs = explode("/",$dir);
        if ( $p ) {
            $tmp  =  '';
        } else {
            $tmp  = "crm/dokumente/".$_SESSION["dbname"]."/";
        };
        foreach ( $dirs as $dir ) {
            if ( !file_exists($_SESSION['erppath'].$tmp.$dir) ) {
                $ok = false;
                if ( is_writeable($_SESSION['erppath'].$tmp.$dir) )
                    echo "(".$_SESSION['erppath'].$tmp.$dir, octdec($dir_mode).")";
                    writeLog("mkdir: ".$_SESSION['erppath'].$tmp.$dir);
                    $ok = mkdir($_SESSION['erppath'].$tmp.$dir, octdec($dir_mode));
                if ( isset($tmpdata['dir_group']) && $tmpdata['dir_group'] && $ok )
                    if ( is_writeable($_SESSION['erppath'].$tmp.$dir) )
                        @chgrp($_SESSION['erppath'].$tmp.$dir,$tmpdata['dir_group']); 
                if ( !$ok ) {
                    return false;
                }
            };
            $tmp .= $dir."/";
        };
        return $_SESSION['erppath'].$tmp;
    }
}

/****************************************************
* liesdir
* in: dir = String
* out: files = Array
* liest die Dateien eines Verzeichnisses
*****************************************************/
function liesdir($dir) {
    $dir = $_SESSION['erppath']."crm/dokumente/$dir/";
    if ( !file_exists($dir) ) return false;
    $cdir = dir($dir);
    while ( $entry = $cdir->read() ) {
        if ( !is_dir($entry) ) {
            $files[] = array("size"=>filesize($dir.$entry),"date"=>date("d.m.Y H:i:s",filemtime($dir.$entry)),"name"=>$entry);
        }
    }
    return $files;
}
function getUserEmployee($keys) {
    $sql  = 'SELECT key,val FROM crmemployee WHERE uid ='.$_SESSION['loginCRM'].' AND manid = '.$_SESSION['manid'];
    $sql .= ' AND key in (\''.join("','",$keys).'\') ';
    $sql .= 'UNION ';
    $sql .= 'SELECT key,val FROM crmdefaults ';
    $sql .= 'UNION ';
    $sql .= 'SELECT \'msignature\' as key, signature as val FROM defaults';
    $rs   = $GLOBALS['db']->getAll($sql);
    $tmp = array();
    foreach ($keys as $key) $tmp[$key] = '';
    if ( $rs ) foreach ($rs as $row) $tmp[$row['key']] = $row['val'];
    return $tmp;
}
function getCRMdefault() {
    $sql = "SELECT key,val FROM crmdefaults WHERE grp = 'mandant'";
    $rs   = $GLOBALS['db']->getAll($sql);
    $tmp = array();
    if ( $rs ) foreach ($rs as $row) $tmp[$row['key']] = $row['val'];
    return $tmp;
}
/****************************************************
* chkFld
* in: val = mixed, empty = boolean, rule = int
* out: ok = boolean
* Daten nach Regeln pruefen
*****************************************************/
function chkFld(&$val,$empty,$rule,$len) {
    if ( $empty===0 ) { $leer="|^$"; }
    else { $leer = false; };
    switch ($rule) {
        case 1 : $ok = preg_match('/[\w\s\d]'.$leer.'/i',$val,$hit); //String
                 if ( strlen($val)>$len && $len>0 ) $val = substr($val,0,$len);
                 $val = strtr($val,array("'"=>"''"));
                 break;
        case 2 : if ( $empty===0 && empty($val) ) { $ok = true; $val = ""; }
                 else { $ok=preg_match('/^[0-9]{2,3}[-]{0,1}[0-9A-Z]{2,4}$/',$val,$hit); }; // Plz: PL=dd-ddd, NL=ddwwww, A=dddd 
                 if ( strlen($val)>$len && $len>0 ) $val = substr($val,0,$len);
                 break;
        case 3 : if ( $empty===0 && empty($val) ) { $ok=true; $val=""; }
                 else { $ok=preg_match('/^070[01][ A-Z]{6,9}$/', $val,$hit) || preg_match('/^\+?[0-9\(\)\/ \-]+$/', $val,$hit); }; //Telefon
                 if ( strlen($val)>$len && $len>0 ) $val = substr($val,0,$len);
                 break;
        case 4 : $ok = preg_match('!^(http(s)?://)?([a-zA-Z0-9\-]*\.)?[a-zA-Z0-9\-]{2,}(\.[a-zA-Z0-9\-]{2,})?(\.[a-zA-Z0-9\-]{2,})(/.*)?$'.$leer.'!',$val,$hit); // www
                 if ( strlen($val)>$len && $len>0 ) $val = substr($val,0,$len);
                 break;
        case 5 : $ok = preg_match('!^([a-z_0-9]+)([a-z_0-9\.\-]+)([a-z_0-9]*)\@([a-z0-9][a-z0-9._-]*\\.)*[a-z0-9][a-z0-9._-]*\\.[a-z]{2,5}$'.$leer.'!i',$val,$hit); //eMail
                 if ( strlen($val) > $len && $len > 0 ) $val = substr($val,0,$len);
                 break;
        case 6 : if ( $empty===0 && empty($val) ) { $ok = true; $val = "null"; }
                 else { $ok = preg_match('/^[0-9]+$/',$val,$hit); } // Ganzzahlen
                 break;
        case 7 : if ( $empty===0 && empty($val) ) { $ok = true; $val = null;} // Datum
                 else if ( $val == '00.00.0000' ) { $ok = true; $val = null;} 
                 else {
                     $t = explode(".",$val);
                     if ( $ok = checkdate($t[1],$t[0],$t[2]) ) {
                        if ( $t[2] <= date('y') ) {
                            $val = sprintf('20%02d-%02d-%02d',$t[2],$t[1],$t[0]);
                        } else if ( $t[2] > 100 ) {
                            $val = sprintf('%04d-%02d-%02d',$t[2],$t[1],$t[0]);
                        } else {
                            $val = sprintf('19%02d-%02d-%02d',$t[2],$t[1],$t[0]);
                        }
                    }
                 };
                 break;
        case 8 : $val = mb_strtoupper($val,'utf-8'); $ok = preg_match("/[\w\s-]+$leer/",$val,$hit); // String, keine Zahlen oder Zeichen kleiner chr(32)
                 if ( strlen($val) > $len && $len > 0 ) $val = substr($val,0,$len);
                 break;
        default : $ok=true;
    }
    return $ok;
}
     
function chkcrm() {
    $crm_exist = $GLOBALS['db']->getOne( "SELECT version FROM crm,information_schema.tables where table_name = 'crm'  ORDER BY version DESC LIMIT 1");
    //"SELECT count(*) FROM information_schema.tables WHERE table_name = 'crm'");
    return $crm_exist['version'];
}

function getVersiondb() {
    $rs = $GLOBALS['db']->getOne("select * from crm order by datum desc limit 1");
    if ( !$rs["version"] ) return "V n.n.n";
    return $rs["version"];
}

function berechtigung($tab="") {
    $grp = getGrp($_SESSION["loginCRM"]);
    $rechte = "( ".$tab."owener=".$_SESSION["loginCRM"]." or ".$tab."owener is null or ".$tab."owener = 0";
    if ( $grp ) $rechte .= " or ".$tab."owener in $grp";
    return $rechte.")";
}

/****************************************************
* getBundesland
* out: array
* Bundeslaender holen
*****************************************************/
function getBundesland($land) {
    $land = mb_strtolower($land,'utf-8');
    if (in_array($land,array('deutschland','germany','allemagne','d'))) {
        $sql = "select * from bundesland where country = 'D' order by country,bundesland";
    } else if (in_array($land,array('österreich','austria','autriche','a'))) {
        $sql = "select * from bundesland where country = 'A' order by country,bundesland";
    } else if (in_array($land,array('schweiz','swiss','suisse','ch'))) {
        $sql = "select * from bundesland where country = 'CH' order by country,bundesland";
    } else {
        $sql = "select * from bundesland order by country,bundesland";
    }
    $rs = $GLOBALS['db']->getAll($sql);
    return $rs;
}

/****************************************************
* mkTelNummer
* in: id = int, tab = char, tels = array
* out: rs = int
* Telefonnummern genormt speichern
*****************************************************/
function mkTelNummer($id,$tab,$tels,$delete=true) {
    if ( $delete ) {
        $sql = "delete from telnr where id=$id and tabelle='$tab'";
        $rs = $GLOBALS['db']->query($sql);
    }
    foreach( $tels as $tel ) {
        $tel = strtr($tel,array(" "=>"","-"=>"","/"=>"","\\"=>"","("=>"",")"=>""));
        if ( substr($tel,0,1) == "+" ) $tel = substr($tel,3);
        if ( substr($tel,0,1) == "0" ) $tel = substr($tel,1);
        if ( trim($tel) <> "" ) {
            $sql = "insert into telnr (id,tabelle,nummer) values (%d,'%s','%s')";
            $sql = sprintf($sql,$id,$tab,$tel);
            $rs = $GLOBALS['db']->query($sql);
        }
    }
}
/****************************************************
* getTelco
* Einstellungen für Telserver lesen
* $raw = false liefert JSON
* sonst normales Array
****************************************************/
function getTelco($raw = false) {
    $sql = "SELECT key,val FROM crmdefaults WHERE grp = 'telco'";
    $rs  = $GLOBALS['db']->getAll( $sql );
    if ( $raw ) {
        $data = array();
        if ( $rs ) foreach ( $rs as $row ) $data[$row['key']] = $row['val'];
            return $data;
        } else {
            return json_encode( $rs );
        }
}

function getAnruf($nr) {
    $nun = date("H:i");
    $name = "_0;$nun $nr unbekannt";
    $sql = "select * from telnr where nummer = '$nr'";
    $rs  = $GLOBALS['db']->getAll($sql);
    if( !$rs ) {
        return false;
    } else {
        $i = 1;
        $more = "";
        while ( count($rs) == 0 && $i < 5 ) {
            $sql = "select * from telnr where nummer like '".substr($nr,0,-$i)."%'";
            $rs  = $GLOBALS['db']->getAll($sql);
            $i++;
            $more = "?";
        };
        if ( $i < 5 ) {
            if ( $rs[0]["tabelle"] == "P" ) {
                $sql = "select cp_name as name2,cp_givenname as name1 from contacts where cp_id=".$rs[0]["id"];
            } else if ( $rs[0]["tabelle"] == "S" ) {
                $sql = "select shipto_name as name1,'' as name2 from shipto where transid=".$rs[0]["id"];
            } else if ( $rs[0]["tabelle"] == "C" ) {
                $sql = "select name as name1,'' as name2 from customer where id=".$rs[0]["id"];
            } else if ( $rs[0]["tabelle"] == "V" ) {
                $sql = "select name as name1,'' as name2 from vendor where id=".$rs[0]["id"];
            } else if ( $rs[0]["tabelle"] == "E" ) {
                $sql = "select name as name1,'' as name2 from employee where id=".$rs[0]["id"];
            } else {
                $name = "_0;".$nun." ".$nr." unbekannt"; return $name;
            }
            $rs1 = $GLOBALS['db']->getAll($sql);
            $name = $rs[0]["tabelle"].$rs[0]["id"].$nun." ".$rs1[0]["name1"]." ".$rs1[0]["name2"].$more;
        } else {
            $name = "_00000".$nun." ".$nr." unbekannt";
        }
    }
    return $name;
}

function getVertretung($user) {
    $sql = "select workphone from employee where vertreter=(select id from employee where workphone='$user')";
    $rs  = $GLOBALS['db']->getAll($sql);
    if ( count($rs) > 0 ) { return $rs; }
    else { return false; };
}

function getVorlagen() {
    $sql = "select * from docvorlage";
    $rs  = $GLOBALS['db']->getAll($sql);
    if ( count($rs) > 0 ) { return $rs; }
    else { return false; };
}

function getDocVorlage($did) {
    if ( !$did ) return false;
    $sql = "select * from docvorlage where docid=$did";
    $rs1 = $GLOBALS['db']->getOne($sql);
    if ( !$rs1 ) return false;
    $sql = "select * from docfelder where docid=$did order by position";
    $rs2 = $GLOBALS['db']->getAll($sql);
    $rs["document"] = $rs1;
    $rs["felder"]   = $rs2;
    if ( count($rs) > 0 ) { return $rs; }
    else { return false; };
}

function getDOCvar($did) {
    $sql = "select * from docvorlage where docid=$did";
    $rs1 = $GLOBALS['db']->getOne($sql);
    return $rs1;
}

function updDocFld($data) {
    $sql  = "update docfelder set feldname='".$data["feldname"]."', platzhalter='".strtoupper($data["platzhalter"]);
    $sql .= "', beschreibung='".$data["beschreibung"]."',laenge=".$data["laenge"].",zeichen='".$data["zeichen"];
    $sql .= "',position=".$data["position"].",docid=".$data["docid"]." where fid=".$data["fid"];
    $rs  = $GLOBALS['db']->query($sql);
    if( !$rs ) {
        return false;
    }
    return $data["fid"];
}

function insDocFld($data) {
    $fid = mknewDocFeld();
    if ( !$fid ) return false;
    $data["fid"] = $fid;
    $fid = updDocFld($data);
    return $fid;
}

function delDocFld($data) {
    $sql = "delete from docfelder where fid=".$data["fid"];
    $rs = $GLOBALS['db']->query($sql);
}

/****************************************************
* mknewDocFeld
* in:
* out: id = int
* Dokumentsatz erzeugen ( insert )
*****************************************************/
function mknewDocFeld() {
    $newID = uniqid (rand());
    $sql = "insert into docfelder (beschreibung) values ('$newID')";
    $rc = $GLOBALS['db']->query($sql);
    if ( $rc ) {
        $sql = "select fid from docfelder where beschreibung = '$newID'";
        $rs = $GLOBALS['db']->getOne($sql);
        if ( $rs ) {
            $id = $rs["fid"];
        } else {
            $id = false;
        }
    } else {
        $id = false;
    }
    return $id;
}

/****************************************************
* mknewDocVorlage
* in:
* out: id = int
* Dokumentsatz erzeugen ( insert )
*****************************************************/
function mknewDocVorlage() {
    $newID = uniqid (rand());
    $sql = "insert into docvorlage (vorlage) values ('$newID')";
    $rc = $GLOBALS['db']->query($sql);
    if ( $rc ) {
        $sql = "select docid from docvorlage where vorlage = '$newID'";
        $rs  = $GLOBALS['db']->getOne($sql);
        if ( $rs ) {
            $id = $rs["docid"];
        } else {
            $id = false;
        }
    } else {
        $id = false;
    }
    return $id;
}

function delDocVorlage($data) {
    $sql = "delete from docfelder where docid=".$data["did"];
    $rs = $GLOBALS['db']->query($sql);
    if ( $rs ) {
        $sql = "delete from docvorlage where docid=".$data["did"];
        $rs  = $GLOBALS['db']->query($sql);
    }
}

function saveDocVorlage($data,$files) {
    if ( !$data["did"] ) {
        $data["did"] = mknewDocVorlage();
        if ( !$data["did"] ) { return false; };
    }
    if ( $files["file"]["name"] ) {
        exec("cp ".$files["file"]["tmp_name"]." ./vorlage/".$files["file"]["name"]);
        $file = $files["file"]["name"];
    } else {
        $file = $data["file_"];
    }
    if ( !$data["vorlage"] ) $data["vorlage"] = "Kein Titel ".datum("d.m.Y");
    $sql  = "update docvorlage set vorlage='".$data["vorlage"]."', beschreibung='".$data["beschreibung"];
    $sql .= "', file='".$file."', applikation='".$data["applikation"]."' where docid=".$data["did"];
    $rs = $GLOBALS['db']->query($sql);
    if( !$rs ) {
        return false;
    } else {
        return $data["did"];
    }
}

function shopartikel() {
    $sql  = "SELECT t.rate,PG.partsgroup,P.partnumber,P.description,P.notes,P.sellprice,P.priceupdate FROM ";
    $sql .= "chart c left join partstax pt on pt.chart_id = c.id,";
    $sql .= "tax t, parts P left join partsgroup PG on PG.id=P.partsgroup_id ";
    $sql .= "where c.category='I' AND t.taxnumber=c.accno  and pt.parts_id = P.id and P.shop=1";
    $rs = $GLOBALS['db']->getAll($sql);
    if( !$rs ) {
        return false;
    } else {
        return $rs;
    }
}

function getAllArtikel($art="A") {
    if ($art == "A") { $where = ""; }
    else if ($art == "W") { $where = "where inventory_accno_id is not null and expense_accno_id is not null"; }
    else if ($art == "D") { $where = "where inventory_accno_id is null and expense_accno_id is not null"; }
    else if ($art == "E") { $where = "where inventory_accno_id is null and expense_accno_id is null"; };
    $sql = "SELECT * from parts $where order by description";
    $rs = $GLOBALS['db']->getAll($sql);
    if(!$rs) {
        return false;
    } else {
        return $rs;
    }
}

function getGrp($usrid,$inkluid=false){
    $sql = "select distinct(grpid) from grpusr where usrid=$usrid";
    $rs  = $GLOBALS['db']->getAll($sql);
    if( !$rs ) {
        if ( $inkluid ) { return "($usrid)"; }
        else { $data = false; };
    } else {
        if ( $rs ) {
           $data = "(";
            foreach( $rs as $row ) {
                $data .= $row["grpid"].",";
            };
            if ( $inkluid ) { $data .= "$usrid)"; }
            else { $data = substr($data,0,-1).")"; };
        } else {
            if ( $inkluid ) { $data .= "($usrid)"; }
            else { $data = false; };
        }
        return $data;
    }
};

function firstkw($jahr) {
    $erster = mktime(0,0,0,1,1,$jahr);
    $wtag = date('w',$erster);
    if ( $wtag <= 4 ) {
        // Donnerstag oder kleiner: auf den Montag zurueckrechnen.
        $montag = mktime(0,0,0,1,1-($wtag-1),$jahr);
    } else {
        // auf den Montag nach vorne rechnen.
        $montag = mktime(0,0,0,1,1+(7-$wtag+1),$jahr);
    }
    return $montag;
}

function mondaykw($kw,$jahr) {
    $firstmonday = firstkw($jahr);
    $mon_monat   = date('m',$firstmonday);
    $mon_jahr    = date('Y',$firstmonday);
    $mon_tage    = date('d',$firstmonday);
    $tage = ($kw-1)*7;
    $mondaykw    = mktime(0,0,0,$mon_monat,$mon_tage+$tage,$mon_jahr);
    return $mondaykw;

}
function clearCSVData() {
    return $GLOBALS['db']->query("delete from tempcsvdata where uid = '".$_SESSION["loginCRM"]."'");
}


/**
 * Schreibt eine :-separierte Zeichenkette in die Tabelle tempcsvdata unter csvdaten
 * Die Eintraege sind ueber die Benutzer-Session wieder auffindbar und werden immer 
 * wieder geloescht. TODO: Soll das immer so sein?, dann vielleicht das Loeschen in diese
 * Funktion integrieren?
 * Der erste Eintrag soll immer der INDEX sein, d.h. die entsprechenden Spaltennamen
 * TODO Ist es moeglich den ersten Eintrag zu pruefen, um somit weniger Fehler beim 
 * Aufrufen zu erzeugen? jb 16.6.2009
 *
 * @param string $data 
 *
 * @return $rc TODO auf boolean setzen und korrekte Fehlerbehandlung umsetzen
 */
function insertCSVData($data,$id){
    $tmpstr = implode(":",$data);                                               // ANREDE:NAME:STRASSE (...)
    $sql = "insert into tempcsvdata (uid,csvdaten,id) values ('"
            . $_SESSION["loginCRM"] . "','" . $tmpstr . "','"
            . $id. "')";

    /*$sql = "insert into tempcsvdata (uid,csvdaten,id) values ('"
            . $_SESSION["loginCRM"] . "','" . $GLOBALS['db']->saveData($tmpstr) . "','" 
            . $GLOBALS['db']->saveData($id) . "')";      */                                  // saveData escapt die Zeichenkette
    $rc = $GLOBALS['db']->query($sql);
    return $rc;     //Fehlerbehandlung? Wie sieht es aus mit exceptions? Muessen wir php4-kompatibel sein?
                    //Sollte eigentlich schon immer in den Funktionen direkt passieren 
                    // http://pear.php.net/manual/de/standars.errors.php
}

/**
 * Liest die entsprechenden Daten der :-separierte Zeichenkette aus der Tabelle tempcsvdata.
 * Ruft selber nur getAll auf und schiebt die Fehlermeldung dann nach oben. Frage:
 * Kann man das eleganter gestalten?
 * 
 * @return false, mixed
 */
function getCSVData(){
    $sql = "select * from tempcsvdata where uid = '" . $_SESSION["loginCRM"] .  "' ORDER BY id";
    return $GLOBALS['db']->getAll($sql);   //liefert false bei misserfolg
}

/**
 * Liefert die aktuell in der CRM-Session gespeicherte IDs.
 * Klammert die CSV-Kopfzeile aus, da nur ids groesser 0 aus der DB gelesen werden
 * False bei Misserfolg. Leider ansonsten Kopie von getCSVData. Ideen?
 * 
 * @return false, mixed
 */
function getCSVDataID(){
    $sql = "select id from tempcsvdata where uid = '" . $_SESSION["loginCRM"] .  "' and id > 0";
    return $GLOBALS['db']->getAll($sql);   //liefert false bei misserfolg
}
function startTime() {
    $zeitmessung = microtime();
    $zeittemp    = explode(" ",$zeitmessung);
    $zeitmessung = $zeittemp[0]+$zeittemp[1];
    return $zeitmessung;
}    
function stopTime($start) {
    $stop = startTime();
    $zeit = $stop - $start;
    return substr($zeit,0,8);
}

/**
 * TODO: Listen in Templates fuellen.
 *
 * @param object $t Template
 * @param string $tpl Templatename
 * @param string $pre Platzhalter / Blockname
 * @param array  $data Daten
 * @param string $id  Spaltenname fuer ID
 * @param string $text  Spaltenname fuer Text
 *
 * @return void
 */
function  doBlock(&$t,$tpl,$liste,$pre,$data,$id,$text,$selid) {
    $t->set_block($tpl,$liste,'Block'.$pre);
    if ( $data ) foreach ( $data as $row ) {
        if ( is_array($text) ) {
            $textval = '';
            foreach ( $text as $txt ) {
                $textval .= $row[$txt]." ";
            }
        } else {
            $textval =  $row[$text];
        }
        $x[$pre.'id']   = $row[$id];
        $x[$pre.'text'] = $textval;
        if ($selid) $x[$pre.'sel']  =  ($row[$id]==$selid)?"selected":"";
        $t->set_var($x);
        $t->parse('Block'.$pre,$liste,true);
    }
}
function mkHeader() {
    $pager = '<span id="pager" class="pager">
                                <form>
                                    <img src="'.$_SESSION["baseurl"].'crm/jquery/plugin/tablesorter-master/addons/pager/icons/first.png" class="first"/>
                                    <img src="'.$_SESSION["baseurl"].'crm/jquery/plugin/tablesorter-master/addons/pager/icons/prev.png" class="prev"/>
                                    <img src="'.$_SESSION["baseurl"].'crm/jquery/plugin/tablesorter-master/addons/pager/icons/next.png" class="next"/>
                                    <img src="'.$_SESSION["baseurl"].'crm/jquery/plugin/tablesorter-master/addons/pager/icons/last.png" class="last"/>
                                    <select class="pagesize" id="pagesize">
                                        <option value="10">10</option>
                                        <option value="20" selected>20</option>
                                        <option value="30">30</option>
                                        <option value="all">Alle</option>
                                    </select>
                                </form>
                            </span>';    
    $SV = '<script type="text/javascript" src="';
    $SN = '"></script>'."\n";
    $LV = '<link rel="stylesheet" type="text/css" href="';
    $LVID = '<link id="'.$_SESSION['theme'].'" rel="stylesheet" type="text/css" href="';
    $LN = '">'."\n";
    $head = array(
        'JQTABLE'       => $LV.$_SESSION['baseurl'].'crm/jquery/plugin/tablesorter-master/css/theme.jui.css'.$LN.
                           $LV.$_SESSION['baseurl'].'crm/jquery/plugin/tablesorter-master/addons/pager/jquery.tablesorter.pager.css'.$LN.
                           $SV.$_SESSION['baseurl'].'crm/jquery/plugin/tablesorter-master/js/jquery.tablesorter.js'.$SN.
                           $SV.$_SESSION['baseurl'].'crm/jquery/plugin/tablesorter-master/js/jquery.tablesorter.widgets.js'.$SN.
                           $SV.$_SESSION['baseurl'].'crm/jquery/plugin/tablesorter-master/addons/pager/jquery.tablesorter.pager.js'.$SN.
                           $SV.$_SESSION['baseurl'].'crm/js/tablesorter.js'.$SN,
        'JQTABLE-BUILD' => $SV.$_SESSION['baseurl'].'crm/jquery/plugin/tablesorter-master/js/widgets/widget-build-table.js'.$SN, // Achtung class="tablesorter" nicht verwenden
        'JQBOX'         => $SV.$_SESSION['baseurl'].'crm/jquery/plugin/selectBoxIt/selectBoxIt.js'.$SN,
        'JQPRINT'       => $SV.$_SESSION['baseurl'].'crm/jquery/plugin/Print_Any_Part/jQuery.print.js'.$SN,
        'JQTIMECSS'     => $LV.$_SESSION['baseurl'].'crm/jquery/plugin/timepicker/jquery.ui.timepicker.css'.$LN,
        'JQTIME'        => $SV.$_SESSION['baseurl'].'crm/jquery/plugin/timepicker/jquery.ui.timepicker.js'.$SN.
                           $SV.$_SESSION['baseurl'].'crm/jquery/plugin/timepicker-master/i18n/jquery.ui.timepicker-'.$_SESSION['countrycode'].'.js'.$SN,
        'JQFILEUP'      => $LV.$_SESSION['baseurl'].'crm/jquery/plugin/FileUpload/css/jquery.fileupload-ui.css'.$LN.
                           $SV.$_SESSION['baseurl'].'crm/jquery/plugin/FileUpload/js/jquery.iframe-transport.js'.$SN.
                           $SV.$_SESSION['baseurl'].'crm/jquery/plugin/FileUpload/js/jquery.fileupload.js'.$SN,
        'JQFILEDOWN'    => $SV.$_SESSION['baseurl'].'crm/jquery/plugin/jquery.fileDownload.js'.$SN,
        'JQWIDGET'      => $SV.$_SESSION['baseurl'].'crm/jquery/ui/minified/jquery.ui.widget.min.js'.$SN,
        'THEME'         => ($_SESSION['theme']!='')? $LVID .$_SESSION['baseurl'].'crm/jquery/themes/'.$_SESSION['theme'].'/jquery-ui.css'.$LN:'<link id="blue-style" rel="stylesheet" type="text/css" href="'.$_SESSION['baseurl'].'crm/jquery/themes/blue-style/jquery-ui.css'.$LN,
        'CRMCSS'        => $LV.$_SESSION['baseurl'].'crm/css/'.$_SESSION["stylesheet"].'/main.css'.$LN.
                           $SV.$_SESSION['baseurl'].'crm/inc/help.js'.$SN,
        'BOXCSS'        => $LV.$_SESSION['baseurl'].'crm/jquery/plugin/selectBoxIt/selectBoxIt.css'.$LN,
        'JUI-DROPDOWN'  => $LV.$_SESSION['baseurl'].'crm/jquery/plugin/jui_dropdown-master/jquery.jui_dropdown.css'.$LN.
                           $SV.$_SESSION['baseurl'].'crm/jquery/plugin/jui_dropdown-master/jquery.jui_dropdown.min.js'.$SN,
        'AUTOCOMPLETE'  => $LV.$_SESSION['baseurl'].'crm/css/autocomplete.css'.$LN,
        'CRMPATH'       => $_SESSION['baseurl'].'crm/' ,
        'FULLCALCSS'    => $LV.$_SESSION['baseurl'].'crm/jquery/plugin/fullcalendar2/fullcalendar.css'.$LN.
                           $LV.$_SESSION['baseurl'].'crm/jquery/plugin/fullcalendar2/fullcalendar.print.css" media="print'.$LN,
        'FULLCALJS'     => $SV.$_SESSION['baseurl'].'crm/jquery/plugin/fullcalendar2/lib/moment.min.js'.$SN.
                           $SV.$_SESSION['baseurl'].'crm/jquery/plugin/fullcalendar2/fullcalendar.min.js'.$SN. 
                           $SV.$_SESSION['baseurl'].'crm/jquery/plugin/fullcalendar2/lang/'.$_SESSION['countrycode'].'.js'.$SN, 
        'COLORPICKERCSS'=> $LV.$_SESSION['baseurl'].'crm/jquery/plugin/colorPicker/syronex-colorpicker.css'.$LN,
        'COLORPICKERJS' => $SV.$_SESSION['baseurl'].'crm/jquery/plugin/colorPicker/syronex-colorpicker.js'.$SN,
        'JTABLECSS'     => $LV.$_SESSION['baseurl'].'crm/jquery/plugin/jtable/themes/jqueryui/jtable_jqueryui.min.css'.$LN,
        'JTABLEJS'      => $SV.$_SESSION['baseurl'].'crm/jquery/plugin/jtable/jquery.jtable.min.js'.$SN,
        'JQCOOKIE'      => $SV.$_SESSION['baseurl'].'crm/jquery/plugin/cookie/jquery.cookie.js'.$SN,
        'TINYMCE'       => $SV.$_SESSION['baseurl'].'crm/jquery/plugin/tinymce/tinymce.min.js'.$SN,
        'DATEPICKER'    => $_SESSION['countrycode'] == 'de' ? $SV.$_SESSION['baseurl'].'js/jquery/ui/i18n/jquery.ui.datepicker-de.js'.$SN : '',
        'FANCYBOX'      => $LV.$_SESSION['baseurl'].'crm/jquery/plugin/fancybox/source/jquery.fancybox.css'.$LN.
                           $SV.$_SESSION['baseurl'].'crm/jquery/plugin/fancybox/source/jquery.fancybox.pack.js'.$SN,
        'QRCODE'        => $SV.$_SESSION['baseurl'].'crm/jquery/plugin/qrcode/jquery.qrcode-0.12.0.js'.$SN,
        'JQTABLE-PAGER' => $pager        
    );
        
    return $head;

}
function doHeader(&$t) {
    $head = mkHeader();
    $menu =  $_SESSION['menu'];
    $t->set_var(array(
        'JAVASCRIPTS'   => $menu['javascripts'],
        'STYLESHEETS'   => $menu['stylesheets'],
        'PRE_CONTENT'   => $menu['pre_content'],
        'START_CONTENT' => $menu['start_content'],
        'END_CONTENT'   => $menu['end_content'],
        'JQTABLE'       => $head['JQTABLE'],
        'JQWIDGET'      => $head['JQWIDGET'],
        'JQFILEUP'      => $head['JQFILEUP'],
        'JQFILEDOWN'    => $head['JQFILEDOWN'],
        'THEME'         => $head['THEME'],
        'CRMCSS'        => $head['CRMCSS'],
        'CRMPATH'       => $head['CRMPATH'],
        'BOXCSS'        => $head['BOXCSS'],
        'JQBOX'         => $head['JQBOX'],
        'FULLCALCSS'    => $head['FULLCALCSS'],
        'FULLCALJS'     => $head['FULLCALJS'],
        'JQCOOKIE'      => $head['JQCOOKIE' ],
        'JQTIME'        => $head['JQTIME'],
        'JQTIMECSS'     => $head['JQTIMECSS'],
	    'TINYMCE'	    => $head['TINYMCE'] ,
        'AUTOCOMPLETE'  => $head['AUTOCOMPLETE'],
        'DATEPICKER'    => $head['DATEPICKER'],
        'FANCYBOX'      => $head['FANCYBOX'],
        'QRCODE'        => $head['QRCODE'],
    ));
}
/**
 * TODO: short description.
 * 
 * @return TODO
 */
function getLanguage() {
    $sql = "select id,description from language";
    return $GLOBALS['db']->getAll($sql);
}
function nextNumber($number) {
    $GLOBALS['db']->begin();
    $sql = "select $number from defaults";
    $rs  = $GLOBALS['db']->getOne($sql);
    preg_match("/([^\d]*)([\d]+)([^\d]*)/",$rs[$number],$hit);
    $nr  = $hit[1].($hit[2]+1).$hit[3];
    $sql = "update defaults set $number = '$nr'";
    $rc  = $GLOBALS['db']->query($sql,"nextnumber");
    if ( !$rc ) {
        $GLOBALS['db']->rollback();
        return false;
    } else {
        $GLOBALS['db']->commit();
        return $nr;
    }
}

/***************************************************************
*** erzeugt valide Verzeichnisnamen für viele Betriebsysteme ***
*** invalide Zeichen sind: Umlaute  \ / : * ? " < > |        ***
***************************************************************/
function mkDirName($name) {
//sollte eigentlich in stdLib, diese müsste dann jedoch utf-8 codiert sein! 
    $ers = array(
        ' ' => '_',  'ä' => 'ae', 'â' => 'ae', 'ã' => 'ae', 'à' => 'ae', 'á' => 'ae', 'ç' => 'c',
        'ï' => 'i',  'í' => 'i',  'ì' => 'i',  'î' => 'i',  'ö' => 'oe', 'ó' => 'oe', 'ò' => 'oe',
        'õ' => 'oe', 'ü' => 'ue', 'ú' => 'ue', 'ù' => 'ue', 'û' => 'ue', 'Ä' => 'Ae', 'Â' => 'Ae',
        'Ã' => 'Ae', 'Á' => 'Ae', 'À' => 'Ae', 'Ç' => 'C',  'É' => 'E',  'È' => 'E',  'Ê' => 'E',
        'Ë' => 'E',  'Í' => 'I',  'Ì' => 'I',  'Î' => 'I',  'Ï' => 'I',  'Ö' => 'Oe', 'Ó' => 'Oe',
        'Ò' => 'Oe', 'Õ' => 'Oe', 'Ô' => 'Oe', 'Ü' => 'Ue', 'Ú' => 'Ue', 'Ù' => 'Ue', 'Û' => 'Ue',
        '\\'=> '_',  'ß' => 'ss', '/' => '_' , ':' => '_',  '*' => '_',  '?' => '_',  '"' => '_',
        '<' => '_',  '>' => '_',  '|' => '_' , ',' => '' );
    return strtr($name,$ers);
}

/*******************************************************************************************************************************************************
*** History wird mit Parameter schreibend benutzt, ohne Paramter gibt Sie die History zurück ***********************************************************
*******************************************************************************************************************************************************/
function accessHistory( $data=false ) {
        $sql  = "select val from crmemployee where uid = '" . $_SESSION["loginCRM"];
        $sql .= "' AND manid = ".$_SESSION['manid']." AND key = 'search_history'";
        $rs   = $GLOBALS['db']->getOne( $sql );
        $array_of_data = json_decode( $rs['val'], true );
        if( is_array ( $array_of_data ) )
            if ( isset ($array_of_data[0]) AND  !is_array ( $array_of_data[0] ) ) 
                unset( $array_of_data[0] );
        
        if ( !$data && $array_of_data ) {
             return array_reverse( $array_of_data );
        } else {
            if ( $array_of_data && in_array( $data, $array_of_data ) ) unset( $array_of_data[array_search( $data, $array_of_data )] );
            $array_of_data[] = $data;
            if ( count( $array_of_data ) > 8 ) array_shift( $array_of_data ); 
            $sql  =  "UPDATE crmemployee SET val = '".json_encode( $array_of_data )."' WHERE uid = ".$_SESSION['loginCRM'];
            $sql .= " AND manid = ".$_SESSION['manid']." AND key = 'search_history'";
            $GLOBALS['db']->query( $sql );
        }
}

function getCurrencies() {
    $sql = "SELECT * from currencies";
    $rs = $GLOBALS['db']->getAll($sql);
    return $rs;
}
?>
