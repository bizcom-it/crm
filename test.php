<?php
    require_once('inc/stdLib.php');
    include_once('template.inc');
    $t = new Template($base);
    $t->set_file(array('test' => 'test.tpl'));
    $menu =  $_SESSION['menu'];
    $head = mkHeader();
    $t->set_var(array(
        'PRE_CONTENT'   => $menu['pre_content'],
        'START_CONTENT' => $menu['start_content'],
        'END_CONTENT'   => $menu['end_content'],
        'STYLESHEETS'   => $menu['stylesheets'],
        'JAVASCRIPTS'   => $menu['javascripts'],
        'CRMCSS'        => $head['CRMCSS'],
        'THEME'         => $head['THEME'],
        'TEST'          => 'Ein Test',
    ));
    $t->Lpparse('out',array('test'),$_SESSION['countrycode'],'work');
?>
