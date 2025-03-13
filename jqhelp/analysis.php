<?php
unset($GLOBALS['php_errormsg']);
require_once("../inc/stdLib.php");
define('TTF_DIR','/usr/share/fonts/truetype/msttcorefonts/');
//define('MBTTF_DIR','/usr/share/fonts/truetype/msttcorefonts/');
//define('TTF_DIR','/usr/share/fonts/truetype/freefont/');
//define('MBTTF_DIR','/usr/share/fonts/truetype/freefont/');
include ("jpgraph.php");
include ("jpgraph_line.php");
include ("jpgraph_date.php");
include ("jpgraph_utils.inc.php");


function doGraph($x,$l,$legend,$data,$w,$h) {
    //$color   = array('blue','red','green','yellow');
    $color   = array('#3333ff','#ff3333','#33ff33','yellow');
    $col     = 0;
    $keys    = array_keys($legend);
    $zeit    = array();
    $betrag  = array();
    $rechts  = 40;
    fputs($GLOBALS['f'],print_r($data,true));
    $graph = new Graph($w,$h);

    //Y-Achsen erzeugen
    for( $i=0; $i<count($keys); $i++ ) { 
        $graph->SetYScale($i,'lin');
        $rechts += 30;
    };

    //Daten separieren für jede Y-Achse
    foreach($data as $row) {
        $zeit[]    = $row[$x];
        foreach ( $legend as $key=>$val ) {
            $betrag[$val][]  = $row[$val];
            if ( $col == 0 ) { $first = $val; $label = $key; }  //Erste Achse
            $col++;
        }
    };

    fputs($GLOBALS['f'],"B".print_r($betrag,true));
    fputs($GLOBALS['f'],"Z".print_r($zeit,true));
    $graph->SetScale('textlin');
    $graph->img->SetMargin(60,$rechts,10,60);
    $graph->xaxis->SetPos('min');

    $graph->yaxis->title->SetFont(FF_ARIAL,FS_NORMAL,14);
    $graph->xaxis->title->SetFont(FF_ARIAL,FS_NORMAL,14);
    $graph->yaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
    $graph->xaxis->SetFont(FF_ARIAL,FS_NORMAL,9);    
    $graph->xaxis->title->Set($l);
    $graph->xaxis->title->SetMargin(17);
    $graph->yaxis->title->SetMargin(12);
    $graph->xaxis->SetLabelAngle(30);
    $graph->xaxis->SetLabelMargin(-2);
    $graph->xaxis->SetTickSize(3);

    //X-Achse
    $graph->xaxis->SetTickLabels($zeit);

    $firstdata  = array_shift($betrag);
    $firstlable   = $keys[0]; //$array_shift($keys);
    array_shift($legend);

    fputs($GLOBALS['f'],"A\n");
    //Erste Y-Achse ausgeben
    $plotb = new LinePlot($firstdata);
    //$plotb->SetLegend($firstlable);
    $plotb->SetColor($color[0]);
    $plotb->mark->SetType(MARK_FILLEDCIRCLE);
    $plotb->mark->SetWidth(3);
    $plotb->mark->SetColor($color[0]);
    $graph->Add($plotb);
    $graph->yaxis->SetColor($color[0]);
    $graph->yaxis->title->Set($firstlable);
    $graph->xgrid->Show();
    //$graph->ynaxis[0]->SetColor($color[0]);
    //$graph->ynaxis[0]->title->Set($firstlable);
    $graph->AddY(0,$plotb);

    fputs($GLOBALS['f'],"B\n");
    $plot = array();
    $col     = 1;
    //Alle übrigen Y-Achsen ausgeben
    foreach ( $legend as $key=>$val ) {
        $plot[$col] = new LinePlot($betrag[$val]);
        $plot[$col]->SetCenter(true);
        $plot[$col]->mark->SetType(MARK_FILLEDCIRCLE);
        $plot[$col]->mark->SetWidth(3);
        $plot[$col]->mark->SetColor($color[$col]);
        $plot[$col]->mark->SetFillColor($color[$col]);
        //$plot[$col]->SetLegend($key);
        $plot[$col]->SetColor($color[$col]);
        $graph->ynaxis[$col]->title->SetFont(FF_ARIAL,FS_NORMAL,14);
        $graph->ynaxis[$col]->title->Set($key);
        $graph->ynaxis[$col]->SetColor($color[$col]);
        $graph->AddY($col,$plot[$col]);
        $col++;
    }
    fputs($GLOBALS['f'],"C\n");
    array_map('unlink', glob($_SESSION['erppath'].'crm/tmp/'.$_SESSION['login'].'_*.png'));
    $IMG=$_SESSION['login'].'_'.time().'.png';
    $graph->Stroke($_SESSION['erppath'].'crm/tmp/'.$IMG);
    return "<img src='tmp/$IMG'>";
}

