<?php
    include_once('../inc/stdLib.php');
    include_once('template.inc');
    include_once('FirmenLib.php');
    include_once('UserLib.php');
    $table = '';
    if ( isset($_GET['tabelle']) ) { $table = $_GET['tabelle']; }
    else if ( isset($_POST['tabelle']) ) { $table = $_POST['tabelle']; };
    if ( $table == '' ) $table = ($_SESSION['searchtable'] != '')?$_SESSION['searchtable']:'B';
    $t = new Template($_SESSION['erppath'].'crm/tpl');
    //doHeader($t);
    if ( isset($_POST['felder']) && $_POST['felder'] != '' ) {
        $rc = doReport($_POST,$table);
        $t->set_file(array('fa1' => 'companies1.tpl'));
        if ( $rc ) { 
            $tmp = "<div style='width:300px'>[<a href='tmp/report_".$_SESSION["login"].".csv'>download Report</a>]</div>";
        } else {
            $tmp = "Sorry, not found";
        }
        $t->set_var(array( 
                'report' => $tmp
        ));
        leertpl($t,1,$table,'',true,true);
    } else if ( (isset($_POST['suche']) and $_POST['suche'] != '') || isset($_POST['first']) ) {
        if ( isset($_POST['first']) ) {
            if ( $table == 'B' ) {
                $daten1 = getAllFirmen(array(1,$_POST['first']),false,'C');
                $daten2 = getAllFirmen(array(1,$_POST['first']),false,'V');
                $daten  = array_merge($daten1,$daten2);
            } else {
                $daten = getAllFirmen(array(1,$_POST['first']),false,$table);
            }
        } else {
            if ( $table == 'B' ) {
                $daten1 = suchFirma($_POST,'C');
                $daten2 = suchFirma($_POST,'V');
                $daten  = array_merge($daten1,$daten2);
            } else {
                $daten = suchFirma($_POST,$table);
            }
        };
        $i=0;
        if (count($daten) == 1 && $daten <> false) {
            echo '<script> showD("'.$daten[0]['tab'].'","'.($daten[0]['id']).'");</script>';
        } else if ( count($daten)>1 ) {
            $t->set_file(array('fa1' => 'companies1Result.tpl'));
            $t->set_block('fa1','Liste','Block');
            if ( $table == 'B' ) {
                $t->set_var(array('FAART' => 'Company',));
            } else if ( $table == 'C' ) {
                $t->set_var(array('FAART' => 'Customer',));
            } else {
                $t->set_var(array('FAART' => 'Vendor',));
            }
            $rc = clearCSVData();
            $header = array('ANREDE', 'NAME1', 'NAME2', 'LAND', 'PLZ', 'ORT', 'STRASSE', 'TEL', 'FAX', 'EMAIL', 'KONTAKT', 'ID',
                            'KDNR', 'USTID', 'STEUERNR', 'KTONR', 'BANK', 'BLZ', 'LANG', 'KDTYP','TABLE','UID');
            if ( isset($_POST['umsatz']) and $_POST['umsatz'] != '' ) $header[] = 'UMSATZ';
            $sql = "select name from custom_variable_configs where module = 'CT'";
            $rs = $GLOBALS['db']->getAll($sql);
            if ($rs) {
                $cvar = 0;
                foreach ($rs as $row) {
                    $cvheader[] = 'vc_cvar_'.$row['name'];
                    $header[] = 'VC_CVAR_'.strtoupper($row['name']);
                    $cvar++;
                };
            }  else {
                $cvar = false;
            }
            insertCSVData($header,-255);
            foreach ($daten as $zeile) {
                $data = array($zeile['greeting'],$zeile['name'],$zeile['department_1'],
                        $zeile['country'],$zeile['zipcode'],$zeile['city'],$zeile['street'],
                        $zeile['phone'],$zeile['fax'],$zeile['email'],$zeile['contact'],$zeile['id'],
                        ($zeile['tab']=='C')?$zeile['customernumber']:$zeile['vendornumber'],
                        $zeile['ustid'],$zeile['taxnumber'],
                        $zeile['account_number'],$zeile['bank'],$zeile['bank_code'],
                        $zeile['language_id'],$zeile['business_id'],$zeile['tab'],$zeile['uid']); 
                if ( isset($_POST['umsatz']) and $_POST['umsatz'] != '' ) $data[]=$zeile['umsatz'];
                if ($cvar>0) {
                    $rs = getFirmaCVars($zeile['id']);
                    if ($rs) {
                        foreach($cvheader as $cvh) {
                            if ( isset($rs[$cvh]) and $rs[$cvh] != '' ) {
                                $data[] = $rs[$cvh];
                            } else {
                                $data[] = '';
                            }
                        }
                    } else {
                        for ($j=0; $j<$cvar; $j++) $data[] = false;
                    }
                }
                insertCSVData($data,$zeile['id']);
                if ( ( $_SESSION['listLimit']>0 && $i < $_SESSION['listLimit'] ) || ( $_SESSION['listLimit'] == 0 ) ) {
                    $t->set_var(array(
                        'tab' => ($zeile['tab']!='')?$zeile['tab']:$table,
                        'ID' => $zeile['id'],
                        'LineCol' => ($i%2)+1,
                        'KdNr' => ($zeile['tab']=='C')?$zeile['customernumber']:$zeile['vendornumber'],
                        'Name' => $zeile['name'],
                        'Plz' => $zeile['zipcode'],
                        'Ort' => $zeile['city'],
                        'Strasse' => $zeile['street'],
                        'Telefon' => $zeile['phone'],
                        'eMail' => $zeile['email'],
                        'obsolete' => ($zeile['obsolete']=='t')?'.:yes:.':''
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
        } else {
            ;//Nichts gefunden
        };
        $t->set_var(array(
            'CRMPATH' => $_SESSION['baseurl'].'crm/',
        ));
    } else {
        leertpl($t,1,$table,'',true,true);
    }

    $t->Lpparse('out',array('fa1'),$_SESSION['countrycode'],'firma');
?>
