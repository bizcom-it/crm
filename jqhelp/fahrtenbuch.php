<?php
unset($GLOBALS['php_errormsg']);
require_once("../inc/stdLib.php");

function getFahrer($id) {
    $sql = 'SELECT * FROM fahrtenbuch WHERE fahrer = '.$id.' ORDER by datum desc, startzeit desc limit 1';
    $rs  = $GLOBALS['db']->getOne( $sql );
    fputs($GLOBALS['f'],print_r($rs,true));
    echo json_encode( $rs );
}
function suchen($data) {
    $sql = "SELECT * FROM fahrtenbuch WHERE fahrer = ".$data['Fahrer'];
    if ( $data['Fahrzeug'] != '' ) $sql .= " and fahrzeug = '".$data['Fahrzeug']."'";
    if ( $data['DatumStart'] != '' && $data['DatumStop'] == '' ) { $start = date2db($data['DatumStart']); $sql .= " and  datum >= '$start'"; };
    if ( $data['DatumStart'] == '' && $data['DatumStop'] != '' ) { $stop  = date2db($data['DatumStop']);  $sql .= " and  datum <= '$stop'"; };
    if ( $data['DatumStart'] != '' && $data['DatumStop'] != '' ) { $start = date2db($data['DatumStart']); 
                                                                   $stop  = date2db($data['DatumStop']); 
                                                                   $sql .= " and  datum >= '$start' and datum <= 'stop'"; };
    fputs($GLOBALS['f'],"Suchen\n");
    fputs($GLOBALS['f'],$sql."\n");
    $rs = $GLOBALS['db']->getAll($sql.' ORDER BY datum,startzeit');
    fputs($GLOBALS['f'],print_r($rs,true));
    $tabrow  = '<tr onClick="delRow(%d)" id="row%d"><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>';
    $tmp     = '';
    $km      = 0;
    $startkm = 0;
    if ( $rs ) { 
        $startkm = $rs[0]['startkm'];
        foreach ( $rs as $row ) {
            $tmp .= sprintf($tabrow,$row['id'],$row['id'],db2date($row['datum']),$row['fahrzeug'],substr($row['startzeit'],0,5),substr($row['stopzeit'],0,5),$row['startkm'],$row['stopkm'],$row['reisegrund'])."\n";
        };
        $km = $row['stopkm'] - $startkm;
    };
    fputs($GLOBALS['f'],$tmp."\n");
    echo json_encode( array('tabelle'=>$tmp,'kmgesamt'=>$km)  );
}
function sichern($data) {
    $Datum = date2db($data['Datum']);
    $sql  = 'INSERT INTO fahrtenbuch (datum,startzeit,stopzeit,reisegrund,startkm,stopkm,fahrer,fahrzeug) VALUES (';
    $sql .= "'$Datum','".$data['StartTime'].":00','".$data['StopTime'].":00','".$data['Grund']."','";
    $sql .= $data['KmStart']."','".$data['KmStop']."','".$data['Fahrer']."','".$data['Fahrzeug']."')";
    $rc   = $GLOBALS['db']->query($sql);
    fputs($GLOBALS['f'],'Sichern');
    fputs($GLOBALS['f'],print_r($rc,true));
    if ( $rc ) {echo true;} else {echo false;};
}
function delRow($id) {
    $sql  = "DELETE FROM fahrtenbuch WHERE id = ".$id;
    $rc   = $GLOBALS['db']->query($sql);
    if ( $rc ) {echo true;} else {echo false;};
}
function getLast($id) {
    $tabrow = '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>';
    $sql = 'SELECT * FROM fahrtenbuch WHERE fahrer = %d order by datum desc, startzeit desc limit 10';
    $rs  = $GLOBALS['db']->getAll(sprintf($sql,$id));
    $tmp = '';
    fputs($GLOBALS['f'],print_r($rs,true));
    if ( $rs ) foreach ( $rs as $row ) {
        $tmp .= sprintf($tabrow,db2date($row['datum']),$row['fahrzeug'],substr($row['startzeit'],0,5),substr($row['stopzeit'],0,5),$row['startkm'],$row['stopkm'],$row['reisegrund']);
    }
    echo $tmp;
}

$task     = (isset($_POST['task']))?$_POST['task']:'initBericht';
$f=fopen('/tmp/ana.log','a');
fputs($f,print_r($_POST,true)."\n");
fputs($f,$task."\n");

switch( $task ){
    case "getUsers":
            $sql = "SELECT id AS value, CASE WHEN name='' or name IS null THEN login ELSE name END AS text FROM employee WHERE deleted = FALSE "; //login
            $rs  = $GLOBALS['db']->getJson( $sql, array('vaule'=>'','text'=>'- - - - - - - -'));
            echo $rs;
    break;
    case "initUser":
        $usr['loginCRM']    = $_SESSION['loginCRM'];
        $usr['countrycode'] = $_SESSION['countrycode'];
        echo json_encode( $usr );
    break;
    case "getFahrer":
            getFahrer($_POST['id']);
    break;
    case "getLast":
            getLast($_POST['id']);
    break;
    case "sichern":
        sichern($_POST);
    break;
    case "suchen":
        suchen($_POST);
    break;
    case "delete":
        delRow($_POST['ID']);
    break;
};

?>
