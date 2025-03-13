<?php
    include("../inc/stdLib.php");
    include_once("crmLib.php");

    function mkdate($dat) {
        $t=explode(" ",substr($dat,0,-3));
        $n=explode("-",$t[0]);
        $d=date("d.M Y",mktime(0, 0, 0, $n[1], $n[2], $n[0]));
        return $d." ".$t[1];
    }

    function getMailListe() {
        $mailcnt = 0;
        $unread  = 0;
        //Mails holen
        $mail = holeMailHeader();
        $content = '';
        if ($mail) foreach($mail as $col){
            $mailcnt++;
            if ( $col['Gelesen'] == '+' ) $unread++;
            $content .= '<tr onClick="showW('.$col['Nr'].',\'E\');">';
            $content .= '<td nowrap>'.$col['Datum'].'</td>';
            $content .= '<td>'.$col['Gelesen'].'</td>';
            $content .= '</td>';
            if ( strlen($col['Betreff']) > 55 ) {                            
                $content .= '<td>'.substr($col['Betreff'],0,55).'<b>...</b><br/>';
            } else {
                $content .= '<td>'.$col['Betreff'].'<br/>';
            }
            $content .= htmlspecialchars($col["Abs"]).'</td></tr>'."\n";
        };
        echo json_encode( array('mailcnt'=>$mailcnt,'unread'=>$unread,'content'=>$content) );
    }
    function getWvListe() {
        //Termine holen !!! 
        //Muß geändert werden. Termine wurden duch Kalender ersetzt
        //$termine = getTermin(date("d"),date("m"),date("Y"),"T",$_SESSION["loginCRM"]);
        //Wiedervorlagen holen
        $wvl = getWvl();
        //if ( $termine && $wvl ) { $wvl = array_merge($termine,$wvl); }
        //else if ( $termine ) { $wvl = $termine; }
        $nunD = date("Y-m-d 00:00:00");
        $nunT = date("Y-m-d H:i");
        $content = '';
        if ( $wvl ) foreach($wvl as $col){
            if ( $col['finishdate'] || $col['stoptag'] ) {
                if ( ( $col['finishdate'] <> '' && $col['finishdate'] < $nunD ) || 
                     ( $col['stoptag'] <> '' && $col['stoptag'].' '.$col['stopzeit'] < $nunT ) ) {
                    $end = 3;
                } else {
                    $end = 2;
                }
                $datum = mkdate(($col['finishdate'])?$col['finishdate']:$col['stoptag'].' '.$col['stopzeit'].':00');
            } else {
                if ( $col["trans_module"] ) {
                    $datum = mkdate($col['initdate'].' 00:00:00');
                } else {
                    $datum = mkdate(($col['initdate'])?$col['initdate']:$col['starttag'].' '.$col['startzeit'].':00');
                }
                $end = 1;
            }
            if ($col['status']=='F')   { $Art = 'F'; }
            else if ($col['starttag']) { $Art = 'T'; }
            else                       { $Art = 'D'; };
            $content .= '<tr group="tc" onClick="showW('.$col['id'].',\''.$Art.'\');">';
            $content .= '<td nowrap>'.$datum.'</td>';
            $content .= '<td>'.$col['status'].' '.(($col['kontakt'])?$col['kontakt']:'X');
            if ( $end == 3 ) $content .= ' <b>!!</b>';
            $content .= '</td>';
            if ( strlen($col['cause']) > 35 ) {                            
                $content .= '<td>'.substr($col['cause'],0,35).'...</td>';
            } else {
                $content .= '<td>'.$col['cause'].'</td>';
            }
            $content .= '<td>'.(($col["ename"])?$col["ename"]:$col["employee"]).'</td>';
            $content .= '</tr>'."\n";            
        };
        echo $content;
    }

    function _getOneWvl($id) {
        $data = getOneWvl($id);
        echo json_encode( $data );
    }
    function _getOneERP($id) {
        $data = getOneERP($id);
        echo json_encode( $data );
    }
    function _getOneMail($id) {
        $data = getOneMail($id);
        $f = fopen('/tmp/mai.log','a');
        fputs($f,print_r($data,true)."\n");
        $out = json_encode( $data );
        fputs($f,"JSON$out!\n");
        if ( $data ) {
            echo trim(json_encode( $data ));
        } else {
            echo json_encode( array('rc'=>-9) );
        }
    }
    function _delMail($id) {
       $rc = delMail($id);
       echo $rc;
    }
    function _saveMail($data) {
        $data['DCaption'] = $data['cause'];
        $rc = insWvlM($data);
        echo $rc;
    }
    function _saveWvl($data) {
        if ( $data['WVLID'] < 1 ) {
            $data = array_merge($data,mknewWVL(false));
            if ( $data['WVLID'] < 1 ) {
                echo "-3";
                return;
            }
        } ;
        if (!$data["DCaption"]) $data["DCaption"] = $data["cause"];
        if ( $data['newfile'] == 1 and $data['filename'] != '' ) {
            $src  = $_SESSION['erppath'].'crm/dokumente/'.$_SESSION["dbname"].'/'.$_SESSION['login'].'/tmp/';
            $rc   = file_exists($src.$data['filename']);
            if ( $rc ) {
                if ( $data["DateiID"] ) delDokument($data["DateiID"]); // ein altes löschen
                require_once("documents.php");
                $dest = $_SESSION['erppath'].'crm/dokumente/'.$_SESSION["dbname"].'/'.$_SESSION["login"].'/';
                copy($src.$data['filename'],$dest.$data['filename']);
                unlink ($src.$data['filename']);
                //Dokument in db speichern
                $dbfile=new document();
                $dbfile->setDocData("descript",$data["subject"]);
                $dbfile->setDocData("pfad",$_SESSION["login"]);
                $dbfile->setDocData("name",$data['filename']);
                $dbfile->setDocData("descript",$data["DCaption"]);
                $rc = $dbfile->newDocument();
                $dbfile->saveDocument();
                if ( ! $dbfile->id > 0 ) {
                    echo "-4";
                    return;
                }
                $data["DateiID"] = $dbfile->id;
            } else {
                echo "-5";
                return;
            }
        }
        $rc = updWvl($data);
        echo $rc;
    }


   // $f=fopen('/tmp/wvl','a');
   // fputs($f,print_r($_POST,true));
   // fputs($f,print_r($_GET,true));
   // fclose($f);
if ( isset($_POST['task']) and $_POST['task'] == 'erp' ) {
    if ( $_POST['kontakt'] == 'F' ) {
        echo updWvlERP($_POST);
        return;
    } else {
        if ($_POST["WVLID"]>0) {
            $ok = updWvl($_POST,$f);
        } else {
            if ($_POST["mail"]) { 
                $ok = insWvlM($_POST); 
            } else {
                $ok = insWvl($_POST,$f);
            }
        }
    }
} else if ( isset($_POST['task']) and $_POST['task'] == 'wvl' ) {
    _saveWvl($_POST);
} else if ( isset($_POST['task']) and $_POST['task'] == 'delmail' ) {
    _delMail($_POST['id']);
} else if ( isset($_POST['task']) and $_POST['task'] == 'mail' ) {
    _saveMail($_POST);
} else {
    switch ($_GET['task']) {
        case 'wvl'    : getWvListe();
                        break;
        case 'wvm'    : getMailListe();
                        break;
        case 'show'   : _getOneWvl($_GET['id']);
                        break;
        case 'erp'    : _getOneERP($_GET['id']);
                        break;
        case 'mail'   : _getOneMail($_GET['id']);
                        break;
        default       : echo $_GET['task'].' nicht erlaubt';          
    };
};
?>
