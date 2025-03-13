<?php
include ( 'inc/carddav.php' );
require_once 'Contact_Vcard_Parse.php';

//$carddav = new carddav_backend('http://silent/oc/remote.php/carddav/addressbooks/hli/kontakte');
$carddav = new carddav_backend('http://192.168.1.35/owncloud/remote.php/carddav/addressbooks/hli/kontakte/');
$carddav->set_auth('hli', '27123');
//var_dump($carddav->check_connection());
$xml = $carddav->get_xml_vcard(); //'8c238181-1db4-4686-9d81-ad81152f7c3e');
$obj = simplexml_load_string($xml);
//print_r($obj);
echo $obj->element->id."\n";
$vcard = $obj->element->vcard;
$parse = new Contact_Vcard_Parse();
$cardinfo = $parse->fromText($vcard);
print_r($cardinfo);
//echo $carddav->get();

?>
