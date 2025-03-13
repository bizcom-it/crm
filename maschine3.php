<?php
    require_once('inc/stdLib.php');
    include_once('template.inc');
    include_once('wvLib.php'); 
    $t = new Template($base);

    $nummern = false;
    $msg     = '';
    $pid     = false;
    $bekannt = false;
    $data    = array('mid'=>false,'inspdatum'=>false,'serialnumber'=>false,
                     'partnumber'=>false,'description'=>false,'notes'=>false,'beschreibung'=>false);
    if ( isSetVar($_POST['parts_sernr']) ) {
        $data    = getMaschSer($_POST['parts_sernr'],$_POST['parts_id']);
        $nummern = getNumber($data['parts_id']);
        $bekannt = getBekannt($data['parts_id']); 
        $pid     = $data['parts_id'];
    }        
    if ( isSetVar($_POST['search']) ) {
        $data   = getArtikel($_POST['partnumber'].'%');
        if ( count($data)>1 ) {
            $t->set_file(array('vert' => 'maschinenL.tpl'));
            doHeader($t);
            $t->set_var(array(
                    'fldname' => 'partnumber',
 		            'search'  => $_POST['partnumber'],
                    'action'  => 'maschine3.php',
            ));
            $t->set_block('vert','Sernumber','Block1');
            foreach( $data as $zeile ) {    
                $t->set_var(array(
                    'number'         => $zeile['partnumber'],
                    'pnumber'        => $zeile['partnumber'],
                    'description'    => $zeile['description']
                ));
                $t->parse('Block1','Sernumber',true);
            }
            $t->pparse('out',array('vert'));            
            exit;
        } else if (!$data) {
            $data['partnumber']='';
            $data['description']='Nicht gefunden';
        } else {
            $data    = $data[0];
            $pid     = $data['id'];
            $nummern = getNumber($data['id']);
            $bekannt = getBekannt($data['id']);            
        };
        $data['mid']          = false;
        $data['inspdatum']    = false;
        $data['serialnumber'] = false;
        $data['beschreibung'] = false;
    } else if ( isSetVar($_POST['ok']) ) {
        if ( isSetVar($_POST['parts_sernr']) ) {
            $rc = updateMaschine($_POST);
        } else {
            $rc = saveNewMaschine($_POST);
        }
        if ($rc) { $msg='Maschine gesichert'; } else { $msg='Fehler beim Sichern'; };
        $data    = getArtikel($_POST['partnumber']);
        $data    = $data[0];
        $data['mid']          = false;
        $data['inspdatum']    = false;
        $data['serialnumber'] = false;
        $data['beschreibung'] = false;
        $pid     = $data['id'];
        $nummern = getNumber($data['id']);
        $bekannt = getBekannt($data['id']);    
    }
    $t->set_file(array('masch' => 'maschinen3.tpl'));
    doHeader($t);
    $t->set_var(array(
        'action'    => 'maschine3.php',
        'msg'       => $msg,
        'parts_id'  => $pid,
        'mid'       => $data['mid'],
        'inspdatum' => db2date($data['inspdatum']),
        'snumber'   => $data['serialnumber'],
        'partnumber'   => $data['partnumber'],
        'description'  =>    $data['description'],
        'notes'     => $data['notes'],
        'beschreibung' => $data['beschreibung'],
    ));
    $t->set_block('masch','Bekannt','Block1');    
    if($bekannt) foreach($bekannt as $zeile) {
        $t->set_var(array(
            'maschine'    =>    $zeile['serialnumber']
        ));
        $t->parse('Block1','Bekannt',true);
    }    
    $t->set_block('masch','Sernumber','Block2');    
    if($nummern) foreach($nummern as $zeile) {
        $t->set_var(array(
            'Snumber'    =>    $zeile['serialnumber']
        ));
        $t->parse('Block2','Sernumber',true);
    }
    $t->pparse('out',array('masch'));

?>