function mkCSV($data,$header,$typ) {
    $fpath = $_SESSION['erppath'].'crm/tmp/';
    $fname = $_SESSION['login'].'_data_'.$typ.'.csv';
    $fn    = fopen($fpath.$fname,'w');
    $line = join(';',$header);
    fputs($fn,$line."\n");
    foreach ($data as $row) {
        $line = join(';',$row);
        fputs($fn,$line."\n");
    };
    fclose($fn);
    return '<a href="tmp/'.$fname.'">CSV-File</a>';
}
function doFormat($format,$data) {
    if ( $format == 'date' ) {
        $tmp = explode('-',$data);
        $out =  sprintf('<td>%02d.%02d.%04d</td>',$tmp[2],$tmp[1],$tmp[0]);
    } else if ( $format == 'jamo' ) {
        $out = '<td>'.substr($data,4,2).' - '.substr($data,0,4).'</td>';
    } else if ( $format == '%s' ) {
        $out = '<td>'.$data.'</td>';
    } else {
        $out = '<td align="right">'.sprintf($format,$data).'</td>';
    };
    return $out;
}


function doTabelle($header,$data) {
    if ( empty($data) ) return 'Keine Daten';
    $tbl = "<br><table id='datatable' class='tablesorter' style='margin:0px; cursor:pointer;'>\n<thead>";
    $tblhead = '';
    $keys = array_keys($header);
    $format = array_values($header);
    if ( !empty($header) ) { 
        $tmp = join('</th><th>',$keys)  ;
        $tblhead .= '<tr><th>'.$tmp.'</th></tr>'."\n";
    };
    $line = 0;
    $tbl .= $tblhead.'</thead><tbody>';
    foreach ($data as $row) {
        $nr = 0;
        $tmp = '';
        $odd = true;
        foreach ( $row as $cell ) {
            $tmp .= doFormat($format[$nr],$cell);
            $nr++;
        };
        $tbl .= "<tr class='line".($line % 2)."'>".$tmp.'</tr>'."\n";
        //$tbl .= '<tr>'.$tmp.'</tr>'."\n";
        $line++;
    };
    //return array('head'=>$tblhead,'body'=>$tbl); 
    return $tbl."</tbody></table>";
}

