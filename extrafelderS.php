<?php
require_once("inc/stdLib.php");
include("template.inc");
$menu = $_SESSION['menu'];
$maske = $_GET["owner"];
$t = new Template($base);
doheader($t);
if ( isset($_GET['notfound'] ) ) {
    $msg = 'Nichts gefunden!';
} else {
    $msg = '';
}
$t->set_file(array("extra" => "extra$maske.tpl"));
$visible = 'style="visibility:visible"';
$hidden  = 'style="visibility:hidden"';
$t->set_var(array(
    'visiblesichern' => $hidden,
    'visiblesuchen'  => $visible,
    'owner'          => $maske,
    'msg'            => $msg,
    'SUCHE'          => 'yes',
    'ZIEL'           => 'extrafelderFA.php',
));
$t->Lpparse("out",array("extra"),$_SESSION['countrycode'],'firma');
?>
