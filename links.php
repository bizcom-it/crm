<?php
/**********************************************************************
*** Erzeugt benutzerfreundliche Links zu den Verzeichnissen         ***
*** Autor: Ronny Kumke, ronny@lxcars.de   02.02.2012                ***
**********************************************************************/  
require_once("inc/stdLib.php");

$tmpdata = getUserEmployee(array('sep_cust_vendor','dir_mode','dir_group'));
if ( !isset($tmpdata['dir_mode']) )        $tmpdata['dir_mode'] = '0755';
if ( !isset($tmpdata['sep_cust_vendor']) ) $tmpdata['sep_cust_vendor'] = 1;

$dir_abs = $_SESSION['erppath']."crm/dokumente/$_SESSION[dbname]";
$link_dir_cust = $tmpdata['sep_cust_vendor'] ? "/link_dir_cust" : "/link_dir";
$link_dir_vend = $tmpdata['sep_cust_vendor'] ? "/link_dir_vend" : "/link_dir";

//Mandatendokumentverzeichnis:
if (!is_dir($dir_abs)) {
    mkdir($dir_abs);
    if ( $tmpdata['dir_group'] ) chgrp($dir_abs, $tmpdata['dir_group']);
}
chmod($dir_abs,octdec($tmpdata['dir_mode']));


//Verzeichnis für Links:
if (!is_dir($dir_abs.$link_dir_cust)) {
    mkdir($dir_abs.$link_dir_cust);
    if ( $tmpdata['dir_group'] ) chgrp($dir_abs.$link_dir_cust, $tmpdata['dir_group']);
}
chmod($dir_abs.$link_dir_cust, octdec($tmpdata['dir_mode']));


if (!is_dir($dir_abs.$link_dir_vend)) {
    mkdir($dir_abs.$link_dir_vend);
    if ( $tmpdata['dir_group'] ) chgrp($dir_abs.$link_dir_vend, $tmpdata['dir_group']);
}
chmod($dir_abs.$link_dir_vend, octdec($tmpdata['dir_mode']));

//Alle Links erzeugen (wird von status.php ausgelöst)
if ($_GET['all']) { 
    $menu = $_SESSION['menu'];
    $head = mkHeader();
    echo "<html><head>";
    echo $menu['stylesheets'];
    echo $menu['javascripts'];
    echo $head['CRMCSS']; 
    echo "</head><body>";
    echo $menu['pre_content'];
    echo $menu['start_content'];
	echo "<h1>Benutzerfreundliche Links in $dir_abs anlegen... </h1>";
	if (is_dir($dir_abs.$link_dir_cust)&&($link_dir_cust != $link_dir_vend)) {
  		if ($dh = opendir($dir_abs.$link_dir_cust)) {
            echo "Alte Links in ".$dir_abs.$link_dir_cust." löschen... </br>";
            while (($link = readdir($dh)) !== false) {
                if ($link != '.' && $link != '..') {
                    echo "lösche: $link </br>";
                    unlink($dir_abs.$link_dir_cust."/".$link);	
                }
            }
            closedir($dh);
        }
    }
    if (is_dir($dir_abs.$link_dir_vend)) {
		if ($dh = opendir($dir_abs.$link_dir_vend)) {
        	echo "Alte Links in ".$dir_abs.$link_dir_vend." löschen... </br>";
   		   	while (($link = readdir($dh)) !== false) {
	     		if ($link != '.' && $link != '..' ) {
	     			echo "lösche: $link </br>";
                    unlink($dir_abs.$link_dir_vend."/".$link);	
                }  
            }
            closedir($dh);
        }
    }
	$sql = "SELECT  name, customernumber FROM customer ORDER BY customernumber"; //::INT ";
    $rs = $GLOBALS['db']->getall($sql);
    if ($rs) { 
    	foreach ($rs as $key => $value) {
			if (!is_dir($dir_abs."/C".$rs[$key]['customernumber'])) {
    			echo "Erzeuge Verzeichnis: ".$dir_abs."/C".$rs[$key]['customernumber']." </br>";
    			mkdir($dir_abs."/C".$rs[$key]['customernumber']);
    		}
    		chmod($dir_abs."/C".$rs[$key]['customernumber'],octdec($tmpdata['dir_mode']));
    		if ( $tmpdata['dir_group'] ) chgrp($dir_abs."/C".$rs[$key]['customernumber'],$tmpdata['dir_group']);
    		echo "Erzeuge Symlink: ".$dir_abs.$link_dir_cust."/".mkDirName($rs[$key]['name'])."_C".$rs[$key]['customernumber']."</br>";
    	 	symlink($dir_abs."/C".$rs[$key]['customernumber'], $dir_abs.$link_dir_cust."/".mkDirName($rs[$key]['name'])."_C".$rs[$key]['customernumber']);
    	   //lchgrp( $dir_abs.$link_dir_cust."/".mkDirName($rs[$key]['name'])."_C".$rs[$key]['customernumber'], $tmpdata['dir_group']);  //funktioniert nicht.. warum???
		}
    }
    $sql = "SELECT  name, vendornumber FROM vendor ORDER BY vendornumber"; //::INT ";
    $rs = $GLOBALS['db']->getall($sql);
    if ($rs) { 
    	foreach ($rs as $key => $value) {
			if (!is_dir($dir_abs."/V".$rs[$key]['vendornumber'])) {
    			echo "Erzeuge Verzeichnis: ".$dir_abs."/V".$rs[$key]['vendornumber']." </br>";
    			mkdir($dir_abs."/V".$rs[$key]['vendornumber']);
    		}
    		chmod($dir_abs."/V".$rs[$key]['vendornumber'],octdec($tmpdata['dir_mode']));
    		if ( $tmpdata['dir_group'] ) chgrp($dir_abs."/V".$rs[$key]['vendornumber'],$tmpdata['dir_group']);
    		echo "Erzeuge Symlink: ".$dir_abs.$link_dir_vend."/".mkDirName($rs[$key]['name'])."_V".$rs[$key]['vendornumber']."</br>";
    	 	symlink($dir_abs."/V".$rs[$key]['vendornumber'], $dir_abs.$link_dir_vend."/".mkDirName($rs[$key]['name'])."_V".$rs[$key]['vendornumber']);
		}
    }
	echo "...done"; 
    echo $menu['end_content'];
    echo "</bod></html>";
}
?>
