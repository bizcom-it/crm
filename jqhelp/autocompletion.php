<?php 
//Sourcefile for Autocompletion 
if (empty($_GET['term'])) exit;
require_once("../inc/stdLib.php"); 
if ($_GET['case']=='name') { 
    require_once("crmLib.php");     
    require_once("FirmenLib.php"); 
    require_once("persLib.php");
    require_once("UserLib.php"); 
    $suchwort = mkSuchwort($_GET['term']); 
    $rsC = getAllFirmen($suchwort,true,"C"); 
    $rsV = getAllFirmen($suchwort,true,"V"); 
    $rsK = getAllPerson($suchwort); 
    $rsE = getAllUser($suchwort); 
    $rs = array(); 
    if ($rsC) foreach ( $rsC as $key => $value ) { 
        if (count($rs) > 11) break;
        array_push($rs,array('label'=>$value['name'],'category'=>translate('.:customers:.','firma'),'src'=>'C','id'=>$value['id'])); 
    } 
    if ($rsV) foreach ( $rsV as $key => $value ) {
        if (count($rs) > 11) break; 
        array_push($rs,array('label'=>$value['name'],'category'=>translate('.:vendors:.','firma'),'src'=>'V','id'=>$value['id']));
        if(isset($_GET['src']) && $_GET['src']=='cv') {
            echo json_encode($rs); 
            return;
        }
    } 
    if ($rsK) foreach ( $rsK as $key => $value ) {
        if (count($rs) > 11) break;  
        array_push($rs,array('label'=>$value['cp_givenname']." ".$value['cp_name'],'category'=>translate('.:persons:.','firma'),'src'=>'K','id'=>$value['id'])); 
    } 
    if ($rsE) foreach ( $rsE as $key => $value ) {
        if (count($rs) > 11) break;  
        if ( $value['name'] == '' ) $value['name'] = $value['login'];
        array_push($rs,array('label'=>$value['name'],'category'=>translate('.:users:.','firma'),'src'=>'E','id'=>$value['id']));
    }
    echo json_encode($rs); 
} else if  ($_GET['case']=='employee') {
    require_once("crmLib.php"); 
    require_once("UserLib.php"); 
    $suchwort = mkSuchwort($_GET['term']); 
    $rsE = getAllUser($suchwort); 
    $rs = array(); 
    if ( $rsE ) foreach ( $rsE as $row ) {
        if (count($rs) > 11) break;  
        if ( $row['name'] == '' ) {
            $name = $row['login'];
        } else {
            $name = $row['name'];
        };
        $name = $name.' <'.$row['val'].'>';
        array_push($rs,array('label'=>$name,'category'=>'','src'=>'E','id'=>''));
    }
    echo json_encode($rs); 
} else if  ($_GET['case']=='ressource') {
    $term = $_GET['term'];
    $sql  = "SELECT ressource AS label, R.id AS src, RC.category,R.color ";
    $sql .= "FROM ressourcen R left join ressourcen_category RC ON RC.id=R.category ";
    $sql .= "WHERE R.ressource ilike '%".$term."%' OR RC.category ilike '%".$term."%' ";
    $sql .= "ORDER BY R.category,R.resorder";
    $res = $GLOBALS['db']->getJson($sql);
    echo $res;
}
?> 
