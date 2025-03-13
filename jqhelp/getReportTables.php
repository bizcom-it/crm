<?php
    include_once("../inc/stdLib.php");


    $tab      = $_GET['tab'];
    $tabellen = array("shipto"=>'shipto', "contacts"=>'contacts');
    if ( $tab == 'C' ) {
        $tabellen['firma'] = 'customer';
    } else if ( $tab == 'V' ) {
        $tabellen['firma'] = 'vendor';
    } else {
        return '';
    }
    $noshow = array("itime","mtime");
 	foreach( $tabellen as $key=>$val ) {
		$sql  = "SELECT a.attname FROM pg_attribute a, pg_class c WHERE ";
		$sql .= "c.relname = '$val' AND a.attnum > 0 AND a.attrelid = c.oid ORDER BY a.attnum";
		$rs   = $GLOBALS['db']->getAll($sql);
		if ( $rs ) { 
            $pre =  substr($key,0,1);
			foreach ( $rs as $row ) {
				if ( !in_array($row["attname"],$noshow) )
					$felder[$key][] = $row["attname"];
			}
		} else {
			$felder[$key] = false;
		}
	}
    echo json_encode(array('tables'=>$felder));
?>
