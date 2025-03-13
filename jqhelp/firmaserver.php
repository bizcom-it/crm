<?php
    require_once("../inc/stdLib.php");
    include_once("FirmenLib.php");
    include_once("crmLib.php");
    include_once("persLib.php");

    function getCustomTermin($id,$tab,$day,$month,$year) {
        $termine = getCustTermin($id,$tab,$day,$month,$year);
        if ($termine)  {
            foreach ($termine as $term) {
               $inhalt .= "<span onClick='getCall(".$term["cid"].")'>";
               $inhalt .= db2date(substr($term["start"],0,10))." ".$term["cause"].":".$term["cp_name"]."<br /></span>";
            }
        } else {
            $inhalt = "Keine Termine";
        };
        echo $inhalt;
    }
    function addFaStamm2Sess( $id, $tab='C', $prod, $cup ) {
        if ( $id ) $data = getFirmenStamm( $id, false, $tab );
        if ( $data and $id ) {
            $fa['RECV_NAME1']   = $data['name'];
            $fa['RECV_NAME2']   = $data[''];
            $fa['RECV_STREET']  = $data['street'];
            $fa['RECV_HOUSENUMBER'] = '';
            $fa['RECV_PLZ']     = $data['zipcode'];
            $fa['RECV_CITY']    = $data['city'];
            $fa['RECV_COUNTRY'] = strtoupper( substr( $data['country'],0,3 ) );
            $fa['RECV_PRODUCT'] = $prod;
            $fa['RECV_CUPON']   = $cup;
            if ( in_array( $fa['RECV_COUNTRY'], array('DEU','AUT','CHE','FRA','ITA','ESP') ) ) {
                echo 'ok';
            } else {
                echo 'ISO3';
            }
        };
        echo 'ERROR';
    }
    function Buland($land) {
        $data=getBundesland(strtoupper($land));
        $rs = array(array('id'=>'','val'=>''));
        foreach ($data as $row) {
            array_push($rs,array('id'=>$row['id'],'val'=>$row['bundesland']));
        }
        echo json_encode($rs);
    }
    function getShipto($id,$tab='C') {
        if ($id) $data=getShipStamm($id,$tab);
        if ( !$data or !$id ) {
            $data = array('trans_id'=>'','shiptoname'=>'','shiptostreet'=>'',
                          'shiptocity'=>'','shiptocountry'=>'','shiptozipcode'=>'',
                          'shiptodepartment_1'=>'','shiptodepartment_2'=>'','shiptocontact'=>'',
                          'shiptobland'=>'');
        };
        echo json_encode($data);
    }
    function showCalls($id,$fa=false) {
        $nun   = date("d.m.Y h:i");
        $items = getAllTelCall($id,$fa); //,$start,200);
        $item = array();
        if ($items) {
            foreach ($items as $row) {
                $row['calldate'] = db2date(substr($row["calldate"],0,10))." ".substr($row["calldate"],11,5);
                if ( !$row['cp_name'] ) $row['cp_name'] = '';
                $item[] = $row;
            }
        } 
        $data = array('items'=>$item);
        echo json_encode($data);
    }
    function showShipadress($id,$tab,$fid=0,$cnt=0){
        $tmpdata = getUserEmployee(array('planspace','streetview','external_mail'));
        $data=getShipStamm($id,$tab,true,$fid);
        $htmllink = '';
        $karte=str_replace(array("%TOSTREET%","%TOZIPCODE%","%TOCITY%"),
                           array(strtr($data["shiptostreet"]," ",$tmpdata['planspace']),$data["shiptozipcode"],$data["shiptocity"]),$tmpdata['streetview']);
        if (preg_match("/%FROM/",$karte)) {
            include_once "UserLib.php";
            $user=getUserStamm($_SESSION["loginCRM"]);
            if ($user["addr1"]<>"" and $user["addr3"]<>"" and $user["addr2"]) {
                $karte=str_replace(array("%FROMSTREET%","%FROMZIPCODE%","%FROMCITY%"),
                                   array(strtr($user["addr1"]," ",$_SESSION['planspace']),$user["addr2"],$user["addr3"]),$karte);
            } else {
                $karte="";
            };
        }
        $maillink=$tmpdata['external_mail']?
            "<a href='mailto:".$data["shiptoemail"]."'>".$data["shiptoemail"]."</a>":
            "<a href='mail.php?aktion=domail&TO=".$data["shiptoemail"]."&KontaktTO=$tab".$data["trans_id"]."'>".$data["shiptoemail"]."</a>";
        echo json_encode(array('karte'=>$karte,'mail'=>$maillink,'www'=>$htmllink,'adr'=>$data));
    }
    function showContactadress($id){
        $data=getKontaktStamm($id,".");
        $tmpdata = getUserEmployee(array('external_mail'));
        if ( !$data ) { 
            $data = array('cp_id'=>-1,'cp_name'=> translate('.:no contact:.','firma'));
        } else {
            $data["cp_email"]=$tmpdata['external_mail']?
                "<a href='mailto:".$data["cp_email"]."'>".$data["cp_email"]."</a>":
                "<a href='mail.php?aktion=domail&TO=".$data["cp_email"]."&KontaktTO=P".$data["cp_id"]."'>".$data["cp_email"]."</a>";
             if ($data["cp_privatemail"]) $data["cp_privatemail"]=$tmpdata['external_mail']?
                "Privat: <a href='mailto:".$data["cp_privatemail"]."'>".$data["cp_privatemail"]."</a>":
                "Privat: <a href='mail.php?aktion=domail&TO=".$data["cp_privatemail"]."&KontaktTO=P".$data["cp_id"]."'>".$data["cp_privatemail"]."</a>";
            $data["cp_homepage"]="<a href='".$data["cp_homepage"]."' target='_blank'>".$data["cp_homepage"]."</a>";
            if (strpos($data["cp_birthday"],"-")) { $data["cp_birthday"]=db2date($data["cp_birthday"]); };
            if ($data["cp_gender"]=='m') { $data["cp_greeting"]=translate('.:greetmale:.','firma'); 
            } else { $data["cp_greeting"]=translate('.:greetfemale:.','firma'); };
            if ( $data['tabelle'] != '' ) {
                $root="dokumente/".$_SESSION["dbname"]."/".$data["tabelle"].$data["nummer"]."/".$data["cp_id"];
            } else {
                $root="dokumente/".$_SESSION["dbname"]."/".$data["cp_id"];
            }
            if (!empty($data["cp_grafik"]) && $data["cp_grafik"]<>"     ") {
                $data['cp_grafikurl'] = "$root/kopf$id.".$data["cp_grafik"];
                $data['cp_grafik']    = "<img src='$root/kopf$id.".$data["cp_grafik"]."' ".$data["icon"]." border='0'>";
            } else {
                $data['cp_grafik']     = '';
                $data['cp_grafikurl']  = '';
            }
            $tmp=glob("../$root/vcard".$data["cp_id"].".*");
            $data["cp_vcard"]    = "";
            $data["cp_vcardurl"] = "";
            if ($tmp)  foreach ($tmp as $vcard) {
                $ext=explode(".",$vcard);
                $ext=strtolower($ext[count($ext)-1]);
                if (in_array($ext,array("jpg","jpeg","gif","png","pdf","ps"))) {
                    $data["cp_vcard"]    = '<img src="image/vcard.png" border="0" id="cpvcard" height="30" width="40">';
                    $data['cp_vcardurl'] = substr($vcard,3);
                    break;
                }
            } 
            $data["extraF"] = '<a href="extrafelder.php?owner=P'.$id.'" target="_blank" title="'.translate('.:extra data:.','firma').'"><img src="image/extra.png" alt="Extras" border="0" /></a>';
        }
        echo json_encode($data);
    }

    function listTevents($id,$fid=0,$tab) {
        $events = getTTEvents($id,"a",false);
        if ( $tab == 'C' ) {
            $link = "<a href='../oe.pl?action=edit&type=sales_order&vc=customer&id=%d&callback=crm/timetrack.php%%3ffid=$fid'>";
        } else {
            $link = "<a href='../oe.pl?action=edit&type=purchase_order&vc=vendor&id=%d&callback=crm/timetrack.php%%3ffid=$fid'>";
        }
        if (!$events) echo json_encode(array('ok'=>0));
        $tt = getOneTT($events[0]["ttid"]);
        //$liste = "<form name='cleared' method='post' action='timetrack.php'>";
        //$liste.= "<input type='hidden' name='tid' value='".$events[0]["ttid"]."'>";
        //$liste.= "<table>";
        $liste = '';
        $i = 0;
        $now = time();
        $diff = 0;
        $lastcleared = 0;
        if ($events) foreach ($events as $row) {
            $a = explode(" ",$row["ttstart"]);
            $t1 = strtotime($row["ttstart"]);
            if ($row["ttstop"]) {
                $b = explode(" ",$row["ttstop"]);   
                $stop = db2date($b[0])." ".substr($b[1],0,5);
                $t2 = strtotime($row["ttstop"]);
            } else {
                if ( $t1 <= $now ) {
                    $t2 = $now;
                } else {
                    $t2 = $t1;
                }
                $stop = "<a href='timetrack.php?tid=".$row["ttid"]."&eventid=".$row["id"]."&stop=now'><b>".translate('.:stop now:.','work')."</b></a>";
            };
	        $min = $t2 - $t1;
            $diff += $min;
            $i++; 
            if ($row["cleared"] > 0 and $row['ordnumber'] != '') {
                $clear = "<td class='klein'>-</td>";
                if ( $row['cleared'] > $lastcleared )  $lastcleared  = $row['cleared']; 
            } else {
                if ($row["uid"] == $_SESSION["loginCRM"]) {
                    $clear = "<td><input type='checkbox' name='clear[]' value='".$row["id"]."'></td>";
                } else {
                    $clear = "<td>+</td>";
                };
            }
            //$liste .= "<tr class='calls".($i%2)."' onClick='editrow(".$row["id"].");'>$clear<td>".db2date($a[0])." ".substr($a[1],0,5)."</td>";
            $liste .= "<tr onClick='editrow(".$row["id"].");'>$clear<td>".db2date($a[0])." ".substr($a[1],0,5)."</td>";
            $liste .= "<td>".$stop."</td><td align='right'>".floor($min/60)."</td><td>".$row["user"]."</td><td>";
            if (strlen($row["ttevent"])>40) {
                $liste .= substr($row["ttevent"],0,40)."...</td><td>";
            } else {
                $liste .= $row["ttevent"]."</td><td>";
            }
	        $liste .= sprintf($link,$row['cleared']).$row["ordnumber"]."</a>";
            if ( $row['closed'] == 't' ) $liste .= "*";
            $liste .= "</td></tr>";
        };
        $diff = floor($diff / 60);
        if ($tt["aim"]>0) {
            $rest = $tt["aim"] * 60 - $diff;
            $rstd = floor($rest/60);
            $rmin = ($rest%60);
            $rest = translate('.:remain:.','work')." $rstd:$rmin ".translate('.:hours:.','work');
        } else {
            $rest = "";
        }
        if ($diff>60) {
            $min = sprintf("%02d",($diff % 60));
            $std = floor($diff/60);
            $use = "$std:$min ".translate('.:hours:.','work');
        } else {    
            $use = $diff." ".translate('.:minutes:.','work');
        }
        $liste .= "</table>";
        $buttons = "<input type='checkbox' name='clrok' value='1'>".translate(".:all:.",'work')." ";
        if ( $lastcleared > 0 ) {
            $buttons .= "<input type='radio' name='order' value='0' checked>".translate('.:new Order:.','work').' ';
            $buttons .= "<input type='radio' name='order' value='$lastcleared'>".translate('.:add to last Order:.','work').' ';
        };
     	$buttons .= "<input type='submit' name='clr' value='".translate(".:clearing:.","work")."'></form>";
        echo json_encode(array('liste'=>$liste,'buttons'=>$buttons,'used'=>$use,'rest'=>$rest,'ok'=>1));
    }
    function editTevent($id) {
        $data = getOneTevent($id);
        $a = explode(" ",$data['t']["ttstart"]);
        $data['startd'] = db2date($a[0]);
        $data['startt'] = substr($a[1],0,5);
        if ($data['t']["ttstop"]) {
            $b = explode(" ",$data['t']["ttstop"]);
            $data['stopd'] = db2date($b[0]);
            $data['stopt'] = substr($b[1],0,5);
        } else {
            $data['stopd'] = "";
            $data['stopt'] = "";
        };
        echo json_encode($data);
    }
    function showDir($id,$directory) {
        if ( substr($directory,0,1) == '!' ) {
                $DIR = $_SESSION['erppath'].'webdav/'.$_SESSION['manid'].'/'.substr($directory,1);
                $pre = '!';
        } else {
            if ($directory != '/')
                $directory = trim( rtrim( $directory, " /\\" ) ); //entferne rechts Leezeichen und Slash bzw Backslash
            //$inhalt = chkdir($directory,"."); Nonsens???
            $inhalt = false;
            $DIR = $_SESSION['erppath'].'crm/dokumente/'.$_SESSION["dbname"].$directory;
            $pre = '';
        };
        if ( is_dir($DIR) ) {
            $dir_object = dir( $DIR ) ; //$_SESSION['erppath'].'/crm/dokumente/'.$_SESSION["dbname"]."/".$directory );
            // Gibt neues Verzeichnis aus
            $inhalt="<ul>";
            $dir="<li class='ptr' onClick='dateibaum(\"$id\",\"%s\")'>%s";
            $datei="<li class='ptr' onClick='showFile(\"$id\",\"%s\")'>%s";
            clearstatcache();
            $Eintrag = array();
            $cnt = 0;
            while ( false !== ( $entry = $dir_object->read() ) ) {
                    // '.' interessiert nicht
                    if ( $entry !== '.' ) {
                        if ($entry === '..' ) {
                            if ($directory=="/" || $directory=="" || $directory == '!') {
                                continue;
                            } else {
                                $tmp       = substr($directory,0,strrpos($directory,"/"));
                                $Eintrag[] = sprintf($dir,$tmp,"[ .. ]");
                            }
                        } else if (is_dir( $DIR.'/'.$entry )) { //$_SESSION['erppath'].'crm/dokumente/'.$_SESSION["dbname"].$directory."/".$entry)) {
                            $Eintrag[] = sprintf($dir,$directory."/".$entry,"[ $entry ]");
                            $cnt++;
                        } else {
                            $Eintrag[]=sprintf($datei,$entry,"$entry");
                            $cnt++;
                        }
                    }
            }
            sort($Eintrag);
            $inhalt.=join("",$Eintrag);
            $inhalt.="</ul>";
            $dir_object->close();
        }
        echo json_encode( array( 'rc'=>'1', 'fb'=>$inhalt, 'path'=>($directory)?$directory:"/" , 'count'=>$cnt) );
    }
    function showFile($pfad,$file) {
        if (substr($pfad,-1)=="/" and $pfad != "/") $pfad=substr($pfad,0,-1);
        if (substr($pfad,0,2) == "//" ) $pfad = substr($pfad,1);
        clearstatcache();
        if ( substr($pfad,0,1) == '!' ) {
            $crmpfad = $_SESSION['erppath'].'webdav/'.$_SESSION["manid"];
            $pfad = substr($pfad,1);
            $picurl  = 'webdav/'.$_SESSION['manid'].'/'.$pfad;
        } else {
            $crmpfad = $_SESSION['erppath'].'crm/dokumente/'.$_SESSION["dbname"];
            $picurl  = 'crm/dokumente/'.$_SESSION["dbname"].$pfad;
        };
        $zeit=date("d.m.Y H:i:s",filemtime($crmpfad."/$pfad/$file"));
        $size=filesize($crmpfad."/$pfad/$file");
        $ext=strtoupper(substr($file,strrpos($file,".")+1));
        $pic="file.gif";
        if ($ext=="PDF") { $type="PDF-File"; $pic="pdf.png"; }
        else if (in_array($ext,array("ODT","ODF","SXW","STW","WPD","DOC","TXT","RTF","LWP","WPS"))) { $type="Textdokument"; $pic="text.png";}
        else if (in_array($ext,array("ODS","SXC","STC","VOR","XLS","CSV","123"))) { $type="Tabellendokument"; $pic="calc.png"; }
        else if (in_array($ext,array("ODP","SXI","SDP","POT","PPS"))) { $type="Pr&auml;sentation"; $pic="praesent.png";}
        else if (in_array($ext,array("ODG","SXD","SDA","SVG","SDD","DXF"))) { $type="Zeichnungen"; $pic="zeichng.png";}
        else if (in_array($ext,array("HTM","HTML","STW","SSI","OTH"))) { $type="Webseiten"; $pic="web.png"; }
        else if (in_array($ext,array("DBF","ODB"))) { $type="Datenbank"; $pic="db.png";}
        else if (in_array($ext,array("PS", "EPS"))) { $type="Postscript"; $pic="ps.png";}
        else if (in_array($ext,array("GZ", "TGZ","BZ","ZIP","TBZ"))) { $type="Komprimiert"; $pic="zip.png"; }
        else if (in_array($ext,array("MP3","OGG","WAV"))) { $type="Audiodatei"; $pic="sound.png";}
        else if (in_array($ext,array("BMP","GIF","JPG","JPEG","PNG","TIF","PGM","PPM","PCX","PSD","TIFF"))) { $type="Grafik-Datei"; $pic="grafik.png";}
        else if (in_array($ext,array("WMF","MOV","AVI","VOB","MPG","MPEG","WMV","RM"))) { $type="Video-Datei"; $pic="video.png";}
        else if ($ext=="XML") { $type="XML-Datei"; $pic="xml.png";}
        else if (in_array($ext,array("SH","BAT"))) { $type="Shell-Script"; $pic="exe.png";}
        else { $type="Unbekannt"; $pic="foo.png"; };
        $info ="<br>$pfad".(($pfad == "/")?'':'/')."<b>$file</b><br><br>";
        if ( $type == 'Grafik-Datei' ) {
            $info.=translate('.:filetyp:.','firma').": <img onClick='showimage();' src='image/icon/$pic'> $type";
            $info.='<a class="facybox" href="'.$_SESSION['baseurl'].$picurl.'/'.$file.'" id="fileimage" ></a><br>';
        } else {
            $info.=translate('.:filetyp:.','firma').": <img src='image/icon/$pic'> $type<br>";
        };
        $info.=translate('.:filesize:.','firma').": $size<br>".translate('.:filetime:.','firma').": $zeit<br>";
        $dbfile=new document();
        $rs=$dbfile->searchDocument($file,$pfad);
        $id=0;
        if ($rs) {
            $rs=$dbfile->getDokument($rs);
            if ($rs["lock"]>0) $info.="<br /><font color='red'>".translate('.:locked:.','firma')." : ".$rs["lockname"]."</font><br />";
            $info.="<br>".translate('.:Description update:.','firma').": ".db2date($rs["datum"])." ".$rs["zeit"]."<br>";
            $info.=translate('.:Description:.','firma').": ".nl2br($rs["descript"])."<br>";
            $id=$rs["id"];
        }
        $return = array( 
                'docname'=> $file, 
                'docoldname'=>$file,
                'docpfad'=>$pfad,
                'docid'=>$id, 
                'docdescript'=>$rs["descript"],
                'fbright'=>$info, 
                'lock'=>$rs["lock"]
             );
        echo json_encode( $return ); 
    }
    function lockFile($file,$path,$id=0) {
        $dbfile=new document();
        if ($id==0) {
            $id=$dbfile->searchDocument($file,$path);
            if ($id) {
                $rs=$dbfile->getDokument($id);
            } else {
                $dbfile->setDocData("pfad",$path);
                $dbfile->setDocData("name",$file);
            }
        } else {
            $rs=$dbfile->getDokument($id);
        }
        if ($dbfile->lock>0) {
            if ($dbfile->lock==$_SESSION["loginCRM"])
                $dbfile->setDocData("lock",0);
                $lock = 'unlock';
        } else {
            $dbfile->setDocData("lock",$_SESSION["loginCRM"]);
                $lock = 'lock';
        }
        $rc = $dbfile->saveDocument();
        if ($rc) { echo $lock; }
        else     { echo 'Error'; };
    }
    function moveFile($file,$pfadleft) {
        $oldpath=substr($file,0,strrpos($file,"/"));
        $file=substr($file,strrpos($file,"/")+1);
        if ($oldpath<>$pfadleft) {
            $pre="../dokumente/".$_SESSION["dbname"];
            $dbfile=new document();
            $tmp = explode("/",$oldpath);
            $opath = "/".implode("/",array_slice($tmp,2));
            $id=$dbfile->searchDocument($file,$opath);
            if ($id) {
                $rs=$dbfile->getDokument($id);
                if ($dbfile->lock>0) {
                    echo json_encode( array( 'rc'=>'0', 'frame'=>'left' ) );
                }
                $dbfile->setDocData("pfad",$pfadleft);
                $rc=$dbfile->saveDocument();
            }
            rename($pre."/".$oldpath."/".$file,$pre.$pfadleft."/".$file);
        }
        if ($file[0]=="/") $file=substr($file,1);
        echo json_encode( array( 'rc'=>'1', 'frame'=>'left', 'pfad'=>$pfadleft, 'file'=>$file ) );
    }
    function saveAttribut($name,$oldname,$pfad,$komment,$id=0) {
        $dbfile=new document();
        if ($id>0) {
            $rc=$dbfile->getDokument($id);
            if ($dbfile->lock>0) {
                echo json_encode( array( 'rc'=>'0', 'frame'=>'left', 'file'=>$oldname, 'lock'=>1 ) );
            }
        } else {
            $dbfile->setDocData("pfad",$pfad);
        };
        if ($oldname<>$name) {
            $path="../dokumente/".$_SESSION["dbname"].$pfad.'/';
            $dbfile->setDocData("name",$name);
            rename($path.$oldname,$path.$name);
            $oldname=$name;
        } else {
            $dbfile->setDocData("name",$oldname);
        }
        $dbfile->setDocData("descript",$komment);
        $rc=$dbfile->saveDocument();
        if ($rc) {
            echo json_encode( array( 'rc'=>'1', 'frame'=>'left', 'pfad'=>$pfad, 'file'=>$name ) );
        } else {
            echo json_encode( array( 'rc'=>'0' ) );
        }
    }
    function newDir($pfad,$newdir) {
        chdir($_SESSION['erppath']."/crm/dokumente/".$_SESSION["dbname"]."/$pfad");
        $tmpdata = getUserEmployee(array('dir_mode','dir_group'));
        $rc = mkdir($newdir);
        if ($rc) {
            chmod($newdir,octdec($tmpdata['dir_mode']));
            chgrp($newdir,$tmpdata['dir_group']);    
            echo 'ok';
        } else {
            echo 'Error';
        }
    }
    function delDir($pfad,$del) {
        error_log($_SESSION['erppath']."crm/dokumente/".$_SESSION["dbname"].$pfad);
        chdir($_SESSION['erppath']."crm/dokumente/".$_SESSION["dbname"].$pfad);
        $rc  = rmdir($del);
        //echo $rc;
        echo $_SESSION['erppath']."crm/dokumente/".$_SESSION["dbname"].$pfad.'/'.$del;
    };
    function delFile($id=0,$pfad="",$file="") {
        $dbfile=new document();
        if ($id>0) {
            $dbfile->getDokument($id);
            if ($dbfile->lock>0) {
                echo 'File lock';
                return;
            }
        } else {
            $dbfile->setDocData("name",$file);
            $dbfile->setDocData("pfad",$pfad);
        }
        $rc = $dbfile->deleteDocument(".");
        echo 'ok';
    }

    function getUsermail( $uid ) {
        $items = getAllTelCallUser($uid,0,"M");
        echo json_encode( $items );
    }
    function setSync($Q,$id,$val){
             if ( $Q == 'P' ) {
               $sql = 'UPDATE contacts SET cp_sync = %d WHERE cp_id = %d'; }
        else if ( $Q == 'C' ) {
               $sql = 'UPDATE customer SET sync = %d WHERE id = %d'; }
        else if ( $Q == 'V' ) {
               $sql = 'UPDATE vendor SET sync = %d WHERE id = %d'; }
        else {
            echo '-1';
            return;
        }
        $rc = $GLOBALS['db']->query(sprintf($sql,$val,$id));
        echo $rc;
    }
