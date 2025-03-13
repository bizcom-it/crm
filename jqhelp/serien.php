<?php

require_once("../inc/stdLib.php");
require_once("crmLib.php");

function connSync() {
    $tmpdata = getUserEmployee(array('cardsrv','cardname','cardpwd','cardsrverror'));
    if ( isset($tmpdata['cardsrv']) && !empty($tmpdata['cardsrv']) ) {
        try{
            $carddav = new carddav_backend($tmpdata['cardsrv'],$tmpdata['cardsrverror']);
            $carddav->set_auth($tmpdata['cardname'], $tmpdata['cardpwd']);
            return $carddav;
        } catch (Exception $e ) {
            $msg = 'Fehler ('.$e->getCode().') '.$e->getMessage();
            echo json_encode(array('cnt'=>$cnt,'add'=>$add,'upd'=>$upd,'last'=>$ID,'msg'=>$msg));
            return false;
        }
    } else {
        echo json_encode(array('cnt'=>$cnt,'add'=>$add,'upd'=>$upd,'last'=>$ID,'msg'=>'Keine Verbindung zum Server'));
        return false;
    };
}
function knownaddr($id,&$tab,&$sync) {
    $sql = sprintf('SELECT id AS C,sync as CS,0 AS V,\'\' as VS  FROM customer WHERE uid = \'%s\' UNION '.
                   'SELECT 0 AS C,\'\' as CS,id AS V,sync as VS FROM vendor WHERE uid = \'%s\' ORDER BY c,v desc',$id,$id);
    $rs = $GLOBALS['db']->getAll($sql);
         if ( count($rs) == 1 ) { $tab = ($rs[0]['c']>0)?'c':'v'; $sync = ($rs[0]['c']>0)?$rs[0]['CS']:$rs[0]['VS']; return true; } //Ein Treffer bei den Firmen
    else if ( count($rs)  > 1 ) { $tab = 'N'; $sync = false; return false; }                     //nicht eindeutige Treffer
    else {                                                                        //Keine Firma
        $sql = sprintf('SELECT cp_id,cp_sync FROM contacts WHERE uid = \'%s\'',$id);
        $rs = $GLOBALS['db']->getAll($sql);
             if ( count($rs) == 1 ) { $tab = 'p'; $sync = $rs[0]['cp_sync']; return true; }                  //Ein Ansprechpartner
        else if ( count($rs)  > 1 ) { $tab = 'N'; $sync = false;  return false; }                 //nicht eindeutige Treffer 
        else { $tab = ''; $sync = false; return false; }                                         //Na, dann keine Treffer  
    }
}
function wellknownaddr($id) {
    //sync == 2 nur diese dürfen upgedatet werden
    $tab = substr($id,0,1);
    $id  = substr($id,1);
    if ( $tab == 'P' ) {
        $sql = 'SELECT cp_sync as sync FROM contacts WHERE cp_id = '.$id;
    } else {
        $sql = 'SELECT sync FROM '.(($tab=='C')?'customer':'vendor').' WHERE id = '.$id;
    };
    $rs = $GLOBALS['db']->getOne($sql);
    return (isset($rs['sync']))?$rs['sync']:false; //'bekannt':'nicht gefunden';
}

