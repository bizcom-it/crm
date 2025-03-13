<?php
    require("inc/stdLib.php");
    include("template.inc");
    include("FirmenLib.php");
    include("crmLib.php");
    $Vars   = false;
    $fid    = ( isset($_GET['fid']) )?$_GET['fid']:false;
    $Q      = ( isset($_GET['Q']) )?$_GET['Q']:'';
    $id     = ( isset($_GET['id']) )?$_GET['id']:false;
    $kdhelp = getWCategorie(true);
    $fa     = getFirmenStamm($id,true,$Q);
    $start  = ( isset($_GET["start"]) )?($_GET["start"]):0;
    $cmsg   = getCustMsg($id);
    $tmp    = getVariablen($id);
    //$telco  = getTelco(true);
    $variablen = '';
    $t = new Template($base);
    $t->set_file(array("fa1" => "firma1.tpl"));
    doHeader($t);
    if (count($tmp)>0) {
        $t->set_block("fa1","vars","BlockS");
        $variablen=count($tmp)." Variablen";
        $Vars = "<table>\n";
        foreach ($tmp as $row) {
            switch ($row["type"]) {
                case "textfield":         //ToDo:  Es sollte dann natürlich auch ein Textfeld zu sehen sein .... 
                    preg_match("/width[ ]*=[ ]*(\d+)/i",$row["options"],$hit); $w = (isset($hit[1])&&$hit[1]>5)?$hit[1]:30;
                    $txt = '';
                    while (strlen($row["text_value"])>$w) {
                        $txt .= substr($row["text_value"],0,$w)."<br>";
                        $row["text_value"] = substr($row["text_value"],$w);
                    };
                    $txt .= $row["text_value"];
                break;
                case "select" :  // ToDo: Implementieren!!!
                case "text" : 
                    $txt = $row["text_value"];
                break;
                case "number" : 
                    preg_match("/PRECISION[ ]*=[ ]*([0-9]+)/i",$row["options"],$pos);
                    if ($pos[1]) { $txt = sprintf("%0.".$pos[1]."f",$row["number_value"]); }
                    else {$txt = $row["number_value"];}
                break;
                case "date" : 
                    $txt = ($row["timestamp_value"])?db2date(substr($row["timestamp_value"],0,10)):"";
                break;
                case "bool" : 
                    $txt = ($row["bool_value"]=='f')?'.:no:.':'.:yes:.';
                break;
                case "customer" : 
                    $txt = getCvarName($row["number_value"]);
                break;
                default	: $txt = $row["text_value"];
            }
            if ( strpos( $txt, "http://" ) === 0 || strpos( $txt, "https://" ) === 0 || strpos( $txt ,"www." ) === 0 ){ 
                $txt = "<a href=\"".$txt."\" target=\"_blank\">".$txt."</a>";
            }
            $t->set_var(array(
                'varname' => $row["description"],
                'varvalue' => $txt
            ));
            $t->parse("BlockS","vars",true);
        }
    }
    if ($fa["grafik"]) {
        if (file_exists("dokumente/".$_SESSION["dbname"]."/$Q".$fa["nummer"]."/logo.".$fa["grafik"])) {
            $Imageurl = 'dokumente/'.$_SESSION['dbname']."/$Q".$fa['nummer'].'/logo.'.$fa['grafik'];
            $Image    = "<img id='logo'  src='$Imageurl' ".$fa["icon"]." border='0'>";// onClick='showimg(\"$Imageurl\");'>";
        } else {
            $Image="Bild nicht<br>im Verzeichnis";
        }
    } else {
        $Image="";
    };
    if ($fa["homepage"]<>"") {
        $internet=(preg_match("^://^",$fa["homepage"]))?$fa["homepage"]:"http://".$fa["homepage"];
    } else {
        $internet = '';
    };
    if ($fa["discount"]) {
        $rab=($fa["discount"]*100)."%";
    } else if($fa["typrabatt"]) {
        $rab=($fa["typrabatt"]*100)."%";
    } else {
        $rab="";
    }
    $SWITCH = getUserEmployee(array('angebot_button','auftrag_button','liefer_button','rechnung_button','zeige_extra','asterisk',
                                    'zeige_karte','zeige_etikett','zeige_lxcars','zeige_bearbeiter','kdviewli','kdviewre','mobiletel',
                                    'external_mail','planspace','streetview','interv','GEODB','workphone'));
    $karte1=str_replace(array("%TOSTREET%","%TOZIPCODE%","%TOCITY%"),array(strtr($fa["street"]," ",$SWITCH['planspace']),$fa["zipcode"],$fa["city"]),$SWITCH['streetview']);
    $karte2=str_replace(array("%TOSTREET%","%TOZIPCODE%","%TOCITY%"),array(strtr($fa["shiptostreet"]," ",$SWITCH['planspace']),$fa["shiptozipcode"],$fa["shiptocity"]),$SWITCH['streetview']);
    if (preg_match("/%FROM/",$karte1)) { //ToDo? Wo wird $karte definiert??
        include_once "UserLib.php";
        $user=getUserStamm($_SESSION["loginCRM"]);
        if ($user["addr1"]<>"" and $user["addr3"]<>"" and $user["addr2"]) {
            $karte1=str_replace(array("%FROMSTREET%","%FROMZIPCODE%","%FROMCITY%"),array(strtr($user["addr1"]," ",$SWITCH['planspace']),$user["addr2"],$user["addr3"]),$karte1);
            $karte2=str_replace(array("%FROMSTREET%","%FROMZIPCODE%","%FROMCITY%"),array(strtr($user["addr1"]," ",$SWITCH['planspace']),$user["addr2"],$user["addr3"]),$karte2);
        };
    }
    if ( isset($telco['LocalContext']) and $telco['LocalContext'] != '' and $fa['phone'] != '' ) {
       $asterisk = '<img src="image/telefon.gif" onClick="doTelCall()">';
    } else {
       $asterisk = '';
    };
    $faphone = strtr($fa['phone'],array('+'=>'00','/'=>'','(0)'=>'','-'=>'',' '=>''));
    $phonelink = $fa['phone'];
    if ( $SWITCH['mobiletel'] ) {
        $phonelink = '<a href="tel:'.$phonelink.'">'.$phonelink.'</a>';
    } 
    $faxlink   = $fa['fax'];

    $taxzone=array("Inland","EU mit UStId","EU ohne UStId","Ausland",'Inland');
    $sales=($Q=="C")?"sales":"purchase";
    $request=($Q=="C")?"sales":"request";
    $c2c  = 'name:'.$fa['name'].'|street:'.$fa['street'].'|zipcode:'.$fa["zipcode"].'|city:'.$fa["city"].'|country:'.$fa['country'];
    $c2c .= '|sw:'.$fa["sw"].'|contact:'.$fa["contact"].'|greeting:'.$fa['greeting'].'|email:'.$fa['email'].'|homepage:'.$fa['homepage'];
    $t->set_var(array(
            'FAART'     => ($Q=="C")?".:Customer:.":".:Vendor:.",
            'CuVe'      => ($Q=="C")?"customer":"vendor",
            'Q'         => $Q,
            'C2C'       => $c2c,
            'FID'       => $id,
            'INID'      => db2date(substr($fa["itime"],0,10)),
            'interv'    => $SWITCH["interv"]*1000,
            'Fname1'    => $fa['name'],
            'kdnr'      => $fa['nummer'],
            'kdtyp'     => $fa['kdtyp'],
            'lead'      => $fa['leadname'],
            'Fdepartment_1' => $fa['department_1'],
            'Fdepartment_2' => ($fa['department_2'])?$fa['department_2']."<br />":"",
            'Strasse'   => $fa['street'],
            'Land'      => $fa['country'],
            'Bundesland' => $fa['bundesland'],
            'Plz'       => $fa['zipcode'],
            'Ort'       => $fa['city'],
            'GEODB'     => ($SWITCH['GEODB']=='t')?'1==1':'1>2',
            'Telefon'   => $phonelink,
            'Fax'       => $faxlink,
            'Fcontact'  => $fa['contact'],
            'eMail'     => $fa['email'],
            'verkaeufer' => $fa['verkaeufer'],
            'bearbeiter' => $fa['bearbeiter'],
            'branche'   => $fa['branche'],
            'sw'        => $fa['sw'],
            'notiz'     => nl2br($fa['notes']),
            'bank'      => $fa['bank'],
            'directdebit' => ($fa['direct_debit']=="t")?".:yes:.":".:no:.",
            'blz'       => $fa['bank_code'],
            'konto'     => $fa['account_number'],
            'iban'      => $fa['iban'],
            'bic'       => $fa['bic'],
            'uid'       => $fa['uid'],
            'konzernname' => $fa['konzernname'], // ToDo?? definiert??
            'konzernmember' => ($fa['konzernmember']>0)?"( ".$fa['konzernmember']." )":"( * )",
            'konzern'   => $fa['konzern'],
            'Internet'  => $internet,
            'USTID'     => $fa['ustid'],
            'Steuerzone' => ($fa['taxzone_id'])?$taxzone[$fa['taxzone_id']]:$taxzone[0],
            'Taxnumber' => $fa['taxnumber'],
            'rabatt'    => $rab,
            'headcount' => ($fa['headcount'])?$fa['headcount']:'-',
            //'terms' => $fa['terms'],
            'terms'     => ($fa['terms_netto'])?$fa['terms_netto']:"0",
            'kreditlim' => sprintf("%0.2f",$fa['creditlimit']),
            'op'        => ($fa['op']>0)?sprintf("<span class='op'>%0.2f</span>",$fa['op']):"0.00",
            'oa'        => ($fa['oa']>0)?sprintf("<span class='oa'>%0.2f</span>",$fa['oa']):"0.00",
            'preisgrp'  => $fa['pricegroup'],
            'language'  => $fa['language'],
            'Sshipto_id' => ($fa['shipto_id']>0)?$fa['shipto_id']:"",
            'Sname1'    => $fa['shiptoname'],
            'Sdepartment_1' => $fa['shiptodepartment_1'],
            'Sdepartment_2' => $fa['shiptodepartment_2'],
            'SStrasse'  => $fa['shiptostreet'],
            'SLand'     => $fa['shiptocountry'],
            'SBundesland' => $fa['shiptobundesland'],
            'SPlz'      => $fa['shiptozipcode'],
            'SOrt'      => $fa['shiptocity'],
            'STelefon'  => $fa['shiptophone'],
            'SFax'      => $fa['shiptofax'],
            'SeMail'    => $fa['shiptoemail'],
            'Scontact'  => $fa['shiptocontact'],
            'Scnt'      => $fa['shiptocnt'],
            'Sids'      => $fa['shiptoids'],
            'Cmsg'      => $cmsg,
            'IMG'       => $Image,
            'IMGURL'    => $Imageurl,
            'KARTE1'    => $karte1,
            'KARTE2'    => $karte2,
            'sales'	    => ($Q=="C")?"sales":"purchase",
            'request'   => ($Q=="C")?"sales":"request",
            'apr'       => ($Q=="C")?"ar":"ap",
            'userphone'  => $SWITCH['workphone'], //.'@'.$telco['LocalContext'],
            'callphone'  => $telco['Vorzeichen'].$faphone, //.'@'.$telco['ExternContext'],
            'asterisk'   => $asterisk,
            'ANGEBOT_BUTTON' => ($SWITCH['angebot_button']=='t')?
                                '<a class="firmabutton" href="#" onClick="doOe(\''.$sales.'_quotation\');"><img src="image/angebot.png" title="Angebot/Anfrage erstellen" border="0"></a>&nbsp;':'',
            'AUFTRAG_BUTTON' => ($SWITCH['auftrag_button']=='t')?
                                '<a class="firmabutton" href="#" onClick="doOe(\''.$request.'_order\');"><img src="image/auftrag.png" title="neuen Auftrag eingeben" border="0"></a>&nbsp;':'',
            'LIEFER_BUTTON'  => ($SWITCH['liefer_button']=='t')? 
                                '<a class="firmabutton" href="#" onClick="doDo();"><img src="image/lieferschein.png" title="neuen Lieferschein erstellen" border="0"></a>&nbsp;':'',
            'RECHNUNG_BUTTON'=> ($SWITCH['rechnung_button']=='t')?
                                '<a class="firmabutton" href="#" onClick="doIr();"><img src="image/rechnung.png" title="neue Rechnung erstellen" border="0"></a>&nbsp;':'',
            'EXTRA_BUTTON'   => ($SWITCH['zeige_extra']=='t')?
                                '<a class="firmabutton" href="#" name="extra" onClick="extra();"><img src="image/extra.png" title="Extrafelder" border="0"></a>&nbsp;':'',                   
                                //'<a class="firmabutton" href="extrafelder.php?owner='.$Q.$id.'" name="extra" target="_blank"><img src="image/extra.png" title="Extrafelder" border="0"></a>&nbsp;':'',                   
            'KARTE_BUTTON'   => ($SWITCH['zeige_karte']=='t')?
                                '<a class="firmabutton" href="'.$karte1.'" name="karte" target="_blank"><img src="image/karte.png" title=".:city map:." border="0"></a>&nbsp;':'',
            'ETIKETT_BUTTON' => ($SWITCH['zeige_etikett']=='t')?
                                '<a class="firmabutton" href="#" onCLick="anschr(1);" title=".:print label:."><img src="image/brief.png" alt=".:print label:." border="0"></a>&nbsp;':'',  
            'QR_BUTTON'      => '<a class="firmabutton" id="qrbutt" href="#" title="QR Code erstellen"><img src="image/qrn.png" alt=".:print qr:." border="0"></a>&nbsp;',
            'SYNC_BUTTON'    => '<a class="firmabutton" id="syncbutt" href="#" title="Sync"><img src="image/syncsend.png" alt=".:sync data:." border="0"></a>&nbsp;',
            'BRIEF_BUTTON'   => '<a class="firmabutton" href="#" onClick="doIb();"><img src="image/mail.png" title="neuen Brief erstellen" border="0"></a>&nbsp;',
            'LxCars_BUTTON'  => ($SWITCH['zeige_lxcars']=='t'&&$Q=="C")?
                               '<a class="firmabutton" href="#" onCLick="doLxCars();" title="KFZ-Daten"><img src="image/auto.png" alt="LxCars"></a>&nbsp;':'',
            'zeige_bearbeiter' => ($SWITCH['zeige_bearbeiter']=='t')?"visible":"hidden",      
            'leadsrc' => $fa['leadsrc'],
            'variablen' => $variablen,
            'Vars' => $Vars,
            'autosync'.$fa['sync'] => 'checked',
            'erstellt' => db2date($fa['itime']),
            'modify' => db2date($fa['mtime']),
            'kdviewli' => $SWITCH['kdviewli'] - 1,
            'kdviewre' => $SWITCH['kdviewre'] - 1,
            'zeige' => ($fa['obsolete']=="f")?"visible":"hidden",
            'verstecke' => ($fa['obsolete']=="t")?"visible":"hidden",
            'chelp' => ($kdhelp)?"visible":"hidden",
            'none' => "visible",
            'mail_pre'      => ($SWITCH['external_mail']=='t')?'mailto:':'mail.php?aktion=domail&TO=',
            'mail_after'    => ($SWITCH['external_mail']=='t')?'':'&KontaktTO=C'.$id
    ));
    $t->set_block("fa1","Liste","Block");
    $i=0;
    $nun=date("Y-m-d H:i");
    if ($kdhelp) {
        $t->set_block("fa1","kdhelp","Block1");
        $kdtmp[]=array("id"=>-1,"name"=>"Online Kundenhilfe");
        $kdhelp=array_merge($kdtmp,$kdhelp);
        foreach($kdhelp as $col) {
            $t->set_var(array(
                'cid' => $col["id"],
                'cname' => $col["name"]
            ));
            $t->parse("Block1","kdhelp",true);
        };
    }
    $t->Lpparse("out",array("fa1"),$_SESSION["countrycode"],"firma");
?>
