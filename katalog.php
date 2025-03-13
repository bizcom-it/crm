<?php
    require("inc/stdLib.php");
    include_once('inc/katalog.php');
    include_once("template.inc");
    $t = new Template($base);
    doHeader($t);
    $link = "";
    $linklog = '';
    $msg = '';
    $pglist = '';
if ( isset($_POST['ok']) ) {
    function delhtml($txt) {
        if ( $txt == '' ) 
            return "Das ist der Langtext";
        $txt = preg_replace('#</?[^>]*>.*?#s','',$txt);
        $txt = preg_replace('#[\r]#s','',$txt);
        $txt = preg_replace('#\n\n#s','\linebreak',$txt);
        return $txt;
    }
    $artikel = getArtikel($_POST);
    $tax = getTax();
    $vorlage = prepTex($_POST['vorlage']);
    $lastPG = '';
    if (file_exists('tmp/katalog.pdf')) unlink('tmp/katalog.pdf');
    if (file_exists('tmp/katalog.log')) unlink('tmp/katalog.log');
    if (file_exists('tmp/tabelle.tex')) unlink('tmp/tabelle.tex');
    if (file_exists('tmp/tabelle.aux')) unlink('tmp/tabelle.aux');
    $f = fopen('tmp/katalog.tex','w');
    $pre =  preg_replace("/<%datum%>/",date('d.m.Y'),$vorlage['pre']);
    $rc = fputs($f,$pre);
    $suche = array('&uuml;','&ouml;','&auml;','&Uuml;','&Ouml;','&Auml;','&szlig;','&lt;','&gt;','&','_','"','!','#','%','(',')');
    $ersetze = array('ü','ö','ä','Ü','Ö','Ä','ß','\textless ','\textgreater ','\&','\_','\"',' : ','\#','\%','\{','\}');
    if ($artikel) foreach($artikel as $part) {
        $line = $vorlage['artikel'];
        if ($lastPG != $part['partsgroup']) {
            $lastPG = $part['partsgroup'];
            $val = str_replace($suche,$ersetze,$part['partsgroup']);
            $line = preg_replace("/<%partsgroup%>/i",$val,$line);
            $line = preg_replace("/<%newpg%>/i",'new',$line);
            //$line = preg_replace("/<%index%>/i",'\index{'.$val.'}',$line);
            $line = preg_replace("/<%[^%]+%>/i",'0',$line);
            $rc = fputs($f,$line);
            $line = $vorlage['artikel'];
        }
        if ($_POST['preise'] == '1') { $preis = $part['sellprice']; }
        else if ($_POST['preise'] == '2') { $preis = $part['listprice']; }
        else { $preis = $part['price']; };
        if ($_POST['prozent'] > 0) {
            if ($_POST['pm']=='+') { $preis += $preis / 100 * $_POST['prozent']; }
            else                   { $preis -= $preis / 100 * $_POST['prozent']; };
        }
        if ($_POST['addtax']) $preis = $preis * (1 + $tax[$part['bugru']]['rate']);
        foreach ($part as $key=>$val) {
            if ($key == 'description') $val = str_replace($suche,$ersetze,$val);
            if ($key == 'partnumber')  $val = str_replace($suche,$ersetze,$val);
            if ($key == 'notes') {
                $val = str_replace($suche,$ersetze,$val);
                $val = delhtml($val);
            };
            //if ($key == 'image') $val = str_replace($suche,$ersetze,$val);
            if ($key == 'image') {
                 if ($val == '') $val = 'image/nopic.png';
                 if (preg_match('/http[s]*:/i',$val)) $val = 'image/nopic.png';
                 if (! preg_match('/\.(png|jpg)$/i',$val)) $val = 'image/nopic.png';
                 if (!file_exists($val)) $val = 'image/nopic.png';
            }
            $line = preg_replace("/<%newpg%>/i",'xxx',$line);
            //$line = preg_replace("/<%index%>/i",'',$line);
            //if ($key == 'partsgroup') $val = 'x';
            if ($key == 'sellprice') $val = sprintf("%0.2f",$preis);
            if ($key == 'bugru') $val = sprintf("%0.1f",$tax[$part['bugru']]['rate']*100);
            $line = preg_replace("/<%$key%>/i",$val,$line);
        }
        $rc = fputs($f,$line);
    }
    $rc = fputs($f,$vorlage['post']);
    fclose($f);
    $home = getenv('HOME');
    $openin_any = getenv('openin_any');
    putenv('HOME='.getcwd().'/tmp');
    putenv('openin_any=p');    
    $rc = @exec('pdflatex -interaction=batchmode -output-directory=tmp/ tmp/katalog.tex',$out,$ret);
    if ( $ret == 0 ) {
        //$rc = @exec('makeindex -o tmp/katalog.ind tmp/katalog.tex',$out,$ret);
        $rc = @exec('pdflatex -interaction=batchmode -output-directory=tmp/ tmp/katalog.tex',$out,$ret);
        if (file_exists('tmp/katalog.pdf'))   {  
            $link = 'tmp/katalog.pdf'; 
            $msg = "RC:$rc Ret:$ret Out:".$out[0];
     	} else { 
            $link = '';
            if (file_exists('tmp/katalog.log'))   { 
                $linklog = 'tmp/katalog.log';
            }
            $msg = "Kein PDF erstellt<br>RC:$rc Ret:$ret Out:".$out[0]; 
        };
    } else {
        if (file_exists('tmp/katalog.pdf'))   {
            $link = 'tmp/katalog.pdf'; 
            $msg  = 'Evlt nicht korrekt<br>';
        }
        $msg .= "Fehler beim Erstellen<br>RC:$rc Ret:$ret Out:".$out[0];
        $linklog = 'tmp/katalog.log';
    }
    putenv('HOME='.$home);
    putenv('openin_any='.$openin_any);
} else if ( isset($_POST['del']) and ( $_POST['del'] != '') ) {
    $name = $_POST['maskem'];
    if ( $name != '' ) {
        $sql = "DELETE FROM katmask WHERE maskname = '$name'";
        $rc  = $GLOBALS['db']->query($sql);
        $msg = "Maske $name gelöscht";
    }
} else if ( isset($_POST['save']) and ( $_POST['save'] != '') ) {
    unset($_POST['save']);
    unset($_POST['load']);
    unset($_POST['maskem']);
    $name = $_POST['maskename'];
    if ( $name != '') {
        unset($_POST['maskename']);
        $maske = serialize($_POST);
        $such = "SELECT * FROM katmask WHERE maskname like '$name'";
        $rs   = $GLOBALS['db']->getOne($such);
        if ( $rs ) {
            $sql = "UPDATE katmask SET mask = '$maske' WHERE maskid = ".$rs['maskid'];
        } else {
            $sql = "INSERT INTO katmask (maskname,mask) VALUES ('%s','%s')";
            $sql = sprintf($sql,$name,$maske);
        };
        //$rc = $GLOBALS['db']->query($sql);
        $rc = $GLOBALS['db']->query($sql,true); //nur easyerdbau
        $msg = "Maske $name gesichert";
    } else {
        $msg = 'Fehlender Maskenname';
    };
} else if ( isset($_POST['load']) and ( $_POST['load'] != '') ) {
    $name = $_POST['maskem'];
    $sql = "SELECT * FROM katmask WHERE maskname = '$name'";
    $rs  = $GLOBALS['db']->getOne($sql);
    if ( $rs ) {
        unset($_POST);
        $datas = unserialize($rs['mask']);
        $keys = array_keys($datas);
        foreach ( $keys as $key)  {
            $_POST[$key] = $datas[$key];
        }
        $msg = "Maske $name geladen";
        /*$_POST = array(
                    'partnumber'    => $data['partnumber'],
                    'description'   => $data['description'],
                    'ean'           => $data['ean'],
                    'prozent'       => $data['prozent'],
                    'pm'            => $data['pm'],
                    'order'         => $data['order'],
                    'partsgroup'    => $data['partsgroup'],
                    'addtax'        => $data['addtax'],
                    'image'         => $data['image'],
                    'shop'          => $data['shop'],
                    'preise'        => $data['preise'],
                    'vorlage'       => $data['vorlage']);*/
    }

} else {
    $_POST = array('partnumber'=>'','description'=>'','ean'=>'','prozent'=>'','pm'=>'-','order'=>'',
                   'partsgroup'=>'','addtax'=>'','image'=>'','shop'=>'','preise'=>'','vorlage'=>'');
};
    $sql = 'SELECT maskname FROM katmask order by maskname';
    $masken = $GLOBALS['db']->getAll($sql);
    $preise  = getPreise();
    $cvars   = getCustoms();
    $pglist  = getPgList();
    $t->set_file(array("kat" => "katalog.tpl"));
    $t->set_block('kat','cvarListe','BlockCV');
    if ($cvars) {
        foreach ($cvars as $cvar) {
           $wert = $_POST['vc_cvar_'.$cvar["type"].'_'.$cvar["name"]];
           switch ($cvar["type"]) {
               case "bool"   : $fld = "<input type='checkbox' name='vc_cvar_bool_".$cvar["name"]."' value='t'>";
                               break;
               case "date"   : $fld = "<input type='text' name='vc_cvar_timestamp_".$cvar["name"]."' size='10' id='cvar_".$cvar["name"]."' value=''>";
                               $fld.="<input name='cvar_".$cvar["name"]."_button' id='cvar_".$cvar["name"]."_trigger' type='button' value='?'>";
                               $fld.= '<script type="text/javascript"><!-- '."\n";
                               $fld.= 'Calendar.setup({ inputField : "cvar_'.$cvar["name"].'",';
                               $fld.= 'ifFormat   : "%d.%m.%Y",';
                               $fld.= 'align      : "BL",';
                               $fld.= 'button     : "cvar_'.$cvar["name"].'_trigger"});';
                               $fld.= "\n".'--></script>'."\n";
                               break;
               case "select" : $o = explode("##",$cvar["options"]);
                               $fld = "<select name='vc_cvar_text_".$cvar["name"]."'>\n<option value=''>---------\n";
                               foreach($o as $tmp) {
                                 $fld .= "<option value='$tmp'>$tmp\n";
                               }
                               $fld .= "</select>";
                               break;
               default       : $fld = "<input type='text' name='vc_cvar_".$cvar["type"]."_".$cvar["name"]."' value='$wert'>";
           }
           $t->set_var(array(
              'varlable' => $cvar["description"],
              'varfld'   => $fld,
           ));
           $t->parse('BlockCV','cvarListe',true); 
        }
    }
    $t->set_block('kat','maskList','BlockMA');
    if ( $masken )  foreach ($masken as $maske) {
           $t->set_var(array(
               'MASKNAME' => $maske['maskname'],
           ));
           $t->parse('BlockMA','maskList',true); 
    }
    $t->set_block('kat','Preise','BlockPr');
    if ($preise) foreach ($preise as $id=>$preis) {
           $t->set_var(array(
              'preisid' => $id,
              'preis' => $preis,
              'select' => ($id==$_POST["preise"])?"selected":"",
           ));
           $t->parse('BlockPr','Preise',true); 
    }
    chdir('vorlage');
    $vorlagen = glob('katalog*tex');
    chdir('..');
    $t->set_block('kat','vorlagen','BlockV');
    if ( $vorlagen ) foreach ( $vorlagen as $tex ) {
           $t->set_var(array(
               'VORLAGE' => $tex,
               'VSEL' => ($_POST['vorlage']==$tex)?'selected':'',
           ));
           $t->parse('BlockV','vorlagen',true); 
    }
    $ordersel = $_POST['order'].'sel';
    $vsel     = $_POST['vorlage'];
    $t->set_var(array(
        'partnumber'	  => $_POST['partnumber'],
        'description'     => $_POST['description'],
        'ean'             => $_POST['ean'],
        'prozent'         => $_POST['prozent'],
        'pm'.$_POST['pm'] => 'checked',
        $ordersel         => 'selected',
        'partsgroup'      => $_POST['partsgroup'],
        'pglist'          => $pglist,
        'addtax'	      => (isset($_POST['addtax'])&&($_POST['addtax']=='1'))?"checked":"",
        'image' 	      => (isset($_POST['image'])&&($_POST['image']=='1'))?"checked":"",
        'shop'   	      => (isset($_POST['shop'])&&($_POST['shop']=='1'))?"checked":"",
        'linklog'	      => $linklog,
        'link'	          => $link,
        'msg'	          => $msg
    ));
    $t->set_block("kat","Liste","Block");
    $t->Lpparse("out",array("kat"),$_SESSION['countrycode'],"firma");

?>
