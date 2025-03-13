<?php
    require_once('inc/stdLib.php');
    include('inc/template.inc');
    include('inc/crmLib.php');
    include('inc/FirmenLib.php');
    include('inc/persLib.php');
    $ep = '';
    $cp_id = $id = false;
    if ( isSetVar($_GET['fid']) ) {
        $fid = $_GET['fid'];
        $Q   = $_GET['Q'];
    } else if ( isSetVar($_POST['fid']) ) {
        $fid = $_POST['fid'];  
        $Q   = $_POST['Q'];    
    } else {
        $fid = false;
        $Q   = false;
    }
    $kdhelp = getWCategorie(true);
    if ( isSetVar($_POST['insk']) ) {
        insFaKont($_POST);
    }
    if ( isSetVar($_GET['ldap']) ) {
        include('inc/ldapLib.php');
        $rc = Ldap_add_Customer($_GET['fid']);
    }
    // Einen Kontakt anzeigen lassen
    if ( isSetVar($_GET['id']) AND $_GET['id']>0 ) {				// Kommt nicht von firma1.php
        $co=getKontaktStamm($_GET['id']);
        if (empty($co['cp_cv_id'])) {
            // Ist keiner Firma zugeordnet
            $id            = $_GET['id'];
            $fa['name']    = 'Einzelperson';
            $fa['department_1']='';
            $fa['department_2']='';
            $fa['zipcode'] = '';
            $fa['city']    = '';
            $fa['id']      = 0;
            $link1         = '#';
            $link2         = '#';
            $link3         = '#';
            $link4         = 'firma4.php?pid='.$id;
            $ep            = '&ep=1';
            $init          = '';
            $liste.="<option value='".$co['cp_id']."' selected>".$co['cp_name'].', '.$co['cp_givenname'].'</option>';
        } else {
            $id       = $_GET['id'];
            $fid      = $co['cp_cv_id'];
            $fa['id'] = 0;
            $ep       = '';
            $Q        = $co['tabelle'];
        }
    } 
    if ( $fid>0 ){ 
        // Aufruf mit einer Firmen-ID
        $co = getAllKontakt($fid);
        $liste = "";
        if ( count($co)>0 ) {
            // Kontakt gefunden
            if ( !$id ) $id = $co[0]['cp_id'];
            foreach ( $co as $row ) {
                $liste .= "<option value='".$row["cp_id"];
                $liste .= ( $row['cp_id']==$id )?"' selected>":"'>";
                $liste .= $row['cp_name'].', '.$row['cp_givenname']."\n";
            }
            $co   = $co[0];
            $init = $co['cp_id'];
            $id   = $co['cp_id'];
        } else if ( count($co)==0 || $co==false ) {
            // Keinen Kontakt gefunden
            $co['cp_name'] = 'Leider keine Kontakte gefunden';
            $init          = '';
            $id            = '';
        }
        $fa = getFirmenStamm($fid,true,$Q);
        $KDNR = ( $Q=="C" )?$fa['customernumber']:$fa['vendornumber'];
        $link1 = "firma1.php?Q=$Q&id=$fid";
        $link2 = "firma2.php?Q=$Q&fid=$fid";
        $link3 = "firma3.php?Q=$Q&fid=$fid";
        $link4 = "firma4.php?Q=$Q&fid=$fid&pid=".$id;
    } else if ( $ep=='' ) {
        $co['cp_name'] = 'Fehlerhafter Aufruf';
        $init          = '';
        $link1         = '#';
        $link2         = '#';
        $link3         = '#';
        $link4         = '#';
    }
    $tmpdata = getUserEmployee(array('interv','zeige_tools'));
    $t = new Template($base);
    $t->set_file(array("co1" => "firma2.tpl"));
    doHeader($t);
    $t->set_var(array(
            'FAART'    => ($Q=='C')?'.:Customer:.':'.:Vendor:.',   //"Kunde":"Lieferant",
            'interv'   => $tmpdata['interv']*1000,
            'Q'        => $Q,
            'Link1'    => $link1,
            'Link2'    => $link2,
            'Link3'    => $link3,
            'Link4'    => $link4,
            'Fname1'   => $fa["name"],
            'Fdepartment_1' => $fa['department_1'],
            'Fdepartment_2' => $fa['department_2'],
            'Plz'     => $fa['zipcode'],
            'Ort'     => $fa['city'],
            'Street'  => $fa['street'],
            'FID'     => ( isset($co['cp_cv_id']) )?$co['cp_cv_id']:$fid,
            'customernumber'    => $KDNR,
            'kontakte' => $liste,
            'tools'   => ( $tmpdata['zeige_tools'] )?'visible':'hidden',
            'ep'      => $ep,
            'Edit'    => ".:edit:.",
            'none'    => ( $ep=="" && $init=="" )?'hidden':'visible',
            'chelp'   => ( $kdhelp )?'visible':'hidden'
    ));
    if ( $kdhelp ) { 
        $t->set_block('co1','kdhelp','Block1');
        $tmp[]  = array('id'=>-1,'name'=>'Online Kundenhilfe');
        $kdhelp = array_merge($tmp,$kdhelp); 
        foreach( $kdhelp as $col ) {
            $t->set_var(array(
                'cid'   => $col['id'],
                'cname' => $col['name']
            ));    
            $t->parse('Block1','kdhelp',true);
        };
    }
    $t->Lpparse('out',array('co1'),$_SESSION['countrycode'],'firma');
?>
