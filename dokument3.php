<?php
    require_once('inc/stdLib.php');
    include('template.inc');
    include('persLib.php');
    
    if ( isSetVar($_POST['ok']) ) {
        $fid   = updDocFld($_POST);
        $docid = $_POST['docid'];
    } else if ( isSetVar($_POST['neu']) ) {
        $fid   = insDocFld($_POST);
        $docid = $_POST['docid'];
    }  else if ( isSetVar($_POST['del']) ) {
        $fid   = delDocFld($_POST);
        $docid = $_POST['docid'];
    } else {
        $docid = ( isSetVar($_GET['docid']) )?$_GET['docid']:$_POST['docid'];
    }
    $link2 = 'dokument2.php?did='.$docid;
    $link3 = 'dokument3.php?docid='.$docid;
    $doc   = getDocVorlage($docid);
    $t     = new Template($base);
    doHeader($t);
    $t->set_file(array('doc' => 'dokument3.tpl'));
    $t->set_var(array(
        'Link2'   => $link2,
        'Link3'   => $link3,
        'vorlage' => $doc['document']['vorlage']
    ));
    $t->set_block('doc','Liste','Block');
    if ($doc['felder']) {
        foreach($doc['felder'] as $zeile) {
            $t->set_var(array(
                'feldname_'     => $zeile['feldname'],
                'platzhalter_'  => $zeile['platzhalter'],
                'laenge_'       => $zeile['laenge'],
                'zeichen_'      => $zeile['zeichen'],
                'position_'     => $zeile['position'],
                'beschreibung_' => $zeile['beschreibung'],
                'docid'         => $zeile['docid'],
                'fid'           => $zeile['fid'],
            ));
            $t->parse('Block','Liste',true);
        }
    } else {
        $t->set_var(array(
            'Block' => '',
            'docid' => $docid
        ));
    }
    $t->pparse('out',array('doc'));
?>
