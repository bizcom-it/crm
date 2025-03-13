<?php
    require_once('../inc/stdLib.php');
    include_once('template.inc');
    include_once('persLib.php');
    include_once('laender.php');
    include_once('UserLib.php');
    include_once('FirmenLib.php');
    $t = new Template('../tpl');
    doHeader($t);   
    if ( isSetVar($_POST['first']) && $_POST['first'] != '') {
        $_POST['cp_name'] = $_POST['first'];
        $_POST['fuzzy']='%';
    }
    //FID1 ist gesetzt, wenn ein neuer Kontakt aus der Liste einer Firma zugeordnet werden soll
    if ( isSetVar($_POST['suche']) || isSetVar($_POST['first']) ) {
        $daten = suchPerson($_POST);
        if ( count($daten) > $_SESSION['listLimit'] ) {
            echo '<script>$( "#dialog_viele" ).dialog( "open" );</script>';
        } if ( count($daten)==1 && $daten<>false && !isSetVar($_POST['FID1']) ) { 
            echo '<script> showK__("'.($daten[0]['cp_id']).'");</script>';
        } else if ( count($daten) > 1 ) {
            $t->set_file(array('pers1' => 'persons1Result.tpl'));
            $t->set_block('pers1','Liste','Block');
            $i=0;
            clearCSVData();
            $header = array('ANREDE','TITEL','NAME1','NAME2','LAND','PLZ','ORT','STRASSE','TEL','FAX','EMAIL','FIRMA','FaID','GESCHLECHT','ID','FATYP');
            $sql    = "select name from custom_variable_configs where module = 'CT'";
            $rs     = $GLOBALS['db']->getAll($sql);
            if ( $rs ) {
                $cvar = 0;
                foreach ($rs as $row) {
                    $cvheader[] = 'vc_cvar_'.$row['name'];
                    $header[]   = 'VC_CVAR_'.strtoupper($row['name']);
                    $cvar++;
                };
            }  else {
                $cvar = false;
            }
            insertCSVData($header,-1);
            $anredenFrau = getCpAnredenGeneric('female');
            $anredenHerr = getCpAnredenGeneric('male');

            if ( $daten ) foreach ($daten as $zeile) { 
                if ( $zeile['cp_gender'] =='f' ){
                    if ( $zeile['language_id'] ) {
                        $zeile['cp_greeting'] = $anredenFrau[$zeile['language_id']];
                    } else {
                        $zeile['cp_greeting'] = 'Frau';
                    }
                } else if ( $zeile['cp_gender'] =='m' ){
                    if ($zeile['language_id']) {
                        $zeile['cp_greeting'] = $anredenHerr[$zeile['language_id']];
                    } else {
                        $zeile['cp_greeting'] = 'Herr';
                    }
                } else {
                        $zeile['cp_greeting'] = 'KEIN GESCHLECHT';
                }
                $save = array($zeile['cp_greeting'],$zeile['cp_title'],$zeile['cp_name'],$zeile['cp_givenname'],
                        $zeile['cp_country'],$zeile['cp_zipcode'],$zeile['cp_city'],$zeile['cp_street'],
                        $zeile['cp_phone1'],$zeile['cp_fax'],$zeile['cp_email'],$zeile['name'],$zeile['cp_cv_id'],
                        $zeile['cp_gender'],$zeile['cp_id'],$zeile['tbl']); 
                if ( $cvar>0 ) {
                    $rs = getFirmaCVars($zeile['cp_cv_id']);
                    if ( $rs ) {
                        foreach($cvheader as $cvh) {
                            $save[] = ( isset($rs[$cvh]) ) ? $rs[$cvh] : false;
                        }
                    } else {
                        for ($i=0; $i<$cvar; $i++) $save[] = false;
                    }
                }
                insertCSVData($save,$zeile['cp_id']);
               
                if ( isSetVar( $_POST['FID1'] ) && $_POST['FID1'] ) {
                    $insk="<input type='checkbox' name='kontid[]' value='".$zeile['cp_id']."'>"; 
                    $js='';
                } else { 
                    $js='showK('.$zeile['cp_id'].',"'.$zeile['tbl'].'");'; //showK({PID},'{TBL}')
                    $insk=''; 
                };
                if ( ( $_SESSION['listLimit']>0 && $i < $_SESSION['listLimit'] ) || ( $_SESSION['listLimit'] == 0 ) ) {
                    $t->set_var(array(
                        'js'        => $js,
                        'LineCol'   => ($i%2+1),
                        'Name'      => $zeile['cp_name'].', '.$zeile['cp_givenname'],
                        'Plz'       => $zeile['cp_zipcode'],
                        'Ort'       => $zeile['cp_city'],
                        'Telefon'   => $zeile['cp_phone1'],
                        'eMail'     => $zeile['cp_email'],
                        'Firma'     => $zeile['name'],
                        'table'     => $zeile['tbl'],
                        'insk'      => $insk,
                        //'DEST'      => $dest,
                    ));
                    $t->parse('Block','Liste',true);
                    $i++;
                }
            }
            if ( $i >= $_SESSION['listLimit'] && $_SESSION['listLimit'] > 0 ) {
                    $t->set_var(array(
                        'report' => $_SESSION['listLimit'].' von '.count($daten).' Treffer',
                    ));
                    echo '<script>$( "#dialog_viele" ).dialog( "open" );</script>';
            }
            echo '<script>$( "#treffer_pers" ).trigger( "update" );</script>';
            $t->set_var(array(
                'FID' => isset($_POST['FID1']) ? $_POST['FID1'] : '',
                'ERPCSS' => $_SESSION['baseurl'].'crm/css/'.$_SESSION['stylesheet'],
                'CRMPATH' => $_SESSION['baseurl'].'crm/',
            ));
        } else {
            ;//nichts gefunden
        }
    } else {
        leertplP($t,'','',1,false,'',true);
    }
    $t->Lpparse('out',array('pers1'),$_SESSION['countrycode'],'firma');
?>
