<?php
    require_once("inc/stdLib.php");
    require_once 'Contact_Vcard_Parse.php';
    $curr = 1;

    if( !function_exists( 'curl_init' ) ){
            die( 'Curl (php5-curl) ist nicht installiert!' );
    }

    $fp = fopen('/tmp/syncop.log','w');

    function logdata($Q,$id,$data) {
        global $fp;
        fputs($fp,print_r($data,true));
        return true;
    }
    function adr2arr($line) {
         $adr["POSTSTELLE"]	= $line['value'][0][0];
         $adr["ERWEITERT"]	= $line['value'][1][0];
         $adr["STRASSE"] 	= $line['value'][2][0];
         $adr["ORT"]    	= $line['value'][3][0];
         $adr["REGIO"]	    = $line['value'][4][0];
         $adr["PLZ"]	    = $line['value'][5][0];
         $adr["LAND"]	    = $line['value'][6][0];
         return $adr;
    }
    function mkAdr($data) {
        if ( isset($data['ORG'][0]['value'][0]) ) {
            $name = $data['ORG'][0]['value'][0][0];
        } else {
            $name  = $data['N'][0]['value'][1][0];
            $name .= ' '.$data['N'][0]['value'][0][0];
        }
        foreach ($data['ADR'] as $line) {
            if ( $line['param']['TYPE'] ) {
               $adr[$line['param']['TYPE'][0]] = adr2arr($line);
               $adr[$line['param']['TYPE'][0]]['NAME'] = $name;
            } else {
               $adr['WORK'] = adr2arr($line);
               $adr['WORK']['NAME'] = $name;
            }
        }; 
        if ( !isset($adr['WORK']) and isset($adr['HOME']) ) $adr['WORK'] = $adr['HOME'];
        if ( isset($adr['HOME']) ) {
            foreach ($adr['HOME'] as $row) {
                //if ( !isset($adr['WORK'] ) ......
            }
        }
        if ($data['EMAIL']) foreach ($data['EMAIL'] as $line) {
            if ( $line['param']['TYPE'] ) {
               $adr[$line['param']['TYPE'][0]]['EMAIL'] = $line['value'][0][0];
            } else {
               $adr['WORK']['EMAIL'] = $line['value'][0][0];
            }
        } else {
            $adr['WORK']['EMAIL'] = '';
        }; 
        if ($data['TEL']) foreach ($data['TEL'] as $line) {
            if ( $line['param']['TYPE'] ) {
               $adr[$line['param']['TYPE'][0]]['TEL'] = $line['value'][0][0];
            } else {
               $adr['WORK']['TEL'] = $line['value'][0][0];
            }
        }  else {
            $adr['WORK']['TEL'] = '';
        }; 
        if ( isset($adr['WORK']) ) {
            return $adr['WORK'];
        } else if ( isset($adr['HOME']) ) {
            return $adr['HOME'];
        } else {
        }
    }
    function updAdr($Q,$id,$data,$rev) {
        global $fp;
        $cp = (($Q=='P')?'cp_':'');
        $tab = array('C'=>'customer','V'=>'vendor','P'=>'contacts');
        $rs = $GLOBALS['db']->getOne('SELECT * FROM '.$tab[$Q].' WHERE '.$cp.'id = '.$id);
        $date = date_parse($rev);
        fputs($fp,"updAdr: $Q $id\n");
        fputs($fp,print_r($date,true),true);
        fputs($fp,print_r($rs,true),true);
        fputs($fp,print_r($data,true),true);
        return true;
    };
    function insAdr($Q,$data) {
        global $fp,$curr;
        $tab = array('C'=>'customer','V'=>'vendor','P'=>'contacts');
        if ( $Q == 'P' ) { 
            $fld = 'cp_name,cp_street,cp_zipcode,cp_city,cp_email,cp_phone1'; 
            $sql = "INSERT INTO contacts ($fld) VALUES ('%s','%s','%s','%s','%s','%s')";
            $sql = sprintf($sql,$data['NAME'],$data['STRASSE'],$data['PLZ'],$data['ORT'],$data['EMAIL'],$data['TEL']);
        } else { 
            $fld = 'name,street,zipcode,city,email,phone,currency_id'; 
            $sql = "INSERT INTO ".$tab[$Q]." ($fld) VALUES ('%s','%s','%s','%s','%s','%s',%d)";
            $sql = sprintf($sql,$data['NAME'],$data['STRASSE'],$data['PLZ'],$data['ORT'],$data['EMAIL'],$data['TEL'],$curr);
        };
        //$rc = $GLOBALS['db']->query($sql);
        fputs($fp,"insAdr: $Q $rc!\n");
        fputs($fp,"$sql\n");
        fputs($fp,print_r($data,true));
        return true;
    };
    function chkAdr($Q,$id,$adr) {
        global $fp;
        $fromto = array(' '=>'.*','-'=>'.*','ß'=>'(ss|ß)','ss'=>'(ss|ß)'); // Anzahl Leerzeichen egal, ss oder ß egal
        $cp = (($Q=='P')?'cp_':'');
        $eindeutig = 0;
        $ID = array();
        $tab = array('C'=>'customer','V'=>'vendor','P'=>'contacts');
        //Gibt es die E-Mail?
        if ( $adr['EMAIL'] != '' ) { 
            $rc = preg_match('/[_a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/',$adr['EMAIL'],$hit);
            $sql = "SELECT * FROM ".$tab[$Q]." WHERE ".$cp."email ilike  '%".$hit[0]."%'";
            $rs = $GLOBALS['db']->getAll($sql);
            if ( count($rs) == 1 ) { $ID[$rs[0]['id']] = 1; $eindeutig++;};
        }
        //Telefonnummer vergeben?
        if ( $adr['TEL'] != '' ) { 
            $ft = array('+'=>'\+',' '=>'.*','-'=>'.*','/'=>'.*'); // Anzahl Leerzeichen egal, ss oder ß egal
            $tel = strtr($adr['TEL'],$ft);
            $phone = ($Q=='P')?'cp_phone1':'phone';
            $sql = "SELECT * FROM ".$tab[$Q]." WHERE ".$phone." ~ '".$tel."'";
            $rs = $GLOBALS['db']->getAll($sql);
            if ( count($rs) == 1 ) {  
                $ID[$rs[0]['id']] = ( isset($ID[$rs[0]['id']]) )?$ID[$rs[0]['id']]+1:1; 
                $eindeutig++;
            };
        }
        //Strasse und PLZ stimmer überein?
        fputs($fp,"chkAdr $id\n$sql\n");
        if ( $adr['PLZ'] != '' && $adr['STRASSE'] != '' ) { 
            $street = strtoupper(strtr($adr['STRASSE'],$fromto)); // GroßKleinschreibung egal
            $sql = 'SELECT * FROM '.$tab[$Q].' WHERE '.$cp."zipcode = '".$adr['PLZ']."' AND ".$cp."street ~* '".$street."'";
            $rs = $GLOBALS['db']->getAll($sql);
            if ( count($rs) == 1 ) { 
               $ID[$rs[0]['id']] = ( isset($ID[$rs[0]['id']]) )?$ID[$rs[0]['id']]+1:1; 
               $eindeutig++;};
        }
        fputs($fp,"chkAdr $id\n$sql\n");
        //Wie ist es mit dem Namen?
        $name = strtoupper(strtr($adr['NAME'],$fromto)); // GroßKleinschreibung und einige Zeichen egal
        $sql  = 'SELECT * FROM  '.$tab[$Q].' WHERE '.$cp."name ~* '".$name."'";
        $rs = $GLOBALS['db']->getAll($sql);
        if ( count($rs) == 1 ) { 
            $ID[$rs[0]['id']] = ( isset($ID[$rs[0]['id']]) )?$ID[$rs[0]['id']]+1:1; 
            $eindeutig++;
        };
        fputs($fp,"chkAdr $id\n$sql\n");
        $id = false;
        while(list($key,$cnt) = each($ID)) {
            if ( $cnt > 1 ) $id = $key;  // mind. 2 von 4 Treffer müssen es sein. 
                                         // was wenn 2 x 2 Treffer? Welcher ist richtig?
                                         // Treffer evtl Gewichten. Dann mind. 3 Trefferpunkte?
        }
        fputs($fp,"chkAdr $id ".print_r($ID,true)."\n");
        return $id;
    }
    function chkKey($key,$adr,$rev) {
        $tab = array('C'=>'customer','V'=>'vendor','P'=>'contacts');
        echo "KEY:$key!";
        if ( preg_match('/^([CVP])([0-9]+)$/',$key,$hit) ) {
            $Q  = $hit[1];
            $id = $hit[2];
            $sql = 'SELECT * FROM '.$tab[$Q].' WHERE '.(($Q=='P')?'cp_':'').'id = '.$id;
            $rs  = $GLOBALS['db']->getOne($sql);
            echo $sql;
            if ( $rs ) {
                $rc = updAdr($Q,$id,$adr,$rev);
            } else {
                $id = chkAdr($Q,$id,$adr);
                if ( $id ) { $rc = updAdr($Q,$id,$adr,$rev); }
                else { $rc = insAdr($Q,$adr); };
            }   
            return $rc;
        } else {
            return $false;
        }
    }
    function chkUid($key,$adr,$rev) {  // Brauch ich das eigentlich??
        echo "UID:$key!";
        echo "!".preg_match('/^([CVP])([0-9]+)@/',$key,$hit)."!";
        if ( preg_match('/^([CVP])([0-9]+)@/',$key,$hit) ) {
            $Q  = $hit[1];
            $id = $hit[2];
            $sql = 'SELECT * FROM '.$tab[$Q].' WHERE '.(($Q=='P')?'cp_':'').'id = '.$id;
            $rs  = $GLOBALS['db']->getOne($sql);
            if ( $rs ) {
                $rc = updAdr($Q,$id,$adr,$rev);
            } else {
                $id = chkAdr($Q,$id,$adr);
                if ( $id ) { $rc = updAdr($Q,$id,$adr,$rev); }
                else { $rc = insAdr($Q,$adr); };
            }   
            return $rc;
        } else {
            return $false;
        }
    }
    function chkCard($adr,$rev) {
        foreach(array('C','V','P') as $Q) {
            $id = chkAdr($Q,$id,$adr);
            if ( $id ) break;
        }
        if ( $id ) { $rc = updAdr($Q,$id,$adr,$rev); }
        else { $rc = insAdr($Q,$adr); };
        return $rc;
    }
    $usr = getUserEmployee(array('getadrsrv','protgetadr','putadrsrv','protputadr','adrname','adrpwd'));
    print_r($usr);
    $BaseUrl  = $usr['protgetadr']."://".$usr['getadrsrv'];
    //echo $BaseUrl;
    //$BaseUrl = 'http://silent/';
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $BaseUrl );
    curl_setopt( $ch, CURLOPT_USERPWD, $usr['adrname'] . ':' . $usr['adrpwd']);
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    if ( curl_errno($ch) )  echo 'Curl error: '.curl_error( $ch );
    $result = curl_exec( $ch );
    echo "Curl init ok $BaseUrl<br>";
    $rc = preg_match_all('^href="(.+\.vcf)"><img^',$result,$hit, PREG_PATTERN_ORDER);
    preg_match('#http[s]?://[^/]+#',$BaseUrl,$srv);
    $server = $srv[0];
    echo $server."<br>";
    echo "---------------------------------------------<br>";
    if ( count($hit)>0 ) foreach ( $hit[1] as $url ){ 
        echo $server.$url."<br>";
        curl_setopt( $ch, CURLOPT_URL, $server.$url );
        $result = curl_exec( $ch );
        $parse = new Contact_Vcard_Parse();
        $cardinfo = $parse->fromText($result);
        $adr = mkAdr($cardinfo[0]);
        $rev = ( isset($cardinfo[0]['REV']) )?$cardinfo[0]['REV'][0]['value'][0][0]:'';
        if ( isset($cardinfo[0]['KEY'] ) ) {
            $key = $cardinfo[0]['KEY'][0]['value'][0][0];
            $rc  = chkKey($key,$adr,$rev);
            echo "KEY:<br>";
        /*} else  if ( isset($cardinfo[0]['UID'] ) ) {
            $uid = $cardinfo[0]['UID'][0]['value'][0][0];
            $rc  = chkUid($uid,$adr,$rev);
        } */ 
        } else { 
            $rc = chkCard($adr,$rev);
            echo "RC:<br>";
        };
        echo "<pre>";
        //if ( $rc ) { print_r($adr); }
        //else { print_r($cardinfo[0]);};
        print_r($adr); echo "<br>";
        print_r($cardinfo[0]); echo "<br>";
        echo "</pre>";
        echo "---------------------------------------------<br>";
    };
    curl_close( $ch );
    fclose($fp);
?>
