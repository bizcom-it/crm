<?php
$db = pg_connect( "host=localhost port=5432 dbname=demo user=lxoffice password=lxgeheim");
$sql = "UPDATE wissencategorie set name = $1,hauptgruppe = $2,kdhelp = $3 WHERE id=123";
$values = array('test',0,'f');
$rs = pg_query_params($db, $sql, $values);
echo $rs;
?>
