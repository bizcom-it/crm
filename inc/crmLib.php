<?php

require_once('documents.php');
include ('mailLib.php');

/****************************************************
* mkSuchwort
* in: suchwort = String
* out: sw = array(Art,suchwort)
* Joker umwandeln, Anfrage ist Telefon oder Name
*****************************************************/
function mkSuchwort($suchwort) {
    $suchwort=str_replace('*','%',$suchwort);
    $suchwort=str_replace('?','_',$suchwort);
    if ( preg_match('!^[0-9]+$!',$suchwort) ) {  //PLZ?
        $sw[0]=2;
    } else if ( $suchwort != '%' and preg_match('!^[0-9+%_]+[0-9 -/%]*$!',$suchwort) ) {   // Telefonnummer?
        $sw[0]=0;
    } else {                                 // nein Name
        if (empty($suchwort)) $suchwort=' ';
        $sw[0]=1;
        //setlocale(LC_ALL,'C');  // keine Großbuchastaben für Umlaute
        //$suchwort=strtoupper($suchwort);
    };
    $sw[1]=$suchwort;
    return $sw;
}


/****************************************************
* getAllTelCall
* in: id = int, firma = boolean
* out: rs = array(Felder der db)
* hole alle Anrufe einer Person oder einer Firma
*****************************************************/
function getAllTelCall($id,$firma) {

    if ( $firma ) {    // dann hole alle Kontakte der Firma
        $sql  = 'SELECT id,caller_id,kontakt,cause,calldate,cp_name,inout FROM ';
        $sql .= 'telcall LEFT JOIN contacts on caller_id=cp_id WHERE bezug=0 ';
        $sql .= 'and (caller_id in (SELECT cp_id FROM contacts WHERE cp_cv_id='.$id.') or caller_id='.$id;
        $sql .= ') UNION ';
        $sql .= "SELECT n.id,n.trans_id as caller_id,'N' as kontakt,n.subject as cause,u.follow_up_date as calldate,'','' ";
        $sql .= 'FROM notes n LEFT JOIN follow_ups u on note_id = n.id ';
        $sql .= 'LEFT JOIN follow_up_links l on follow_up_id = u.id ';
        $sql .= 'WHERE (l.trans_type = \'customer\' or l.trans_type = \'vendor\') and n.trans_id = '.$id;
     } else {  // hole nur die einer Person
        $sql  = 'SELECT id,caller_id,kontakt,cause,calldate,cp_name,inout FROM ';
        $sql .= 'telcall LEFT JOIN contacts on caller_id=cp_id WHERE bezug=0 and caller_id='.$id;
        $where= 'and caller_id=cp_id and caller_id='.$id;
    }
    $rs = $GLOBALS['db']->getAll($sql.' ORDER BY calldate desc ');
    if( !$rs ) {
        $rs = false;
    } else {
        //Neuesten Eintrag ermitteln
        $sql  = 'SELECT telcall.*,cp_name FROM telcall LEFT JOIN contacts on caller_id=cp_id WHERE ';
        $sql .= '(caller_id in (SELECT cp_id FROM contacts WHERE cp_cv_id='.$id.') or caller_id='.$id;
        $sql .= ') ORDER BY calldate desc limit 1';
        $rs2  = $GLOBALS['db']->getAll($sql);
        if ( $rs2[0]['bezug']==0 ) { $new = $rs2[0]['id']; }
        else                       { $new = $rs2[0]['bezug']; };
        $i = 0;
        foreach ( $rs as $row ) {
            if ( $row['id']==$new ) $rs[$i]['new'] = 1;
            $rs[$i]['datum'] = db2date(substr($row['calldate'],0,10));
            $rs[$i]['zeit']  = substr($row['calldate'],11,5);
            $i++;
        }
    }
    return $rs;
}

/****************************************************
* getAllTelCallMax
* in: id = int, firma = int
* out: count(rs) = int
* Anzahl aller Einträge einer Fa
*****************************************************/
function getAllTelCallMax($id,$firma) {
    if ($firma) {    // dann hole alle Kontakte der Firma
        $sql  = 'SELECT id,caller_id,kontakt,cause,calldate,contacts.cp_name FROM ';
        $sql .= 'telcall LEFT JOIN contacts on caller_id=cp_id WHERE bezug=0 ';
        $sql .= 'and (caller_id in (SELECT cp_id FROM contacts WHERE cp_cv_id='.$id.') or caller_id='.$id.')';
     } else {  // hole nur die einer Person
        $where = 'and caller_id='.$id.' and caller_id=cp_id';
        $sql   = 'SELECT id,caller_id,kontakt,cause,calldate,cp_name FROM ';
        $sql  .= 'telcall LEFT JOIN contacts on caller_id=cp_id WHERE bezug=0 and caller_id='.$id;
    }
    $rs = $GLOBALS['db']->getAll($sql);
    return count($rs);
}
/****************************************************
* getAllTelCallUser
* in: id = int, firma = boolean
* out: rs = array(Felder der db)
* hole alle Anrufe einer Person oder einer Firma
*****************************************************/
function getAllTelCallUser($id,$start=0,$art) {
    if (!$start) $start=0;
    $sql  = 'SELECT telcall.id,caller_id,kontakt,cause,calldate,cp_email,C.email as cemail,';
    $sql .= 'V.email as vemail,V.id as vid, C.id as cid,cp_id as pid FROM telcall ';
    $sql .= 'LEFT JOIN contacts on cp_id=caller_id ';
    $sql .= 'LEFT JOIN customer C on C.id=caller_id ';
    $sql .= 'LEFT JOIN vendor V on V.id=caller_id ';    
    $sql .= "WHERE telcall.employee=$id and kontakt = '$art'";
    $rs   = $GLOBALS['db']->getAll($sql.' ORDER BY calldate desc offset '.$start.' limit 19');
    if( !$rs ) {
        $rs = false;
    } else {
        $sql  = 'SELECT telcall.* FROM telcall LEFT JOIN contacts on caller_id=cp_id WHERE  ';
        $sql .= '(caller_id in (SELECT cp_id FROM contacts WHERE cp_cv_id='.$id.') or caller_id='.$id;
        $sql .= ') ORDER BY calldate desc limit 1';
        $rs2  = $GLOBALS['db']->getAll($sql);
        if ( $rs2[0]['bezug']==0 ) { $new = $rs2[0]['id']; }
        else                       { $new = $rs2[0]['bezug']; };
        $i = 0;
        foreach ( $rs as $row ) {
            if ( $row['id']==$new ) {
                $rs[$i]['new'] = 1;
            };
            $rs[$i]['datum'] = db2date(substr($rs[$i]['calldate'],0,10));
            $rs[$i]['zeit']  = substr($rs[$i]['calldate'],11,5);
            $i++;
        }
    }
    return $rs;
}

/****************************************************
* delTelCall
* in: id = int
* out: 
* einen TelCall Eintrag löschen
*****************************************************/
function delTelCall($id) {
    //Wenn eine Datei angebunden ist, noch löschen.
    $rs = $GLOBALS['db']->getAll('SELECT * FROM telcall WHERE id='.$id);
    if ( $rs[0]['bezug']==0 ) {
        $sql = 'delete FROM telcall WHERE bezug='.$id;
        $rs  = $GLOBALS['db']->query($sql);
    }
    $sql = 'delete FROM telcall WHERE id='.$id;
    $rc  = $GLOBALS['db']->query($sql);
}

/****************************************************
* saveAllTelCall
* in: id = int
* out: rs = array(Felder der db)
* sichert einen geänderten TelCall-Eintrag
*****************************************************/
function saveTelCall($id,$empl,$grund) {
    $sql  = 'SELECT id,cause,caller_id,calldate,c_long,employee,kontakt,bezug,dokument FROM telcall WHERE id = %d';
    $rs   = $GLOBALS['db']->getAll(sprintf($sql,$id));
    $tmp  = $rs[0];
    $sql  = 'insert into telcallhistory (orgid,cause,caller_id,calldate,c_long,employee,kontakt,bezug,dokument,chgid,grund,datum)';
    $sql .= " values (%d,'%s',%d,'%s','%s',%d,'%s',%d,%d,%d,'%s','%s')";
    $rs   = $GLOBALS['db']->query(sprintf($sql,$tmp['id'],$tmp['cause'],$tmp['caller_id'],$tmp['calldate'],$tmp['c_long'],
                                  $tmp['employee'],$tmp['kontakt'],$tmp['bezug'],$tmp['dokument'],$empl,$grund,date('Y-m-d H:i:s')));
    return $rs;
}

/****************************************************
* mkPager
* in: items = array, 
* in: pager = int, start = int, next = int, prev = int
* out: 
* TelCall-Einträge Seitenweise bereitstellen
*****************************************************/
function mkPager(&$items,&$pager,&$start,&$next,&$prev) {
    if ( $items ) {
        $pager = $start;
        if ( count($items)==19 ) {
            $next = $start+19;
            $prev = ($start>19)?($start-19):0;
        } else {
            $next = $start;
            $prev = ($start>19)?($start-19):0;
        }
    } else if ( $start>0 ) {
        $pager  = ($start>19)?($start-19):0;
        $item[] = array(id => '',calldate => '', caller_id => $employee, cause => 'Keine weiteren Eintr&auml;ge' );
        $next   = $start;
        $prev   = ($pager>19)?($pager-19):0;
    } else {
        $pager = 0;
        $next  = 0;
        $prev  = 0;
    }
}

/****************************************************
* mvTelCall
* in: TID = int, Anzeige = int, CID = int
* out: rs = boolean
* einen TelCall-Eintrag verschieben
*****************************************************/
function mvTelcall($TID,$Anzeige,$CID) {
    $call   = getCall($Anzeige,$_SESSION['loginCRM'],'U');
    $caller = '';
    if ( $call['CID']!=$CID ) {
        //saveTextCall($Anzeige);
        if ( $call['bezug']==0 ) {
            $sql = "update telcall set caller_id=$CID WHERE id=$Anzeige";
        } else {
            $sql = "update telcall set bezug=0, caller_id=$CID WHERE id=$Anzeige";
        }
        $rc = $GLOBALS['db']->query($sql);
    } 
    if ( $TID<>$Anzeige ) {
        if ( $call['bezug']==0 ) {
            $sql  = "update telcall set bezug=$TID WHERE id=$Anzeige or bezug=$Anzeige";
            $sqlH = "update telcallhistory set orgid=$TID WHERE orgid=$Anzeige or bezug=$Anzeige";
        } else {
            $sql  = "update telcall set bezug=$TID WHERE id=$Anzeige";
            $sqlH = "update telcallhistory set orgid=$TID WHERE orgid=$Anzeige";
        }
        $rc = $GLOBALS['db']->query($sqlH);
    } else {
        return false;
    }
    $rs = $GLOBALS['db']->query($sql);
    return $rs;
}

/****************************************************
* getAllUsrCall
* in: id = int
* out: rs = array(Felder der db)
* hole alle Anrufe einer Person
* wo erfolgt er aufruf? kann ersetzt werden, s.o.
*****************************************************/
function getAllUsrCall($id) {
    $sql = "SELECT * FROM telcall WHERE caller_id=$id ORDER BY calldate desc";
    $rs  = $GLOBALS['db']->getAll($sql);
    if( !$rs ) { $rs = false; }
    else       { return $rs;  };
}

/****************************************************
* getAllCauseCall
* in: id = int
* out: rs = array(Felder der db)
* hole alle Anrufe einer Person zu einem Betreff
*****************************************************/
function getAllCauseCall($id) {
    $sql = "SELECT * FROM telcall WHERE id=$id";
    $rs  = $GLOBALS['db']->getAll($sql);
    if ( !$rs ) {
        $sql  = "SELECT n.id,n.trans_id as caller_id,n.subject as cause,n.body as c_long,u.follow_up_date as calldate,";
        $sql .= "'N' as kontakt,0 as bezug,' ' as inout,created_for_user as employee ";
        $sql .= "FROM notes n LEFT JOIN follow_ups u on note_id = n.id ";
        $sql .= "LEFT JOIN follow_up_links l on follow_up_id = u.id ";
        $sql .= "WHERE (l.trans_type = 'customer' or l.trans_type = 'vendor') and n.id = $id";
        $rs   = $GLOBALS['db']->getAll($sql);
    };
    if( !$rs ) { 
        return false; 
    } else {
        if ( $rs[0]['kontakt'] != 'N' ) {
            if ( $rs[0]['bezug']===0 ) {  // oberste Ebene
                $sql = "SELECT * FROM telcall WHERE bezug=".$rs[0]['id']."ORDER BY calldate desc";
            } else {
                $sql = "SELECT * FROM telcall WHERE bezug=".$rs[0]['id']." or id=$id ORDER BY calldate desc";
            }
            $rs = $GLOBALS['db']->getAll($sql);
            if( !$rs ) {
               return false;
            }
        }
        return $rs;
    }
}


/****************************************************
* insFormDoc  !!wird nur in prtWVertragOOo.php benutzt. ändern!!
* in: data = array(Formularfelder)
* out: id = des Calls
* ein neues FormDokument speichern
*****************************************************/
function insFormDoc($data,$file) {
    $sql     = "SELECT * FROM docvorlage WHERE docid=".$data['docid'];
    $rs      = $GLOBALS['db']->getAll($sql);
    $datum   = date("Y-m-d H:i:00");
    $id      = mknewTelCall();
    $dateiID = 0;
    $did     = "null";
    $datei['Datei']['tmp_name'] = 'tmp/'.$file;
    $datei['Datei']['size']     = filesize('tmp/'.$file);
    $datei['Datei']['name']     = $file;
    $dateiID = saveDokument($datei,$rs[0]['vorlage'],$datum,$data['CID'],$data['CRMUSER'],''); //##### letzten Parameter noch ändern
    $did     = documenttotc($id,$dateiID);
    $c_cause = addslashes($rs[0]['beschreibung']);
    $c_cause = nl2br($rs[0]['beschreibung']);
    $sql     = "update telcall set cause='".$rs[0]['vorlage']."',c_long='$c_cause',caller_id='".$data['CID'];
    $sql    .= "',calldate='$datum',kontakt='D',dokument=$did,bezug='0',employee=".$data['CRMUSER']." WHERE id=$id";
    $rs      = $GLOBALS['db']->query($sql);
    if( !$rs ) {
        return false;
    } else {
        return $id;
    }
}


/****************************************************
* insCall
* in: data = array(Formularfelder) datei = übergebene Datei
* out: id = des Calls
* einen neuen Anruf speichern
*****************************************************/
function insCall($data,$datei) {
    $id     = mknewTelCall();
    if ( isset($data['fid']) and ($data['fid'] != $data['CID']) ) {
        //Ein Ansprechpartner ausgewählt
        $pfad = 'P'.$data['CID'];
        $wv['cp_cv_id'] = $pfad;
    } else {
        if ( isset($data['Q']) && isset($data['fid']) ) {
            $pfad = $data['Q'][0].$data['fid'];
        } else { 
            $pfad = false;
        };
        $wv['cp_cv_id'] = $pfad;
    }
    if ( $datei['Datei']['name'][0] <> '' ) {
        $pfad = mkPfad($pfad,$data['CRMUSER']);
        $dat['Datei']['name']    = $datei['Datei']['name'][0];
        $dat['Datei']['tmp_name']= $datei['Datei']['tmp_name'][0];
        $dat['Datei']['type']    = $datei['Datei']['type'][0];
        $dat['Datei']['size']    = $datei['Datei']['size'][0];
        $text = ($data['DCaption'])?$data['DCaption']:$data['cause'];
        $dbfile = new document();
        $dbfile->setDocData("descript",$text);
        $rc = $dbfile->uploadDocument($dat,"/".$pfad);
        //$val['dokument'] = $dbfile->id;
        $dateiID = $dbfile->id;
        $did = documenttotc($id,$dateiID);
        $fields[] = 'dokument';
    } else {
        if ( $data['DateiID'] ) {
            $dateiID = $data['DateiID'];
        } else{
            $dateiID = 0;
        };
    };
    $val['cause'] = $data['cause'];
    $c_long = addslashes($data['c_long']);
    $val['c_long']      = nl2br($c_long);
    $val['caller_id']   = $data['CID'];
    if ( !isset($data['datum']) ) { 
        $val['calldate'] = date('Y-d-m H:i:s');
    } else {
        if ( !isset($data['zeit']) ) {
            $zeit = date('H:i:s');
        } else {
            $zeit = $data['zeit'].':00';
        }
        $val['calldate']    = date2db($data['datum']).' '.$zeit;  // Postgres timestamp
    }
    $val['kontakt']     = $data['kontakt'];
    $val['bezug']       = $data['bezug'];
    $val['employee']    = $data['CRMUSER'];
    $val['inout']       = ($data['inout'])?$data['inout']:'';
    $val['dokument']    = $dateiID;
    $rc = $GLOBALS['db']->updateval('telcall',$val,'id='.$id);
    if( !$rc ) {
        $id=false;
    }
    if ( isset($data['wvldate']) && $data['wvldate']) {
        $wv['c_long']   = $data['c_long'];
        $wv['cause']    = $data['cause'];
        $wv['cp_cv_id_old'] = $wv['cp_cv_id'];
        $wv['DateiID']  = $dateiID;
        $wv['kontakt']  = $data['kontakt'];
        $wv['status']   = "1";
        $wv['CRMUSER']  = $data['CRMUSER'];
        $wv['Finish']   = $data['wvldate'];
        $wv['tellid']   = $id;
        insWvl($wv,False);
    }
    return $id;
}

