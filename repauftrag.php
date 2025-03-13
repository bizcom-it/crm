<?php
    require_once('inc/stdLib.php');
    include_once('template.inc');
    include_once('FirmenLib.php');
    include_once("UserLib.php");
    include_once('wvLib.php');
    
    $rep = array('schaden'=>false,'reparatur'=>false,'cause'=>false,'aid'=>false,'mid'=>false,
                 'anlagedatum'=>false,'bearbdate'=>false,'employee'=>false,'dauer'=>false,
                 'counter'=>false,'datum'=>false,'anlagedatum'=>false,'bearbeiter'=>false);
    $msg = '';
    $disp1 = false;
    $disp2 = false;
    $disp3 = false;
    $mid = false;
    function check(&$var) {
        return isSetVar($var) && !empty($var);
    }
    if ( isSetVar($_POST['ok']) && $_POST['ok'] != '' ) {
        if ( ($_POST['status'] == '2' && !check($_POST['datum'])) ) {
            $msg = 'Bitte Reparaturdatum angeben';
            $rep = $_POST;
            $mid = (check($_POST['mid']))?$_POST['mid']:false;
        } else if ( $_POST['status'] == '1' && !check($_POST['cause']) ) {
            $msg = 'Bitte Kurzbeschreibung angeben';
            $rep = $_POST;
            $mid = (check($_POST['mid']))?$_POST['mid']:false;
        } else {
            $rep = $_POST;
            $rc  = saveRAuftrag($_POST);
            if ( $rc ) { $rep['aid'] = $rc; $msg = 'Auftrag gesichert'; }
            else { $msg = 'Fehler beim Sichern'; };
            $mid = $rep['mid'];
        }
    } else if ( isSetVar($_GET['hole']) && $_GET['hole'] != '' ) {
        $rs = getRAuftrag($_GET['hole']);
        if ( !$rs ) { 
            $msg = 'Nicht gefunden';
        } else {
            $rep = $rs;
            $mid = isSetVar($rs['mid'])?$rs['mid']:false;
        }
    } else if ( isSetVar($_GET['neu']) && $_GET['neu'] != '' ) {
        $mid = $_GET['neu'];
    };

    $masch = getAllMaschine($mid);
    $kdnr  = ( isset($rep['kdnr']) && $rep['kdnr'] != '' )?$rep['kdnr']:$masch['customer_id'];
    $firma = getFirmenStamm($kdnr);
    $hist  = getHistory($mid);
    
    $t = new Template($base);
    $t->set_file(array('masch' => 'repauftrag.tpl'));
    doHeader($t);

    if ( !isset($rep['datum']) || $rep['datum'] == '' ) $rep['datum'] = date('d.m.Y');

    $t->set_block('masch','History','Block1');    
    if( $hist ) {
        if ( isSetVar($rep['aid']) ) {
            $t->set_var(array(
                'date'   => '',
                'time'   => '',
                'art'    => '',
                'open'   => ' ',
                'beschreibung' =>  "<a href='repauftrag.php?mid=$mid'>Neuer Auftrag</a>" 
            ));
            $t->parse('Block1','History',true);
        };
        while ( $zeile = array_shift($hist) ) {
            $open = ' ';
            if ( $zeile['art'] == 'RepAuftr' ) {
                $open = ( $zeile['status'] == 2 )?'close':'open';
                $art  = "<a href='repauftrag.php?hole=".$zeile['bezug']."'>RepAuftr</a>";
            } else if ( $zeile['art'] == 'contsub' ) {
                $vid = suchVertrag($beschr);
                $art = "<a href='vertrag3.php?vid=".$vid[0]['cid']."'>contsub</a>";
            } else {
                continue;
            }
            $t->set_var(array(
                'date'   =>  db2date(substr($zeile['itime'],0,10)),
                'time'   =>  substr($zeile['itime'],10,4),
                'art'    =>  $art,
                'open'   =>  $zeile['bezug'].' '.$open,
                'beschreibung' =>  $zeile['beschreibung'] 
            ));
            $t->parse('Block1','History',true);
        }
    }        
    if ( !isSetVar($rep['aid']) ) {
        $disp2 = "style='display:none'";
        $disp3 = $disp2;
        $sel1  = 'checked';
        $sel2  = ''; $sel3='';
    } else if ( isSetVar($rep['status']) and $rep['status'] == 1 ) {
        $disp3 = "style='display:none'";
        $sel1  = 'checked';
        $sel2  = ''; $sel3='';
    } else if ( isSetVar($rep['status']) and $rep['status'] == 2 ) {
        $disp1 = "style='display:none'";
        $sel2  = 'checked';
        $sel1  = ''; $sel3='';
    } else if ( isSetVar($rep['status']) and $rep['status'] == 3 ) {
        $disp1 = "style='display:none'";
        $sel3  = 'checked';
        $sel1  = ''; $sel2='';
    } 
    $usr = getAllUser(array(0=>true,1=>'%'));
    $nousr[0] = array('login' => '-----', 'name'=>'', 'id'=>0 );
    $user = array_merge($nousr,$usr);
    $t->set_block('masch','Userbox','BlockU');
    foreach($user as $zeile) {
        $t->set_var(array(
            'Sel'     => ($rep['bearbeiter']==$zeile['id'])?' selected':'',
            'UID'     => $zeile['id'],
            'Login'   => ( $zeile['name'] != '' )?$zeile['name']:$zeile['login']
        )); 
        $t->parse('BlockU','Userbox',true);
        };
    $t->set_var(array(
        'action'    => 'repauftrag.php',
        'msg'       => $msg,
        'AID'       => $rep['aid'],
        'mid'       => $mid,
        'name'      => $firma['name'],
        'kdnr'      => $firma['id'],
        'customernumber' => $firma['customernumber'],
        'strasse'   => $firma['street'],
        'plz'       => $firma['zipcode'],
        'ort'       => $firma['city'],
        'telefon'   => $firma['phone'],
        'standort'  => $masch['standort'],
        'description'    => $masch['description'],
        'serialnumber'   => $masch['serialnumber'],
        'contractnumber' => $masch['contractnumber'],
        'cid'       => $masch['cid'],        
        'schaden'   => $rep['schaden'],
        'behebung'  => (isSetVar($rep['reparatur']))?$rep['reparatur']:'',
        'bearbdate' => (isSetVar($rep['bearbdate']))?db2date(substr($rep['bearbdate'],0,10)):'',
        'cause'     => $rep['cause'],
        'counter'   => $rep['counter'],        
        'datum'     => (isSetVar($rep['datum']))?db2date(substr($rep['datum'],0,10)):'',
        'dauer'     => $rep['dauer'],
        'anlagedatum'    => db2date(substr($rep['anlagedatum'],0,10)),
        'sel1'      => $sel1,
        'sel2'      => $sel2,
        'sel3'      => $sel3,
        'disp1'     => $disp1,
        'disp2'     => $disp2,
        'disp3'     => $disp3,
    ));
    $t->pparse('out',array('masch'));

?>
