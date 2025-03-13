<?php

if(!isset($_SESSION)) session_start();
$_SESSION['logfile']    = true;
$_SESSION['errlogfile'] = true;
$_SESSION['erppath']    = '/var/www/openkonto33';
$_SESSION['sql_error']  = true;

require 'inc/PgSQL.php';

$dbcon['dbhost']        = 'localhost'; 
$dbcon['dbport']        = 5432;
$dbcon['dbuser']        = 'lxoffice';
$dbcon['dbpasswd']      = 'lxgeheim';
$dbcon['dbname']        = 'ok33';

$db = new myDB($dbcon);

$sql = "INSERT INTO test (name,id) VALUES ('%s',%d)";
//$db->begin();
//$rc = $db->query(sprintf($sql,'Hans',2));
//$rc = $db->insert('test',array('name'),array('Werner'),'id');
//$rc = $db->convert('test',array('name'=>'Susi','id'=>'10'));
//var_dump($rc);
//if  ( $rc ) { echo "ok\n";  $db->commit(); }
//else        { echo "err\n"; $db->rollback(); };
//$sql = "SELECT * FROM test";
//$rs  = $db->getOne($sql);
//$rs = $db->update('test',array('name'),array('Heinrich'),'id = 8');
$db->begin();
$rs = $db->executeMultiple('INSERT INTO test (name) VALUES ($1)',array(array('Beutel'),'Teufel',array('Klee'))); 
if  ( $rs ) { echo "ok\n";  $db->commit(); }
else        { echo "err\n"; $db->rollback(); };
var_dump($rs);
/*
$insert = "INSERT INTO crmdefaults (name,id) VALUES (?,?)";
$data   = array(array('Willi',2),array('Birgit',3),array('Chris',4));
$rc = $db->executeMultiple( $insert, $data );
*/
?>