/****************************************************
* updCall
* in: data = array(Formularfelder) datei = übergebene Datei
* out: id = des Calls
* einen geänderten Anruf speichern
*****************************************************/
function updCall($data,$datei=false) {
    if ( $data['kontakt'] == 'N' ) return updERPNote($data);
    if ( $data['fid']!=$data['CID'] ) {
        $pfad = 'P'.$data['CID'];
        $wv['cp_cv_id'] = 'P'.$data['CID'];
    } else {
        $pfad = $data['Q'][0].$data['fid'];
        $wv['cp_cv_id'] = $data['Q'][0].$data['CID'];
    }
    if ($datei['Datei']['name'][0]<>'') {
        $pfad = mkPfad($pfad,$data['CRMUSER']);
        $dat['Datei']['name']     = $datei['Datei']['name'][0];
        $dat['Datei']['tmp_name'] = $datei['Datei']['tmp_name'][0];
        $dat['Datei']['type']     = $datei['Datei']['type'][0];
        $dat['Datei']['size']     = $datei['Datei']['size'][0];
        $text    = ($data['DCaption'])?$data['DCaption']:$data['cause'];
        $dbfile  = new document();
        $dbfile->setDocData('descript',$text);
        $rc      = $dbfile->uploadDocument($dat,'/'.$pfad);
        $dateiID = $dbfile->id;
        $did     = documenttotc($data['id'],$dateiID);
        if ( $data['datei'] != '' ) {
            $oldfile = new document();
            $oldfile->setDocData('id',$data['datei']);
            $oldfile->setDocData('name',$data['dateiname']);
            $oldfile->setDocData('pfad','/'.$pfad);
            $oldfile->deleteDocument();
        }
    } else if ( $data['datei'] ) {
        $dateiID = $data['datei'];
    } else {
        $dateiID = 'Null';
    }
    $data['datum'] = date2db($data['datum']).' '.$data['zeit'].':00';  // Postgres timestamp
    $c_long = addslashes($data['c_long']);
    $c_long = nl2br($c_long);
    $sql     = "update telcall set cause='".$data['cause']."',c_long='$c_long',caller_id='".$data['CID']."',";
    $sql    .= "calldate='".$data['datum']."',kontakt='".$data['kontakt']."',dokument=$dateiID,bezug='".$data['bezug']."',";
    $sql    .= "employee='".$data['CRMUSER']."',inout='".$data['inout']."' WHERE id=".$data['id'];
    $rs      = $GLOBALS['db']->query($sql);
    if( !$rs ) {
        $id = false;
    } else {
        $id = $data['id'];
    }
    if ( $data['wvldate'] ) {
        $wv['c_long'] = $data['c_long'];
        $wv['cause']  = $data['cause'];
        $wv['cp_cv_id_old']=$wv['cp_cv_id'];
        $wv['DateiID'] = $dateiID;
        $wv['kontakt'] = $data['kontakt'];
        $wv['status']  = '1';
        $wv['CRMUSER'] = $data['CRMUSER'];
        $wv['Finish']  = $data['wvldate'];
        $wv['tellid']  = $id;
        $wv['WVLID']   = $data['wvlid'];
        if ( $data['wvlid'] && $data['wvl'] ) {
            updWvl($wv,False);
        } else if ( $data['wvlid'] && !$data['wvl'] ) {
            $wv['status'] = '0';
            updWvl($wv,False);
        } else if ( $data['wvldate'] ) {
            insWvl($wv,False);
        }
    }
    return $id;
}
function updERPNote($data) {
    $c_cause = addslashes($data['c_cause']);
    $c_cause = nl2br($c_cause);
    $sql     = "UPDATE notes SET subject = '".$data['cause']."', body = '".$c_cause."' WHERE id = ".$data['id'];
    $rc      = $GLOBALS['db']->query($sql);
    $sql     = "UPDATE follow_ups SET follow_up_date = '".date2db($data['Datum'])."' WHERE note_id = ".$data['id'];
    $rc      = $GLOBALS['db']->query($sql);
    return $data['id'];
}
/****************************************************
* mknewTelCall
* in:
* out: id = int
* TelCallsatz erzeugen ( insert )
*****************************************************/
function mknewTelCall() {
    $newID = uniqid (rand());
    $datum = date('Y-m-d H:m:i');
    $sql   = "insert into telcall (cause,caller_id,calldate) values ('$newID',0,'$datum')";
    $rc    = $GLOBALS['db']->query($sql);
    if ( $rc ) {
        $sql = "SELECT id FROM telcall WHERE cause = '$newID'";
        $rs  = $GLOBALS['db']->getAll($sql);
        if ( $rs ) {
            $id = $rs[0]['id'];
        } else {
            $id = false;
        }
    } else {
        $id = false;
    }
    return $id;
}

/****************************************************
* mknewWVL
* in:
* out: id = int
* WVLnsatz erzeugen ( insert )
*****************************************************/
function mknewWVL($erp=false) {
    $newID = uniqid (rand());
    $datum = date('Y-m-d H:m:i');
    $GLOBALS['db']->begin();
    if ( $erp ) {
        $sql = "insert into notes (subject,created_by) values ('$newID',".$_SESSION['loginCRM'].')';
        $rc  = $GLOBALS['db']->query($sql);
        if ( $rc ) {
            $sql = "SELECT id FROM notes WHERE subject = '$newID'";
            $rs  = $GLOBALS['db']->getAll($sql);
            if ( $rs ) {
                $sql  = 'insert into follow_ups (note_id,follow_up_date,created_for_user,created_by) values (';
                $sql .= $rs[0]['id'].",'".substr($datum,0,10)."',".$_SESSION['loginCRM'].",".$_SESSION['loginCRM'].")";
                $rc   = $GLOBALS['db']->query($sql);
                if ( $rc ) {
                    $data['noteid'] = $rs[0]['id'];
                    $sql = 'SELECT id FROM follow_ups WHERE note_id = '.$data['noteid'];
                    $rs = $GLOBALS['db']->getAll($sql);
                    if ( $rs ) {
                        $data['WVLID'] = $rs[0]['id'];
                        $GLOBALS['db']->commit();
                    } else {
                        $data['WVLID'] = false;
                        $GLOBALS['db']->rollback();
                    }
                }
            } else {
                $data['WVLID'] = false;
                $GLOBALS['db']->rollback();
            }
        } else {
            $data['WVLID'] = false;
            $GLOBALS['db']->rollback();
        } 
    } else {
        $sql = "insert into wiedervorlage (cause,initdate,initemployee) values ('$newID','$datum',".$_SESSION['loginCRM'].')';
        $rc  = $GLOBALS['db']->query($sql);
        if ( $rc ) {
            $sql = "SELECT id FROM wiedervorlage WHERE cause = '$newID'";
            $rs  = $GLOBALS['db']->getAll($sql);
            if ( $rs ) {
                $data['WVLID'] = $rs[0]['id'];
                $GLOBALS['db']->commit();
            } else {
                $data['WVLID'] = false;
                $GLOBALS['db']->rollback();
            }
        } else {
            $data['WVLID'] = false;
            $GLOBALS['db']->rollback();
        }
    }
    return $data;
}

// Das hier muß raus!! Class Document()
/****************************************************
* getDokument
* in: id = int
* out: rs = array(Felder der db)
* ein Dokument aus db holen
*****************************************************/
function getDokument($id) {
    $sql = 'SELECT * FROM documents WHERE id='.$id;
    $rs  = $GLOBALS['db']->getOne($sql);
    if( !$rs ) {
        return false;
    } else {
        return $rs;
    }
}

/****************************************************
* getAllDokument
* in: id = int
* out: rs = array(Felder der db)
* alle Dokumente zu einem telcall aus db holen
*****************************************************/
function getAllDokument($id){
    $sql = "SELECT B.* FROM documenttotc A,documents B WHERE A.telcall=$id and A.documents=B.id";
    $rs  = $GLOBALS['db']->getAll($sql);
    if( !$rs ) {
        $rs = false;
    }
    return $rs;
}

/****************************************************
* getCall
* in: id = int
* out: rs = array(Felder der db)
* einen Datensatz aus telcall holen
*****************************************************/
function getCall($id) {
    $sql = 'SELECT T.*,W.finishdate as wvldate,W.id as wvlid FROM telcall T LEFT JOIN wiedervorlage W on W.tellid=T.id WHERE T.id='.$id;
    $rs  = $GLOBALS['db']->getAll($sql);
    if( !$rs ) {
        $sql = 'SELECT n.id,n.trans_id as caller_id,n.subject as cause,n.body as c_long,u.follow_up_date as calldate,';
        $sql.= "'N' as kontakt,0 as bezug,' ' as inout,created_for_user as employee ";
        $sql.= 'FROM notes n LEFT JOIN follow_ups u on note_id = n.id ';
        $sql.= 'LEFT JOIN follow_up_links l on follow_up_id = u.id ';
        $sql.= "WHERE (l.trans_type = 'customer' or l.trans_type = 'vendor') and n.id = $id";
        $rs  = $GLOBALS['db']->getAll($sql);
    }
    if( !$rs ) {
        $daten = false;
    } else {
        $daten['datum']    = db2date(substr($rs[0]['calldate'],0,10));
        $daten['zeit']     = substr($rs[0]['calldate'],11,5);
        $daten['cause']    = $rs[0]['cause'];
        $daten['kontakt']  = $rs[0]['kontakt'];
        $c_long            = str_replace('<br />','',$rs[0]['c_long']);
        $c_long            = stripslashes($c_long);
        $daten['c_long']   = $c_long;
        $daten['CID']      = $rs[0]['caller_id'];
        $daten['inout']    = $rs[0]['inout'];
        $daten['bezug']    = $rs[0]['bezug'];
        $daten['wvldate']  = db2date(substr($rs[0]['wvldate'],0,10));
        $daten['wvlid']    = $rs[0]['wvlid'];
        $daten['employee'] = $rs[0]['employee'];
        $daten['datei']    = $rs[0]['dokument'];
        if ( $rs[0]['dokument']==1 ) {
            $daten['Files'] = getAllDokument($id);
            $daten['datei'] = 1;
        } else if ( $rs[0]['dokument']>1 ) {
            $dat = getDokument($rs[0]['dokument']);
            if ( $dat ) {
                $daten['Kunde']     = ($dat['kunde']>0)?$dat['kunde']:$dat['employee'];
                $daten['dateiname'] = $dat['filename'];
                $daten['Dpfad']     = $dat['pfad'];
                $daten['DCaption']  = $dat['descript'];
            } else {
                $daten['Dpfad']     = '';
                $daten['dateiname'] = '';
                $daten['DCaption']  = '';
                $daten['Kunde']     = '';
            }
        } else {
            $daten['Dpfad']     = '';
            $daten['dateiname'] = '';
            $daten['DCaption']  = '';
            $daten['Kunde']     = '';
        }
        $daten['id']      = $id;
        $daten['history'] = getCntCallHist($id);
    }
    return $daten;
}

/****************************************************
* getCntCallHist
* in: id = int, bezug = boolean
* out: int
* Änderungen an TelCall inst History schreiben 
*****************************************************/
function getCntCallHist($id,$bezug=false) {
    if ( $bezug ) {
        $sql = "SELECT count(*) as cnt FROM telcallhistory WHERE bezug=$id and grund='D'";
    } else  {
        $sql = 'SELECT count(*) as cnt FROM telcallhistory WHERE orgid='.$id;
    }
    $rs = $GLOBALS['db']->getOne($sql);
    return $rs['cnt'];
}

/****************************************************
* getCallHistory
* in:  id = int, bezug = boolean
* out: array
* History zu einem TelCall holen
*****************************************************/
function getCallHistory($id,$bezug=false) {
    if ( $bezug ) {
        $sql = "SELECT * FROM telcallhistory WHERE bezug=$id ORDER BY datum desc";
    } else  {
        $sql = "SELECT * FROM telcallhistory WHERE orgid=$id ORDER BY datum desc";
    }
    $rs = $GLOBALS['db']->getAll($sql);
    return $rs;
}

/****************************************************
* getWvl
* in: crmuser = int
* out: rs = array(Felder der db)
* alle wiedervorlagen eines Users auslesen
*****************************************************/
function getWvl() {
    $sql  = "SELECT *,(SELECT name FROM employee WHERE id= employee) as ename,'' as starttag,'' as stopzeit,'' as stoptag ";
    $sql .= "FROM wiedervorlage WHERE (employee=".$_SESSION['loginCRM']." or employee is null) and status > '0' ORDER BY  finishdate asc ,initdate asc";
    $rs1  = $GLOBALS['db']->getAll($sql);
    //Überarbeiten:
    if( !$rs1 ) {
        $rs1 = false;
    } else {
        if (count($rs1)==0) $rs1=array(array('id'=>0,'initdate'=>date('Y-m-d H:i:00'),'cause'=>'Keine Eintr&auml;ge'));
    }
    $sql  = 'SELECT follow_ups.id,follow_up_date,created_for_user,follow_ups.created_by,subject,body,trans_id,note_id,trans_module,E.name as ename FROM ';
    $sql .= 'follow_ups LEFT JOIN notes on note_id=notes.id LEFT JOIN employee E on E.id=follow_ups.created_for_user ';
    $sql .= "WHERE done='f' and created_for_user=".$_SESSION['loginCRM'];
    $rs2  = $GLOBALS['db']->getAll($sql);
    if ( $rs2 ) {
        foreach ( $rs2 as $row ) {
            $rs1[] = array(
                'id'         => $row['id'],
                'cause'      => $row['subject'],
                'descript'   => $row['body'],
                'kontakt'    => $row['trans_module'],
                'status'     => 'F',
                'kontakt'    => 'F',
                'trans_module'  => $row['trans_module'],
                'initemployee'  => $row['created_by'],
                'employee'   => $row['created_for_user'],
                'ename'      => $row['ename'],
                'initdate'   => $row['follow_up_date'].' 00:00',
                'finishdate' => '',
                'stoptag'    => '',
                'stopzeit'   => '',
                'starttag'   => '',
                'note_id'    => $row['note_id']);
        }
    }
    if ( $rs1 ) $rc = usort($rs1,'sorttime');
    return $rs1;
}

function sorttime($a, $b) {
    return ( ($a['initdate'] < $b['initdate']) ? -1 : ( ($a['initdate'] > $b['initdate']) ? 1 : 0) );
}

