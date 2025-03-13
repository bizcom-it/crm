<?php

require("androidLib.php");

if ( debug ) {
    include ('logging.php');
    $log = new logging();
    $log->write("get_adress:\n".print_r($_POST,true));
} else {
    $log = false;
}

$dbA  = authDB();
$auth = userData($dbA,$_POST["sessid"],$_POST["ip"],$_POST['mandant'],$_POST["login"],$_POST["password"],$f);

if ( $log ) $log->write("Auth:".print_r($auth,true));
function mkwort($txt) {
    $txt = strtr($txt,'?*','_%');
    if ( substr($txt,0,1) == '!' ) return substr($txt,1);
    return '%'.$txt;
}
if ($auth["db"]) {
    $postarray = array("partnumber","description","partsgroup");
    $sql = array();
    $rs = false;
    $db = $auth["db"];
    foreach( $postarray as $key ) {
        if ( !empty($_POST[$key] )) {
            if ( $key == 'partsgroup' ) {
                $sql[] = "pg.description ilike '".mkwort($_POST[$key])."%'";
            } else {
                $sql[] = "p.".$key." ilike '".mkwort($_POST[$key])."%'";
            }
        }
    }
    $offset  = ($_POST["offset"])?$_POST["offset"]:0;
    $max     = ($_POST["max"])?$_POST["max"]:25;
    $vonbis  = " offset $offset limit $max";
    $sqlfld  = "SELECT p.id,p.partnumber,p.description,p.sellprice,p.weight,p.unit,p.onhand,pg.partsgroup,bg.description as bugru ";
    $sqlfld .= "FROM parts p LEFT JOIN partsgroup pg ON partsgroup_id=pg.id ";
    $sqlfld .= "LEFT JOIN buchungsgruppen bg ON bg.id=p.buchungsgruppen_id ";
    $where  = "";
    if ($_POST["cbw"]=="t" AND $_POST["cbd"]=="f") {
        $where = "WHERE p.inventory_accno_id is not null ";
        $oe = false;
    } else if ($_POST["cbw"]=="f" AND $_POST["cbd"]=="t") {
        $where = "WHERE p.inventory_accno_id is null ";
        $oe = false;
    } else if ($_POST["cbw"]=="t" AND $_POST["cbd"]=="t") {
        $where = "1=1 ";
        $oe = false;
    } else {
        $where = "1=1 ";
        $oe = true;
    };
    if ($_POST["cb"]=="t" and $oe) {
        $where .= "AND assembly = 't' ";
    } else {
        $where .= "";
    };
    if ( count($sql) > 0 ) $where .= " AND ".join(" AND ",$sql);
    if ( $log ) $log->write($sqlfld.$where.$vonbis);

    $rs = $db->getAll($sqlfld.$where.$vonbis);

    header("Content-type: text/json; charset=utf-8;");   
    if ( !$rs ) {
        $rs[0] = array("id"=>"","partnumber"=>"Nichts gefunden","description"=>"","sellprice"=>"","weight"=>"","unit"=>"",
                       "onhand"=>"","partsgroup"=>"","bugru"=>""); 
    } else {
        $max = count($rs);
        if ($max > 25) {
            $rs = array_slice($rs,$offset,25);
        }
        $rs[] = array("max"=>$max,"offset"=>$offset);
    };
    if ( $log ) $log->write(print_r($rs,true));
    print(json_encode($rs));
} else {
    echo "NO";
}
if ( $log ) $log->close();
?>
