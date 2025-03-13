<?php
/*
Filter Helper-Klassen
*/
if ( substr(getcwd(),-3) == 'crm' ) {
    require_once("inc/stdLib.php");
} else {
    require_once("../inc/stdLib.php");
}

function getgruppen ( $id ) {
    $sql = 'SELECT * FROM filter_gruppen WHERE parent = '.$id.' ORDER BY id';
    $rs  = $GLOBALS['db']->getAll( $sql );
    if ( $rs ) {
        $rc = array( 'cnt' => 0, 'msg' => 'Error', 'data' => $rs );
    } else {
        $rc = array( 'cnt' => 0, 'msg' => 'Error', 'data' => false );
    };
    echo json_encode( $rc );
}

function readMainGroup($part_id = 0) {
   $sql = 'SELECT DISTINCT gruppe as value, gruppe as text FROM artikel_merkmale_wert';
   $rc  = $GLOBALS['db']->getAll( $sql );
   array_unshift( $rc , array('vaule'=>'','text'=>''));
   fputs($GLOBALS['f'],'readMainGroup:'.print_r($rc,true));
   if ( $part_id > 0 ) {
      $sql = 'SELECT * FROM artikel_to_wert where part_id = '.$part_id.' and wert in (SELECT id FROM artikel_merkmale WHERE parent = 0)';
      $sel = $GLOBALS['db']->getAll( $sql );
      if ( count($sel) > 0 ) { 
        $sel = $sel[0]['wert']; 
      } else { 
        $sel = false;
      };
   };
   $rg = false;
   if ( $sel ) $rg = readSubGroups($sel);
   echo json_encode(array('merkmale'=>$rc,'select'=>$sel,'subgroup'=>$rg));
}

function readSubGroups($gruppe = '') {
    #$sql = 'SELECT * FROM artikel_merkmale where parent = '.$parent.' ORDER by position';
    $sql = "SELECT * FROM artikel_merkmale_wert WHERE gruppe = (SELECT gruppe FROM artikel_merkmale WHERE id = $gruppe) ORDER BY poslabel,poswert";
    $rc  = $GLOBALS['db']->getAll( $sql );
    return $rc;
    #echo json_encode($rc);
}

$f = fopen('/tmp/filter.log','w');
fputs($f,'GET:'.print_r($_GET,true));
fputs($f,'POST'.print_r($_POST,true));
switch ($_GET['task']) {
    case 'getgrp'          : getgruppen( $_GET['id'] );
                             break;
    case 'readMainGroup'   : readMainGroup( $_GET['part_id'] );
                             break;
    default                : echo $_GET['task'].' nicht erlaubt';
};

fclose($f);

?>