/****************************************************
* getOneWvl
* in: id = int
* out: rs = array(Felder der db)
* einen Datensatz aus wiedervorlage holen
*****************************************************/
function getOneWvl($id) {
    $sql = 'SELECT * FROM wiedervorlage WHERE id='.$id;
    $rs  = $GLOBALS['db']->getAll($sql);
    if( !$rs ) {
        $data = false;
    } else {
        switch ( $rs[0]['kontakttab'] ) {
            case 'C' : $sql="SELECT name,'' as sep,'' as name2 FROM customer WHERE id = ".$rs[0]['kontaktid']; 
                       $rsN=$GLOBALS['db']->getAll($sql); 
                       break;
            case 'V' : $sql="SELECT name,'' as sep,'' as name2  FROM vendor WHERE id = ".$rs[0]['kontaktid'];
                       $rsN=$GLOBALS['db']->getAll($sql); 
                       break;
            case 'P' : $sql="SELECT cp_name as name ,', ' as sep ,cp_givenname as name2 FROM contacts WHERE cp_id = ".$rs[0]['kontaktid'];
                       $rsN=$GLOBALS['db']->getAll($sql); 
                       break;
            default  : $rsN=false;
        }
        if ( $rs[0]['document'] ) { // gibt es ein Dokument
            $datei = getDokument($rs[0]['document']);
            if ( $datei ) {
                $pre   = ($datei['kunde']>0)?$datei['kunde']:$datei['employee'];
                $pre   = $datei['pfad'];
                $name  = $datei['filename'];
                $path  = $_SESSION['dbname'].'/'.$pre.'/';
            } else {
                $name  = '';
                $path  = '';
            }
        } else {
            $name = '';
            $path = '';
        }
        $data['id']          = $rs[0]['id'];
        $data['Initdate']    = $rs[0]['initdate'];
        $data['Change']      = $rs[0]['changedate'];
        $data['Finish']      = ($rs[0]['finishdate']<>'')?db2date(substr($rs[0]['finishdate'],0,12)):'';
        $data['cause']       = $rs[0]['cause'];
        $data['c_long']      = stripslashes(ereg_replace('<br />','',$rs[0]['descript']));
        $data['Datei']       = $rs[0]['document'];
        $data['DName']       = $name;
        $data['DPath']       = $path;
        $data['DCaption']    = $datei['descript'];
        $data['status']      = $rs[0]['status'];
        $data['CRMUSER']     = $rs[0]['employee'];
        $data['InitCrm']     = $rs[0]['initemployee'];
        $data['kontakt']     = $rs[0]['kontakt'];
        $data['tellid']      = $rs[0]['tellid'];
        $data['kontaktid']   = $rs[0]['kontaktid'];
        $data['kontakttab']  = $rs[0]['kontakttab'];
        $data['kontaktname'] = $rsN[0]['name'].$rsN[0]['sep'].$rsN[0]['name2'];
    }
    return $data;
}

/****************************************************
* getOneERP
* in: id = int
* out: rs = array(Felder der db)
* einen Datensatz aus follow_ups/notes holen
*****************************************************/
function getOneERP($id) {
    $sql  = 'SELECT follow_ups.id,follow_up_date,created_for_user,subject,body,trans_id,note_id,trans_module,follow_ups.created_by,';
    $sql .= 'follow_ups.itime,follow_ups.mtime,C.id as c,V.id as v, coalesce(V.name,C.name) as name ';
    $sql .= 'FROM follow_ups LEFT JOIN notes on note_id=notes.id ';
    $sql .= 'LEFT JOIN vendor V on V.id=trans_id LEFT JOIN customer C on C.id=trans_id ';
    $sql .= "WHERE done='f' and follow_ups.id=$id";
    $rs   = $GLOBALS['db']->getAll($sql);
    $data['id']         = $rs[0]['id'];
    $data['Initdate']   = substr($rs[0]['itime'],0,19);
    $data['Change']     = substr($rs[0]['mtime'],0,19);
    $data['Finish']     = ($rs[0]['follow_up_date']<>'')?db2date($rs[0]['follow_up_date']):'';
    $data['cause']      =  $rs[0]['subject'];
    $data['c_long']     = stripslashes(ereg_replace('<br />','',$rs[0]['body']));
    $data['Datei']      = '';
    $data['DName']      = '';
    $data['DPath']      = '';
    $data['DCaption']   = '';
    $data['status']     = '1';
    $data['CRMUSER']    = $rs[0]['created_for_user'];
    $data['InitCrm']    = $rs[0]['created_by'];
    $data['kontakt']    = 'F';
    $data['noteid']     = $rs[0]['note_id'];
    if ( $rs[0]['c']>0 ) {
        $data['kontaktid']   = $rs[0]['c'];
        $data['kontakttab']  = 'C';
        $data['kontaktname'] = $rs[0]['name'];
    } else if ( $rs[0]['v']>0 ) {
        $data['kontaktid']   = $rs[0]['v'];
        $data['kontakttab']  = 'V';
        $data['kontaktname'] = $rs[0]['name'];
    } else {
        $data['kontaktid']   = false;
        $data['kontakttab']  = '';
        $data['kontaktname'] = '';
    }
    return $data;
}

/****************************************************
* mkPfad
* in: wer = String
* out: pfad = String
* einen Dokumentenpfad erstellen
*****************************************************/
function mkPfad($wer,$alternate) {
    $pfad = '';
    if ( substr($wer,0,1)=='P' ) {
        $tmp = substr($wer,1);
        $rs  = $GLOBALS['db']->getAll('SELECT customernumber FROM customer C, contacts P WHERE P.cp_cv_id=C.id and cp_id='.$tmp);
        if ( $rs[0]['customernumber'] ) {
            $pfad = "C".$rs[0]['customernumber']."/$tmp";
        } else {
            $rs = $GLOBALS['db']->getAll('SELECT vendornumber FROM vendor V, contacts P WHERE P.cp_cv_id=V.id and cp_id='.$tmp);
            if ( $rs[0]['vendornumber'] ) {
                $pfad = 'V'.$rs[0]['vendornumber'].'/$tmp';
            } else {
                $pfad = $tmp;
            }
        }
    } else if ( $wer<>'' ){
        $tmp  = substr($wer,1);
        $ttmp = substr($wer,0,1);
        if ( $ttmp=='C' ) {
            $rs = $GLOBALS['db']->getAll('SELECT customernumber as number FROM customer WHERE id='.$tmp);
        } else {
            $rs = $GLOBALS['db']->getAll('SELECT vendornumber as number FROM vendor WHERE id='.$tmp);
        }
        $pfad = $ttmp.$rs[0]['number'];
    } else  {
        $pfad = $alternate;
    }
    return $pfad;
}

/****************************************************
* insWvl
* in: data = array(Formularfelder), datei = übergebene Datei
* out: rs = boolean
* einen Datensatz in wiedervorlage einfügen
*****************************************************/
function insWvl($data,$datei='') {
    $data = array_merge($data,mknewWVL($data['kontakt']=='F'));
    $rs   = updWvl($data,$datei);
    if ( $rs < 0 ) {
        if ( $data['kontakt']=='F' ) {
            $sql = 'DELETE FROM follow_ups WHERE id = '.$data['WVLID'];
            $rc  = $GLOBALS['db']->query($sql);
            $sql = 'DELETE FROM notes WHERE id = '.$data['noteid'];
            $rc  = $GLOBALS['db']->query($sql);
        } else {
            $sql = 'DELETE FROM wiedervorlage WHERE id = '.$data['WVLID'];
            $rc  = $GLOBALS['db']->query($sql);
        }
    }
    return $rs;
}
function updWvlERP($data) {
    if ( substr($data['CRMUSER'],0,1) == 'G' || $data['CRMUSER'] == '' ) { 
        return -1; 
    };
    if ( !$data['WVLID'] ) $data = array_merge($data,mknewWVL(true));
    $finish   = ($data['Finish']<>'')?", finishdate='".date2db($data['Finish'])." 0:0:00'":'';
    $descript = addslashes($data['c_long']);
    $descript = nl2br($descript);    
    $sql      = "update notes set subject='".$data['cause']."',body='$descript', created_by=".$_SESSION['loginCRM'];
    if ( $data['cp_cv_id'] ) {
        $sql .= ",trans_id=".substr($data['cp_cv_id'],1);
        $sql .= ",trans_module='ct'";
    } else {
        $sql .= ",trans_id=".$data['WVLID'];
        $sql .= ",trans_module='fu'";
    }
    $sql .= " WHERE id=".$data['noteid'];
    $rc   = $GLOBALS['db']->query($sql);
    if ( !$rc ) { $GLOBALS['db']->query("ROLLBACK"); return -3; };
    $sql  = "update follow_ups set created_for_user=".$data['CRMUSER'].",done='".$data['status']."', ";
    $sql .= "follow_up_date ='".date2db($data['Finish'])."' WHERE id = ".$data['WVLID'];
    $rc   = $GLOBALS['db']->query($sql);
    if ( !$rc ) { $GLOBALS['db']->query("ROLLBACK"); return -4; };
    if ( $data['cp_cv_id'] ) {
        $sql = "SELECT id FROM follow_up_links WHERE follow_up_id = ".$data['WVLID'];
        $rs  = $GLOBALS['db']->getOne($sql);
        $rc  = $GLOBALS['db']->query("BEGIN");
        if ( !$rs ) {
            $sql  = "insert into follow_up_links (follow_up_id,trans_id,trans_type,trans_info) values (";
            $sql .= $data['WVLID'].','.substr($data['cp_cv_id'],1).",'".((substr($data['cp_cv_id'],0,1)=='C')?'customer':'vendor');
            $sql .= "','".$data['name']."')";
            $rc   = $GLOBALS['db']->query($sql);
            $rs   = 1;
        } else {
            $sql  ="update follow_up_links set trans_id=".substr($data['cp_cv_id'],1);
            $sql .=",trans_type='".((substr($data['cp_cv_id'],0,1)=='V')?'vendor':'customer');
            $sql .="',trans_info='".$data['name']."' WHERE follow_up_id = ".$data['WVLID'];
            $rc   = $GLOBALS['db']->query($sql);
        }
        if ( !$rc ) { $GLOBALS['db']->query('ROLLBACK'); return -5; };
        $rs = $GLOBALS['db']->query('COMMIT');
        $rs = 1;
    } else {
       $rs = 1;
    }
    return $rs;
}
/****************************************************
* updWvl
* in: data = array(Formularfelder), datei = übergebene Datei
* out: rs = boolean
* einen Datensatz in wiedervorlage aktualisieren
*****************************************************/
function updWvl($data,$datei='') {
    $nun = date('Y-m-d H:i:00');
    $dateiID=$data['DateiID'];
    if ( empty($dateiID) ) $dateiID = 0;
    $finish = ($data['Finish']<>'')?", finishdate='".date2db($data['Finish'])." 0:0:00'":'';
    $descript = addslashes($data['c_long']);
    $descript = nl2br($descript);
    if ( $data['kontakt']=='F' ) {
        $rs = updWvlERP($data);
    } else {
        if ( $data['status'] == '' || $data['status'] < 0 || $data['status'] > 3 ) $data['status'] = 1;
        $sql  = "update wiedervorlage set  cause='".$data['cause']."', descript='$descript', ";
        $sql .= "document=$dateiID, status=".$data['status'].",kontakt='".$data['kontakt']."',changedate='$nun'".$finish;
        if ( $data['tellid'] ) {
             $sql .= ",kontaktid=".substr($data['cp_cv_id'],1).",kontakttab='".substr($data['cp_cv_id'],0,1)."'";
             $sql .= ",tellid=".$data['tellid'];
        }
        if ( $data['CRMUSER'] ) {
            if ( substr($data['CRMUSER'],0,1) == 'G' ) {
                $sql .= ",gruppe=true, ";
                $data['CRMUSER'] = substr($data['CRMUSER'],1);
            }
            $sql .= ",employee=".$data['CRMUSER'];
        }
        $sql .= " WHERE id=".$data['WVLID'];
        $rs = $GLOBALS['db']->query($sql);
        if( !$rs ) {
            $rs = -7;
        } else {
            $rs = $data['WVLID'];
        };
        if ( $data['cp_cv_id']<>$data['cp_cv_id_old'] or $data['status']<1 ) {  // es wurde eine neue Zuweisung an einen Kunden gemacht
            $id = kontaktWvl($data['WVLID'],$data['cp_cv_id'],$pfad);
            if ($id) {
                $rs = $data['WVLID'];} else { $rs = -8; };
        }
    }
    return $rs;
}

/****************************************************
* documenttotc
* in: newID,did = integer
* out: rs = boolean
* eine DockId zum Telcall oder Person zuordnen
*****************************************************/
function documenttotc($newID,$did) {
    $sql="insert into documenttotc (telcall,documents) values ($newID,$did)";
    $rs=$GLOBALS['db']->query($sql);
    return $rs;
}

/****************************************************
* documenttotc
* in: newID,did = integer
* out: rs = boolean
* eine DockId von Person auf Telcall ändern
*****************************************************/
function documenttotc_($newID,$tid) {

    $sql="update documenttotc set telcall=$tid WHERE telcall=$newID";
    $rs=$GLOBALS['db']->query($sql);
    return $rs;
}

/****************************************************
* insWvlM
* in: data = array(Formularfelder)
* out: rs = boolean
* einen Mail-Datensatz in WVL nach telcall verschieben
*****************************************************/
function insWvlM($data) { 
    $f=fopen('/tmp/inswvl.log','w');
    fputs($f,print_r($data,true));
    if(empty($data['cp_cv_id'])  && $data['status'] < 1) {
        $kontaktID = $data['CRMUSER'];
        $data['cp_cv_id'] = ''; //$data['CRMUSER'];
    } else {  
        $kontaktID  = substr($data['cp_cv_id'],1);
        $kontaktTAB = substr($data['cp_cv_id'],0,1);
    }
    if(!empty($kontaktID)) {
        $data['status']  = 0;
        $nun  = date('Y-m-d H:i:00');
        $data['kontakt'] = 'M';
        $did  = false;
        $data['c_long'] = $data['c_long'];
        $data['cause']   = $data['cause'];
        $data['bezug']   = 0;
        $data['kontakt'] = 'M';
        $data['datum']   = date('d.m.Y');
        $data['zeit']    = date('H:i');
        $CID = $_SESSION['loginCRM'];
        $data['CID']     = $kontaktID;
        $tid = insCall($data,false);  //Kontaktthreadeintrag
        if ( !$tid ) return -6;
        if ( !empty($data['dateien']) ) {
            $data['datei'] = true;
            foreach( $data['dateien'] as $mail ){
            print_r($f,print_r($mail,true));
                //trenne Anhang und speichere in tmp
                $file = explode(',',$mail);
                $Datei['Datei']['name'] = $file[0];
                $Datei['Datei']['tmp_name'] = $_SESSION['erppath'].'crm/tmp/'.$file[0];
                $Datei['Datei']['size'] = $file[1];
                $dbfile = new document();
                $dbfile->setDocData('descript',$data['DCaption']);
                $pfad = mkPfad($data['cp_cv_id'],$data['CRMUSER']);
                $rc = $dbfile->uploadDocument($Datei,$pfad);
                if ( !$rc ) return -8;
                $did = $dbfile->id;     
                documenttotc($tid,$did);
            }
            $sql = 'update telcall set dokument=1 WHERE id = '.$tid;
            $rc = $GLOBALS['db']->query($sql);
            return $rc;
        } else {
            $data['datei'] = false;
        }
        moveMail($data['WVLID']); //,$tmpmail['MailDelete']); 
        // bis hier ok
        $rs = 1;
    } else { 
        $rs = -7;
    };
    return $rs;
}

/****************************************************
* kontaktWvl
* in: id,fid = int
* out: rs = id
* eine wiedervorlage mit telcall verbinden
*****************************************************/
function kontaktWvl($id,$fid,$pfad) {
    $sql = 'SELECT * FROM wiedervorlage WHERE id='.$id;
    $rs  = $GLOBALS['db']->getAll($sql);
    if( !$rs ) return false;
    $nun = date('Y-m-d H:i:00');
    $tab = substr($fid,0,1);
    $fid = substr($fid,1);
    if ( !$GLOBALS['db']->begin() ) return false;;
    if ( $rs[0]['kontaktid']>0 and $fid<>$rs[0]['kontaktid'] ){
        // bisherigen Kontakteintrag ungültig markieren
        $sql = "update telcall set cause=cause||' storniert' WHERE id=".$rs[0]['tellid'];
        //$rc=$GLOBALS['db']->query($sql);
        if ( !$GLOBALS['db']->query($sql) ) return false;
    } 
    if ( !$rs[0]['kontaktid']>0 or empty($rs[0]['kontaktid']) ) {
        $tid  = mknewTelCall();
        $sql  = "update telcall set cause='".$rs[0]['cause']."',caller_id=$fid,calldate='$nun',";
        $sql .= "c_long='".$rs[0]['descript']."',employee=".$rs[0]['employee'].",kontakt='".$rs[0]['kontakt'];
        $sql .= "',bezug=0,dokument=".$rs[0]['document']." WHERE id=$tid";
        $rc = $GLOBALS['db']->query($sql);
        if( !$rc ) {
            $GLOBALS['db']->rollback();
            return false;
        } else {
            $ok = $tid;
            $sql = "update wiedervorlage set kontaktid=$fid,kontakttab='$tab',tellid=$tid WHERE id=$id";
            if ( !$GLOBALS['db']->query($sql) ) {
                $GLOBALS['db']->rollback();
                return false;
            }
        }
    }
    if ( $rs[0]['status']<1 ) {
        if ( $rs[0]['document'] && $rs[0]['kontakt']<>"M" ) {
            $sql = "SELECT * FROM documents WHERE id=".$rs[0]['document'];
            $rsD = $GLOBALS['db']->getAll($sql);
            $von = "dokumente/".$_SESSION['dbname']."/".$rsD[0]['employee']."/".$rsD[0]['filename'];
            if ( !$pfad ) {
                //$pfad=$_SESSION['dbname']."/".$pfad;
            //} else {
                $pfad = mkPfad($tab.$fid,$fid);
            }
            $ok = chkdir($pfad);
            $nach = 'dokumente/'.$_SESSION['dbname'].'/'.$pfad.'/'.$rsD[0]['filename'];
            if ( file_exists($von) ) {
                $rc = rename($von,$nach);
                if ($rc) {
                    $sql="update documents set kunde=".$fid.", pfad='".$pfad."' WHERE id=".$rsD[0]['id'];
                    if (!$GLOBALS['db']->query($sql)) {
                        $GLOBALS['db']->rollback();
                        return false;
                    }
                }
            } else if( file_exists($nach) ) {
                $sql = "update documents set kunde=".$fid.", pfad='".$pfad."' WHERE id=".$rsD[0]['id'];
                if ( !$GLOBALS['db']->query($sql) ) {
                    $GLOBALS['db']->rollback();
                    return false;
                }
            } else {
                $GLOBALS['db']->rollback();
                return false;
            }
        }
    }
    return $GLOBALS['db']->commit();
}

