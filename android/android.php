<?php
require("androidLib.php");

if ( debug ) {
    include ('logging.php');
    $log = new logging();
} else {
    $log = false;
}


if ( $log ) $log->write("android:".print_r($_POST,true));

$db = authDB();

if ($db) {
    $session = authuser($db,$_POST['mandant'],$_POST["login"],$_POST["password"],$_POST["ip"]);
    if ( $log ) $log->write("androit2\n".print_r($session,true));
}
if ($session) {
    if ( $log ) { 
        $log->write("androit3\n");
        $log->close();
    };
    echo "200:".$session['sess'];
} else {
    if ( $log ) {
        $log->write("androit4\n");
        $log->close();
    };
    echo false;
}
?>