function doVCget() {
    //Adressen vom SyncServer holen, zwischenspeichern, Prüfen ob bekannt, anzeigen
    $selbox  = '<select name="neu[]"><option value="-"></option><option value="C%d">Kunde</option><option value="V%d">Lieferant</option><option value="P%d">Person</option></select>';
    $cnt     = 0;
    $new     = 0;
    $ID      = 0;
    $msg     = '';
    $vcard   = array();
    $carddav = connSync();
    if ( !$carddav ) { echo 'Kein Server eingerichtet oder nicht erreichbar.'; return; };
    $tmpdata = getUserEmployee(array('getcarddate'));
    $lastdate = ( isset($tmpdata['getcarddate']) )?$tmpdata['getcarddate']:false;
    $carddav->enable_debug();
    $data = $carddav->get(true,false); //true,false);
    if ( $data ) {
        date_default_timezone_set('Europe/Berlin');
        echo '<table><tr><th>Name</th><th>Firma</th><th>Key/Uid</th><th>Update</th><th>Insert</th></tr><tr>';
        $rc = $GLOBALS['db']->query('DELETE FROM tempcsvdata WHERE uid = '.$_SESSION['loginCRM']);
        $parse = new Contact_Vcard_Parse();
        $xml = new SimpleXMLElement($data);
        if ( $xml ) foreach ( $xml as $card ){
           $cnt++;
           if ( $lastdate ) {   //Es wurden schon einmal Daten an diesem Zeitpunkt geholt
               $date = date('YmdHis', strtotime($card->last_modified));
               if ( $lastdate > $date) continue;  //Nur neue/geänderte beachten.
           }
           $new++;
           $rc = $GLOBALS['db']->query( 'INSERT INTO tempcsvdata (uid,csvdaten,id) VALUES ('.$_SESSION['loginCRM'].',\''.$card->vcard.'\','.$cnt.')' );
           echo '<tr><td>';
           if (preg_match('/FN:(.*)/',$card->vcard,$fn) ) echo $fn[1].'</td><td>';
           if (preg_match('/ORG:(.*)/',$card->vcard,$org) ) echo $org[1];
           echo '</td><td>';
           if (preg_match('/KEY:(.*)/',$card->vcard,$key) ) {
               $tab = substr($key[1],0,1); $id = substr($key[1],1);
               echo sprintf('<a href="firma1.php?Q=%s&id=%d" target="_blank">',$tab,$id).$key[1].'</a></td><td>';
               //echo sprintf('<a href="firma1.php?Q=%s&id=%d" target="_blank">',substr($key[1],0,1),substr($key[1],1)).$key[1].'</a></td><td>';
               $ok = wellknownaddr($key[1]); 
               if ( $ok == '2' ) {
                   echo '<input type="checkbox" name="update[]" value="'.substr($key[1],0,1).$cnt.'"></td><td>';
               } else if ( $ok == '1' ) {
                   echo 'no</td><td>';
               } else if ( $ok === '0' ) {
                   echo '?</td><td>';
               } else {
                   echo '</td><td>'.sprintf($selbox,$cnt,$cnt,$cnt);
               }
           } else if (preg_match('/UID:(.*)/',$card->vcard,$key) ) {
               preg_match('/[^@]*/',$key[1],$match); 
               echo $match[0].'</td><td>'; 
               $ok = knownaddr($match[0],$tab,$sync);
               if ( $ok ) {
                   if ( $sync == '2' ) {
                       echo '<input type="checkbox" name="update[]" value="'.$tab.$cnt.'"></td><td>';
                   } else if ( $sync == '1' ) {
                       echo 'no</td><td>';
                   } else {
                       echo '?</td><td>';
                   }
               } else {
                   echo '</td><td>'.sprintf($selbox,$cnt,$cnt,$cnt);
               }
           } else {
               echo '</td><td></td><td>'.sprintf($selbox,$cnt,$cnt,$cnt);
           }
           echo '</td></tr>';
        };
        echo '</table><br>';
        echo $cnt.' Adressen vom Server geholt. Davon '.(($new==1)?'ist ':'sind ').$new.' seit dem letzten mal geändert worden<br>';
        echo '<input type="button" name="aktion" onClick="importVC();" value="anwenden">';
    };
    //Letzten Zugriff auf SyncServer merken.
    //$rc = $GLOBALS['db']->query('DELETE FROM crmemployee WHERE key = \'getcarddate\' AND uid = '.$_SESSION['loginCRM'].' AND manid = '.$_SESSION['manid']);
    //$rc = $GLOBALS['db']->query('INSERT INTO crmemployee (uid,key,val,manid) VALUES ('.$_SESSION['loginCRM'].',\'getcarddate\',\''.date('YmdHis').'\','.$_SESSION['manid'].')');
}   