/****************************************************
* decode_string
* in: string = string
* out: string = string
* dekodiert einen MailString
*****************************************************/
function decode_string ($string) {
   if ( preg_match('/=?([A-Z,0-9,-]+)?([A-Z,0-9,-]+)?([A-Z,0-9,-,=,_]+)?=/i', $string) ) {
      $coded_strings = explode('=?', $string);
      $counter       = 1;
      $string        = $coded_strings[0]; // add non encoded text that is before the encoding 
      while ( $counter < sizeof($coded_strings) ) {
         $elements   = explode('?', $coded_strings[$counter]); // part 0 = charset 
         if (preg_match('/Q/i', $elements[1])) {
            $elements[2] = str_replace('_', ' ', $elements[2]);
            $elements[2] = eregi_replace("=([A-F,0-9]{2})", "%\\1", $elements[2]);
            $string     .= urldecode($elements[2]);
         } else { // we should check for B the only valid encoding other then Q 
            $elements[2] = str_replace('=', '', $elements[2]);
            if ( $elements[2] ) { $string .= base64_decode($elements[2]); }
         }
         if ( isset($elements[3]) && $elements[3] != '' ) {
            $elements[3] = ereg_replace('^=', '', $elements[3]);
            $string     .= $elements[3];
         }
         $string .= ' ';
         $counter++;
      }
   }
   return $string;
}

/****************************************************
* holeMailHeader
* in: usr = int
* out: rs = array
* alle Mailheader holen
*****************************************************/
function holeMailHeader() {
    $tmpmail = getCRMdefault();
    $srv     = getUsrMailData();
    $flags   = json_decode(strtolower($tmpmail['MailFlag']));
    $m       = array();
    if ( $srv['msrv'] && $srv['postf'] ) {  // Mailserver/Postfach eingetragen
        $mbox = mail_login($srv['msrv'],$srv['port'],$srv['postf'],$srv['mailuser'],$srv['kennw'],$srv['proto'],$srv['ssl']);
        if ( $mbox ) {
            $status = mail_stat($mbox);
            $anzahl = $status['Nmsgs'] - $status['Deleted'];
            if ( $anzahl>0 ) {
                $overview = mail_list($mbox);
                $m        = false;
                if ( is_array ($overview ) ) {
                    foreach ( $overview as $mail ) {
                        //if (!$mail['deleted'] && !$mail[strtolower($tmpmail['MailFlag'])]) {
                        $zeige = 0;
                        if ( $flages ) foreach ( $flags as $flag ) if ( $mail[$flag] ) $zeige++;
                        if ( $zeige == 0 ) {
                            $gelesen = ($mail['seen'])?'-':'+';
                            $m[] = array('Nr'        =>  $mail['msgno'],
                                         'Datum'     =>  $mail['date'].' '.$mail['time'],
                                         'date'      =>  $mail['orgdate'],
                                         'Betreff'   =>  $mail['subject'],
                                         'Abs'       =>  $mail['FROM'],
                                         'Gelesen'   =>  $gelesen,
                                         'sel'       =>  $mail['flagged']);
                        }
                    }
                    if ( empty($m) ) { $m[] = array('Nr'=>0,'Datum'=>'','Betreff'=>'Keine Mails','Abs'=>'','Gelesen'=>'');}
                    else             { usort($m,'datesort');  };
                }
                imap_close ($mbox);
            } else {
                $m[] = array('Nr'=>0,'Datum'=>'','Betreff'=>'Keine Mails','Abs'=>'','Gelesen'=>'');
            }
            mail_close($mbox);
        } else {  // Mailserver nicht erreicht
            $m[] = array('Nr'=>0,'Datum'=>'','Betreff'=>'can\'t connect to Mailserver ','Abs'=>'','Gelesen'=>'');
        }
        return $m;
    } else {
        return false;
    };
}

function datesort($a,$b) {
    return ( ($a['date'] > $b['date']) ? -1 : ( ($a['date'] < $b['date']) ? 1 : 0) );
}
/**
 * TODO: short description.
 * 
 * @param mixed        
 * @param mixed $email 
 * @param mixed $clean 
 * 
 * @return TODO
 */
function getSenderMail($email) {
    if ( !preg_match('/[^<]*<(.*@.+\.[^>]+)/',$email,$clean) ) {
        $clean = $email;
    } else {
        $clean = $clean[1];
    }
    $sql = "SELECT id,name FROM customer WHERE email ilike '%$clean%'";
    $rs  = $GLOBALS['db']->getOne($sql);
    $t   = 'C';
    if ( !$rs ) {
        $sql = "SELECT id,name FROM vendor WHERE email ilike '%$clean%'";
        $rs  = $GLOBALS['db']->getOne($sql);
        $t   = 'V';
    } 
    if ( !$rs ) {
        $sql = "SELECT cp_id as id ,cp_name as name FROM contacts WHERE cp_email ilike '%$clean%'";
        $rs  = $GLOBALS['db']->getOne($sql);
        $t   = 'P';
    } 
    if ( $rs ) {
        return array('kontaktname'=>$rs['name'],'kontaktid'=>$rs['id'],'kontakttab'=>$t);
    } else {
        return array('name'=>'','id'=>'');
    }
}

/****************************************************
* getOneMail
* in: usr = int, nr = int
* out: data = array
* eine Mail holen
*****************************************************/
function getOneMail($nr) {
    $files = array();
    mb_internal_encoding($_SESSION['charset']);
    $srv   = getUsrMailData();
    $mbox  = mail_login($srv['msrv'],$srv['port'],$srv['postf'],$srv['mailuser'],$srv['kennw'],$srv['proto'],$srv['ssl']);
    $head  = mail_parse_headers(mail_retr($mbox,$nr));
    if ( !$head ) return;
    $info  = mail_fetch_overview($mbox,$nr);
    $info2 = mail_fetchstructure($mbox,$nr);
    $senderadr = $head['From']."\n".$head['Date']."\n";
    $sender = getSenderMail($head['From']);
    $mybody = $senderadr;
    $htmlbody  = 'Empty Message Body';
    $subject   = $head['Subject'];
    $structure = imap_fetchstructure($mbox,$nr);
    if ( $structure->parts ) {
        $parts = create_part_array($structure);
        $body  = mail_get_body($mbox,$nr,$parts[0]);
    } else {
        $head['encoding']  = $structure->encoding;
        $head['ifsubtype'] = $structure->ifsubtype;
        $head['subtype']   = $structure->subtype;
        $body = mail_getBody($mbox,$nr,$head);
    }
    if ( !preg_match('/PLAIN/i',$structure->subtype) )  {
       for ($p=1; $p < count($parts); $p++) {
            $attach = mail_get_file($mbox,$nr,$parts[$p]);
            if ($attach) $files[] = $attach;
        }
    }
    $rc = mail_SetFlag($mbox,$nr,'Seen'); //$_SESSION['MailFlag']);
    mail_close($mbox);
    $data['id']          = $nr;
    $data['muid']        = $info[0]->uid;
    $data['kontaktname'] = ($sender['kontaktname']=='')?'':$sender['kontaktname'];
    $data['kontakttab']  = ($sender['kontakttab']=='')?'':$sender['kontakttab'];
    $data['kontaktid']   = ($sender['kontaktid']=='')?'':$sender['kontaktid'];
    $data['sendername']  = ($sender['name']=='')?'':$sender['name'];
    $data['senderid']    = ($sender['id']=='')?False:$sender['id'];
    $data['Initdate']    = ($head['Date']=='')?'':date("d.m.Y H:m:i",strtotime($head['Date']));
    $data['cause']       = $subject;
    $data['c_long']      = $mybody.$body; 
    //$data['Datei']  =   $anhang;
    $data['status']     = '1';
    $data['InitCrm']    = $_SESSION['loginCRM'];    //$head[''];
    $data['CRMUSER']    = $_SESSION['login'];       //$head[''];
    $data['DCaption']   = ($files)?$data['cause']:'';
    $data['Anhang']     = $files;
    $data['flags']      = array('flagged'=>$info[0]->flagged,'answered'=>$info[0]->answered,
                                'deleted'=>$info[0]->deleted,'seen'=>$info[0]->seen,
                                'draft'=>$info[0]->draft);//,'recend'=>$info[0]->recend);
    return $data;
}

/****************************************************
* getUsrMailData
* in: id = int
* out: data = array
* die Maildaten des Users holen
*****************************************************/
function getUsrMailData() {
    $sql = "SELECT * FROM crmemployee WHERE uid=".$_SESSION['loginCRM']." and (typ = 't' or typ = 'i') AND manid = ".$_SESSION['manid'];
    $rs  = $GLOBALS['db']->getAll($sql);
    if( !$rs ) {
        $data = false;
    } else {
        $data = array('msrv'=>'','port'=>'','mailuser'=>'','postf'=>'','ssl'=>'','kennw'=>'','postf2'=>'', 'proto'=>'');
        $mail = array_keys($data);
        foreach ( $rs as $row ) {
            if ( in_array($row['key'],$mail) ) {
                $data[$row['key']] = $row['val'];
            };
        }
    }
    return $data;
}

/****************************************************
* eine neue Mailbox erstellen
* in: name = string, id = int
* out:
* eine Mailbox anlegen
* !! geht nicht mit jeder IMAP - Installation
* !! noch weiter Testen
*****************************************************/
function createMailBox($name,$mbox=false) {
    $srv   = getUsrMailData();
    if ( !$mbox ) $mbox  = mail_login($srv['msrv'],$srv['port'],$srv['postf'],$srv['mailuser'],$srv['kennw'],$srv['proto'],$srv['ssl']);
    $name1   = $name;
    $name2   = imap_utf7_encode ($name);
    $newname = $name1;
    //echo "Newname will be '$name1'<br>\n";
    if ( @imap_createmailbox ($mbox,imap_utf7_encode ("{".$srv['msrv']."}INBOX.$newname")) ) {
        $status = @imap_status($mbox,"{".$srv['msrv']."}INBOX.$newname",SA_ALL);
    };
    /*    if($status) {
            print("your new mailbox '$name1' has the following status:<br>\n");
            print("Messages:   ". $status->messages   )."<br>\n";
            print("Recent:     ". $status->recent     )."<br>\n";
            print("Unseen:     ". $status->unseen     )."<br>\n";
            print("UIDnext:    ". $status->uidnext    )."<br>\n";
            print("UIDvalidity:". $status->uidvalidity)."<br>\n";

            if (imap_renamemailbox ($mbox,"{".$srv['msrv']."}INBOX.$newname", "{your.imap.host}INBOX.$name2")) {
                echo "renamed new mailbox FROM '$name1' to '$name2'<br>\n";
                $newname=$name2;
            } else {
                print "imap_renamemailbox on new mailbox failed: ".imap_last_error ()."<br>\n";
            }
        } else {
            print "imap_status on new mailbox failed: ".imap_last_error()."<br>\n";
        }
        if (@imap_deletemailbox($mbox,"{".$srv['msrv']."}INBOX.$newname")) {
            print "new mailbox removed to restore initial state<br>\n";
        } else {
            print  "imap_deletemailbox on new mailbox failed: ".implode ("<br>\n", imap_errors())."<br>\n";
        }
    } else {
        print "could not create new mailbox: ".implode ("<br>\n",imap_errors())."<br>\n";
    }*/
    imap_close($mbox);
    return $status;
}

function chkMB($mbox,$srv,$inbox,$pf) {
    $inbox = strtoupper($inbox);
    $boxes = imap_list($mbox,'{'.$srv.'}'.$inbox,'*');
    if ( in_array('{'.$srv.'}'."$inbox.$pf",$boxes) ) { 
        return true;
    } else {
        $rc = createMailBox($pf,$mbox);
        return $rc;
    }
}

/****************************************************
* moveMail
* in: mail,id = int
* out:
* eine Mail markieren bzw. löschen
*****************************************************/
function moveMail($mail) {
    $srv  = getUsrMailData();
    $mbox = mail_login($srv['msrv'],$srv['port'],$srv['postf'],$srv['mailuser'],$srv['kennw'],$srv['proto'],$srv['ssl']);
    $f    = fopen('/tmp/cmb.log','w'); 
    $mb   = false;
    $mb   = chkMB($mbox,$srv['msrv'],$srv['postf'],$srv['postf2']);
    if ( $srv['postf2'] != '' AND $mb ) { 
        $rc = imap_mail_move ($mbox,$mail,strtoupper($srv['postf']).'.'.$srv['postf2']);
                               fputs($f,strtoupper($srv['postf']).'.'.$srv['postf2']."!$mail!\n");
        if ( $rc ) $rc = imap_expunge($mbox);
        fputs($f,"!$rc!\n");
    } else { 
        $tmpmail = getCRMdefault();
        fputs($f,$tmpmail['MailDelete']."$mail\n");
        $rc = mail_SetFlag($mbox,$mail,$tmpmail['MailDelete']); 
    };
    mail_close($mbox);
}

/****************************************************
* delMail
* in: mail,id = int
* out:
* eine Mail löschen marmieren oder gelöscht markieren
*****************************************************/
function delMail($mail) {
    $tmpmail = getCRMdefault();
    $srv     = getUsrMailData();
    $mbox    = mail_login($srv['msrv'],$srv['port'],$srv['postf'],$srv['mailuser'],$srv['kennw'],$srv['proto'],$srv['ssl']);
    if ( $tmpmail['MailDelete'] == 'Deleted' ) {
        if ( mail_dele($mbox,$mail) ) { $rc = true; } else { $rc = -10; };
    } else {
        if ( mail_SetFlag($mbox,$mail,$tmpmail['MailDelete']) ) { $rc = true; } else { $rc = -11; } ;
    }
    mail_close($mbox);
    return $rc;
}

/****************************************************
* getIntervall
* in: id = int
* out: rs = int
* Userspezifischen Updateintervall holen
*****************************************************/
function getIntervall($id) {
    $sql = "SELECT * FROM employee WHERE id=$id";
    $rs  = $GLOBALS['db']->getAll($sql);
    if( !$rs ) {
        return 60;
    }
    if ( $rs[0]['interv'] ) { return $rs[0]['interv']; }
    else { return 60; }
}

