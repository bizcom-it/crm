<?php
require("androidLib.php");

if ( debug ) {
    include ('logging.php');
    $log = new logging();
    $log->write("!get_addressdata!");
    $log->write(print_r($_POST,true));
} else {
    $log = false;
};


$dbA = authDB();
$auth = userData($dbA,$_POST["sessid"],$_POST["ip"],$_POST['mandant'],$_POST["login"],$_POST["password"],$f);

if ( $log ) $log->write("auth:".print_r($auth,true));
$BaseUrl  = (empty( $_SERVER['HTTPS'] )) ? 'http://' : 'https://';
$BaseUrl .= $_SERVER['HTTP_HOST'];
$BaseUrl .= preg_replace( "^crm/.*^", "", $_SERVER['REQUEST_URI'] );
if ( $log ) $log->write("URL:". $BaseUrl);


if ($auth['db']) {
    $db      = $auth['db'];
    $custsql = array();
    $vendsql = array();
    $contsql = array();
    $rs      = false;
    $tab     = $_POST["tab"];
    $id      = $_POST["ID"];

    $sql  = "SELECT p.*,pg.partsgroup,bg.description as bugru ";
    $sql .= "FROM parts p LEFT JOIN partsgroup pg ON partsgroup_id=pg.id ";
    $sql .= "LEFT JOIN buchungsgruppen bg ON bg.id=p.buchungsgruppen_id ";
    $sql .= "WHERE p.id = ".$_POST['ID'];
    $rs = $db->getOne($sql);
    header("Content-type: text/json; charset=utf-8;");   
    if ( !$rs ) {
        echo "";
    } else {
        if ( $log ) $log->write(print_r($rs,true));
        $sql  = "SELECT rate FROM tax WHERE id = ";
        $sql .= "(SELECT tax_id FROM taxkeys WHERE chart_id = ";
        $sql .= "(SELECT income_accno_id_0 FROM buchungsgruppen WHERE id = ".$rs['buchungsgruppen_id'].") ";
        $sql .= "AND startdate <= now() ORDER BY startdate DESC LIMIT 1)";
        $rst  = $db->getOne($sql);
        $tax  = $rst['rate'] + 1;
        $rs['vkbrutto'] = $rs['sellprice'] * $tax;
        $rs['tax'] = $rst['rate'] * 100;
        $sql  = "SELECT pricegroup,price FROM prices LEFT JOIN pricegroup pg ON ";
        $sql .= "pg.id=pricegroup_id WHERE parts_id = ".$rs['id'];
        $rsp  = $db->getAll($sql);
        if ( $rsp ) foreach ( $rsp as $row ) {
            $row['brutto'] = $row['price'] * $tax;
            $rs['prices'][] = $row;
        };
        $rs['onhand'] = $rs['onhand'] * 1;
        $sql  = "SELECT weightunit FROM defaults";
        $rsd  = $db->getOne($sql);
        $rs['weightunit'] = $rsd['weightunit'];
        if ( $rs['image'] != '' and file_exists($GLOBALS['basepath'].$rs['image']) ) {
            $imagedata = file_get_contents($GLOBALS['basepath'].$rs['image']);
            $rs['image'] = base64_encode($imagedata);
        }
        while( list($key,$val) = each($rs) ) {
             if ($val == Null) $rs[$key] = '';
        }
        print(json_encode($rs));
    };
};
if ( $log ) $log->close();
?>