function mkAdress($card,$pre=false) {
           if ( $pre == 'P' || $pre == 'p') { 
               $pre = 'cp_';
               $adress = array('cp_name'=>'','cp_givenname'=>'','cp_gender'=>'','cp_street'=>'','cp_zipcode'=>'','cp_city'=>'','cp_country'=>'','cp_fax'=>'',
                               'cp_phone1'=>'','cp_phone2'=>'','cp_mobile1'=>'','cp_mobile2'=>'','cp_email'=>'','cp_privatemail'=>'','uid'=>''); //'cp_bland'=>'',
           } else {
               $pre = '';
               $adress = array('name'=>'','department_1'=>'','street'=>'','zipcode'=>'','city'=>'','country'=>'','phone'=>'','fax'=>'','email'=>'','bland'=>'','uid'=>'');
           }
           $parse    = new Contact_Vcard_Parse();
           $cardinfo = $parse->fromText($card);
           reset($cardinfo[0]);
           while (list($key,$line) = each($cardinfo[0]))  {
               switch ($key) {
               case "UID":      if ( strpos($line[0]["value"][0][0],'@') ) {
                                   $adress["uid"] = substr($line[0]["value"][0][0],0,strpos($line[0]["value"][0][0],'@')); 
                                } else {
                                   $adress["uid"] = $line[0]["value"][0][0]; 
                                }; break;
               case "REV":      $adress["REV"]=$line[0]["value"][0][0]; break;
               case "KEY":      $adress["KEY"]=$line[0]["value"][0][0]; break;
               case "ADR":      $adress[$pre.'street']       = $line[0]["value"][2][0];
                                $adress[$pre.'city']         = $line[0]["value"][3][0];
                                $adress[$pre.'bland']        = $line[0]["value"][4][0];
                                $adress[$pre.'zipcode']      = $line[0]["value"][5][0];
                                $adress[$pre.'country']      = $line[0]["value"][6][0];               
                                break;                       
               case "TITLE" :   $adress[$pre.'title']        = $line[0]["value"][0][0]; break;
               case "X-GENDER": $adress[$pre.'gender']       = $line[0]["value"][0][0]; break;
               case "N":        
                                $adress[$pre.'name']         = $line[0]["value"][0][0];
                                if ( $pre ) {
                                    $adress['cp_givenname']  = $line[0]["value"][1][0];
                                    $adress['cp_givenname'] .= ($line[0]["value"][2][0])?" ".($line[0]["value"][2][0]):"";
                                    if ( $line[0]["value"][3][0] != '' ) {
                                             if ( preg_match('/^[Ff]|[Mm]rs/',$line[0]["value"][3][0]) ) { $adress['cp_gender'] = 'f'; } 
                                        else if ( preg_match('/^[Hh]|[Mm]r/',$line[0]["value"][3][0]) )  { $adress['cp_gender'] = 'm'; }
                                    }
                                } else {
                                    $adress['department_1']  = $line[0]["value"][1][0];
                                    $adress['department_1'] .= ($line[0]["value"][2][0])?" ".($line[0]["value"][2][0]):"";
                                    $adress['greeting']      = $line[0]["value"][3][0];
                                }
                                $adress[$pre.'title']        = $line[0]["value"][4][0]; break;
               case "BDAY":     $adress['cp_birthday']       = $line[0]["value"][0][0]; break;
               case "URL":      $adress[$pre.'cp_homepage']           = $line[0]["value"][0][0]; break;
               case "NOTE":     $adress[$pre.'notes']        = $line[0]["value"][0][0]; break;
               case "ROLE":     $adress["ROLE"]              = $line[0]["value"][0][0]; break;
               case "TEL":      if ( !$pre ) {
                                   $adress['phone']          = $line[0]["value"][0][0];
                                } else {
                                   $p = 1; $m = 1;
                                   foreach ($line as $row) {
                                       if ( in_array('CELL',$row['param']['TYPE']) ) {
                                           $adress['cp_mobile'.$p] = $row["value"][0][0];
                                           $p++;
                                       } else {
                                           $adress['cp_phone'.$p] = $row["value"][0][0];
                                           $m++;
                                      }
                                   }
                                }; break;
               case "EMAIL":    if ( !$pre ) { 
                                   $adress['email'] = $line[0]["value"][0][0];
                                } else {
                                   foreach ( $line as $row ) {
                                       if ( in_array('HOME',$row['param']['TYPE'] ) ) {
                                          $adress['cp_privatemail'] = $row["value"][0][0];
                                       } else {
                                          $adress['cp_email'] = $row["value"][0][0];
                                       }
                                   }
                                }; break;
               case "ORG":      if (count($line[0]["value"])>1) {
                                    if ( $pre ) {
                                        $adress['cp_firma'] = $line[0]["value"][0][0];
                                    } else {
                                        $adress['name']         = $line[0]["value"][0][0];
                                        $adress['department_1'] = $line[0]["value"][1][0];
                                    }
                                } else {
                                    if ( $pre ) {
                                        $adress['cp_firma'] = $line[0]["value"][0][0];
                                    } else {
                                        if ( !empty($adress['name']) ) $adress['department_1'] .= $adress['name'];
                                        $adress['name']       = $line[0]["value"][0][0];
                                    }
                                }; break;
               default:         //
               }
           }
           if ( !$pre && isset($adress['title']) ) unset($adress['title']);
           if ( isset($adress['REV']) ) unset($adress['REV']);
           return $adress;
}
function doVCput() {
    $cnt  = 0;
    $add  = 0;
    $upd  = 0;
    $ID   = 0;
    $carddav = connSync();
    if ( !$carddav ) return;
    $msg  = 'ok';
    $hr   = 'Hr. ';  //Noch aus der DB lesen
    $fr   = 'Fr. ';
    $sql  = 'SELECT id,greeting,name,department_1,country,street,zipcode,city,phone,fax,homepage,email,uid,\'C\' as tab,\'\' as title,\'\' as firma FROM customer WHERE sync > 0 UNION ';
    $sql .= 'SELECT id,greeting,name,department_1,country,street,zipcode,city,phone,fax,homepage,email,uid,\'V\' as tab,\'\' as title,\'\' as firma FROM vendor WHERE sync > 0 UNION ';
    $sql .= 'SELECT cp_id as id,(CASE WHEN cp_gender = \'m\' THEN \''.$hr.'\' ELSE \''.$fr.'\' END) as greeting, cp_name as name,cp_givenname as department_1,cp_country as country,';
    $sql .= 'cp_street as street,cp_zipcode as zipcode,cp_city as city,cp_phone1 as phone,cp_mobile1 as fax,cp_homepage as homepage,cp_email as email,contacts.uid,\'P\' as tab, ';
    $sql .= 'cp_title as title,COALESCE(C.name,V.name) as firma ';
    $sql .= 'FROM contacts LEFT JOIN customer C on C.id=cp_cv_id LEFT JOIN vendor V on V.id=cp_cv_id WHERE cp_sync > 0';
    $data  = $GLOBALS['db']->getAll($sql);
    if ( $data ) foreach ($data as $row) {
        $vcard = new Contact_Vcard_Build();
        $ID    = $row['id'];
        $vcard->setRevision(date('c'));
        $vcard->setKey($row['tab'].$ID);
        if ( $row['tab'] == 'P' ) {
		    $vcard->setFormattedName($row["name"].', '.$row['department_1']);
            $vcard->setName($row['name'],$row['department_1'],'',$row['greeting'],$row['title']);
            $vcard->addOrganization($row['firma']);
        } else {
		    $vcard->setFormattedName($row["name"]);
            $vcard->setName($row['name'],$row['department_1'],'',$row['greeting'],'');
            $vcard->addOrganization($row['name']);
        }
        $vcard->addAddress('', '', $row['street'], $row['city'], 
                                '', $row['zipcode'], $row['country']);
        $vcard->addParam('TYPE', 'WORK');
        if ( $row['email'] ) {
            $vcard->addEmail($row['email']);
            $vcard->addParam('TYPE', 'WORK');
        }
        if ( $row['phone'] ) {
            $vcard->addTelephone($row['phone']);
            $vcard->addParam('TYPE', 'WORK');
        }
        if ( $row['fax'] ) {
            $vcard->addTelephone($row['fax']);
            if ( $row['tab'] == 'P' ) {
            $vcard->addParam('TYPE', 'CELL');
            } else {
                $vcard->addParam('TYPE', 'FAX');
            }
        }
        if ( $row['uid'] ) {
            $UID = $row['uid'];
        } else {
            $UID = false;
        };
        $text = $vcard->fetch();
        $cnt++;
        try {
            if ( $UID ) {
                $vcard_id = $carddav->update($text,$UID);
                $upd++;
            } else {
                $vcard_id = $carddav->add($text);
                $add++;
            }
        } catch (Exception $e ) {
            $msg = 'Fehler ('.$e->getCode().') '.$e->getMessage();
            fputs($GLOBALS['s'],$msg.lf);
            break;
        }
        if ( $row['tab'] == 'P' ) { 
                $sql = 'UPDATE contacts SET uid = \''.$vcard_id.'\' WHERE cp_id = '.$ID;
                $rc  = $GLOBALS['db']->query($sql);
            } else {
                $sql = 'UPDATE '.(($row['tab']=='C')?'customer':'vendor').' SET uid = \''.$vcard_id.'\' WHERE id = '.$ID;
                $rc  = $GLOBALS['db']->query($sql);
        }        
    }
    echo json_encode(array('cnt'=>$cnt,'add'=>$add,'upd'=>$upd,'last'=>$ID,'msg'=>$msg));
}