/****************************************************
* getAllMails
* in: sw = string
* out: rs = array(Felder der db)
* hole alle eMails
*****************************************************/
function getAllMails($suche) {
    //Benutzer
    //$sql1 = "SELECT name,'E' as src,id,email FROM employee WHERE upper(email) like '".$_SESSION['pre'].strtoupper($suche)."%' and email <> '' ORDER BY email";
    //crmemployee key=email -> val
    $sql1 = "SELECT name,'E' as src,id,val as email FROM crmemployee LEFT JOIN employee on id = manid WHERE key = 'email' AND val ilike  '".$_SESSION['pre']."$suche%'";
    $rs1  = $GLOBALS['db']->getAll($sql1);
    //Kunden
    $sql2 = "SELECT '' as name,'C' as src,id,email FROM customer WHERE upper(email) like '".$_SESSION['pre'].strtoupper($suche)."%' and email <> '' ORDER BY email";
    $rs2  = $GLOBALS['db']->getAll($sql2);
    //Personen
    $sql3 = "SELECT cp_name as name,'K' as src,cp_id as id,cp_email as email FROM contacts WHERE upper(cp_email) like '".$_SESSION['pre'].strtoupper($suche)."%' and cp_email <> '' ORDER BY cp_email";
    $rs3  = $GLOBALS['db']->getAll($sql3);
    //Abweichende Anschr.
    $sql4 = "SELECT '' as name,'S' as src,trans_id as id,shiptoemail as email FROM shipto WHERE upper(shiptoemail) like '".$_SESSION['pre'].strtoupper($suche)."%' and shiptoemail <> ''  ORDER BY shiptoemail";
    $rs4  = $GLOBALS['db']->getAll($sql4);
    //Lieferanten
    $sql5 = "SELECT '' as name,'V' as src,id,email FROM vendor WHERE upper(email) like '".$_SESSION['pre'].strtoupper($suche)."%' and email <> '' ORDER BY email";
    $rs5  = $GLOBALS['db']->getAll($sql5);
    $rs   = array_merge($rs2,$rs3,$rs5,$rs4,$rs1);
    usort($rs,"eMailSort");
    return $rs;
}

/****************************************************
* eMailSort
* in: a,b = array
* out: array
* Sortierfunktion für eMail-Adressen
*****************************************************/
function eMailSort($a,$b) {
    if ( $a['name'] == $b['name'] ) return 0;
    return ($a['name'] < $b['name']) ? -1 : 1;
}

/****************************************************
* chkMailAdr
* in: mailadr = string
* out: string
* Mailaddr. auf Gültigkeit prüfen
*****************************************************/
function chkMailAdr ($mailadr) {
    if ( strpos($mailadr,',')>0 ) {
        $tmp = explode(',',$mailadr);
    }else {
        $tmp = array($mailadr);
    }
    foreach( $tmp as $mailadr ) {
        $syntax = preg_match('/^(.*<)?([_A-Z0-9-]+[\._A-Z0-9-]*@[\.A-Z0-9-]+\.[A-Z]{2,4})>?$/i',trim($mailadr),$x);
        //$syntax = preg_match('/^(<[^>]*>?[\s]?)?([_A-Z0-9-]+[\._A-Z0-9-]*@[\.A-Z0-9-]+\.[A-Z]{2,4})$/i',trim($mailadr),$x);
        if ( $syntax ) {
            list($user, $host) = explode('@', array_pop($x));
            $dns = (checkdnsrr($host, 'MX') or checkdnsrr($host, 'A'));
            if ( !$dns ) return  'DNS-Fehler';
        } else {
            return 'Syntax-Fehler';
        }
    }
    return 'ok';
}

/****************************************************
* getReJahr
* in: fid = int
* out: rechng = array
* Rechnungsdaten je Monat
*****************************************************/
function getReJahr($fid,$jahr,$liefer=false,$user=false) {
    $lastYearV = date('Y-m-d',mktime(0, 0, 0, date('m')+1, 1, $jahr-1));
    $lastYearB = date('Y-m-d',mktime(0, 0, 0, date('m'), 31, $jahr));
    $sea = '';
    if ( $user ) {
        $sea = ' and salesman_id = '.$fid.' ';
    } else if ( $_SESSION['sales_edit_all'] == 'f' ) {
        $sea = sprintf(" and (employee_id = %d or salesman_id = %d) ", $_SESSION['loginCRM'], $_SESSION['loginCRM']);
    }
    $sql  = "SELECT sum(netamount),count(*),substr(cast(transdate as text),1,4)||substr(cast(transdate as text),6,2) as month,'%s' as tab FROM %s ";
    $sql .= "WHERE %s=%d and transdate >= '%s' and transdate <= '%s' %s group by month ";

    if ( $liefer ) {
        $bezug = ($user)?'employee_id':'vendor_id';
        $rs2   = $GLOBALS['db']->getAll(sprintf($sql,'A','oe',$bezug,$fid,$lastYearV,$lastYearB,$sea));
        $sql   = sprintf($sql,'R','ap',$bezug,$fid,$lastYearV,$lastYearB,$sea);
        $curr  = getCurrCompany($fid,'V');
        $curr  = $curr['name'];
    } else {
        $bezug = ($user)?'employee_id':'customer_id';
        $rs2   = $GLOBALS['db']->getAll(sprintf($sql,'A','oe',$bezug,$fid,$lastYearV,$lastYearB,$sea));
        $sql   = sprintf($sql,'R','ar',$bezug,$fid,$lastYearV,$lastYearB,$sea);
        $curr  = getCurrCompany($fid,'C');
        $curr  = $curr['name'];
    };
    $rs1    = $GLOBALS['db']->getAll($sql);
    $rs     = array_merge($rs1,$rs2);
    $rechng = array();
    for ( $i=11; $i>=0; $i-- ) {
        $dat = date('Ym',mktime(0, 0, 0, date('m')-$i, 1 , $jahr));
        $rechng[$dat] = array('summe'=>0,'count'=>0,'curr'=>$curr);
    }
    $rechng['Jahr  '] = array('summe'=>0,'count'=>0,'curr'=>$curr);
    // unterschiedliche Währungen sind noch nicht berücksichtigt. Summe stimmt aber.
    if ( $rs ) foreach ( $rs as $re ){
        if ( $re['tab']=='R' ) {
        $m = $re['month'];
        $rechng[$m]['summe'] = $re['sum'];
        $rechng[$m]['count'] = $re['count'];
        $rechng['Jahr  ']['summe'] += $re['sum'];
        $rechng['Jahr  ']['count']++;
        }
    }
    return $rechng;
}

/****************************************************
* getAngebJahr
* in: fid = int
* out: rechng = array
* Angebotsdaten je Monat
*****************************************************/
function getAngebJahr($fid,$jahr,$liefer=false,$user=false) {
    $lastYearV = date('Y-m-d',mktime(0, 0, 0, date('m'), 1, $jahr-1));
    $lastYearB = date('Y-m-d',mktime(0, 0, 0, date('m')+1, -1, $jahr));
    $sea = '';
    if ( $user ) {
        $sea = ' and salesman_id = '.$fid.' ';
    } else if ( $_SESSION['sales_edit_all'] == 'f' ) {
        $sea = sprintf(' and (employee_id = %d or salesman_id = %d) ', $_SESSION['loginCRM'], $_SESSION['loginCRM']);
    }
    $sql  = 'SELECT sum(netamount),count(*),substr(cast(transdate as text),1,4)||substr(cast(transdate as text),6,2) as month FROM oe ';
    $sql .= "WHERE %s=%d and quotation = 't' and transdate >= '%s' and transdate <= '%s' %s group by month ";
    if ( $liefer ) {
        $bezug = ($user)?'employee_id':'vendor_id';
        $curr  = getCurrCompany($fid,'V');
        $curr  = $curr['name'];
    } else {
        $bezug = ($user)?'employee_id':'customer_id';
        $curr  = getCurrCompany($fid,'C');
        $curr  = $curr['name'];
    }
    $rs     = $GLOBALS['db']->getAll(sprintf($sql,$bezug,$fid,$lastYearV,$lastYearB,$sea));
    $rechng = array();
    for ( $i=11; $i>=0; $i-- ) {
        $dat = date('Ym',mktime(0, 0, 0, date('m')-$i, 1, date('Y')));
        $rechng[$dat] = array('summe'=>0,'count'=>0,'curr'=>$curr);
    }
    $rechng['Jahr  '] = array('summe'=>0,'count'=>0,'curr'=>$curr);
    if ( $rs ) foreach ( $rs as $re ){
        $m = $re['month'];
        $rechng[$m]['summe'] = $re['sum'];
        $rechng[$m]['count'] = $re['count'];
        $rechng['Jahr  ']['summe'] += $re['sum'];
        $rechng['Jahr  ']['count']++;
    }
    return $rechng;
}

/****************************************************
* getCurr
* out: curr = String
*****************************************************/
function getCurr($ID=False) {
    $sql = 'SELECT name,id FROM currencies WHERE id = (SELECT currency_id FROM defaults)';
    $rsc = $GLOBALS['db']->getOne($sql);
    if ( $ID ) {
       return  $rsc['id'];
    } else {
       return  $rsc['name'];
    }
}
function getCurrCompany($ID,$Q='C') {
    $sql  = 'SELECT name,id FROM currencies WHERE id = (SELECT currency_id FROM ';
    if ( $Q == 'C' ) {
        $src = 'customer';
    } else if ( $Q == 'V' ) {
        $src = 'vendor';
    } else if ( $Q == 'I' ) {
        $src = 'ar';
    } else if ( $Q == 'E' ) {
        $src = 'ap';
    } else if ( $Q == 'O' ) {
        $src = 'oe';
    };
    $sql .= "$src WHERE id = $ID)";
    $rs   = $GLOBALS['db']->getOne($sql);
    return $rs;
}

/****************************************************
* getReMonat
* in: fid = int
* jahr = char(4)
* monat = char(2)
* liefern = boolean
* out: rs = array
* Rechnungsdaten für den Monat
*****************************************************/
function getReMonat($fid,$jahr,$monat,$liefer=false){
    if ( $_SESSION['sales_edit_all'] == 'f' ) $sea = sprintf(" and (employee_id = %d or salesman_id = %d) ", $_SESSION['loginCRM'], $_SESSION['loginCRM']);
        if ( $monat=='00' ) {
            $next  = ($jahr+1).'-01-01';
            $monat = '01';
        } else {
            $next = ($monat<12)?"$jahr-".($monat+1).'-01':($jahr+1).'-01-01';
        }
        if ( $liefer ) {
                $sql1 = "SELECT * FROM ap WHERE vendor_id=$fid and transdate >= '$jahr-$monat-01' and transdate < '$next' $sea ORDER BY transdate desc";
                $sql2 = "SELECT * FROM oe WHERE vendor_id=$fid and transdate >= '$jahr-$monat-01' and transdate < '$next' $sea and closed = 'f' ORDER BY transdate desc";
        } else {
                $sql1 = "SELECT * FROM ar WHERE customer_id=$fid and transdate >= '$jahr-$monat-01' and transdate < '$next' $sea ORDER BY transdate desc";
                $sql2 = "SELECT * FROM oe WHERE customer_id=$fid and transdate >= '$jahr-$monat-01' and transdate < '$next' $sea ORDER BY transdate desc";
        };
    $rs2 = $GLOBALS['db']->getAll($sql2);
    $rs1 = $GLOBALS['db']->getAll($sql1);
    $rs  = array_merge($rs1,$rs2);
    usort($rs,'cmp');
    return $rs;
}

/****************************************************
* cmp
* in: $a,$b = datum
* out: 0,1,-1
* Funktion für Usort
*****************************************************/
function cmp ($a, $b) {
    return strcmp($b['transdate'],$a['transdate']);
    //if ($a['transdate'] == $b['transdate']) return 0;
    //return ($a['transdate'] < $b['transdate']) ? -1 : 1;
}

/****************************************************
* getRechParts
* in: $id = int
*     $tab = char(1)
* out: $rs = array
* Reschnungspositionen holen
*****************************************************/
function getRechParts($id,$tab) {
    if ( $tab=='R' || $tab=='V' ) {
        $sql  = "SELECT *,I.sellprice as endprice,I.fxsellprice as orgprice,I.discount,I.description as artikel ";
        $sql .= "FROM invoice I LEFT JOIN parts P on P.id=I.parts_id WHERE trans_id=$id";
        if ( $tab=='V' ) {
            $sql1 = "SELECT amount as brutto, netamount as netto,transdate, intnotes, notes,quonumber,ordnumber,currency_id FROM ap WHERE id=$id";
        } else {
            $sql1 = "SELECT amount as brutto, netamount as netto,transdate, intnotes, notes,quonumber,ordnumber,currency_id FROM ar WHERE id=$id";
        }
    } else {
        $sql   = "SELECT *,O.sellprice as endprice,O.sellprice as orgprice,O.discount,O.description as artikel ";
        $sql  .= "FROM orderitems O LEFT JOIN parts P on P.id=O.parts_id WHERE trans_id=$id";
        $sql1  = "SELECT amount as brutto, netamount as netto,transdate, intnotes, notes, quotation,quonumber,ordnumber,currency_id FROM oe WHERE id=$id";
    }
    $rs = $GLOBALS['db']->getAll($sql);
    if( !$rs ) {
        return false;
    } else {
        $rs2     = $GLOBALS['db']->getAll($sql1);
        $data[0] = $rs;
        if( $rs2 ) {
            $data[1] = $rs2[0];
        }
        return $data;
    }
}

/****************************************************
* getRechAdr
* in: $id = int
*     $tab = char(1)
* out: $rs = array
* Reschnungadress holen
*****************************************************/
function getRechAdr($id,$tab) {
    if ( $tab=='R' || $tab=='V' ) {
        if ( $tab=='R' ) { $tab='ar'; $firma='customer'; } else { $tab='ap'; $firma='vendor'; };
        $rs = $GLOBALS['db']->getAll("SELECT shipto_id FROM $tab WHERE id=$id");
        if ( $rs[0]['shipto_id']>0 ) {
             $sql  = "SELECT F.*,S.* FROM $tab A LEFT JOIN shipto S on S.shipto_id=A.shipto_id, $firma F WHERE ";
             $sql .= "A.id=$id and F.id=A.".$firma."_id";
        } else {
            $rs = $GLOBALS['db']->getAll("SELECT * FROM shipto WHERE trans_id=$id and module='".strtoupper($tab)."'");
            if ( $rs[0]['shipto_id']>0 ) {
                 $sql  = "SELECT F.*,S.* FROM $tab A LEFT JOIN shipto S on S.trans_id=A.id, $firma F WHERE ";
                 $sql .= "A.id=$id and F.id=A.".$firma."_id and S.module='".strtoupper($tab)."'";
            } else {
                $sql = "SELECT F.* FROM $tab A LEFT JOIN $firma F on F.id=A.".$firma."_id WHERE A.id=$id";
            }
        }
        $rs = $GLOBALS['db']->getAll($sql);
        if( $rs ) { return $rs[0]; } else { return false;    };
    } else {
        $firma = "customer";
        $rs    = $GLOBALS['db']->getAll("SELECT shipto_id FROM oe WHERE id=$id");
        if ( $rs[0]['shipto_id']>0 ) {
            $sql  = "SELECT F.*,S.* FROM oe O LEFT JOIN shipto S on S.shipto_id=O.shipto_id, $firma F WHERE ";
            $sql .= "O.id=$id and C.id=O.".$firma."_id";
        } else {
            $rs = $GLOBALS['db']->getAll("SELECT * FROM shipto WHERE trans_id=$id and module='OE'");
            if ( $rs[0]['shipto_id']>0 ) {
                $sql  = "SELECT F.*,S.* FROM oe O LEFT JOIN shipto S on S.trans_id=O.id, $firma F WHERE ";
                $sql .= "O.id=$id and F.id=O.".$firma."_id and S.module='OE'";
            } else {
                $sql = "SELECT F.* FROM oe O LEFT JOIN $firma F on F.id=O.".$firma."_id WHERE O.id=$id";
            }
        }
        $rs = $GLOBALS['db']->getAll($sql);
        if( $rs ) { return $rs[0]; } else { return false;    };
    }
}

/****************************************************
* getUsrNamen
* in: user = string
* out: array
* 
*****************************************************/
function getUsrNamen($user) {
    if ($user) foreach ($user as $row) {
             if (substr($row,0,1)=='G') {$grp.=substr($row,1).',';}
        else if (substr($row,0,1)=='E') {$empl.=substr($row,1).',';}
        else if (substr($row,0,1)=='V') {$ven.=substr($row,1).',';}
        else if (substr($row,0,1)=='C') {$cust.=substr($row,1).',';}
        else if (substr($row,0,1)=='P') {$cont.=substr($row,1).',';};
    }
    if ($grp)  $sql[]="SELECT 'G'||grpid as id,grpname as name FROM gruppenname WHERE  grpid in (".substr($grp,0,-1).")";
    if ($empl) $sql[]="SELECT 'E'||id as id,name,login FROM employee WHERE  id in (".substr($empl,0,-1).")";
    if ($ven)  $sql[]="SELECT 'V'||id as id,name FROM vendor WHERE  id in (".substr($ven,0,-1).")";
    if ($cust) $sql[]="SELECT 'C'||id as id,name FROM customer WHERE  id in (".substr($cust,0,-1).")";
    if ($cont) $sql[]="SELECT 'P'||cp_id as id,cp_name as name FROM contacts WHERE cp_id in (".substr($cont,0,-1).")";
    $data=false;
    if ($sql) foreach ($sql as $row) {
        $rs=$GLOBALS['db']->getAll($row);
        if($rs) {
            if (empty($data)) {$data=$rs;}
            else {$data=array_merge($data,$rs);};
        }
    }
    return $data;
}

