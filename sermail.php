<?php
// $Id$
    require_once("inc/stdLib.php");
    include_once("template.inc");
    include_once("crmLib.php");
    include_once("UserLib.php");
    $menu = $_SESSION['menu'];
    $head = mkHeader();
    $sendtxt  = '';
    $MailSign = '';
    $CC       = '';
    $msg      = '';
    $Subject  = '';
    $tmpdata = getUserEmployee(array('interv','feature_ac_minlength','feature_ac_delay'));
    $t = new Template($base);
    if ( $_GET['src'] == 'F' ) {
        $t->set_var(array(
            'STYLESHEETS'   => $menu['stylesheets'],
            'CRMCSS'        => $head['CRMCSS'],
            'JAVASCRIPTS'   => $menu['javascripts'],
            'PRE_CONTENT'   => '',
            'START_CONTENT' => '',
            'END_CONTENT'   => '',
            'THEME'         => $head['THEME'],
            'FILEUP'        => $head['JQFILEUP'],
            'feature_ac_minlength'  => $tmpdata['feature_ac_minlength'],
            'feature_ac_delay'      => $tmpdata['feature_ac_delay'],
            //'JQDATE'        => $head['JQDATE'],
        ));
    } else {
        $t->set_var(array(
            'STYLESHEETS'   => $menu['stylesheets'],
            'CRMCSS'        => $head['CRMCSS'],
            'JAVASCRIPTS'   => $menu['javascripts'],
            'PRE_CONTENT'   => $menu['pre_content'],
            'START_CONTENT' => $menu['start_content'],
            'END_CONTENT'   => $menu['end_content'],
            'THEME'         => $head['THEME'],
            'FILEUP'        => $head['JQFILEUP'],
            'feature_ac_minlength'  => $tmpdata['feature_ac_minlength'],
            'feature_ac_delay'      => $tmpdata['feature_ac_delay'],
            //'JQDATE'        => $head['JQDATE'],
        ));
    }
    $user     = getUserStamm($_SESSION["loginCRM"]);
    $MailSign = str_replace("\n","<br>",$user["mailsign"]);
    $MailSign = str_replace("\r","",$MailSign);
    $BodyText=" \n".str_replace("\r","",$user["mailsign"]);

    $t->set_file(array("mail" => "sermail.tpl"));

    $t->set_block('mail','Betreff','Block');
    $mailvorlagen = getMailVorlage();
    if ($mailvorlagen) foreach ($mailvorlagen as $vorlage) {
        $t->set_var(array(
            'MID'    => $vorlage['id'],
            'CAUSE'  => $vorlage['cause']
        ));
        $t->parse('Block','Betreff',true);
    }    

    $t->set_var(array(
                'Msg'      => $msg,
                'CC'       => $CC,
                'Subject'  => $Subject,
                'BodyText' => $BodyText,
                'Sign'     => $MailSign,
                'SENDTXT'  => $sendtxt
    ));
    $t->pparse("out",array("mail"));
            
?>