$f=fopen('/tmp/ana.log','a');
fputs($f,print_r($_GET,true));
switch ($_GET['task']) {
    case 'bland'             : Buland( $_GET['land'] );
                               break;
    case 'showFaAdress'      : getFaStamm( $_GET['id'], $_GET['Q'] );
                               break;
    case 'shipto'            : getShipto( $_GET['id'], $_GET['Q'] );
                               break;
    case 'showCalls'         : showCalls( $_GET['id'], $_GET['firma'] );
                               break;
    case 'showShipadress'    : showShipadress( $_GET['id'], $_GET['Q'], $_GET['fid'], $_GET['cnt'] );
                               break;
    case 'showContact'       : showContactadress( $_GET['id'] );
                               break;
    case 'editTevent'        : editTevent( $_GET['id'] );
                               break;
    case 'geteventlist'      : listTevents( $_GET['id'], $_GET['fid'] , $_GET['tab']);
                               break;
    case 'getCustomTermin'   : getCustomTermin( $_GET['id'], $_GET['tab'], $_GET['day'], $_GET['month'], $_GET['year'] );
                               break;
    case 'showDir'           : showDir( $_GET['id'], $_GET['dir'] );
                               break;
    case 'showFile'          : showFile( $_GET['pfad'], $_GET['file'] );
                               break;
    case 'lockFile'          : lockFile( $_GET['file'],  $_GET['pfad'], $_GET['id'] );
                               break;
    case 'moveFile'          : moveFile( $_GET['file'], $_GET['pfadleft'] );
                               break;
    case 'saveAttribut'      : saveAttribut( $_GET['name'],  $_GET['oldname'], $_GET['pfad'], $_GET['komment'], $_GET['id'] );
                               break;
    case 'newDir'            : newDir( $_GET['pfad'], $_GET['newdir'] );
                               break;
    case 'delDir'            : delDir( $_GET['pfad'], $_GET['del'] );
                               break;
    case 'delFile'           : delFile( $_GET['id'], $_GET['pfad'], $_GET['file'] );
                               break;
    case 'usermail'          : getUsermail( $_GET['uid'] );
                               break;
    case 'setsync'           : setSync($_GET['Q'],$_GET['id'],$_GET['val']);
                               break;
    default                  : echo "nicht erlaubt";
};
?>
