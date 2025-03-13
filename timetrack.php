<?php
    require_once("inc/stdLib.php");
    require_once("crmLib.php");
    include_once("template.inc");
    $action = false;
    if ( isSetVar($_POST) ) {
        foreach ( $_POST as $key=>$val) { ${$key} = $val; };
    } else if ( isSetVar($_GET) ) {
        foreach ( $_GET as $key=>$val) { ${$key} = $val; };
    };

    $t = new Template($base);
    doHeader($t);
    $t->set_file(array("tt" => "timetrack.tpl"));

    $datatmp = array('backlink'=>false,'active'=>false, 'budget'=>false, 'clear'=>1,
                  'name'=>false,'fid'=>false,'ttname'=>false,'cur'=>false,
                  'startdate'=>false,'stopdate'=>false, 'ttname'=>false,'msg'=>false,
                  'ttdescription'=>false,'id'=>false,'tab'=>false,'aim'=>false);

    $data = $datatmp;
    $msg  = '';
    $visible = false;
    $delete  = false;

    if ( $action == "save") {
        //Sichert die obere Maske
        if ($_POST['fid'] <= 0 )                           { $data = $_POST; $data['msg'] = '.:missinge:. .:company:.';   }
        else if ( isSetVar($_POST['ttname'],'')    == '' ) { $data = $_POST; $data['msg'] = '.:missings:. .:project:.';   }
        else if ( isSetVar($_POST['startdate'],'') == '' ) { $data = $_POST; $data['msg'] = '.:missings:. .:startdate:.'; }
        else if ( isSetVar($_POST['aim'],'')       == '' ) { $data = $_POST; $data['msg'] = '.:missinge:. .:hours:.';     }
        else    { $data = saveTT($_POST); };
    } else if ( $action == "clear") {
        if ($fid != '' && $clear < 2) {
            //unset($data);
            $data['name']   = $name;
	        $data['fid']    = $fid;
	        $data['tab']    = $tab;
            $data['active'] = 't';
            $data['clear']  = 2;
        }
    } else if ( isset($_POST['action']) && $_POST['action'] == 'delete') {
        //Einen Zeiteintrag löschen, obere Maske
        $rc = deleteTT($_POST['id']);
        if ($rc) {
            $msg = ".:deleted:.";
            $data['name'] = $name;
	        $data['fid']  = $fid;
	        $data['tab']  = $tab;
        } else {
            $data = getOneTT($id);
            $msg  = '.:not posible:.';
        };
    } else if ( $action == "search" ) { // Das ist MÜLL!!!!!!!
        //Suchen eines Zeiteintrages, obere Maske
        $data = searchTT(array('name'=>$name,'fid'=>$fid));
        if ( count($data)>1 ) {
            $t->set_block("tt","Liste","Block");
            foreach ($data as $row) {
                $t->set_var(array(
                    'tid' => $row['id'],
                    'ttn' => $row['ttname']
                ));
                $t->parse("Block","Liste",true);
            }
            $visible = true;
            $data    = $_POST;
	        if ( $fid ) $data['backlink'] = "firma1.php?Q=".$Q."&id=".$fid;
        } else if (count($data)==0) {
            $data = $_POST;
            $data['msg'] = ".:not found:.";
	        if ( $fid ) $data['backlink'] = "firma1.php?Q=".$Q."&id=".$fid;
        } else {
            $data   = getOneTT($data[0]['id']);
	        $delete = ($data['uid']==$_SESSION['loginCRM'])?True:False;
        }
    } else if ( isset($_POST['getone']) ) {
        //Eintrag der Auswahlliste der Zeiteinträge einer Firma holen
        $data   = getOneTT($_POST['tid']);
        $delete = ($data['uid']==$_SESSION['loginCRM'])?True:False;
    } else if ( isset($_POST['savett']) ) {
        //Einen Zeiteintrag sichern, untere Maske
        $rc   = saveTTevent($_POST);
        $data = getOneTT($_POST['tid']);
    } else if ( isset($_GET['stop']) && $_GET['stop']=="now") {
        //Endzeitpunkt für einen Zeiteintrag sichern, untere Maske
        $rc   = stopTTevent($_GET['eventid'],date('Y-m-d H:i'));
        $data = getOneTT($_GET['tid']);
        if ( !$rc ) $data['msg'] = ".:error:. .:close event:.";
    } else if ( isset($_POST['clr']) ) {
            if ($_POST['clrok']=="1" || count($_POST['clear'])>0) {
                if ($_POST['tid']) {
                    if (count($_POST['clear'])>0) $evids = "and t.id in (".implode(",",$_POST['clear']).") ";
                    $msg = mkTTorder($_POST['tid'],$evids,$_POST['order']);
                    $data = getOneTT($_POST['tid']);
                    $data['msg'] = $msg;
                } else {
                    $data['msg'] = ".:missing:. .:customer:.";
                }
            } else {
                $data = getOneTT($_POST['tid']);
                $data['msg'] = ".:clrok:.";
            }
    } else {
        $data = $datatmp;
        if ( isSetVar($data['fid'])  ) {
            $curr = getCurrCompany($data['fid'],$data['tab']);
            $data['cur'] = $curr['name'];
        } else {
            $data['cur'] = getCurr();
        }
        $data['active'] = 't';
    }
    if ( isSetVar($data['events']) ) {
	    $delete = False;
    }

    if ( isSetVar($data['fid']) ) $data['backlink'] = "firma1.php?Q=".$data['tab']."&id=".$data['fid'];
    $tmpdata = getUserEmployee(array('interv','feature_ac_minlength','feature_ac_delay'));
    if ( ! $data['active'] ) $data['active'] = 't';
    $t->set_var(array(
        'backlink'  => $data['backlink'],
        'blshow'    => ($data['backlink'])?"visible":"hidden",
        'noevent'   => ($data['active']=="t" && $data['id'])?"visible":"hidden",
        'noown'     => ($data['id']>0 && $data['uid']!=$_SESSION['loginCRM'])?"hidden":"visible",
        'id'        => $data['id'],
        'budget'    => sprintf('%0.2f',$data['budget']),
        'cur'       => $data['cur'],
        'clear'     => $data['clear'],
        'name'      => $data['name'],
        'fid'       => $data['fid'],
        'tab'       => $data['tab'],
        'aim'       => $data['aim'],
        'ttname'    => $data['ttname'],
        'ttdescription' => $data['ttdescription'],
        'startdate' => $data['startdate'],
        'stopdate'  => $data['stopdate'],
        'active'.$data['active'] => "checked",
        'msg'       => $data['msg'],
        'visible'   => ($visible)?"block":"none",
        'delete'    => ($delete)?"visible":"hidden",
        'chkevent'  => ($data['id']>0)?"onLoad='getEventListe();'":'',
        'feature_ac_minlength'  => $tmpdata['feature_ac_minlength'],
        'feature_ac_delay'      => $tmpdata['feature_ac_delay'],
    ));

    $t->Lpparse("out",array("tt"),$_SESSION['countrycode'],"work");
?>
