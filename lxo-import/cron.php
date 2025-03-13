<?php
//Initialisierung
$login="mike";
$dbhost="localhost";
$dbport="";
$dbuser="lxoffice";
$dbpasswd="lxgeheim";
$dbname="myhard_de";
$trenner=",";
$trennzeichen=false;
$POST["precision"]="2";
$POST["quotation"]="0";
$POST["quottype"]="%";
$POST["TextUpd"]="1";
$POST["test"]="0";
$POST["update"]="U";
$POST["ware"]="W";
$POST["bugru"]="815";
$POST["bugrufix"]="2";
//$POST[""]="";

$date=date("Y-m-d H:i:s");
$log=fopen("/tmp/cron.log","a");
if ($argc<2) { echo "Fehlende Argumente\n"; fputs($log,$date." Cron Fehler\n"); exit(1); };
require ("import_lib.php");
require ("parts_import.php");
$conffile="../config/lx_office.conf";
if (!is_file($conffile)) {
     exit(-1);
}
function mkListe($db) { //Alle Artikelnummern in eine Datei schreiben
    $sql="select partnumber from parts order by partnumber";
    $rs=$db->getAll($sql);
    $file=@fopen("artikel.csv","w");
    if ($rs && $file) foreach ($rs as $row) {
        fputs($file,$row["partnumber"]."\n");
    }
    fclose($file);
}
function mkDiff() { //import.asc wird von parts_import erstellt und enthält alle importierten Artikel.
    $line = exec('sort import.asc artikel.csv | uniq -u > cleanup.csv',$out,$rc);
    return $rc;
}
/* get DB instance */
$db=new myDB($dbhost,$dbuser,$dbpasswd,$dbname,$port);
if ( !$db ) exit(-2);
if  ($argv[1]=="Import") {
    if (is_readable("parts.csv")) {
	echo import_parts($db, "parts", $trenner, $trennzeichen, $parts, False, True, False, $POST);
	if (file_exists("parts.csv.last")) unlink("parts.csv.last");
	rename("parts.csv","parts.csv.last");
	echo "\n";
	fputs($log,$date." Import ok\n");
        mkListe($db);
        if ( mkDiff() ) {;
           fputs($log,"Diff ok\n");
        } else {
           fputs($log,"Diff Error\n");
        }
    } else {
	echo "parts.csv nicht lesbar\n";
	fputs($log,$date." Import Fehler\n");
    }
} else if  ($argv[1]=="Liste") {
    mkListe($db);
    fputs($log,$date." Liste ok\n");
} else if  ($argv[1]=="Delete") {
    if (is_readable("cleanup.csv")) {
    	del_parts($db,"cleanup.csv");
	if (file_exists("cleanup.csv.last")) unlink("cleanup.csv.last");
        rename("cleanup.csv","cleanup.csv.last");
        fputs($log,$date." Delete ok\n");
    } else {
        echo "cleanup.csv nicht lesbar\n";
        fputs($log,$date." Delete Fehler\n");
    }
} else if  ($argv[1]=="Diff") {
    if ( mkDiff() ) {;
       fputs($log,"Diff ok\n");
    } else {
       fputs($log,"Diff Error\n");
    }
} else {
    echo "Argumentfehler\n";
    fputs($log,$date." Cron Fehler\n");
}
fclose($log);
exit(0);
?>