function doVCsync() {
    $sql      = "select * from tempcsvdata where uid = '".$_SESSION["loginCRM"]."' order by id";
    $csvdata  = $GLOBALS['db']->getAll($sql);
    $cnt      = 0;
    $add      = 0;
    $upd      = 0;
    $ID       = 0;
    $msg      = 'ok';
    $carddav = connSync();
    if ( !$carddav ) return;
    if ( count($csvdata) > 1 ) {
        $felder   = explode(":",$csvdata[0]["csvdaten"]);
        $personen = False;
        if ( in_array("TITEL",$felder) ) $personen = True;
        $i        = 0;
        foreach ( $felder as $feld ) $felder[$feld] = $i++;
        array_shift($csvdata);
        foreach ( $csvdata as $row ) {
            $vcard = new Contact_Vcard_Build();
            $data = explode(":",$row["csvdaten"]);
            $vcard->setFormattedName($data[$felder["NAME1"]]);
            $ID = $data[$felder['ID']];
            $vcard->setKey($row['tab'].$ID);
            if ( $data[$felder["NAME2"]] ) {
                if ( $personen ) {
                    $vcard->setName($data[$felder["NAME2"]],$data[$felder["NAME1"]],"",$data[$felder["ANREDE"]],$data[$felder["TITEL"]]);
                    $vcard->addOrganization($data[$felder["FIRMA"]]);
                } else {
                    $vcard->setName($data[$felder["NAME1"]],$data[$felder["NAME2"]],"","","");
                    $vcard->addOrganization($data[$felder["NAME1"]]);
                    $vcard->addOrganization($data[$felder["NAME2"]]);
                }
            } else {
                if ( $personen ) {
                    $vcard->setName($data[$felder["NAME1"]],"","",$data[$felder["ANREDE"]],$data[$felder["TITEL"]]);
                    $vcard->addOrganization($data[$felder["FIRMA"]]);
                } else {
                    $vcard->setName($data[$felder["NAME1"]],"","","","");
                    $vcard->addOrganization($data[$felder["NAME1"]]);
                    $vcard->addOrganization($data[$felder["NAME2"]]);
                }
            }
            $vcard->addAddress('', '', $data[$felder["STRASSE"]], $data[$felder["ORT"]], 
                                    '', $data[$felder["PLZ"]], $data[$felder["LAND"]]);
            $vcard->addParam('TYPE', 'WORK');
            if ( $data[$felder["EMAIL"]] ) {
                $vcard->addEmail($data[$felder["EMAIL"]]);
                $vcard->addParam('TYPE', 'WORK');
            }
            if ( $data[$felder["TEL"]] ) {
                $vcard->addTelephone($data[$felder["TEL"]]);
                $vcard->addParam('TYPE', 'WORK');
            }
            if ( $data[$felder["FAX"]] ) {
                $vcard->addTelephone($data[$felder["FAX"]]);
                $vcard->addParam('TYPE', 'FAX');
            }
            if ( $data[$felder["UID"]] ) {
                $UID = $data[$felder["UID"]];
            }
            $text = $vcard->fetch();
            try {
                if ( $UID ) {
                    $vcard_id = $carddav->update($text,$UID);
                    $upd++;
                } else {
                    $vcard_id = $carddav->add($text);
                    $add++;
                }
            } catch (Exception $e ) {
                $msg = 'Fehler ('.$e->getCode().') '.$e->getMessage();
                break;
            }
            if ( $personen ) {
                    $sql = 'UPDATE contacts SET uid = \''.$vcard_id.'\' WHERE cp_id = '.$ID;
                    $rc  = $GLOBALS['db']->query($sql);
                } else {
                    $sql = 'UPDATE '.(($data[$felder['TABLE']]=='C')?'customer':'vendor').' SET uid = \''.$vcard_id.'\' WHERE id = '.$ID;
                    $rc  = $GLOBALS['db']->query($sql);
            }
            unset($vcard);
            unset($text);
            $cnt++;
        };        
    }
    echo json_encode(array('cnt'=>$cnt,'add'=>$add,'upd'=>$upd,'last'=>$ID,'msg'=>$msg));
}
function doVcards($single,$extension,$targetcode,$zip,$sync=false) {
    $sql     = "select * from tempcsvdata where uid = '".$_SESSION["loginCRM"]."' order by id";
    $csvdata = $GLOBALS['db']->getAll($sql);
    $cnt     = 0;
    if ( $csvdata ) {
        if ( $sync ) {
            $rc   = chkdir ("crm/tmp/".$sync,true);
            $pfad = $_SESSION['erppath']."crm/tmp/".$sync.'/';
        } else {
            chkdir($_SESSION["login"]."/vcard",'.');
            $pfad = $_SESSION['erppath']."crm/dokumente/".$_SESSION["dbname"]."/".$_SESSION["login"]."/vcard/";
        };
        $url      = $_SESSION['baseurl']."crm/dokumente/".$_SESSION["dbname"]."/".$_SESSION["login"]."/vcard/";
        $felder   = explode(":",$csvdata[0]["csvdaten"]);
        $personen = False;
        if ( in_array("TITEL",$felder) ) $personen = True;
        $i        = 0;
        foreach ( $felder as $feld ) $felder[$feld] = $i++;
        array_shift($csvdata);
        if ( $single ) {
            if ( $personen ) {
                $filename = "Pvcard.".$extension;
            } else {
                $filename = "Fvcard.".$extension;
            }
            $f = fopen($pfad.$filename,"w");
        }
        $srvcode = strtoupper($_SESSION["charset"]);
        foreach ( $csvdata as $row ) {
            $vcard = new Contact_Vcard_Build();
            if ( $targetcode !=  $srvcode )  $row["csvdaten"] = iconv($srvcode,$targetcode,$row["csvdaten"]);
            $data = explode(":",$row["csvdaten"]);
            $vcard->setFormattedName($data[$felder["NAME1"]]);
            if ( $data[$felder["NAME2"]] ) {
                if ( $personen ) {
                    $vcard->setName($data[$felder["NAME2"]],$data[$felder["NAME1"]],"",$data[$felder["ANREDE"]],$data[$felder["TITEL"]]);
                } else {
                    $vcard->setName($data[$felder["NAME1"]],$data[$felder["NAME2"]],"","","");
                }
            } else {
                if ( $personen ) {
                    $vcard->setName($data[$felder["NAME1"]],"","",$data[$felder["ANREDE"]],$data[$felder["TITEL"]]);
                } else {
                    $vcard->setName($data[$felder["NAME1"]],"","","","");
                }
            }
            $vcard->addAddress('', '', $data[$felder["STRASSE"]], $data[$felder["ORT"]], 
                                    '', $data[$felder["PLZ"]], $data[$felder["LAND"]]);
            $vcard->addParam('TYPE', 'WORK');
            if ( $personen ) {
                $vcard->addOrganization($data[$felder["FIRMA"]]);
            } else {
                $vcard->addOrganization($data[$felder["NAME1"]]);
                $vcard->addOrganization($data[$felder["NAME2"]]);
            }
            if ( $data[$felder["EMAIL"]] ) {
                $vcard->addEmail($data[$felder["EMAIL"]]);
                $vcard->addParam('TYPE', 'WORK');
            }
            if ( $data[$felder["TEL"]] ) {
                $vcard->addTelephone($data[$felder["TEL"]]);
                $vcard->addParam('TYPE', 'WORK');
            }
            if ( $data[$felder["FAX"]] ) {
                $vcard->addTelephone($data[$felder["FAX"]]);
                $vcard->addParam('TYPE', 'FAX');
            }
            if ( $data[$felder["UID"]] ) {
                $vcard->setUniqueID($data[$felder["UID"]]);
            }
            // get back the vCard and print it
            $text = $vcard->fetch();
            if ( !$single ) {
                if ( $personen ) {
                    $f = fopen($pfad."/".$data[$felder["ID"]].$data[$felder["NAME1"]]."_vcard.".$extension,"w");
                } else {
                    $f = fopen($pfad."/".$data[$felder["KDNR"]]."_vcard.".$extension,"w");
                }
                fputs($f,$text);
                fclose($f);
            } else {
                fputs($f,$text);
            }
            unset($vcard);
            unset($text);
            $cnt++;
        };
        if ( $single ) fclose($f);
        if ( $zip ) {
            require 'pclzip.lib.php';
            require 'zip.lib.php';
            if ( !$single ) {
                $oldpath = getCWD();
                chdir($pfad);
                $archiveFiles = glob("*_vcard.".$extension);
                chdir($oldpath);
            } else {
                //$archiveFiles[] = "vcard.".$_POST["extension"];
                $archiveFiles[] = $filename; 
            }
            $filename = "vcard.".$extension.".zip";
            $archive  = new PclZip($pfad.$filename);
            $v_list   = $archive->create($pfad.$_SESSION["login"], '', $pfad.$_SESSION["login"], '', "vcardPreAdd");
            $zip      = new zipfile();
            for( $i = 0; $i < count($archiveFiles); $i++ ) {
                $file = $archiveFiles[$i];
                // zip.lib dirty hack
                $fp   = fopen($pfad.$file, "r");
                $content = @fread($fp, filesize($pfad.$file));
                fclose($fp);
                $zip->addFile($content, $file);
                unlink($pfad.$file);
            }
            $fp = fopen($pfad.$filename, "w+");
            fputs($fp, $zip->file());
            fclose($fp);
        }
        if ( $single || $zip ) {
            if ( !$sync) echo "[<a href='".$url.$filename."'>download</a>]<br />";
        } else {
        }
    };
    if ( !$sync ) { echo "$cnt Adressen bearbeitet."; }
    else          { return $cnt; };

}

