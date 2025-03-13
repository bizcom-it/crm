<?php
// $Id:  $
    require_once('inc/stdLib.php');
    include_once('template.inc');
    include_once('crmLib.php');
    include_once('UserLib.php');
    $t = new Template($base);
    doheader($t);
    $stamm   = 'none';
    $show    = 'visible';
    $history = false;
    $msg     = '';
    $button  = '';
    $daten   = array('id'=>'','oppid'=>'','auftrag'=>'0','tab'=>'','fid'=>'','title'=>'',
                     'firma'=>'','zieldatum'=>'','betrag'=>'','next'=>'','notiz'=>'---',
                     'user'=>'','itime'=>'','status'=>'','chance'=>'');
    if ( isset($_GET) AND isset($_GET['Q']) and isset($_GET['fid']) ) {
        $fid = $_GET['fid'];
        if ( isset($_GET['new']) && $_GET['new'] != '' ) {
            include_once('FirmenLib.php');
            $daten['firma']  = getName($fid,$_GET['Q']);
            $daten['fid']    = $fid;
            $daten['tab']    = $_GET['Q'];
        } else {
            $_POST['tab']    = $_GET['Q'];
            $_POST['fid']    = $fid;
            $_POST['action'] = 'suchen';
            $_POST['suchen'] = '1';
        }
        $search = 'visible';
        $save   = 'visible';
        $none   = 'block';
        $stamm  = 'block';
        $block  = 'none';            
    } else if ( isset($_GET) AND  isset($_GET['history']) ) {
        $history = true;
        $show    = 'hidden';
        $_POST['oppid'] = $_GET['history'];
    }
    $oppstat  = getOpportunityStatus();
    $salesman = getAllUser(array(0=>true,1=>'%'));
    if ( isset($_POST['suchen']) and ($_POST['action']=='suchen' || $history ) ) {
        $data  = suchOpportunity($_POST);
        $none  = 'block';
        $block = 'none';
        if ( count($data) > 1 ){
            $t->set_file(array('op' => 'opportunityL.tpl'));
            $t->set_var(array(
                'TITLE'     => 'ok .:opportunity:. L',
            ));
            $t->set_block('op','Liste','Block');
            $last = 0;
            foreach ( $data as $row ) {
                if ($last <> $row['oppid'] || $history) {
                    $t->set_var(array(
                        'LineCol'  => ($i%2+1),
                        'id'       => $row['id'], 
                        'firma'    => ($last==$row['oppid'])?'':$row['firma'], 
                        'oppid'    => $row['oppid'],
                        'show'     => $show,
                        'title'    => $row['title'],
                        'chance'   => $row['chance']*10, 
                        'betrag'   => sprintf('%0.2f',$row['betrag']), 
                        'status'   => $row['statusname'],
                        'datum'    => db2date($row['zieldatum']),
                        'user'     => $row['user'],
                        'chgdate'  => db2date($row['itime'])
                    ));
                    $t->parse('Block','Liste',true);
                    $i++;
                } 
                $last = $row['oppid'];
            }
            $stamm = 'block';
            $t->set_var(array(
                'ERPCSS'    =>  $_SESSION['baseurl'].'crm/css/'.$_SESSION['stylesheet'],
            ));
            $t->Lpparse('out',array('op'),$_SESSION['countrycode'],'work');
            exit;
        } else if ( count($data)==0 || !$data ){
            if ($_POST['fid']) {
                include_once('FirmenLib.php');
                $data['firma'] = getName($_POST['fid'],$_POST['tab']);
            };
            $msg            = '.:notfound:.!';
            $daten['fid']   = $_POST['fid'];
            $daten['firma'] = $data['firma'];
            $daten['tab']   = $_POST['tab'];
            $search         = 'visible';
            $save           = 'visible';
            $none           = 'block';
            $block          = 'none';
            $stamm          = 'none';
        } else if ( $data[0]['id'] > 0 ) {
            $daten  = getOneOpportunity($data[0]['id']);
            $save   = 'visible';
            $search = 'hidden';
            $none   = 'none';
            $block  = 'block';
            $stamm  = 'block';
        }
    } else if ( isset($_POST['action']) AND $_POST['action'] == 'save') {
        $rc = saveOpportunity($_POST);
        if (!$rc) { 
            $msg   = '.:error:. .:save:.';
            $daten = $_POST;
            if ($_POST['id']) {
                $data = getOneOpportunity($_POST['id']);    
                $daten['orders'] = $data['orders'];
            };
            $daten['zieldatum'] = date2db($daten['zieldatum']);
            $save   = 'visible';
            $search = 'hidden';
            $none   = 'block';
            $block  = 'none';
            $stamm  = 'block';
        } else {
            $daten  = getOneOpportunity($rc);
            $msg    = '.:datasave:.';
            $save   = 'visible';
            $search = 'hidden';
            $none   = 'none';
            $block  = 'block';
            $stamm  = 'block';
        }
    } else if ( isset($_GET['id']) && $_GET['id']>0 ) {
        //Genau diesen einen Eintrag holen
        $daten  = getOneOpportunity($_GET['id']);
        $save   = 'visible';
        $search = 'hidden';
        $none   = 'none';
        $block  = 'block';
        $stamm  = 'block';
    } else {
        $save   = 'visible';
        $search = 'visible';
        $none   = 'block';
        $block  = 'none';
    }
    $t->set_file(array('op' => 'opportunityS.tpl'));
    $t->set_var(array(
        'TITLE'     => 'ok .:opportunity:. S',
    ));
    $t->set_block('op','status','BlockS');
    $sel = (isset($daten['status']))?$daten['status']:'';
    if ($oppstat) foreach ($oppstat as $row) {
        $t->set_var(array(
            'ssel'  => ($row['id']==$sel)?'selected':'',
            'sval'  => $row['id'],
            'sname' => $row['statusname']
        ));
        $t->parse('BlockS','status',true);
    }
    $t->set_block('op','salesman','BlockV');
    if ( !isset($daten['salesman']) ) $daten['salesman']=$_SESSION['loginCRM'];
    if ($salesman) foreach ($salesman as $row) {
        $t->set_var(array(
            'esel'  => ($row['id']==$daten['salesman'] && !isset($_GET['action']))?'selected':'',
            'evals' => $row['id'],
            'ename' => ($row['name'])?$row['name']:$row['login']
        ));
        $t->parse('BlockV','salesman',true);
    }
    $t->set_block('op','auftrag','BlockA');
    if ( isset($daten['orders']) ) foreach ($daten['orders'] as $row) {
        $t->set_var(array(
            'asel'  => ($row['id']==$daten['auftrag'])?'selected':'',
            'aval'  => $row['id'],
            'aname' => $row['ordnumber'].' : '.db2date($row['transdate'])
        ));
        $t->parse('BlockA','auftrag',true);
    }
    $backlink = '';
    if ( isset($daten['fid']) and $daten['fid']>0 ) $backlink = 'firma1.php?Q='.$daten['tab'].'&id='.$daten['fid'];
    $tmpdata = getUserEmployee(array('interv','feature_ac_minlength','feature_ac_delay'));
    if ( isset($daten['firma']) ) { $firma = $daten['firma']; } else { $firma = isset($_POST['firma'])?$_POST['firma']:'??'; };
    $t->set_var(array(
        'id'        => $daten['id'],
        'oppid'     => $daten['oppid'],
        'auftrag'   => ($daten['auftrag']>0)?$daten['auftrag']:'0',
        'zumauftrag'  => ($daten['auftrag']>0)?'zum Auftrag':'',
        'auftragshow' => ($daten['auftrag']>0)?'visible':'hidden',
        'auftragsnummer' => $daten['auftrag'],
        'tab'       => $daten['tab'],
        'fid'       => $daten['fid'],
        'title'     => $daten['title'],
        'firma'     => $firma,
        'zieldatum' => ($daten['zieldatum'])?db2date($daten['zieldatum']):'',
        'betrag'    => ($daten['betrag'])?sprintf('%0.2f',$daten['betrag']):'',
        'next'      => ($daten['next'])?$daten['next']:isset($_POST['next'])?$_POST['next']:'',
        'notxt'     => ($daten['notiz'])?nl2br($daten['notiz']):'---',
        'notiz'     => $daten['notiz'],
        'user'      => $daten['user'],
        'chgdate'   => db2date($daten['itime']),
        'ssel'.$daten['status'] => 'selected',
        'csel'.$daten['chance'] => 'selected',
        'save'      => $save,
        'search'    => $search,
        'stamm'     => $stamm,
        'block'     => $block,
        'none'      => $none,
        'button'    => $button,
        'backlink'  => $backlink,
        'blshow'    => ($backlink)?'visible':'hidden',
        'msg'       => $msg,
        'feature_ac_minlength'  => $tmpdata['feature_ac_minlength'],
        'feature_ac_delay'      => $tmpdata['feature_ac_delay'],
    ));
    if ( isset($daten['oppid']) && $daten['oppid']>0 ) 
                    $history = $daten = getOpportunityHistory($daten['oppid']);
    $i = 0;
    $t->set_block('op','Liste','Block');
    if ($history) foreach ($history as $row) {
       $t->set_var(array(
           'nr'          => $i,
           'LineCol'     => ($i%2+1),
           'histtitle'   => $row['title'],
           'histchance'  => $row['chance']*10,
           'histbetrag'  => sprintf('%0.2f',$row['betrag']),
           'histstatus'  => $row['statusname'],
           'histdatum'   => db2date($row['zieldatum']),
           'histauftrag' => $row['ordnumber'],
           'histnext'    => $row['next'],
           'histnotiz'   => strtr($row['notiz'],array('\n'=>'<br>')),
           'user'        => $row['user'],
           'chgdate'     => db2date($row['itime'])
       ));
       $t->parse('Block','Liste',true);
       $i++;
    }

    $t->Lpparse('out',array('op'),$_SESSION['countrycode'],'work');
?>