/****************************************************
* advent
* in: year = int
* out: int
* 
*****************************************************/
function advent($year= -1) {
    if ( $year == -1 ) $year = date('Y');
    $s = mktime(0, 0, 0, 11, 26, $year);
    while ( 0 != date('w', $s) ) $s+= 86400;
    return $s;
}

/****************************************************
* eastern
* in: year = int
* out: int
* 
*****************************************************/
function eastern($year= -1) {
      if ( $year == -1 ) $year= date('Y');
      // the Golden number
      $golden = ($year % 19) + 1;
      // the "Domincal number"
      $dom = ($year + (int)($year / 4) - (int)($year / 100) + (int)($year / 400)) % 7;
      if ( $dom < 0 ) $dom+= 7;
      // the solar and lunar corrections
      $solar = ($year - 1600) / 100 - ($year - 1600) / 400;
      $lunar = ((($year - 1400) / 100) * 8) / 25;
      // uncorrected date of the Paschal full moon
      $pfm = (3 - (11 * $golden) + $solar - $lunar) % 30;
      if ( $pfm < 0 ) $pfm += 30;
      // corrected date of the Paschal full moon
      // days after 21st March
      if ( ($pfm == 29 ) || ($pfm == 28 && $golden > 11)) {
        $pfm--;
      }
      $tmp = (4 - $pfm - $dom) % 7;
      if ( $tmp < 0 ) $tmp += 7;
      // Easter as the number of days after 21st March */
      $easter = $pfm + $tmp + 1;
      if ( $easter < 11 ) {
        $m = 3;
        $d = $easter + 21;
      } else {
        $m = 4;
        $d = $easter - 10;
      }
      return mktime(0, 0, 0, $m, $d, $year, -1);
}

/****************************************************
* ostern
* in: intYear = int
* out:  int
* 
*****************************************************/
function ostern($intYear) {
    $a = 0; $b = 0; $c = 0; $d = 0; $e = 0;
    $intDay = 0; $intMonth = 0;
    $a = $intYear % 19;
    $b = $intYear % 4;
    $c = $intYear % 7;
    $d = (19 * $a + 24) % 30;
    $e = (2 * $b + 4 * $c + 6 * $d + 5) % 7;
    $intDay   = 22 + $d + $e;
    $intMonth = 3;
    if( $intDay > 31 ) {
        $intDay = $d + $e - 9;
        $intMonth = 4;
    } else if( $intDay == 26 && $intMonth == 4 )
        $intDay = 19;
    else if( (($intDay == 25 && $intMonth == 4) && ($d == 28 && $e == 6)) && $a > 10 )
       $intDay = 18;
    return mktime(0,0,0,$intMonth,$intDay,$intYear);
}

/****************************************************
* feiertage
* in:  jahr = int
* out: array
* 
*****************************************************/
function feiertage($jahr) {
    $holiday = array();
    $CAL_SEC_DAY=86400;
    $easter = eastern($jahr);
    $advent = advent($jahr);
    // Feste Feiertage
    $holiday[mktime(0, 0, 0, 1,   1, $jahr)]= 'G,Neujahr';
    $holiday[mktime(0, 0, 0, 1,   6, $jahr)]= 'R,Heilige 3 K&ouml;nige BW,BY,ST';
    $holiday[mktime(0, 0, 0, 5,   1, $jahr)]= 'G,Tag der Arbeit';
    $holiday[mktime(0, 0, 0, 8,  15, $jahr)]= 'R,Maria Himmelfahrt BY,SL';
    $holiday[mktime(0, 0, 0, 10,  3, $jahr)]= 'G,Tag der deutschen Einheit';
    $holiday[mktime(0, 0, 0, 10, 31, $jahr)]= 'R,Reformationstag BB,MV,SN,ST,TH';
    $holiday[mktime(0, 0, 0, 11,  1, $jahr)]= 'R,Allerheiligen BW,BY,NW,RP,SL';
    $holiday[mktime(0, 0, 0, 12, 24, $jahr)]= 'F,Heiligabend';
    $holiday[mktime(0, 0, 0, 12, 25, $jahr)]= 'G,1. Weihnachtsfeiertag';
    $holiday[mktime(0, 0, 0, 12, 26, $jahr)]= 'G,2. Weihnachtsfeiertag';
    $holiday[mktime(0, 0, 0, 12, 31, $jahr)]= 'F,Sylvester';

    // Bewegliche Feiertage, von Ostern abhängig
    $holiday[$easter - $CAL_SEC_DAY * 48]= 'R,Rosenmontag';
    $holiday[$easter - $CAL_SEC_DAY * 46]= 'R,Aschermittwoch';
    $holiday[$easter - $CAL_SEC_DAY *  2]= 'G,Karfreitag';
    $holiday[$easter]=                     'F,Ostersonntag';
    $holiday[$easter + $CAL_SEC_DAY *  1]= 'G,Ostermontag';
    $holiday[$easter + $CAL_SEC_DAY * 39]= 'G,Himmelfahrt';
    $holiday[$easter + $CAL_SEC_DAY * 49]= 'F,Pfingstsonntag';
    $holiday[$easter + $CAL_SEC_DAY * 50]= 'G,Pfingstmontag';
    $holiday[$easter + $CAL_SEC_DAY * 60]= 'R,Fronleichnam BW,BY,HE,NW,RP,SL';

    // Bewegliche Feiertage, vom ersten Advent abhängig
    $holiday[$advent]=                      'F,1. Advent';
    $holiday[$advent + $CAL_SEC_DAY *  7]=  'F,2. Advent';
    $holiday[$advent + $CAL_SEC_DAY * 14]=  'F,3. Advent';
    $holiday[$advent + $CAL_SEC_DAY * 21]=  'F,4. Advent';
    $holiday[$advent - $CAL_SEC_DAY * 35]=  'F,Volkstrauertag';
    $holiday[$advent - $CAL_SEC_DAY * 32]=  'R,Bu&szlig;- und Bettag SN';
    $holiday[$advent - $CAL_SEC_DAY * 28]=  'F,Totensonntag';
    return $holiday;
}

/****************************************************
* getCustMsg
* in: id = int, all = boolean
* out: string
* 
*****************************************************/
function getCustMsg($id,$all=false) {
    if ( !$all ) { $where="fid=$id and akt='t'"; }
    else {
        if ( $id ) {$where="fid=$id"; }
        else { return false; }
    }
    $sql = 'SELECT * FROM custmsg WHERE '.$where ;
    $rs  = $GLOBALS['db']->getAll($sql);
    if(!$rs) {
        $sql  = "SELECT id,cause,coalesce(finishdate,'9999-12-31 00:00:00') as finishdate  ";
        $sql .= "FROM wiedervorlage WHERE status > '0' and (kontaktid = $id or ";
        $sql .= "kontaktid in (SELECT cp_id FROM contacts WHERE cp_cv_id = $id)) ";
        $sql .= " ORDER BY finishdate,initdate";
        $rs   = $GLOBALS['db']->getAll($sql);
        if ( $rs ) {
            $cnt = count($rs);
            $msg = "<font color='red'>.:wv:. ($cnt) ".$rs[0]['cause'];
            if ( $rs[0]['finishdate'][4] != "9999" ) $msg .= " &gt;&gt; ".db2date($rs[0]['finishdate'],0,10);
            return $msg.'</font>';
        } else {
            return false;
        }
    } else {
        if ( $all==1 ) {
            return $rs;
        } else if ( $all>1 ) {
            return $rs[0];
        } else {
            if ( $rs[0] ) {
                switch ( $rs[0]['prio'] ) {
                    case 1  : $atre="<font color='red'><blink>"; $atra="</blink></font>";break;
                    case 2  : $atre="<blink>"; $atra="</blink>"; break;
                    case 3  : $atre=''; $atra=''; break;
                    default : $atre=''; $atra='';
                }
                $msg = $atre.$rs[0]['msg'].$atra;
            }
        }
        return $msg;
    }
}

/****************************************************
* saveCustMsg
* in:  data = array
* out: 
* 
*****************************************************/
function saveCustMsg($data) {
    if ( !$data['cp_cv_id'] ) return false;
    $sql = "delete FROM custmsg WHERE fid=".$data['cp_cv_id'];
    $rc  = $GLOBALS['db']->query($sql);
    if ( $rc ) for( $i=1; $i<=3; $i++ ) {
        if ( $data['message$i'] ) { 
            $sql  = "insert into custmsg (msg,prio,fid,uid,akt) values (";
            $sql .= "'".$data['message$i']."',$i,".$data['cp_cv_id'].",".$_SESSION['loginCRM'].",".(($data['prio']==$i)?"'t'":"'f'").")";
            $rc   = $GLOBALS['db']->query($sql);
        }
    }
}

/****************************************************
* getOneLable
* in: format = int
* out: array
* 
*****************************************************/
function getOneLable($format) {
    $lab = false;
    $sql = "SELECT * FROM labels WHERE id=".$format;
    $rs  = $GLOBALS['db']->getOne($sql);
    if ( $rs ) {
        $sql = "SELECT * FROM labeltxt WHERE lid=".$rs['id'];
        $rs2 = $GLOBALS['db']->getAll($sql);
        $rs['Text'] = $rs2;
    }
    return $rs;
}

/****************************************************
* getLableNames
* in: 
* out: array
* 
*****************************************************/
function getLableNames() {   
    $sql = 'SELECT id,name FROM labels ORDER BY name';
    $rs  = $GLOBALS['db']->getAll($sql);
    if ( !$rs ) $rs[] = array('id'=>0,'name'=>'------');
    return $rs;
}

/****************************************************
* mknewLable
* in: id = int
* out: int
* 
*****************************************************/
function mknewLable($id=0) {
    $newID = uniqid (rand());
    $sql   = "insert into labels (name) values ('$newID')";
    $rc    = $GLOBALS['db']->query($sql);
    if ( $rc ) {
        $sql = "SELECT id FROM labels WHERE name = '$newID'";
        $rs  = $GLOBALS['db']->getAll($sql);
        if ( $rs ) {
            $id = $rs[0]['id'];
        } else {
            $id = false;
        }
    } else {
        $id = false;
    }
    return $id;
}

/****************************************************
* insLable
* in: data = array
* out: int
* 
*****************************************************/
function insLable($data) {
    $data['id']   = mknewLable();
    $data['name'] = $data['custname'];
    $data['cust'] = "C";
    return updLable($data);
}

/****************************************************
* updLable
* in: data = array
* out: int
* 
*****************************************************/
function updLable($data) {
    
    $data['fontsize'] = '10';
    $felder = array('name','cust','papersize','metric','marginleft','margintop','nx','ny','spacex','spacey','width','height','fontsize');
    $tmp    = 'update labels set ';
    foreach ( $felder as $feld ) {
        $tmp .= $feld."='".$data[$feld]."',";
    }
    $sql = substr($tmp,0,-1).' WHERE id='.$data['id'];
    if ( $data['cust']=='C' ) {
        $rc = $GLOBALS['db']->query($sql);
        $i  = 0;
        $GLOBALS['db']->query('delete FROM labeltxt WHERE lid='.$data['id']);
        if( $data['Text'] ) foreach( $data['Text'] as $row ) {
            $sql = sprintf("insert into labeltxt (lid,font,zeile) values (%d,%d,'%s')",$data['id'],$data['Schrift'][$i],$row);
            $GLOBALS['db']->query($sql);
            $i++;
        }
    } else {
        return false;
    }
    return $data['id'];
}

/****************************************************
* getWPath
* in: id = int
* out: string
* 
*****************************************************/
function getWPath($id) {
    $sql = 'SELECT * FROM wissencategorie WHERE id = '.$id;
    $rs  = $GLOBALS['db']->getAll($sql);
    if ( $rs ) {
        $pfad = $rs[0]['id'];
        if ( $rs[0]['hauptgruppe']==0 ) return $pfad;
    }
    while ( $rs and $rs[0]['hauptgruppe']>0 ) {
        $sql = "SELECT * FROM wissencategorie WHERE id = ".$rs[0]['hauptgruppe'];
        $rs  = $GLOBALS['db']->getAll($sql);
        if ( $rs ) $pfad.=','.$rs[0]['id'];
    }
    return $pfad;
}

/****************************************************
* getWCategorie
* in: kdhelp = boolean
* out: array
* 
*****************************************************/
function getWCategorie($kdhelp=false) {
    if ( $kdhelp ) { 
        $sql = 'SELECT * FROM wissencategorie WHERE kdhelp is true ORDER BY name';
    } else {
        $sql = 'SELECT * FROM wissencategorie ORDER BY hauptgruppe,name';
    }
    $rs   = $GLOBALS['db']->getAll($sql);
    $data = array();
    if ( $rs ) { 
        if ( $kdhelp ) if ( count($rs)>0 ) { return $rs; } else { return false; };
        foreach ( $rs as $row ) {
            $data[$row['hauptgruppe']][] = array('name'=>$row['name'],'id'=>$row['id'],'kdhelp'=>$row['kdhelp']);
        }
        return $data;
    } else {
        return false;
    }
}

/****************************************************
* insWCategorie
* in: data = array
* out: int
* 
*****************************************************/
function insWCategorie($data) {
    if ( !$data['cid'] ) {
        $newID = uniqid (rand());
        $sql   = "insert into wissencategorie (name,kdhelp) values ('$newID','".$data['kdhelp']."')";
        $rc    = $GLOBALS['db']->query($sql);
        $sql   = "SELECT * FROM wissencategorie WHERE name='$newID'";
        $rs    = $GLOBALS['db']->getOne($sql);
        if( !$rs ) {
            return false;
        } else {
            $id = $rs['id'];
        }
    } else {
        $id = $data['cid'];
    }
    if ( $kat == '' ) {
        $kat = 0;
    } 
    $name = html_entity_decode( $data['catname'] );
    if ( $GLOBALS['db']->update('wissencategorie',
                      array( 'name', 'hauptgruppe', 'kdhelp' ),
                      array( 'name'=>$name, 'hauptgruppe'=>$data['hg'], 'kdhelp'=>$data['kdhelp'] ),
                     'id='.$id ) ) {
         return $id;
     } else {  return false; };
}

/****************************************************
* getOneWCategorie
* in: id = int
* out: array
* 
*****************************************************/
function getOneWCategorie($id) {
    $sql = 'SELECT * FROM  wissencategorie WHERE id = '.$id;
    $rs  = $GLOBALS['db']->getAll($sql);
    return $rs[0];
}

/****************************************************
* getWContent
* in: id = int
* out: array
* 
*****************************************************/
function getWContent($id) {
    $rechte = berechtigung();
    $sql    = "SELECT O.*,A.name,E.login FROM wissencontent O LEFT JOIN wissencategorie A on A.id=O.categorie ";
    $sql   .= "LEFT JOIN employee E on O.employee=E.id WHERE categorie = $id and $rechte ORDER BY initdate desc limit 1";
    $rs     = $GLOBALS['db']->getOne($sql);
    if ( $rs ) {
        return $rs;
    } else {
        return false;
    }
}

/****************************************************
* insWContent
* in: data = array
* out: int
* 
*****************************************************/
function insWContent($data) {
    $kat  = $data['kat'];
    $own  = ($data['owener'] > 0)?$data['owener']:0;
    $rc   =  $GLOBALS['db']->begin();
    $sql  = "SELECT coalesce(max(version),0)+1 as ver FROM wissencontent WHERE categorie = $kat";
    $rs   = $GLOBALS['db']->getOne($sql);
    $vers = $rs['ver'];
    $rc   = $GLOBALS['db']->insert('wissencontent',
                      array('initdate','content','employee','categorie','version','owener'),
                      array('now()',trim($data['content']),$_SESSION['loginCRM'],$kat,$vers,$own));
    if ( $rc ) $GLOBALS['db']->commit();
    else       $GLOBALS['db']->rollback();
    return $rc;
}


