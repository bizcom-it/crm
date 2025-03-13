<?php
    require_once('inc/stdLib.php');
    include_once('template.inc');
    include_once('crmLib.php');
    
    $t = new Template($base);
    $tmpdata = getUserEmployee(array('tinymce'));

    if ( isset($_GET['P']) && $_GET['P']==1 ) {
        $menu =  $_SESSION['menu'];
        $head = mkHeader();
        $t->set_var(array(
            'JAVASCRIPTS'   => $menu['javascripts'],
            'STYLESHEETS'   => $menu['stylesheets'],
            'PRE_CONTENT'   => '',
            'START_CONTENT' => '',
            'END_CONTENT'   => '',
            'CRMPATH'       => $head['CRMPATH'],
            'THEME'         => $head['THEME'],
            'CRMCSS'        => $head['CRMCSS'],
        ));
    } else {
        doHeader($t);
    }
    $t->set_file(array('doc' => 'dokument.tpl'));
    $t->set_var(array(
            'PICUP'   => ( isset($_GET['P']) )?'true':'false',
            'mandant' => $_SESSION['dbname'],
            'tiny'    => ($tmpdata['tinymce'])?'true':'false',
            'BASEURL' => 'dokumente/'.$_SESSION['dbname'],
            'DAVURL'  => '../webdav/'.$_SESSION['manid'],
            //'BASEURL' => $_SESSION['baseurl'].'crm/dokumente/'.$_SESSION['dbname'],
            //'DAVURL'  => $_SESSION['baseurl'].'webdav/'.$_SESSION['manid'],
    ));
    $t->Lpparse('out',array('doc'),$_SESSION['countrycode'],'firma');
?>
