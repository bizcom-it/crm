<?php
require_once("../inc/stdLib.php"); // nur wegen chkdir
require('../jquery/plugin/FileUpload/server/php/UploadHandler.php');
$tmpdata = getUserEmployee(array('dir_mode','dir_group'));
if ( $tmpdata['dir_mode'] == '0' || $tmpdata['dir_mode'] == '') $tmpdata['dir_mode'] = '0775';
$f = fopen('/tmp/uploader.log','a');
fputs($f,"GET\n");
fputs($f,print_r($_GET,true));
fputs($f,"POSTT\n");
fputs($f,print_r($_POST,true));
//$options['mkdir_mode'] = $tmpdata['dir_mode'];
//$options['file_group'] = $tmpdata['dir_group'];
//error_reporting(E_ALL | E_STRICT);
if ( isset($_GET['DAV']) ) {
    $options['upload_dir'] = $_SESSION['erppath'].'webdav/'.$_GET['DAV'];
} else if ( isset($_GET['PART']) ) {
    chkdir($_GET['PART'],true);
    $options['upload_dir'] = $_SESSION['erppath'].$_GET['PART'];
    if ( isset($_POST['rename']) && $_POST['rename'] == '1' )   $options['rename'] = False;
} else {
    chkdir($_SESSION['login'].'/tmp/');
    $options['upload_dir'] = $_SESSION['erppath'].'crm/dokumente/'.$_SESSION['dbname'].'/'.$_SESSION['login'].'/tmp/';
    $options['upload_url'] = $_SESSION['baseurl'].'crm/dokumente/'.$_SESSION['dbname'].'/'.$_SESSION['login'].'/tmp/';
    if ( isset($_POST['rename']) && $_POST['rename'] == '1' )  {  $options['rename'] = False; } else {  $options['rename'] = True; } 
}
$options['image_versions'] = array();
$options['mkdir_mode'] = base_convert($tmpdata['dir_mode'],8,10);
//$options['mkdir_mode'] = 0775;
fputs($f,"Options\n");
fputs($f,print_r($options,true));
$upload_handler = new UploadHandler($options);