function serbrief($data) {
        $rc = file_exists("../dokumente/".$_SESSION["dbname"].'/'.$_SESSION['login']."/tmp/".$data['filename']);
        if ( $rc ) {
            require_once("documents.php");
            $dest = "./dokumente/".$_SESSION["dbname"]."/serbrief/";
            $ok = chkdir("serbrief");
            copy("../dokumente/".$_SESSION["dbname"].'/'.$_SESSION['login']."/tmp/".$data['filename'],'.'.$dest.$data['filename']);
            unlink ("../dokumente/".$_SESSION["dbname"].'/'.$_SESSION['login']."/tmp/".$data['filename']);

            //Verzeichnis anlegen für die Serienbriefe
            @mkdir(".".$dest.substr($_POST['filename'],0,-4));
            
            //Dokument in db speichern
            $dbfile=new document();
            $dbfile->setDocData("descript",$data["subject"]);
            $dbfile->setDocData("pfad","serbrief");
            $dbfile->setDocData("name",$data['filename']);
            $dbfile->setDocData("descript",$data["body"]);
            $rc=$dbfile->newDocument();
            $dbfile->saveDocument();
            
            //benötigte Daten in Session speichern
            $_SESSION["dateiId"] = $dbfile->id;
            $_SESSION["SUBJECT"]=$_POST["subject"];
            $_SESSION["BODY"]=$_POST["body"];
            $_SESSION["DATE"]=$_POST["datum"];
            $_SESSION["src"]=$_POST["src"];
            $_SESSION["savefiledir"]=$dest.substr($_POST['filename'],0,-4);
            $_SESSION["datei"]=$_POST['filename'];

            echo json_encode( array( 'rc'=>true, "msg"=>"Datei gesichert") );
        } else {
            echo json_encode( array( 'rc'=>false, "msg"=>"Fehler beim Upload ".$_POST['filename']));
        }

}
function updAdr($card,$tab) {
    if ( !$card ) return;
    if ( (!isset($card['KEY']) && empty($card['KEY'])) && (!isset($card['uid']) && empty($card['uid']) ) ) return;
    if ( !empty($card['KEY']) ) {
        $tab = substr($card['KEY'],0,1);
        $id  = substr($card['KEY'],1);
    } else {
        $id = $card['uid'];
    }
    $save = array();
    if ( $tab == 'P' || $tab == 'p' ) {
        $felderP = array('cp_name','cp_givenname','cp_gender','cp_street','cp_city','cp_zipcode','cp_country','cp_phone1','cp_phone2',    //'cp_bland',  gibt es nicht?????
                         'cp_mobile1','cp_mobile2','cp_fax','cp_email','cp_privatemail','uid');
        foreach ( $felderP as $feld ) if ( !empty($card[$feld]) ) $save[$feld] = $card[$feld];
        $felder = array_keys($save);
        $rc = $GLOBALS['db']->update( 'contacts', $felder, $save, ($tab=='P')?'cp_id = '.$id:'uid = \''.$id.'\'' );
    } else {
        $felderF = array('name','department_1','street','city','zipcode','country','bland','phone','fax','email','uid');
        foreach ( $felderF as $feld ) if ( !empty($card[$feld]) ) $save[$feld] = $card[$feld];
        $felder = array_keys($save);
        $tabelle = ( $tab == 'C' || $tab == 'c' )?'customer':'vendor';
        $where   = ( $tab == 'C' || $tab == 'V' )?'id = '.$id:'uid = \''.$id.'\'';
        $rc = $GLOBALS['db']->update( $tabelle, $felder, $save, $where );
    }
    return $rc;
}
function insAdr($card,$tab,$stat) {
    $save = array();
    if ( isset($card['uid']) ) {
        if ( knownaddr($card['uid'],$tmp) )  unset($card['uid']); // Hier am Besten im Anschluß noch ein Put-Card durchführen um KEY und uid zu erzeugen.
    }
    if ( $tab == 'P' ) {
        $felderP = array('cp_name','cp_givenname','cp_gender','cp_street','cp_city','cp_zipcode','cp_country','cp_phone1','cp_phone2',
                         'cp_mobile1','cp_mobile2','cp_fax','cp_email','cp_privatemail','uid','cp_sync'); //'cp_bland',
        foreach ( $felderP as $feld ) if ( !empty($card[$feld]) ) $save[$feld] = $card[$feld];
        $save['cp_sync'] = $stat;
        $felder = array_keys($save);
        $rc = $GLOBALS['db']->insert('contacts',$felder,$save);
    } else {
        $felderF = array('greeting','name','department_1','street','city','zipcode','country','bland','phone','fax','email','taxzone_id','uid','currency_id','sync');
        $card['taxzone_id'] = 4;
        $card['currency_id'] = 1;
        foreach ( $felderF as $feld ) if ( !empty($card[$feld]) ) $save[$feld] = $card[$feld];
        foreach ( $felderF as $feld ) if ( empty($card[$feld]) ) unset ($card[$feld]);
        $save['sync'] = $stat;
        $felder = array_keys($save);
        $rc = $GLOBALS['db']->insert(($tab=='C')?'customer':'vendor',$felder,$save);
    }
    return $rc;
}
function doImportVC($upd,$ins) {
   $tmpdata = getUserEmployee(array('syncstat');
   $stat    = $tmpdata['syncstat'];
   $no      = 0;
   $rc      = false;
   $alt     = array();
   $neu     = array();
   if ( !empty($ins) ) foreach ( $ins as $key ) $neu[substr($key,1)] = substr($key,0,1); 
   if ( !empty($upd) ) foreach ( $upd as $key ) $alt[substr($key,1)] = substr($key,0,1);
   $stored = $GLOBALS['db']->getAll('SELECT * FROM tempcsvdata WHERE uid = '.$_SESSION['loginCRM']);
   if ( $stored && $stored[0]['id'] == -255 ) { echo 'Keine Importdaten'; return; }
   $ins = 0;
   $upd = 0;
   if ( $stored ) foreach ( $stored as $row ) {
            if ( array_key_exists($row['id'],$alt) ) { $rc  = updAdr( mkAdress($row['csvdaten'],$alt[$row['id']]),$alt[$row['id']] ); if ( $rc ) $upd++; }
       else if ( array_key_exists($row['id'],$neu) ) { $adr = mkAdress($row['csvdaten'],$neu[$row['id']]); 
                                                       $rc  = insAdr( $adr , $neu[$row['id']],$stat ); if ( $rc ) $ins++; }
       else                                          { $no++; };
   }
   echo "($ins) Importiert, ($upd) Aktualisiert, ($no) nicht bearbeitet";
}

function setSyncStat($tab,$status) {
    $sql = 'SELECT id FROM tempcsvdata WHERE ID > 0';
    $rs  = $GLOBALS['db']->getAll($sql);
    if ( $rs ) {
        $ids = [];
        foreach ( $rs as $row ) $ids[] = $row['id'];
        if ( $tab == 'P' ) {
            echo count($rs).' Ansprechpartner';
            $sql = sprintf('UPDATE contacts SET cp_sync = %d WHERE cp_id in ('.join(',',$ids).')',$status);
            $rc  = $GLOBALS['db']->query($sql);
        } else {
            echo count($rs).' Firmen';
            $sql = 'UPDATE %s SET sync = %d WHERE id in ('.join(',',$ids).')';
            $rc  = $GLOBALS['db']->query(sprintf($sql,'customer',$status));
            $rc  = $GLOBALS['db']->query(sprintf($sql,'vendor',$status));
        }
    }
    echo ($rc)?' ok':' Fehler' ;
}

$s = fopen('/tmp/ser.log','a');
if ( isset($_GET['task']) ) fputs($s,"isset\n");
     if ( isset($_GET['task'])  && $_GET['task']  != '' ) { $task = $_GET['task']; }
else if ( isset($_POST['task']) && $_POST['task'] != '' ) { $task = $_POST['task']; }
else                                                      { $task = 'Nix'; };
fputs($s,"$task\n");
if ( isset($_GET) ) fputs($s,print_r($_GET,true));
if ( $task == 'vcard') {
     require_once 'Contact_Vcard_Build.php';
     doVcards($_GET['single'],$_GET['extension'],$_GET['targetcode'],$_GET['zip']) ;
} else if ( $task == 'brief') {
     serbrief($_POST);
} else if ( $task == 'sync') {
     require_once 'Contact_Vcard_Build.php';
     include_once('carddav.php');
     doVCsync();
} else if ( $task == 'putcard') {
     require_once 'Contact_Vcard_Build.php';
     include_once('carddav.php');
     doVCput();
} else if ( $task == 'getcard') {
     require_once 'Contact_Vcard_Parse.php';
     require_once 'vCard.php';
     include_once('carddav.php');
     doVCget();
} else if ( $task == 'importvc') {
     require_once 'Contact_Vcard_Parse.php';
     require_once 'vCard.php';
     fputs($s,print_r($_POST,true));
     doImportVC( (isset($_POST['update']))?$_POST['update']:false, (isset($_POST['insert']))?$_POST['insert']:false );
} else if ( $task == 'cleansync') {
     if ( isset($_GET['pfad']) && is_dir($_SESSION['erppath']."crm/tmp/".$_GET['pfad']) ) {
         array_map('unlink', glob($_SESSION['erppath']."crm/tmp/".$_GET['pfad'].'/*'));
         if  ( rmdir($_SESSION['erppath']."crm/tmp/".$_GET['pfad']) ) { echo 'ok'; }
         else                                                         { echo 'Verzeichnis nicht gelöscht'; };
     } else {
         echo 'Falscher Pfad oder nicht angegeben';
     }
} else if ( $task == 'sersynstat') {
     setSyncStat($_GET['tab'],$_GET['status']);
} else {
   fputs($s,"Nix\n");
};
?>