/****************************************************
* getWHistory
* in: id = int
* out: array
* 
*****************************************************/
function getWHistory($id) {
    $rechte = berechtigung();
    $sql    = "SELECT W.*,E.login FROM  wissencontent W LEFT JOIN employee E on W.employee=E.id WHERE  $rechte and categorie = $id ORDER BY initdate";
    $rs     = $GLOBALS['db']->getAll($sql);
    return $rs;
}
/**
 * TODO: short description.
 * 
 * @param mixed $wort 
 * @param mixed $kat  
 * 
 * @return TODO
 */

/****************************************************
* suchWDB
* in: wort = string, kat = int
* out: array
* 
*****************************************************/
function suchWDB($wort,$kat) {
    $rechte = berechtigung();
    $sql    = "SELECT distinct WK.* as cid FROM wissencontent WC LEFT JOIN wissencategorie WK on WC.categorie=WK.id WHERE $rechte and ";
    if ( $wort != '' ) {
        $sql .= " content ilike '%".trim($wort)."%' ";
    } else if ( $kat != '' ) {
        $sql .= " categorie = $kat ";
    } else {
        return false;
    }
    $rs = $GLOBALS['db']->getAll($sql);
    return $rs;
}
/****************************************************
* diff
* in: text1,text2 = string
* out: array
*
* Geschrieben von TBT-Moderator php-resource.de am 28-11-2002
*****************************************************/
function diff($text1,$text2) {
    $text1  = preg_replace('/(<[a-z]+[a-z]*[^>]*?>)/e',"ereg_replace(' ','°°','\\1')",$text1);
    $text2  = preg_replace('/(<[a-z]+[a-z]*[^>]*?>)/e',"ereg_replace(' ','°°','\\1')",$text2);
    $array1 = explode(' ', str_replace(array('   ','    ','  ', "\r", "\n"), array(' ',' ',' ', '', ''), $text1));
    $array2 = explode(' ', str_replace(array('   ','    ','  ', "\r", "\n"), array(' ',' ',' ', '', ''), $text2));
    $max1   = count($array1);
    $max2   = count($array2);
    $start1 = $start2 = 0;
    $jump1  = $jump2 = 0;
    while( $start1 < $max1 && $start2 < $max2 ){
        $pos11 = $pos12 = $start1;
        $pos21 = $pos22 = $start2;
        $diff2 = 0; 
        // schaukel 1. Array hoch
        while( $pos11 < $max1 && $array1[$pos11] != $array2[$pos21] ){
            ++$pos11;
        }
        // Ende des 1 Arrays erreicht ?
        if( $pos11 == $max1 ){
            $start2++;
            continue;
        } 
        // Gegenschaukel wenn übersprunge Wörter
        if( ($diff1 = $pos11 - $pos21) > 1 ){
            while( $pos22 < $max2 && $array1[$pos12] != $array2[$pos22] ){
                ++$pos22;
            }
            $diff2 = $pos22 - $pos12 + $jump2;
        } 
        // Ende des 2 Arrays erreicht ?
        if ( $pos22 == $max2 ){
            $start1++;
            continue;
        }
        $diff1 += $jump1; 
        // Auswertung der Schaukel
        if ( $diff1 >= $diff2 && $diff2 ){
            unset($array1[$pos12], $array2[$pos22]);
            $start1 = $pos12 + 1;
            $start2 = $pos22 + 1;
            $jump2  = $diff2;
        }else{
            unset($array1[$pos11], $array2[$pos21]);
            $start1 = $pos11 + 1;
            $start2 = $pos21 + 1;
            $jump1  = $diff1;
        }
    }
    $safe1 = explode(' ', str_replace(array('   ','    ','  ', "\r", "\n"), array(' ',' ',' ', '', ''), $text1));
    reset($array1);
    while( list($key1,) = each($array1) ){
        if ( preg_match('/<\/?([ou]l|li|img|input)/i',$safe1[$key1]) ) {
            $safe1[$key1] = '[_' . $safe1[$key1] . '_]';
        } else {
            $safe1[$key1] = "<span class='diff1'>" . $safe1[$key1] . "</span>";
        }
    }
    $safe2 = explode(' ', str_replace(array('   ','    ','  ', "\r", "\n"), array(' ',' ',' ', '', ''), $text2));
    reset($array2);
    while( list($key2,) = each($array2) ){
        $safe2[$key2] = "<span class='diff2'>" . $safe2[$key2] . '</span>';
    }
    $text1 = implode(' ', $safe1);
    $text2 = implode(' ', $safe2);
    $text1 = preg_replace('/(<[a-z]+[a-z]*[^>]*?>)/e',"ereg_replace('°°',' ','\\1')",$text1);
    $text2 = preg_replace('/(<[a-z]+[a-z]*[^>]*?>)/e',"ereg_replace('°°',' ','\\1')",$text2);
    return array($text1,$text2);
}

/****************************************************
* getOpportunityStatus
* in: 
* out: array
*****************************************************/
function getOpportunityStatus() {
    $sql = "SELECT * FROM opport_status ORDER BY sort";
    $rs  = $GLOBALS['db']->getAll($sql);
    return $rs;
}

/****************************************************
* getOneOpportunity
* in: id = int, all = boolean
* out: array
*****************************************************/
function getOneOpportunity($id) {
    $sql  = "SELECT O.*,coalesce(V.name,C.name) as firma,coalesce(E.name,E.login) as user  FROM  opportunity O ";
    $sql .= "LEFT JOIN employee E on O.memployee=E.id ";
    $sql .= "LEFT JOIN customer C on O.fid=C.id LEFT JOIN vendor V on O.fid=V.id WHERE ";
    $sql .= "O.id = $id"; 
    $rs   = $GLOBALS['db']->getAll($sql);
    $sql  = "SELECT id,ordnumber,transdate FROM oe WHERE ( vendor_id = ".$rs[0]['fid'];
    $sql .= " or customer_id = ".$rs[0]['fid'].") and (closed = 'f' or ordnumber = '".$rs[0]['auftrag']."') ORDER BY transdate";
    $rs[0]['orders'] = $GLOBALS['db']->getAll($sql);
    return $rs[0];
}

/****************************************************
* getOpportunityHistory
* in: oppid = int
* out: array
*****************************************************/
function getOpportunityHistory($oppid) {
    $sql  = "SELECT O.*,OS.statusname,coalesce(E.name,E.login) as user,oe.ordnumber ";
    $sql .= "FROM  opportunity O ";
    $sql .= "LEFT JOIN employee E on O.memployee=E.id ";
    $sql .= "LEFT JOIN oe on O.auftrag=oe.id ";
    $sql .= "LEFT JOIN opport_status OS on O.status = OS.id ";
    $sql .= "WHERE O.oppid = $oppid ORDER BY itime desc offset 1"; 
    $rs   = $GLOBALS['db']->getAll($sql);
    return $rs;
}

/****************************************************
* getOpportunity
* in: fid = int
* out: array
*****************************************************/
function getOpportunity($fid) {
    $sql  = "SELECT O.*,coalesce(V.name,C.name) as firma FROM  opportunity O LEFT JOIN customer C on O.fid=C.id ";
    $sql .= "LEFT JOIN vendor V on O.fid=V.id WHERE fid = $fid ORDER BY oppid, itime desc";
    $rs   = $GLOBALS['db']->getAll($sql);
    return $rs;
}

/****************************************************
* suchOpportunity
* in: data = array
* out: boolean
*****************************************************/
function suchOpportunity($data) {
    $where = '';
    if ( $data ) while ( list($key,$val)=each($data) ) {
        if ( in_array($key,array('title','notiz','zieldatum','next') ) and $val) { 
            $val=str_replace('*','%',$val); $where.="and $key ilike '$val%' "; 
        } else if ( in_array($key,array('status','chance','salesman') ) and $val) { 
            $where .= "and $key = $val "; 
        };
    }
    if ( $data['fid'] ) { 
        $where .= "and fid = ".$data['fid']." and tab='".$data['tab']."'"; 
    } else if ( $data['name'] ) {
        $where .= "and (fid in (SELECT id FROM customer WHERE name ilike '%".$data['name']."%')";
        $where .= "or fid in (SELECT id FROM vendor WHERE name ilike '%".$data['name']."%') )";
    } else if ( $data['oppid'] ) {
        $where = "    oppid = ".$data['oppid'];
    }
    $sql  = 'SELECT O.*,OS.statusname,coalesce(V.name,C.name) as firma,coalesce(E.name,E.login) as user ';
    $sql .= 'FROM  opportunity O LEFT JOIN opport_status OS on OS.id=O.status ';
    $sql .= 'LEFT JOIN customer C on O.fid=C.id ';
    $sql .= 'LEFT JOIN employee E on O.memployee=E.id ';
    $sql .= 'LEFT JOIN vendor V on O.fid=V.id WHERE '.substr($where,3).' ORDER BY oppid,itime desc'; //chance desc,betrag desc";
    $rs   = $GLOBALS['db']->getAll($sql);
    return $rs;
}

/****************************************************
* saveOpportunity
* in: data = array
* out: int
* Eine Auftragschance sichern
*****************************************************/
function saveOpportunity($data) {
    if ( $data['fid'] and $data['title'] and $data['betrag'] and $data['status'] and $data['chance'] and $data['zieldatum'] ) {
        if ( !$data['oppid'] ) {   //Eine neue Auftragschance
            $rs = $GLOBALS['db']->getOne('SELECT coalesce(max(oppid)+1,1001) as id FROM opportunity');
            $data['oppid'] =  $rs['id'];
        };
        $data['zieldatum'] = date2db($data['zieldatum']);
        $data['betrag']    = str_replace(",",".",$data['betrag']);
        unset($data['id']); unset($data['name']); unset($data['action']); unset($data['firma']);
        $data['memployee'] = $_SESSION['loginCRM'];
        $rc = $GLOBALS['db']->insert('opportunity',array_keys($data),array_values($data));
        if ( !$rc ) { return false; }
        return $rc;
    } else {
        if ( $data['oppid'] > 0 ) {
            $rs = $GLOBALS['db']->getOne("SELECT id FROM opportunity WHERE oppid = ".$data['oppid']." ORDER BY id desc limit 1");
            return $rs['id']; 
        } else {
            return false;
        }
    };
    return false;
};

/****************************************************
* saveMailVorlage
* in: data = array
* out: int
* Ein Mail-Temlate sichern
*****************************************************/
function saveMailVorlage($data) {
    if ( $data['MID'] ) {
        $rc  = $GLOBALS['db']->update('mailvorlage',array('cause','c_long'),array('cause'=>$data['Subject'],'c_long'=>$data['BodyText']),'id = '.$data['MID']);
        //$sql = "UPDATE mailvorlage SET cause='%s', c_long='%s' WHERE id = %d";
        //$rc = $GLOBALS['db']->query(sprintf($sql,$data['Subject'],$data['BodyText'],$data['MID']));
    } else { 
        $sql  = "INSERT INTO mailvorlage (cause,c_long,employee) VALUES ('%s','%s',%d)";
        $rc   = $GLOBALS['db']->query(sprintf($sql,$data['Subject'],$data['BodyText'],$_SESSION['loginCRM']));
        $sql  = "SELECT id FROM mailvorlage WHERE cause='".$data['Subject'];
        $sql .= "' AND c_long='".$data['BodyText']."' AND employee=".$_SESSION['loginCRM'];
        $rc   = $GLOBALS['db']->getAll($sql);
        if ( $rc[0]['id'] > 0 ) $rc = $rc[0]['id'];
    }
    return $rc;
}

/****************************************************
* getMailVorlage
* in: 
* out: array
* Alle Mail-Templates holen 
*****************************************************/
function getMailVorlage() {
    $sql = "SELECT * FROM mailvorlage ORDER BY cause";
    $rs  = $GLOBALS['db']->getAll($sql);
    if( !$rs ) {
        return false;
    } else {
        return $rs;
    }
}

/****************************************************
* getOneMailVorlage
* in: MID = int
* out: array
* Ein Mail-Template holen 
*****************************************************/
function getOneMailVorlage($MID) {
    $sql = "SELECT * FROM mailvorlage WHERE id = $MID";
    $rs  = $GLOBALS['db']->getOne($sql);
    if( !$rs ) {
        return false;
    } else {
        return $rs;
    }
}

/****************************************************
* deleteMailVorlage
* in: id = int
* out: int
* Ein Mail-Template löschen
*****************************************************/
function deleteMailVorlage($id) {
    $sql = "DELETE FROM mailvorlage WHERE id = $id";
    $rc  = $GLOBALS['db']->query($sql);
    return $rc;
}

/****************************************************
* saveTT
* in: data = array
* out: data = array
* Einen Zeiteintrag, obere Maske, sichern
*****************************************************/
function saveTT($data) {
    if ( $data['name'] && !$data['fid'] ) {
    //Firma an Hand des Namens suchen
        $rs = getFaID($data['name']);
        if (count($rs)==1) {
            $data['fid'] = $rs[0]['id'];
            $data['tab'] = $rs[0]['tab'];
        } else if (count($rs)>1) {
        //Mehrere Treffer
            $data['msg'] = '.:customer:. .:non ambiguous:.';
            return $data;
        } else {
        //Kein Treffer
            $data['msg'] = '.:customer:. .:not found:.';
            return $data;
        }
    }
    if ( !$data['id'] > 0 ) {
        //Neuer Timetrack
        $newID = uniqid (rand());
        $sql   = "INSERT INTO timetrack (uid,ttname) VALUES (1,'$newID')";
        $rc    = $GLOBALS['db']->query($sql);
        if ( $rc ) {
            $sql = "SELECT * FROM timetrack WHERE ttname = '$newID'";
            $rs  = $GLOBALS['db']->getOne($sql);
            $data['id'] = $rs['id'];
        }
    }
    $sql  = "UPDATE timetrack SET ttname = '".$data['ttname']."',";
    $sql .= "ttdescription = '".$data['ttdescription']."',";
    $sql .= "uid = ".$_SESSION['loginCRM'].",";
    if ( $data['fid'] )       $sql .= "fid = ".$data['fid'].",tab = '".$data['tab']."',";
    if ( $data['startdate'] ) $sql .= "startdate = '".date2db($data['startdate'])."',";
    if ( $data['stopdate'] )  $sql .= "stopdate = '".date2db($data['stopdate'])."',";
    if ( $data['aim'] )       $sql .= "aim = ".$data['aim'].",";
    if ( $data['budget'] )    $sql .= "budget = ".$data['budget'].",";
    $sql .= "active = '".$data['active']."' ";
    $sql .= "WHERE id = ".$data['id'];
    $rc   = $GLOBALS['db']->query($sql);
    if ( $rc ) {
        $data['msg'] = '.:saved:.';
    } else {
        $data['msg'] = '.:error:. .:saving:.';
    }
    $curr = getCurrCompany($fid,$data['tab']);
    $data['curr'] = $curr['name'];
    $data['uid'] = $_SESSION['loginCRM'];
    return $data;
}

/****************************************************
* searchTT
* in: data = array
* out: rs = array
* Zeiteintrag/träge, obere Maske, suchen
*****************************************************/
function searchTT($data) {
    $sql = 'SELECT *  FROM timetrack WHERE 1=1 ';
    if ( $data['fid'] ) { 
        $sql .= 'AND fid = '.$data['fid'];
    } else if ( $data['name'] ) {
        $sql .= "AND fid IN (SELECT id FROM customer WHERE name ILIKE '%".$data['name']."%')";
    }
    if ( isset($data['ttname']) )        $sql .= " AND ttname ilike '%".strtr($data['ttname'],'*','%')."%'";
    if ( isset($data['ttdescription']) ) $sql .= " AND ttdescription ilike '%".strtr($data['ttdescription'],'*','%')."%'";
    if ( isset($data['startdate']) )     $sql .= " AND startdate >= '".date2db($data['startdate'])."'";
    if ( isset($data['stopdate']) )      $sql .= " AND stopdate <= '".date2db($data['stopdate'])."'";
    if ( isset($data['active']) )        $sql .= " AND active = '".$data['active']."'";
    $rs = $GLOBALS['db']->getAll($sql);
    return $rs;
}

/****************************************************
* getOneTT
* in: data = array
* out: rs = array
* Einen Zeiteintrag, obere Maske, holen
*****************************************************/
function getOneTT($id,$event=true) {
    $sql  = "SELECT t.*,v.name as vname,c.name as cname FROM timetrack t ";
    $sql .= "LEFT JOIN customer c on c.id=t.fid ";
    $sql .= "LEFT JOIN vendor v on v.id=t.fid ";
    $sql .= "WHERE t.id = $id";
    $rs   = $GLOBALS['db']->getOne($sql);
    $rs['name']      = ( $rs['tab'] == "C" )?$rs['cname']:$rs['vname'];
    $rs['startdate'] = db2date($rs['startdate']);
    $rs['stopdate']  = db2date($rs['stopdate']);
    $curr            = getCurrCompany($rs['fid'],$rs['tab']);
    $rs['cur']       = $curr['name'];
    if ( $event ) $rs['events'] = getTTEvents($id,"o",false);
    return $rs;
}

