<?php
require_once("inc/stdLib.php");
include("template.inc");
$menu = $_SESSION['menu'];
$tab = $_POST['owner'];

function suchFelder($data) {
    $tab = $data['owner'];
    foreach ($data as $key=>$val) { 
        if ($key == "suche") continue;
        if ($key == "owner") continue;
        if ($val) $where .= "(fkey = '$key' and  fval ilike '$val%' and tab = '$tab') or ";
    }
    $where = substr($where,0,-3) ;
    $sqle="select owner from extra_felder where ".$where;
    if ($tab == "C") { 
        $sql = "select * from customer where id in ($sqle)";
    } else if ($tab == "V") {
        $sql = "select * from vendor where id in ($sqle)";
    } else if ($tab == "P") {
        $sql = "SELECT contacts.*,C.name as cfirma,V.name as vfirma  ";
        $sql.= "from contacts left join customer C on C.id=cp_cv_id ";
        $sql.= "left join vendor V on V.id=cp_cv_id where cp_id in ($sqle)";
    }
    $rs = $GLOBALS['db']->getAll($sql);
    return $rs;
}

$daten = suchFelder($_POST);
if (count($daten)==1 && $daten<>false) {
    if ( $tab == 'P' ) {
        header ("location:kontakt.php?id=".$daten[0]["cp_id"]);
    } else {
        header ("location:firma1.php?Q=$tab&id=".$daten[0]["id"]);
    }
} else if (count($daten)>1) {
    $t = new Template($base);
    doheader($t);
    $t->set_file(array('fa1' => 'extrafelderR.tpl'));
    $t->set_block('fa1','Liste','Block');
    if ( $tab == 'C' ) {
        $t->set_var(array('FAART' => 'Company',));
    } else {
        $t->set_var(array('FAART' => 'Vendor',));
    }
    $i=0;
    clearCSVData();
    if ($tab == "P") {
        insertCSVData(array("ANREDE","TITEL","NAME1","NAME2","LAND","PLZ","ORT","STRASSE",
                    "TEL","FAX","EMAIL","FIRMA","FaID","GESCHLECHT","ID","TABLE"),-1);
    } else {
        insertCSVData(array("ANREDE","NAME1","NAME2","LAND","PLZ","ORT","STRASSE","TEL","FAX","EMAIL",
                    "KONTAKT","ID","KDNR","USTID","STEUERNR","KTONR","BANK","BLZ","LANG","KDTYP","TABLE"),-255);
    }
    foreach ($daten as $zeile) {
        if ($tab == "P") {
           insertCSVData(array($zeile["cp_greeting"],$zeile["cp_title"],$zeile["cp_name"],$zeile["cp_givenname"],
                    $zeile["cp_country"],$zeile["cp_zipcode"],$zeile["cp_city"],$zeile["cp_street"],
                    $zeile["cp_phone1"],$zeile["cp_fax"],$zeile["cp_email"],(($zeile["cfirma"])?$zeile["cfirma"]:$zeile["vfirma"]),
                    $zeile["cp_cv_id"],$zeile["cp_gender"],'P'),$zeile["cp_id"]);
            if ( $i <= $_SESSION['listLimit'] ) {
                $ID       = $zeile["cp_id"];
                $KdNr     = $zeile["cp_id"];
                $Name     = $zeile["cp_name"].", ".$zeile["cp_givenname"];
                $Plz      = $zeile["cp_zipcode"];
                $Ort      = $zeile["cp_city"];
                $Strasse  = '';
                $Telefon  = $zeile["cp_phone1"];
                $eMail    = $zeile["cp_email"];
                $obsolet  = '';
            }
        } else {
           insertCSVData(array($zeile["greeting"],$zeile["name"],$zeile["department_1"],
                    $zeile["country"],$zeile["zipcode"],$zeile["city"],$zeile["street"],
                    $zeile["phone"],$zeile["fax"],$zeile["email"],$zeile["contact"],$zeile["id"],
                    ($Q=="C")?$zeile["customernumber"]:$zeile["vendornumber"],
                    $zeile["ustid"],$zeile["taxnumber"],
                    $zeile["account_number"],$zeile["bank"],$zeile["bank_code"],
                    $zeile["language"],$zeile["business_id"],$tab),$zeile["id"]);
            if ( $i <= $_SESSION['listLimit'] ) {
                $ID       = $zeile["id"];
                $KdNr     = ($maske=="C")?$zeile["customernumber"]:$zeile["vendornumber"];
                $Name     = $zeile["name"];
                $Plz      = $zeile["zipcode"];
                $Ort      = $zeile["city"];
                $Strasse  = $zeile['street'];
                $Telefon  = $zeile["phone"];
                $eMail    = $zeile["email"];
                $obsolete = ($zeile['obsolete']=='t')?'.:yes:.':'';
            };
        }
        if ( $i <= $_SESSION['listLimit'] ) {
                $t->set_var(array(
                    'tab'       => $tab,
                    'ID'        => $ID,
                    'LineCol'   => ($i%2+1),
                    'KdNr'      => $KdNr,
                    'Name'      => $Name,
                    'Plz'       => $Plz,   
                    'Ort'       => $Ort,    
                    'Strasse'   => $Strasse,
                    'Telefon'   => $Telefon,
                    'eMail'     => $eMail,
                    'obsolete'  => $obsolete,
                ));
                $t->parse("Block","Liste",true);
                $i++;
                if ( $i >= $_SESSION['listLimit'] ) {
                    $t->set_var(array(
                        'report' => $_SESSION['listLimit'].' von '.count($daten).' Treffer',
                    ));
                }
                $t->set_var(array(
                    'CRMTL' => ($_SESSION['CRMTL'] == 1)?'visible':'hidden'
                ));
            }
        }
} else {
    header('location:extrafelderS.php?notfound=1&owner='.$tab);
}
$t->Lpparse("out",array("fa1"),$_SESSION['countrycode'],"firma");
$t->pparse("out",array("extra"));
?>
