<?php
    require_once('inc/stdLib.php');
    include_once('template.inc');
    include_once('wvLib.php');    
    $t    = new Template($base);
    $disp = "style='display:none'";
    $data = array('parts_id'=>false,'partnumber'=>false,'description'=>false,'notes'=>false,'name'=>false,
                  'standort'=>false,'serialnumber'=>false,'contractnumber'=>false,'inspdatum'=>false,'id'=>false,
                  'counter'=>false,'cid'=>false,'mid'=>false,'customer'=>false,'custid'=>false,'customer_id'=>false);
    $msg  = '';
    if ( (isSetVar($_POST['search']) and $_POST['search'] != '') or isSetVar($_GET['sernr']) ) {
        if ( isSetVar($_POST['serialnumber']) ) {
            $tmp  = explode('|',$_POST['serialnumber']);
            $data = getSernumber($tmp[0],(isSetVar($tmp[1]))?$tmp[1]:false);
        } else if ( isSetVar($_GET['sernr']) ) {
            $data = getSernumber($_GET['sernr']);
        } else {
            $data = getArtnumber($_POST['partnumber'].'%');
        }
        if ( count($data)>1 ) {  //mehr als 1 Treffer, daher Auswahlliste anzeigen
            $t->set_file( array('vert' => 'maschinenL.tpl') );        
            doHeader($t);
            $t->set_var(array(
                'search'  => 'Artikelnr: '.$_POST['partnumber'].' SerNr. '.$_POST['serialnumber'],
                'action'  => 'maschine1.php',
                'fldname' => 'serialnumber'
            ));
            $t->set_block('vert','Sernumber','Block1');
            foreach($data as $zeile) {    
                $t->set_var(array(
                    'number'      => $zeile['serialnumber'].'|'.$zeile['parts_id'],
                    'pnumber'     => $zeile['serialnumber'],
                    'description' => $zeile['description'].' - '.$zeile['contractnumber'].' - '.$zeile['name']
                ));
                $t->parse('Block1','Sernumber',true);
            }
            $t->pparse('out',array('vert'));            
            exit;
        } else if (!$data) {
            $data['serialnumber'] = '';
            $data['description']  = 'Nicht gefunden';
        } else {        //genau ein Treffer, daher erstes Element in $data speichern
            $data = $data[0];
            $hist = getHistory(($data['mid'])?$data['mid']:$data['id']);
            if ( $data['contractnumber'] ) { $disp=''; };
        };
    } else if ( isSetVar($_POST['save']) == 'sichern' and isSetVar($_POST['mid']) != '') {
        if ( $_POST['standort'] != '' )  $rc = saveNewStandort($_POST['standort'],$_POST['mid']);
        if ( $_POST['inspdatum'] != '' ) $rc = updateIdat($_POST['inspdatum'],$_POST['mid']);
        if ( $_POST['counter'] != '' )   $rc = updateCounter($_POST['counter'],$_POST['mid']);
        $data = getSernumber($_POST['serialnumber']);
        $data = $data[0];
        $disp = '';
    };
    $cnt = ( $data['mid'] )?getCounter($data['mid']):'';
    $t->set_file(array('masch' => 'maschinen1.tpl'));
    doHeader($t);
    $hist = getHistory($data['mid']);
    $t->set_var(array(
        'action'         => 'maschine1.php',
        'msg'            => $msg,
        'disp'           => $disp,
        'parts_id'       => $data['parts_id'],
        'partnumber'     => $data['partnumber'],
        'description'    => $data['description'],
        'notes'          => strtr($data['notes'],array("\n"=>'<br>')),
        'standort'       => $data['standort'],
        'serialnumber'   => $data['serialnumber'],
        'contractnumber' => $data['contractnumber'],
        'inspdatum'      => db2date($data['inspdatum']),
        'counter'        => $data['counter'],
        'cid'            => $data['cid'],
        'mid'            => ($data['mid'])?$data['mid']:$data['id'], 
        'customer'       => $data['name'],
        'custid'         => $data['customer_id']
    ));
    $t->set_block('masch','History','Block1'); 
    $maschzusatz = '';
    if($hist) { foreach($hist as $zeile) {
        $open = ' ';
        if ($zeile['art']=='RepAuftr') {
            $open = ($zeile['status']==2)?'close':'open';
            $art  = "<a href='repauftrag.php?hole=".$zeile['bezug']."'>RepAuftr</a> ".$zeile['bezug'];
        } else if ($zeile['art']=='contsub' or $zeile['art']=='contadd') {
            //$vid = suchVertrag($beschr);
            $art = "<a href='vertrag3.php?vid=".$zeile['bezug']."'>".$zeile['art'].'</a>';
        } else {
            if ($zeile['art']=='neu') $maschzusatz=$zeile['beschreibung'];
            $art = $zeile['art'];
        };
        $t->set_var(array(
            'date'         =>  db2date(substr($zeile['itime'],0,10)),
            'time'         =>  substr($zeile['itime'],10,4),
            'art'          =>  $art,
            'open'         =>  $open,
            'beschreibung' =>  substr($zeile['beschreibung'],0,40)
        ));
        $t->parse('Block1','History',true);
    }} else {
        $t->set_var(array(
            'date'         => '',
            'time'         => '',
            'art'          => '',
            'open'         => '',
            'beschreibung' => 'Kein Eintrag'
        ));
        $t->parse('Block1','History',true);
    }
    $t->set_var(array(
        'maschzusatz' => $maschzusatz
    ));
    $t->pparse('out',array('masch'));

?>