/****************************************************
* getTTEvents
* in: id = int
* in: alle = boolean
* in: evtid = int
* out: rs = array
* Alle Zeiteinträge, untere Maske, holen
*****************************************************/
function getTTEvents($id,$alle,$evtid,$abr=False) {
    $sql  = "SELECT t.*,COALESCE(NULLIF(e.name,''),e.login) AS user,oe.ordnumber,oe.closed FROM tt_event t ";
    $sql .= "LEFT JOIN employee e ON e.id=t.uid LEFT JOIN oe ON t.cleared=oe.id WHERE ttid = $id ";
    if ( !$alle ) $sql .= "AND (cleared < 1 OR cleared IS NUll) ";
    if ( $_SESSION['clearonly'] AND $abr ) $sql .= 'AND uid = '.$_SESSION['loginCRM'].' ';
    $sql .= $evtid." ORDER BY t.ttstart";
    $rs   = $GLOBALS['db']->getAll($sql);
    if ( $rs[0]['ordnumber'] == '' ) {
        $sql = 'UPDATE tt_event SET cleared = Null WHERE ttid = '.$id;
        $rc  = $GLOBALS['db']->query($sql);
    }
    return $rs;
}

/****************************************************
* getOneTT
* in: id = int
* out: rs = array
* Einen Zeiteintrag, obere Maske, löschen
*****************************************************/
function deleteTT($id) {
    $ev = getTTEvents($id,"d",false);
    if ( count($ev) > 0 ) return false;
    $sql = "DELETE FROM timetrack WHERE  id = $id";
    $rc  = $GLOBALS['db']->query($sql);
    return $rc;
}

/****************************************************
* saveTTevent
* in: data = array
* out: boolean
* Einen Zeiteintrag, unterte Maske, sichern
*****************************************************/
function saveTTevent($data) {
    if ( $data['start'] == '1' ) {
    //Begin jetzt
        $adate = date('Y-m-d H:i');
    } else {
        list($d,$m,$y) = explode('.',$data['startd']);
        list($h,$i)    = explode(':',$data['startt']);
        if ( checkdate($m,$d,$y) && ( $h>=0 && $h<24 ) && ( $i>=0 && $i<60 ) ) { 
            $adate = sprintf('%04d-%02d-%02d %02d:%02d:00',$y,$m,$d,$h,$i);
        } else {
            return false;
        }
    };
    if ( $data['stop'] == '1' ) {
        //Ende jetzt
        $edate = date("'Y-m-d H:i:00'");
    } else if ( $data['stopd'] ) {
        list($d,$m,$y) = explode('.',$data['stopd']);
        list($h,$i)    = explode(':',$data['stopt']);
        if ( checkdate($m,$d,$y) && ( $h>=0 && $h<24 ) && ( $i>=0 && $i<60 ) ) { 
            $edate = sprintf('%04d-%02d-%02d %02d:%02d:00',$y,$m,$d,$h,$i);
            if ( $edate < $adate ) $edate = '';
        } else {
            return false;
        }
    } else {
    //Ende offen
        $edate = false;
    }
    if ( $data['eventid'] ) {
        $values = array('ttevent'=>$data['ttevent'],'ttstart'=>$adate,'uid'=>$_SESSION['loginCRM']);
        $fields = array('ttevent','ttstart','uid');
        if ( $edate ) { $values['ttstop'] = $edate; $fields[] = 'ttstop'; };
        $rc     = $GLOBALS['db']->update('tt_event',$fields,$values,'id = '.$data['eventid']);
    } else {
        $values = array($data['tid'],$data['ttevent'],$adate,$_SESSION['loginCRM']);
        $fields = array('ttid','ttevent','ttstart','uid');
        if ( $edate ) { $values[] = $edate; $fields[] = 'ttstop'; };
        $rc     = $GLOBALS['db']->insert('tt_event',$fields,$values);
        //Annahme: Der User erstellt nicht GLEICHZEITIG 2 Events für den gleichen Auftrag.
        $sql    = "SELECT * FROM tt_event WHERE cleared is Null AND ttid = ".$data['tid']." AND uid = ".$_SESSION['loginCRM']." ORDER BY id desc limit 1";
        $rs     = $GLOBALS['db']->getOne($sql);
        $data['eventid'] = $rs['id'];
    }
    if ( $data['parray'] != '' ) {
        $GLOBALS['db']->begin();
        $sql    = 'DELETE FROM tt_parts WHERE eid = '.$data['eventid'];
        $GLOBALS['db']->query($sql);
        $tmp    = explode('###',$data['parray']);
        $sqltpl = "INSERT INTO tt_parts (eid,qty,parts_id,parts_txt) VALUES (".$data['eventid'].",%f,%d,'%s')";
        foreach ( $tmp as $row ) {
            $ttp = explode('|',$row);
            $sql = sprintf($sqltpl,str_replace(',','.',$ttp[0]),$ttp[1],trim($ttp[2]));
            $rc  = $GLOBALS['db']->query($sql);
            if ( !$rc ) {
                $GLOBALS['db']->rollback();
                $data['msg'] = ".:error:. .:saving:.";
                break;
            }
        }
        $GLOBALS['db']->commit();
    }
    return $rc;
}

/****************************************************
* saveTTevent
* in: id = int
* in: stop = String
* out: boolean
* Endezeitpunkt für einen Zeiteintrag, unterte Maske, sichern
*****************************************************/
function stopTTevent($id,$stop) {
    $sql = "SELECT * FROM tt_event WHERE id = $id";
    $rs  = $GLOBALS['db']->getOne($sql,'stopTTevent');
    if ( $rs['ttstart'] < $stop ) {
        $sql = "UPDATE tt_event SET ttstop = '$stop' WHERE id = $id";
        $rc  = $GLOBALS['db']->query($sql);
        return $rc;
    } else {
        return false;
    }
}

/****************************************************
* getOneTevent
* in: id = int 
* out: rs = array
* Einen Zeiteintrag, unterte Maske, holen
*****************************************************/
function getOneTevent($id) {
    $sql = "SELECT * FROM tt_event WHERE id = $id";
    $rs1 = $GLOBALS['db']->getOne($sql);
    $sql = "SELECT * FROM tt_parts WHERE eid = $id";
    $rs2 = $GLOBALS['db']->getAll($sql);
    return array('t'=>$rs1,'p'=>$rs2);
}

function getTTparts($eid) {
    $sql = 'SELECT * FROM tt_parts LEFT JOIN parts ON parts.id=parts_id WHERE eid = '.$eid; 
    $rs  = $GLOBALS['db']->getAll($sql);
    return $rs;
}

//Wird nicht gebraucht! Kann raus???
//Nein, aber liefert das noch das richtige Ergebnis?
function getTax($tzid) {
    $sql = "SELECT id,income_accno_id_$tzid AS chartid FROM buchungsgruppen";
    $rs  = $GLOBALS['db']->getAll($sql);
    $tax = array();
    if ( $rs ) foreach ( $rs as $row ) {
       $sql  = "SELECT rate + 1 AS tax FROM tax LEFT JOIN taxkeys ON taxkey=taxkey_id WHERE taxkeys.chart_id = ".$row['chartid'];
       $sql .= " AND tax_id = tax.id AND startdate <= now() ORDER BY startdate DESC LIMIT 1";
       $rsc  = $GLOBALS['db']->getOne($sql); 
       $tax[$row['id']] = $rsc['tax'];
    }
    return $tax;
}
/****************************************************
* mkTTorder
* in: id = int 
* in: evids = array
* out: String
* Aus Zeiteinträgen einen Auftrag generieren
*****************************************************/
function mkTTorder($id,$evids,$trans_id) {
    $tt = getOneTT($id,$false);
    $vendcust = ($tt['tab']=='C')?'customer':'vendor';
    //Steuerzone ermitteln (0-3) NEU: (1-4)!!! aus 0 wurde 4
    $sql  = "SELECT taxzone_id FROM ".$vendcust." WHERE id = ".$tt['fid'];
    $rs   = $GLOBALS['db']->getOne($sql);
    $tzid = $rs['taxzone_id'];
    $TAX  = getTax($tzid);
    //Artikeldaten holen
    $sql    = "SELECT * FROM parts WHERE partnumber = '".$_SESSION['ttpart']."'";
    $part   = $GLOBALS['db']->getOne($sql); 
    $partid = $part['id'];
    $sellprice = $part['sellprice'];
    $unit   = $part['unit'];
    //Steuersatz ermitteln
    $tax    = $TAX[$part['buchungsgruppen_id']];
    $curr   = getCurr(True);
    //Events holen
    $events = getTTEvents($id,false,$evids,True);
    if ( !$events ) { 
        return ".:nothing to do:.";
    };
    if ( !$evids ) {
        $evids = 'and t.id in (';
        foreach ( $events as $row ) {
            $tmp[] = $row['id'];
        };
        $evids .= implode(',',$tmp).') ';
    };
    $GLOBALS['db']->begin();
    if ( $trans_id < 1 ) {
        //Auftrag erzeugen
        $sonumber = ($tt['tab']=='C')?nextNumber("sonumber"):nextNumber("ponumber");
        if ( !$sonumber ) return ".:error:.";
        $sql  = "INSERT INTO oe (notes,transaction_description,ordnumber,".$vendcust."_id,taxincluded,currency_id,taxzone_id) ";
        $sql .= "VALUES ('".$tt['ttdescription']."','".$tt['ttname']."',$sonumber,'".$tt['fid']."','f',";
        $sql .= "coalesce((SELECT currency_id FROM ".$vendcust." WHERE id = ".$tt['fid']."),$curr),$tzid)";
        $rc   = $GLOBALS['db']->query($sql,"newOE");
        if ( !$rc ) {
            $sql = "DELETE FROM oe WHERE ordnumber = '$sonumber'";
            $rc  = $GLOBALS['db']->query($sql,"delOE");
            return ".:error:. 0";
        }
        $sql = "SELECT id FROM oe WHERE  ordnumber = '$sonumber'";
        $rs  = $GLOBALS['db']->getOne($sql);
        $trans_id = $rs['id'];
        if ( $trans_id <= 0 ) {
            $sql = "DELETE FROM oe WHERE ordnumber = '$sonumber'";
            $rc  = $GLOBALS['db']->query($sql,"delOE");
            return ".:error:. 0";
        }
        $netamount = 0;
    } else {
        $sql = "SELECT * FROM oe WHERE id = ".$trans_id;
        $rc = $GLOBALS['db']->getOne($sql,'');
        if ( ! $rc ) {
            return ".:error:. 00";
        }
        $netamount = $rc['netamount'];
    }
    $fields = array('trans_id', 'parts_id', 'description', 'qty', 'sellprice', 'unit', 'ship', 'discount', 'serialnumber', 'reqdate','position');
    $pos    = 0;
    foreach ( $events as $row ) {
        if ( $row['ttstop'] == '' ) {
            $GLOBALS['db']->rollback();
            return ".:close event:.";
        }
        $t1 = strtotime($row['ttstart']);
        $t2 = strtotime($row['ttstop']);
        //Minuten
        $diff = floor(($t2 - $t1) / 60);
        //Abrechnungseinheiten
        $time = floor($diff / $_SESSION['tttime']);
        //Ist der Rest über der Tolleranz
        if ( $diff - ($_SESSION['tttime'] * $time) > $_SESSION['ttround'] ) $time++;
        $price =  $time * $sellprice;
        //Orderitemseintrag
        $rqdate = substr($row['ttstop'],0,10);
        $pos++;
        $values = array($trans_id,$partid,$row['ttevent'],$time,$sellprice,$unit,0,0,$diff,$rqdate,$pos);
        $rc     = $GLOBALS['db']->insert('orderitems',$fields,$values);
        if ( !$rc ) {
            $GLOBALS['db']->rollback();
            return ".:error:. 1";
        }
        $netamount += $price;
        $amount    += $price * $tax; 
        $parts      = getTTparts($row['id']);
        if ( $parts ) {
            foreach ( $parts as $part ) {
                    $pos++;
                    $values = array($trans_id,$part['parts_id'],$part['parts_txt'],$part['qty'],$part['sellprice'],$part['unit'],0,0,Null,$rqdate,$pos);
                    $rc = $GLOBALS['db']->insert('orderitems',$fields,$values);
                    if ( !$rc ) {
                        $GLOBALS['db']->rollback();
                        return ".:error:. 2";
                    }
                    $netamount += $part['qty'] * $part['sellprice'] ;
                    $amount +=  $part['qty'] * $part['sellprice'] * $TAX[$part['buchungsgruppen_id']]; 
            }
        }
    }
    //OE-Eintrag updaten
    $nun = date('Y-m-d');
    $fields = array('transdate','amount','netamount','reqdate','notes','employee_id');
    $values = array('transdate'=>$nun,'amount'=>$amount,'netamount'=>$netamount,'reqdate'=>$nun,'notes'=>$tt['ttdescription'],'employee_id'=>$_SESSION['loginCRM']);
    $rc     = $GLOBALS['db']->update('oe',$fields,$values,'id = '.$trans_id);
    if ( !$rc ) {
        $GLOBALS['db']->rollback();
        return '.:error:. 2';
    } else {
        //Events als Abgerechnet markieren.
        $sql = "UPDATE tt_event t set cleared = $trans_id WHERE t.ttid = $id $evids";
        $rc  = $GLOBALS['db']->query($sql);
        $GLOBALS['db']->commit();
        return '.:ok:.';
    }
}
function getPart($part) {
    $sql = "SELECT  id,partnumber,description FROM parts WHERE partnumber ilike '%$part%' or description ilike '%$part%' ORDER by description";
    $rs  = $GLOBALS['db']->getAll($sql);
    return $rs;
}
function getIOQ($fid,$Q,$type,$close){
    //ToDo Option "Nur offene IOQ anzeigen"
    //if ($_SESSION['sales_edit_all'] == "f") $sea = sprintf(" and (employee_id = %d or salesman_id = %d) ", $_SESSION['loginCRM'], $_SESSION['loginCRM']);
    //$closed_sql = $close?"AND closed = 'f' ":" ";
    $cust_vend = ($Q=='C')?'customer_id':'vendor_id';
    $ar_ap     = ($Q=='C')?'ar':'ap';
    switch($type) {
        case 'inv': //Rechnungen
            $sql  = "SELECT DISTINCT ON ($ar_ap.id) to_char($ar_ap.transdate, 'DD.MM.YYYY') as date, description, COALESCE(ROUND(amount,2))||' '||COALESCE(C.name) as amount, ";
            $sql .= "invnumber as number, $ar_ap.id FROM $ar_ap LEFT JOIN invoice  ON $ar_ap.id=trans_id LEFT JOIN currencies C on currency_id=C.id  WHERE ";
            $sql .= "$cust_vend = $fid ORDER BY $ar_ap.id DESC, invoice.id";
            break;
        case 'ord': //Aufträge
            $sql  = "SELECT DISTINCT ON (oe.id) to_char(oe.transdate, 'DD.MM.YYYY') as date, description, COALESCE(ROUND(amount,2))||' '||COALESCE(C.name) as amount, ";
            $sql .= "oe.ordnumber as number, oe.id FROM oe LEFT JOIN orderitems ON oe.id=trans_id LEFT JOIN currencies C on currency_id=C.id ";
            $sql .= "WHERE quotation = FALSE AND $cust_vend = $fid ORDER BY oe.id DESC, orderitems.id"; 
            break;
        case 'quo': //Angebote
            $sql  = "SELECT DISTINCT ON (oe.id) to_char(oe.transdate, 'DD.MM.YYYY') as date, description, COALESCE(ROUND(amount,2))||' '||COALESCE(C.name) as amount, ";
            $sql .= "oe.quonumber as number, oe.id FROM oe LEFT JOIN orderitems ON oe.id=trans_id LEFT JOIN currencies C on currency_id=C.id WHERE ";
            $sql .= "quotation = TRUE AND $cust_vend = $fid ORDER BY oe.id DESC, orderitems.id";
    } 
    if ( isSetVar($sql) ) return $GLOBALS['db']->getAll($sql);
    return false;
}

?>
