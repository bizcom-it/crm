<?php
    require_once("inc/stdLib.php");
    include_once("template.inc");
    //include_once("UserLib.php");

    $tpl = new Template($base);
    $tpl->set_file(array("wi" => "wissen.tpl"));
    $menu =  $_SESSION['menu'];

    if ( isset($_GET['kdhelp']) && $_GET["kdhelp"] > '0') {
        $popup = 'hidden';
        $close = 'visible';
        $head = mkHeader();
        $tpl->set_var(array(
            'JAVASCRIPTS'   => $menu['javascripts'],
            'STYLESHEETS'   => $menu['stylesheets'],
            'PRE_CONTENT'   => '',
            'START_CONTENT' => '',
            'END_CONTENT'   => '',
            'THEME'         => $head['THEME'],
            'CRMCSS'        => $head['CRMCSS'],
	        'TINYMCE'       => $head['TINYMCE'],
            'baseurl'       => $_SESSION['baseurl'],
        ));
        $init = "var initkat = ".$_GET['kdhelp'].";\n";
    } else {
        $popup = 'visible';
        $close = 'hidden';
        doHeader($tpl);
        $init = "\tvar initkat = -1;\n";
        $tpl->set_var(array(
            'baseurl'       => $_SESSION['baseurl'],
        ));
    }
    $tmpdata = getUserEmployee(array('tinymce'));
    if ($tmpdata['tinymce']) {
        $tiny  =  '';//"<script language='javascript' type='text/javascript' src='".$_SESSION['baseurl']."crm/inc/tiny_mce/tiny_mce.js'></script>\n";
        $init  .= "\tvar tiny = true;\n";
    } else {
        $tiny  = '';
        $init  .= "\tvar tiny = false;\n";
    };

    $tpl->set_var(array(
        'init'     => $init,
        'popup'    => $popup,
        'close'    => $close,
        'PICUP'    => "false",
        'tiny'     => $tiny,
        ));

    $tpl->Lpparse("out",array("wi"),$_SESSION["countrycode"],"work");
?>