function doBericht($data) {
    $msg     = '';
    $sql     = '';
    $header  = '';
    $file    = '';
    $tabelle = false;
    $graph   = false;
    $WHERE   = '';
    $HAVING  = '';
    $LIMIT   = '';
    $ORDER   = '';
    if ( empty($data['von']) ) {
        $VONDATE = '1900-01-01';
    } else {
        $VONDATE = date2db($data['von']);
    };
    if ( empty($data['bis']) ) {
        $BISDATE = date2db();
    } else {
        $BISDATE = date2db($data['bis']);
    };
    $VONYYYYMM = substr($VONDATE,0,7);
    $BISYYYYMM = substr($BISDATE,0,7);
    $sql = "SELECT * FROM analysistyp WHERE bericht = '".$data['bericht']."'";
    $bericht = $GLOBALS['db']->getOne($sql);
    $W = array();
    $H = array();
    if ( $bericht ) {
        if ( ! empty($data['zusatz']) ) {
            $tmp = explode(';',$data['zusatz']);
            if ( $tmp ) foreach ($tmp as $tmp2) {
                if ( $tmp2 != '' ) {
                    $tmp3 = splitZusatz($tmp2);
                    if ( $tmp3['HAVING'] ) {
                       $H[] = $tmp3['SQL'];
                    } else {
                       $W[] = $tmp3['SQL'];
                    };
                };
            };
  	        if ( $W[0] != '' ) $WHERE = ' AND '.join(' AND ',$W);
  	        if ( $H[0] != '' ) $HAVING = ' HAVING '.join(' AND ',$H);
        };

        if ( isset($data['best']) && $data['best']*1 > 0 ) {
            $LIMIT = ' LIMIT '.$data['best'];
            if ( $data['bestart'] == 'sum' ) {
                $ORDER = 'betrag DESC ';
            } else {
                $ORDER = 'cnt DESC ';
            }
        } else {
            $ORDER = $bericht['orders'];
            $LIMIT = '';
        };
        $header = (array) json_decode($bericht['header']);
        $legend = (array) json_decode($bericht['legend']);
        $xaxis  = $bericht['xaxis'];
        $xlabel = $bericht['xlabel'];
        $note   = strtr($bericht['note'],array("\n"=>'<br>'));
        $sql    = $bericht['sql'].' '.$ORDER.' '.$LIMIT;
        $sql    = preg_replace('!#VONDATE#!',$VONDATE,$sql);
        $sql    = preg_replace('!#BISDATE#!',$BISDATE,$sql);
        $sql    = preg_replace('!#VONYYYYMM#!',$VONYYYYMM,$sql);
        $sql    = preg_replace('!#BISYYYYMM#!',$BISYYYYMM,$sql);
        $sql    = preg_replace('!#WHERE#!',$WHERE,$sql);
        $sql    = preg_replace('!#HAVING#!',$HAVING,$sql);
        if ( $sql != '' ) {
            $rs = $GLOBALS['db']->getAll($sql);
            if ( $rs ) {
                $file = mkCSV($rs,array_keys($header),$data['bericht']);
                if ( $data['ausgabe'] == 1 || $data['ausgabe'] == 3 ) $tabelle = doTabelle($header,$rs);
                if ( $data['ausgabe'] == 2 || $data['ausgabe'] == 3 ) {
                    $tmpdata = getUserEmployee(array('iwidth','iheight'));
                    $graph = doGraph($xaxis,$xlabel,$legend,$rs,$tmpdata['iwidth'],$tmpdata['iheight']);
                };
            } else {
                $msg = '<h3>Keine Treffer</h3>';
            };
        } else {
            $msg = '<h3>Bericht nicht möglich</h3>';
        };
    } else {
        $msg = 'Fehler Datenbankabfrage'; $tabelle = False; $graph = False; $file = False; $note = ''; 
    }
    echo json_encode(array('msg'=>$msg,'tabelle'=>$tabelle,'grafik'=>$graph,'file'=>$file, 'note' => $note, 'sql' => $sql));
}
function splitZusatz($data) {
    $having = false;
    $tmp    = explode(':',$data);
    if ( $tmp[1] != '' ) {
        if ( substr($tmp[0],0,1) == 'H' ) {
		        $having = true;
                $key    = substr($tmp[0],1);
	    } else {
            $key = $tmp[0];
        }
        if ( substr($tmp[1],0,1) == '>' or 
             substr($tmp[1],0,1) == '<' or
             substr($tmp[1],0,1) == '=' ) {
                 $bed = substr($tmp[1],0,1);
                 $val = "'".substr($tmp[1],1)."'";
        } else {
                if ( $tmp[1] == '' ) $tmp[1] = '%';
                $bed = " ilike ";
                $val = "'".$tmp[1]."'";
        };
    } else {
        return False;
    };
    return array('HAVING'=>$having,'SQL'=>$key.$bed.$val);
}
function doZusatzfelder($bericht) {
    $sql = "SELECT felder,note FROM analysistyp WHERE bericht = '$bericht'";
    $rs  = $GLOBALS['db']->getOne($sql);
    $felder = '';
    if ( $rs['felder'] != '' ) {
        $tmp = split(';',$rs['felder']);
        foreach ($tmp as $line) {
            $tmp2 = split(':',$line);
            $felder .= $tmp2[0].' <input type="text" name="'.$tmp2[1].'" size="20" id="'.$tmp2[1].'" ><br>';
        }
    }
    echo json_encode(array('msg'=>$msg,'felder'=>$felder,'note'=>strtr($rs['note'],array("\n"=>'<br>'))));
}
function getForm($id) {
    $sql = "SELECT * FROM analysistyp WHERE bericht = '$id'";
    $rs  = $GLOBALS['db']->getOne($sql);
    if ( $rs ) { $msg = 'ok'; }
    else { $msg = 'Bericht nicht gefunden?!?'; };
    echo json_encode(array('msg'=>$msg,'data'=>$rs));
}
function saveForm($data) {
    $fields    = array('xaxis','xlabel','bericht','label','sql','legend','header'); //Müssen Inhalt haben
    $allfields = array('bericht','label','sql','orders','felder','header','legend','xaxis','xlabel','note'); //zu sichernde Felder
    $ok = true;
    foreach ( $fields as $feld ) {
        if ( empty($data[$feld]) ) {
            echo json_encode(array('msg'=>'Fehler! Feld darf nicht leer sein: '.$feld,'feld'=>$feld,'data'=>$data));
            return;
        };
    };
    $fields[] = 'felder';
    $fields[] = 'note';
    $values = array();
    #foreach ($data as $key=>$val) $values[$key] = addslashes($val) ;
    #foreach ($data as $key=>$val) $values[$key] = $GLOBALS['db']->saveData($val) ;
    if ( $data['id'] > 0 ) {  //Update
       //$sql  = "UPDATE analysistyp SET bericht='%s',label='%s',felder='%s',xaxis='%s',xlabel='%s',";
       //$sql .= "sql='%s',orders='%s',header='%s',legend='%s',note='%s' WHERE id = %s";
       //$rc   = $GLOBALS['db']->query(sprintf($sql,$values['bericht'],$values['label'],
       //                                           $values['felder'],$values['xaxis'],
       //                                           $values['xlabel'],$values['sql'],
       //                                           $values['orders'],$values['header'],
       //                                           $values['legend'],$values['note'],$values['id']),true);
       //$rc     = $GLOBALS['db']->update('analysistyp',$allfields,$values,'id = '.$data['id']);
       $id = $data['id'];
       unset($data['id']);
       $rc     = $GLOBALS['db']->update('analysistyp',$allfields,$data,'id = '.$id);
    } else {                  //Insert
       //$sql  = "INSERT INTO analysistyp (bericht,label,felder,sql,orders,header,legend,xaxis,xlabel,note) ";
       //$sql .= "VALUES ('".$values['bericht']."','".$values['label']."','";
       //$sql .= $values['felder']."','".$values['sql']."','".$values['orders']."','";
       //$sql .= $values['header']."','".$values['legend']."','".$values['xaxis']."','";
       //$sql .= $values['xlabel']."','".$values['note']."')";
       //$rc   = $GLOBALS['db']->query($sql,true); 
       unset($data['id']);
       $rc     = $GLOBALS['db']->insert('analysistyp',$allfields,$data);
    }
    if ( $rc ) { $msg = $data['bericht'].' gesichert'; $data = False; }
    else { $msg = 'Fehler beim Sichern. ID eindeutig?'; }
    echo json_encode(array('msg'=>$msg,'data'=>$data));
}
function delForm($id) {
    if ( $id > 0 ) {
        $sql = "DELETE FROM analysistyp WHERE bericht = '".$id."'";
        $rc  = $GLOBALS['db']->query($sql);
        if ( $rc ) {
            $msg = 'Bericht '.$id.' entfernt';
        } else {
            $msg = 'Fehler beim Löschen';
        }
    } else {
        $msg = 'Auswertung nicht angegeben';
    };
    echo $msg;
}

