<?php
    require_once('inc/stdLib.php');
    include_once('template.inc');
    include_once('crmLib.php');
    include_once('wvLib.php');
    $t = new Template($base);
    doHeader($t);

    $vertrag   = false;
    $maschinen = false;
    $msg       = '';
    $template  = 'vertrag3.tpl';
    $vid       = ( isSetVar($_POST['vid']) )?$_POST['vid']:( isSetVar($_GET['vid']) )?$_GET['vid']:false;
    if ( isSetVar($_POST['stat']) ) {
        $jahr    = ($_POST['jahr'])?$_POST['jahr']:date('Y');
        $data    = getVertragStat($vid,$jahr);
        $vertrag = getVertrag($vid);
        $SM      = substr($vertrag['anfangdatum'],5,2);
        $SJ      = substr($vertrag['anfangdatum'],0,4);
        if ( $SJ<$jahr ) { $einnahme = $vertrag['betrag']*12; } 
        else             { $EM = date('m'); $einnahme = $vertrag['betrag']*($EM-$SM+1); };
        $template = 'vertragS.tpl';    
        $t->set_file(array('vert' => $template));
        $t->set_block('vert','Liste','Block1');
        //$m=$data[0]['mid']; 
        $first  = true;
        $m      = false;
        $zsum   = 0;
        $gt     = 0;
        $zeit   = 0;
        $gz     = 0;
        if( $data ) foreach($data as $zeile) {
            if ( $zeile['mid']<>$m ) { 
                if ( !$first ) {
                    $t->set_var(array(
                        'MID'     => 'Summe Maschine',     'RID'        => '',     'BETRAG'    => '', 
                        'DAUER' => $zeit,   'SUMME'   => sprintf('%0.2f',$zsum)
                    ));
                    $t->parse('Block1','Liste',true);
                    $gt  += $zsum;
                    $gz  += $zeit;
                    $zsum = 0;
                    $zeit = 0;
                }
                $first=false;
                $t->set_var(array(
                    'MID'    => '[<a href="maschine1.php?sernr='.$zeile['serialnumber'].'">'.$zeile['parts_id'].' #'.$zeile['serialnumber'].'</a>] ',
                    'RID'    => '[<a href="repauftrag.php?hole='.$zeile['aid'].'">'.$zeile['aid'].'</a>]',
                    'DAUER'  => $zeile['dauer'],
                    'BETRAG' => sprintf('%0.2f',$zeile['summe']),
                    'SUMME'  => ''
                ));
                $t->parse('Block1','Liste',true);
                $zsum += $zeile['summe'];
                $zeit += $zeile['dauer'];
                $m     = $zeile['mid'];
            } else  {
                $t->set_var(array(
                    'MID'     => '',
                    'RID'     => '[<a href="repauftrag.php?hole='.$zeile['aid'].'">'.$zeile['aid'].'</a>]',
                    'DAUER'  => $zeile['dauer'],
                    'BETRAG'  => sprintf('%0.2f',$zeile['summe']),
                    'SUMME'   => ''
                ));
                $t->parse('Block1','Liste',true);
                $zsum += $zeile['summe'];
                $zeit += $zeile['dauer'];
            }
        }
        $t->set_var(array(
            'MID'     => 'Summe Maschine',     'RID'        => '',     'BETRAG'    => '', 
            'DAUER' => $zeit,   'SUMME'   => sprintf('%0.2f',$zsum)
        ));
        $t->parse('Block1','Liste',true);
        $gt  += $zsum;
        $gz  += $zeit;
        $t->set_var(array(
            'MID'      => 'Summe Vertrag',     'RID'        => '',     'BETRAG'    => '', 
            'DAUER'    => $gz,     'SUMME'    => sprintf('%0.2f',$gt)
        ));
        $t->parse('Block1','Liste',true);
        $diff = $einnahme-$gt;
        $diff = ($diff>0)?'&Uuml;berschuss '.sprintf('%0.2f',$diff):'Mehrkosten '.sprintf('%0.2f',$diff*-1);
        $t->set_var(array(
            'VID'         => $vid,
            'jahr'        => $jahr,
            'VertragNr'   => $vertrag['contractnumber'],
            'FID'         => $vertrag['customer_id'],
            'KDNR'        => $vertrag['customernumber'],
            'Firma'       => $vertrag['name'],
            'betrag'      => sprintf('%0.2f',$vertrag['betrag']),        
            'anfangdatum' => db2date($vertrag['anfangdatum']),
            'endedatum'   => db2date($vertrag['endedatum']),            
            'kosten'      => sprintf('%0.2f',$gt),
            'einnahme'    => sprintf('%0.2f',$einnahme),
            'diff'        => $diff,
            'msg'         => $msg
        ));        
        $t->pparse('out',array('vert'));
        exit;
    }

    if ( isSetVar($_POST['ok']) ) {
        if ( isSetVar($_POST['vid']) ) {
            $vid = updateVertrag($_POST);
        } else  {
            if ( !isSetVar($_POST['cp_cv_id'] ) ){
                $msg = 'Bitte einen Kunden über suchen auswählen.';
                $vertrag = $_POST;
                $vertrag['customer_id'] = $_POST['cp_cv_id'];
            } else if ( empty($_POST['maschinen'][0][0]) ) { 
                $msg = 'Bitte mind. eine Maschine angeben.';
                $vertrag = $_POST;
                $vertrag['customer_id'] = $_POST['cp_cv_id'];
            } else {
                $vid = saveNewVertrag($_POST); 
            }

        }
    }    
    $vorlagen = getWVorlagen();
    if ( $vid ) {
        $template  = 'vertrag3e.tpl';
        $vertrag   = getVertrag($vid);
        //$maschinen=getVertragMaschinen($vertrag['contractnumber']);
        $maschinen = getVertragMaschinen($vid);
    }

    $t->set_file(array('vert' => $template));

    $t->set_var(array(
        'VID'       => $vid,
        'vorlage_old' => $vertrag['customer_id'].'/'.$vertrag['template'],
        'vorlage'   => $vertrag['template'],
        'Notiz'     => $vertrag['bemerkung'],
        'FID'       => $vertrag['customer_id'],
        'KDNR'      => $vertrag['customernumber'],
        'Firma'     => $vertrag['name'],
        'betrag'    => sprintf('%0.2f',$vertrag['betrag']),        
        'anfangdatum' => db2date($vertrag['anfangdatum']),
        'endedatum' => db2date($vertrag['endedatum']),                        
        'VertragNr' => $vertrag['contractnumber'],
        'msg'         => $msg
    ));

    $t->set_block('vert','Vorlage','Block1');
    if($vorlagen) foreach($vorlagen as $zeile) {
        if ($zeile==$vertrag['template']) { $sel=' selected'; } else { $sel=''; };
        $t->set_var(array(
            'Vsel'     => $sel,
            'Vertrag'  =>    $zeile
        ));
        $t->parse('Block1','Vorlage',true);
    }
    $t->set_block('vert','Maschinen','Block2');    
    $i=0;

    if( $maschinen ) foreach($maschinen as $zeile) {
        $t->set_var(array(
            'I' => ++$i,
            'MID'       => $zeile['mid'],
            'Maschine'  => $zeile['partnumber'].' | '.$zeile['serialnumber'],
            'SerNr'     => $zeile['serialnumber'],
            'Standort'  => $zeile['standort']
        ));
        $t->parse('Block2','Maschinen',true);
    }
    $t->pparse('out',array('vert'));

?>
