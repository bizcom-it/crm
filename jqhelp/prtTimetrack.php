<?php
	require_once("../inc/stdLib.php");
	include_once("FirmenLib.php");	
    include_once("pdfpos.php");
    $f = fopen('/tmp/prtpdf.log','w');
    fputs($f,print_r($_POST,true)."\n");
    define("FPDF_FONTPATH","/usr/share/fpdf/font/");
    define("FONTART","2");
    define("FONTSTYLE","1");
	$firma = getFirmenStamm($_POST['fid']);
    $sql      = 'SELECT * FROM timetrack WHERE id = '.$_POST['tid'];
    $event    = $GLOBALS['db']->getOne($sql);
    $sql      = 'SELECT * FROM tt_event WHERE ';
    $where    = 'ttid = '.$_POST['tid'];
    if ( $_POST['was'] == '1' ) {
        $where .= ' AND cleared = NULL';
    } else if ( $_POST['was'] == '2' ) {
        if ( $_POST['von'] == '' ) { $von = '1970-01-01'; $tvon = '';}
        else { $von = date2db( $_POST['von'] ); $tvon = $_POST['von']; };
        if ( $_POST['bis'] == '' ) { $bis = "now()"; $tbis = 'jetzt'; }
        else { $bis = "'".date2db( $_POST['bis'] )."'";  $tbis = $_POST['bis']; };
        $where .= " AND ttstart BETWEEN  '$von' AND $bis";
    }
    $ttevents = $GLOBALS['db']->getAll($sql.$where.' ORDER BY id');
    fputs($f,$sql."\n");
    fputs($f,print_r($ttevents,true)."\n");
    $sql      = "SELECT * FROM tt_parts WHERE eid in ( SELECT id FROM tt_event WHERE $where )";
    $ttparts  = $GLOBALS['db']->getAll($sql);
    $parts = array();
    if ( $ttparts ) foreach ( $ttparts as $tmp ) { $parts[$tmp['eid']][] = $tmp; };
    fputs($f,$sql."\n");
    fputs($f,print_r($ttparts,true)."\n");
    fputs($f,print_r($parts,true)."\n");
	require("fpdf.php");
	require("fpdi.php");
	$pdf = new FPDI('P','mm','A4');
	$seiten=$pdf->setSourceFile("../vorlage/timetracker.pdf");
	$hdl=$pdf->ImportPage(1);
	$pdf->addPage();
	$pdf->useTemplate($hdl);
    $pdf->SetFont($ttfont,'B',$ttsize);
    $pdf->Text($ttname[x] ,$ttname[y] ,utf8_decode($firma["name"]));
    $pdf->Text($ttstr[x]  ,$ttstr[y]  ,utf8_decode($firma["street"]));
    $pdf->Text($ttort[x]  ,$ttort[y]  ,$firma["zipcode"]." ".utf8_decode($firma["city"]));
	$pdf->Text($tttid[x],  $tttid[y]  ,$_POST["tid"]);

    $pdf->SetFont($ttfont ,'',$ttsize);
    $pdf->Text($ttkdnr[x]  ,$ttkdnr[y]  ,$firma["customernumber"]);
	$pdf->Text($ttdate[x]  ,$ttdate[y]  ,date("d.m.Y"));		
	$pdf->Text($ttstart[x] ,$ttstart[y] ,$tvon);		
	$pdf->Text($ttende[x]  ,$ttende[y]  ,$tbis);		
	$pdf->Text($ttpro[x]   ,$ttpro[y]   ,utf8_decode($event["ttname"]));
    if ( $ttbem['print'] )	$pdf->Text($ttbem[x]   ,$ttbem[y]   ,utf8_decode($event["ttdescription"]));
	$pdf->SetY($ttevent1[y]);
    $pdf->SetLeftMargin($ttevent1[x]);
    $pdf->SetAutoPageBreak(true);
    $output  = '';
    $evx     = $ttevent1[x];
    $evy     = $ttevent1[y];
    foreach ( $ttevents as $ttrow ) {
        $pdf->cell(28,10,db2date($ttrow['ttstart']),1,0);
        $pdf->cell(28,10,db2date($ttrow['ttstop']),1,0);
        $txt = utf8_decode(strtr($ttrow['ttevent'],chr(13),''));
        //$pdf->cell(110,10,$txt,1,0);
        $x=$pdf->GetX();
        $y=$pdf->GetY();
        $pdf->MultiCell(110,10,$txt,1,'L',0);
        $pdf->SetXY($x+110, $y);
        $pdf->Ln(10);
        fputs($f,"suche ".$ttrow['id']."\n");
        if ( isset($parts[$ttrow['id']]) ) {
            fputs($f,$ttrow['id']." in parts\n");
            foreach ( $parts[$ttrow['id']] as $teile ) {
                fputs($f,print_r($teile,true)."\n");
                $pdf->cell(56,10,'',1,0);
                if ( intval($teile['qty']) == $teile['qty'] ){
                    $txt  = sprintf('%d x ',$teile['qty']);
                } else {
                    $txt  = sprintf('%.f x ',$teile['qty']);
                }
                $txt .= utf8_decode($teile['parts_txt']);
                $pdf->cell(110,10,$txt,1,0);
                $pdf->Ln(10);
            }
        }
    }
    $PDF = $pdf->OutPut('Zeiterfassung_'.$_POST["tid"].'.pdf',"S");
    $ff = fopen('/tmp/x.pdf','wb'); fputs($ff,$PDF);
    echo json_encode(array('Ret'=>'ok','PDF'=>$PDF)); 
?>
