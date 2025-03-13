<?php
    require_once("inc/stdLib.php");
    include("inc/template.inc");
    include("inc/crmLib.php");
    include("inc/FirmenLib.php");
    include("inc/persLib.php");
    include_once("inc/UserLib.php");
    $INIT  = 0;
    $bezug = 0;
    $pid   = 0;
    $fid   = 0;
    $Q     = '';
    if ( isset($_POST) ) {
        $fid   = (isset($_POST["fid"]))?$_POST["fid"]:false;
        $pid   = (isset($_POST["pid"]))?$_POST["pid"]:false;
        $INIT  = (isset($_POST["INIT"]))?$_POST["INIT"]:0;
        $bezug = (isset($_POST["bezug"]))?$_POST["bezug"]:0;
        $Q     = (isset($_POST["Q"]))?$_POST["Q"]:'';
    };
    if ( isset($_GET) ) {
        $fid   = (isset($_GET["fid"]))?$_GET["fid"]:$fid;
        $pid   = (isset($_GET["pid"]))?$_GET["pid"]:$pid;
        $INIT  = (isset($_GET["INIT"]))?$_GET["INIT"]:$INIT;
        $bezug = ( isSetVarNotEmpty($_GET["bezug"]) )?$_GET["bezug"]:$bezug;
        $Q     = (isset($_GET["Q"]))?$_GET["Q"]:$Q;
    }; 
    $select  = $_SESSION["loginCRM"];
    $selectC = (strlen($Q)==1)?$fid:$pid;
    if ( $INIT>0 ) { $daten = getCall($INIT); }
    $daten["datum"]     = date("d.m.Y");
    $daten["zeit"]      = date("H:i");
    $daten["kontakt"]   = "T";
    $daten["c_long"]    = '';
    $daten["Files"]     = false;
    $daten["Anzeige"]   = 0;
    $daten["datei"]     = '';
    $daten["dateiname"] = '';
    $daten["DCaption"]  = '';
    $daten["Q"]         = $Q;
    $daten["id"]        = 0;
    $daten["CID"]       = ($pid>0)?$pid:$fid;
    $daten["Kunde"]     = 0;
    $daten["Anzeige"]   = 0;                           
    $daten["wvldate"]   = '';
    $daten["wvlid"]     = false;
    if ( isset($_POST["verschiebe"]) ) {
        $rc = mvTelcall($_POST["TID"],$_POST["id"],$_POST["CID"]);
        $daten["Betreff"] = $_POST["Betreff"];
        if ($_POST["bezug"]==$_POST["id"]) {
            $daten["id"] = $_POST["TID"];
            $bezug       = $_POST["TID"];
        } else {
            $daten["id"]=$_POST["bezug"];
        }                                            // verschiebe
    } else  if ( isset($_POST["delete"]) ) {
        $rc = saveTelCall($_POST["id"],$_SESSION["loginCRM"],"D");
        $rc = delTelCall($_POST["id"]);
        //if ($_POST["bezug"]==0) $bezug = 0;
    } else  if ( isset($_GET["hole"]) ) {
        $daten  = getCall($_GET["hole"]);
        $bezug  = ($daten["bezug"]==0)?$daten["id"]:$daten["bezug"];
        //$bezug  = $daten["id"];
        $select = $daten["employee"];
        $co     = getKontaktStamm($daten["CID"]);
        if ($co["cp_id"]) {
            $pid = $co["cp_id"];
            $fid = $co["cp_cv_id"];
        } else {
            $fid = $daten["CID"];        // Einzelperson o. Firma allgem.
        }
        $selectC = $daten["CID"];                        // if ($_GET["hole"])
    } else if ( isset($_POST["update"]) ) {
        if ( $_POST['kontakt'] != 'N' ) $rc = saveTelCall($_POST["id"],$_SESSION["loginCRM"],"U");
        $rc = updCall($_POST,$_FILES);
        if ( $rc ) {
            $daten["cause"] = $_POST["cause"];
        } else {    
            $daten = $_POST;
        }                                            // if ($rc)
    } else if ( isset($_POST["sichern"]) ) {
        unset($_POST["id"]);
        $rc = insCall($_POST,$_FILES);
        if ($rc) {
            $daten["Betreff"] = $_POST["cause"];
            if ( $bezug==0 ) $bezug = $rc;
        } else {    
            $daten = $_POST;
        }                                            // if ($rc)
    }                                //  end sichern
    switch ($Q) {
        case 'C' :  $fa = getFirmenStamm($fid,true,'C');
                    $daten["Firma"]  = $fa["name"];
                    $daten["Plz"]    = $fa["zipcode"];
                    $daten["Ort"]    = $fa["city"];
                    $daten["nummer"] = $fa["nummer"];
                    break;
        case 'V' :  $fa=getFirmenStamm($fid,true,'V');
                    $daten["Firma"]  = $fa["name"];
                    $daten["Plz"]    = $fa["zipcode"];
                    $daten["Ort"]    = $fa["city"];
                    $daten["nummer"] = $fa["nummer"];
                    break;
        case "XC" : 
        case "CC" : 
        case "VC" : $co=getKontaktStamm($pid);
                    $daten["Firma"]  = $co["cp_givenname"].' '.$co["cp_name"];
                    $daten["Plz"]    = $co["cp_zipcode"];
                    $daten["Ort"]    = $co["cp_city"];
                    $daten["nummer"] = $co["nummer"];
                    break;
        default   : $daten["Firma"]  = 'xxxxxxxxxxxxxx';
                    $daten["Plz"]    = '';
                    $daten["Ort"]    = '';
                    $daten["nummer"] = '';    
    } 

    //------------------------------------------- Beginn Ausgabe
    $t = new Template($base);
    $t->set_file(array("cont" => "getCall.tpl"));
    doHeader($t);
    //------------------------------------------- CRMUSER
    $t->set_block("cont","Selectbox","Block2");
    $user = getAllUser(array(0=>true,1=>'%'));
    if ( $user ) foreach( $user as $zeile ) {
        $t->set_var(array(
            'Sel'   => ($select==$zeile["id"])?" selected":'',
            'UID'   => $zeile["id"],
            'Login' => $zeile["login"],
        ));
        $t->parse("Block2","Selectbox",true);
    }
    //------------------------------------------- Firma/Kontakte
    $t->set_block("cont","Selectbox2","Block3");
    if ($fid) {
        $contact = getAllKontakt($fid);
        $first[] = array("cp_id"=>$fid,"cp_name"=>"Firma","cp_givenname"=>"allgemein");
        if ( $contact ) {
            $contact = array_merge($first,$contact);
        } else {
            $contact = $first;
        }
    } else {
        if ( isset($co["cp_cv_id"]) ) {
            $first[] = array("cp_id"=>$co["cp_cv_id"],"cp_name"=>"Firma","cp_givenname"=>"allgemein");
            $contact = getAllKontakt($co["cp_cv_id"]);
            if ( $contact ) {
                $contact = array_merge($first,$contact);
            } else {
                $first[] = array("cp_id"=>$pid,"cp_name"=>$co["cp_name"],"cp_givenname"=>$co["cp_givenname"]);
                $contact = $first;
            }
        } else {
            $contact[] = array("cp_id"=>$pid,"cp_name"=>$co["cp_name"],"cp_givenname"=>$co["cp_givenname"]);
        }
    }
    foreach( $contact as $zeile ) {
        $t->set_var(array(
            'Sel'   => ($selectC==$zeile["cp_id"])?" selected":'',
            'CID'   => $zeile["cp_id"],
            'CName' => $zeile["cp_name"].", ".$zeile["cp_givenname"],
        ));
        $t->parse("Block3","Selectbox2",true);
    }
    //------------------------------------------- Kontaktverl�ufe
    $t->set_block("cont","Selectbox3","Block4");
    if ( $Q<>"XX" )    {
        $thread = getAllTelCall(($pid)?$pid:$fid,($Q=='C' || $Q=='V'),0,-1); // Liste Verschieben
        if ($thread) {
            $thread = array_merge(array(array("id"=>"0")) ,$thread);
        } else {
            $thread = array(array("id"=>"0"),array("id"=>"$id"));
        }
    } else {
        $thread = array(array("id"=>"0"),array("id"=>"$id"));
    }
    if ( $thread ) foreach($thread as $zeile) {
        $t->set_var(array(
            'Sel' => (isset($daten['id']) && $daten['id']==$zeile['id'])?' selected':'',
            'TID' => $zeile['id'],
        ));
        $t->parse("Block4","Selectbox3",true);
    }
    //------------------------------------------- Kontakte
    $i = 0;
    $t->set_block("cont","Liste","Block");
    $zeile = '';
    if ( $bezug<>0 ) {
        $calls = getAllCauseCall($bezug);
        if ( $calls ) foreach( $calls as $zeile ) {
            $t->set_var(array(
                'LineCol'  => ($zeile["bezug"]==0)?4:($i%2+1),
                'Type'     => $zeile["kontakt"],
                'Datum'    => db2date($zeile["calldate"]).substr($zeile["calldate"],10,6),
                'Betreff'  => $zeile["cause"],
                'Kontakt'  => $zeile["kontakt"],
                'IID'      => $zeile["id"]
            ));
            $t->parse("Block","Liste",true);
            $i++;
        };
        $cause = isset($zeile["cause"])?$zeile['cause']:'';
    } else {
        $t->set_var(array(
            'LineCol'  => 1,
            'Type'     => '',
            'Datum'    => '',
            'Betreff'  => 'Keine Einträge',
            'Kontakt'  => '',
            'IID'      => false
        ));
        $t->parse("Block","Liste",true);
        $cause = (empty($daten['Betreff']))?'':$daten['Betreff'];
    };
    //------------------------------------------- Eingabemaske
    if ( empty($daten["CID"]) ) {
        $cid = (empty($zeile["caller_id"])?"0":$zeile["caller_id"]);
    } else {
        $cid = $daten["CID"];
    }
    $tmpdata = getUserEmployee(array('CallEdit','CallDel'));
    $EDIT    = ( $tmpdata['CallEdit']=='t' and isset($_GET["hole"]) )?"visible":"hidden";
    if ( $daten['kontakt'] == 'N' ) {
        $SAVE = 'hidden';
        $EDIT = 'visible';
    } else {
        $SAVE = 'visible';
    }
    if ( empty($daten["cause"]) ) { 
       if ( empty($zeile["cause"]) ) {
          $cause   = '';
       } else {
          $cause   = $zeile["cause"];
       }
    } else {
       $cause = $daten["cause"];
    }
    $deletes = getCntCallHist($bezug,true);
    $t->set_var(array(
        'id'      => (isset($daten["id"]))?$daten["id"]:'',     //TelCallID
        'nummer'  => $daten["nummer"],                          //AdressID
        'EDIT'    => $EDIT,
        'SAVE'    => $SAVE,
        'DELETE'  => ($tmpdata['CallDel']=='t' and isset($_GET["hole"]) )?"visible":"hidden",
        //'HISTORY' => (isset($daten['history']) && $daten["history"]>0)?"visible":"hidden",
        'HISTORY' => (isSetVarTrue($daten['history'],'>',0))?"visible":"hidden",
        'HDEL'    => ($deletes>0)?"visible":"hidden",
        'Q'       => $Q,
        'bezug'   => (isset($daten["bezug"]))?$daten['bezug']:$bezug,
        'cause'   => addslashes($cause),
        'c_long'  => $daten["c_long"],
        'datum'   => $daten["datum"],
        'zeit'    => $daten["zeit"],
        'INOUT'.((isset($daten['inout']))?$daten['inout']:'') => "checked",
        'R'.$daten["kontakt"] => ' checked',
        'Firma'   => $daten["Firma"],
        'Plz'     => $daten["Plz"],
        'Ort'     => $daten["Ort"],
        'wvl'     => ($daten["wvldate"])?' checked':'',
        'wvldate' => $daten["wvldate"],
        'WVLID'   => $daten["wvlid"],
        'CID'     => $cid,
        'fid'     => $fid,
        'pid'     => $pid,
        'dateiname'   => $daten["dateiname"],
        'ODatei'  => (empty($daten["datei"]))?'':("<a href='dokumente/".$_SESSION["dbname"]."/".$daten["Dpfad"]."/".$daten["dateiname"]."' target='_blank'>".$daten["dateiname"]."</a>"),
        'datei'   => (isset($daten["datei"]))?$daten["datei"]:'',
        'Dcaption' => $daten["DCaption"],
    ));
    //------------------------------------------- Dateianhänge
     if ( isset($daten["Files"]) ){
        $t->set_block("cont","Files","Block1");
        if ($daten["Files"]) foreach($daten["Files"] as $zeile) {
            $filelink = "<a href='dokumente/".$_SESSION["dbname"]."/".$zeile["pfad"]."/".$zeile["filename"]."' target='_blank'>".$zeile["filename"]."</a>";
            $t->set_var(array(
                'Anhang'    => $filelink,
                'DCaption'  => $zeile["descript"]
            ));
            $t->parse("Block1","Files",true);
            $i++;
        }
    };
    $t->pparse("out",array("cont"));

?>