function helpText($label) {
    $txt = file_get_contents($_SESSION['erppath'].'crm/hilfe/_'.$label.".txt");
    echo $txt;
}
$task     = (isset($_POST['task']))?$_POST['task']:'initBericht';
$f=fopen('/tmp/ana.log','a');
fputs($f,print_r($_POST,true)."\n");
fputs($f,$task."\n");
switch( $task ){
    case "getBerichte":
            $WHERE = '';
            $sql  = "select count(*) as cnt from information_schema.tables  WHERE table_name = 'ekbon'";
            $rs   = $GLOBALS['db']->getOne($sql);
            if ( $rs['cnt'] == 0 ) $WHERE = " WHERE bericht not like 'K%' ";
            $sql  = "SELECT bericht AS value,bericht||' '||label AS text FROM analysistyp $WHERE ORDER BY bericht";
            $rs  = $GLOBALS['db']->getJson( $sql, array('vaule'=>'','text'=>'- - - - - - - -'));
            echo $rs;
    break;
    case "initBericht":
            $usr['loginCRM']    = $_SESSION['loginCRM'];
            $usr['countrycode'] = $_SESSION['countrycode'];
            $sql  = "SELECT count(*) as cnt FROM gruppenname n LEFT JOIN grpusr u ON n.grpid=u.grpid ";
            $sql .= "WHERE n.rechte = 'r' AND u.usrid = ".$_SESSION['loginCRM'];
            $rs = $GLOBALS['db']->getOne($sql);
            $usr['manager'] = $rs['cnt'] > 0;
            echo json_encode( $usr );
    break;
    case "doBericht":
            doBericht($_POST);
    break;
    case "getZusatzFelder":
            doZusatzfelder($_POST['bericht']);
    break;
    case "getFormData":
            getForm($_POST['bericht']);
    break;
    case "saveFormData":
            saveForm($_POST['data']);
    break;
    case "deleteFormData":
            delForm($_POST['id']);
    break;
    case "helpText":
            helpText($_POST['label']);
    break;
};

?>
