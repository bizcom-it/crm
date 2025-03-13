<?php
	require_once("inc/stdLib.php");
	include("inc/crmLib.php");
	$menu = $_SESSION['menu'];
    $head = mkHeader();
    $init = array('id'=>false, 'name'=>false, 'cust'=>false, 'Text'=>false, 'Schrift'=>false, 'margintop'=>false, 'marginleft'=>false,
                  'PA3'=>false,'PA4'=>false,'PA5'=>false,'Pletter'=>false,'Plegal'=>false,'mm'=>false,'in'=>false,'format'=>false,
                  'spacex'=>false,'spacey'=>false,'width'=>false,'height'=>false,'metric'=>false,'papersize'=>false );
    $Textzeilen = 0;
    foreach ( $init as $key=>$val ) ${$key} = $val;
    for ( $i=1; $i<7;  $i++) ${'S'.$i}  = false;
    for ( $i=1; $i<13; $i++) ${'Z'.$i}  = false;
    if ( !isset($_POST) ) $_POST = $init; 


    function schriften($sel) {
        $line = '';
        for ( $i=6; $i<17; $i++ ) {
            $line .= "\t<option value='$i'".(( $i == $sel )?' selected>':'>')."$i\n";
        }
        return $line;
    }


	if ( isSetVar($_POST['hole']) &&  isSetVar($_POST['format']) ){
		if ( isSetVar($_POST['Text']) ) unset($_POST['Text']);
		$label  = getOneLable($_POST['format']);
		$id     = $label['id'];
		$format = $id;
		$name   = $label['name'];
		$cust   = $label['cust'];
		$papersize  = $label['papersize']; 
		$metric = $label['metric']; 
		$margintop  = $label['margintop']; 
		$marginleft = $label['marginleft']; 
		$spacex = $label['spacex'];;
		$spacey = $label['spacey']; 
		$nx     = $label['nx']; 
		$ny     = $label['ny']; 
		$width  = $label['width']; 
		$height = $label['height']; 
		$Ssel   = "S".$nx;	
        $Zsel   = "Z".$ny; 
        $Psel   = "P".$papersize; 
        $tmp    = $metric;
		${$Ssel} = " selected";	${$Zsel} = " selected"; ${$Psel} = " selected"; ${$tmp} = " selected";
		$Textzeilen = count($label['Text']);
		if ( $Textzeilen>0 ) { 
			$i = 0; unset($Text); unset($Schrift);
			foreach( $label['Text'] as $row ) {
				$Text[]     = $row['zeile'];
                $Schrift[]  = $row['font'];
				$i++;
			}
		}
	} else if ( isSetVar($_POST['ok']) || isSetVar($_POST['csave']) ){
		if ( isSetVar($_POST['ok']) ) { 
			updLable($_POST); $id = $_POST['id']; 
			$format = $_POST['format']; 
		} else { 
			$id     = insLable($_POST); 
			$format = $id; 
		};
		$margintop  = $_POST['margintop']; 
		$marginleft = $_POST['marginleft']; 
		$spacex     = $_POST['spacex'];;
		$spacey     = $_POST['spacey']; 
		$nx         = $_POST['nx']; 
		$cust       = $_POST['cust']; 
		$name       = $_POST['name']; 
		$ny         = $_POST['ny']; 
		$papersize  = $_POST['papersize']; 
		$metric     = $_POST['metric']; 
		$width      = $_POST['width']; 
		$height     = $_POST['height']; 
		$Ssel       = "S".$nx;	$Zsel="Z".$ny;$Psel="P".$papersize; $tmp=$metric;
		${$Ssel}    = " selected";	${$Zsel}=" selected"; ${$Psel}=" selected"; ${$tmp}=" selected";
		if ( isSetVar($_POST['Text']) ) {
			$Text    = $_POST['Text'];
			$Schrift = $_POST['Schrift'];
		    $Textzeilen = count( $_POST['Text'] );
		}
	} else if ( isSetVar($_POST['test']) ) {
		$lableformat=array("paper-size"=>$_POST['papersize'],'name'=>$_POST['name'], 'metric'=>$_POST['metric'], 
							'marginLeft'=>$_POST['marginleft'], 'marginTop'=>$_POST['margintop'], 
							'NX'=>$_POST['nx'], 'NY'=>$_POST['ny'], 'SpaceX'=>$_POST['spacex'], 'SpaceY'=>$_POST['spacey'],
							'width'=>$_POST['width'], 'height'=>$_POST['height'], 'font-size'=>6);
		require_once('inc/PDF_Label.php');
		$SX=1; $SY=1; unset($tmp);
		$metric     = $lableformat['metric']; 
		$pdf = new PDF_Label($lableformat, $metric, $SX, $SY);
		$pdf->Open();
		if ($SX<>1 or $SY<>1)	$pdf->AddPage();
		for ($i=0; $i<count($_POST['Text']); $i++) {
			$tmp[]=array("text"=>$_POST['Text'][$i],"font"=>$_POST['Schrift'][$i]);
		};
		for ($i=0; $i<($_POST['nx']*$_POST['ny']); $i++) {
			$pdf->Add_PDF_Label2($tmp);
		};
		$pdf->Output();
		$Textzeilen=count($_POST['Text']);
	}
	$ALabels=getLableNames();
	
	if (!$Textzeilen || $Textzeilen===0) {
        if ( isSetVar($_POST['height']) ) {
            $Textzeilen=floor($_POST['height']/(($_POST['metric']=="mm")?5:0.197));
        } else{
            $Textzeilen = 6;
        }
    }

	if ( isSetVar($_POST['more']) ) {
		$Textzeilen = count($_POST['Text']);
        //$Schrift = 
        $_POST['Schrift'][$Textzeilen] = '6';
        $_POST['Text'][$Textzeilen]    = '';
		$Textzeilen++;
	}
	if ( isSetVar($_POST['less']) ) {
		$Textzeilen = count($_POST['Text']);
		if ( $Textzeilen>1 ) $Textzeilen--;
        $Schrift = $_POST['Schrift'];
	}
	if ( isSetVar($_POST['less']) || isSetVar($_POST['more']) ) {
		$Text       = $_POST['Text'];
		$format     = $_POST['format']; 
		$margintop  = $_POST['margintop']; 
		$marginleft = $_POST['marginleft']; 
		$spacex     = $_POST['spacex'];;
		$spacey     = $_POST['spacey']; 
		$papersize  = $_POST['papersize']; 
		$metric     = $_POST['metric']; 
		$nx         = $_POST['nx']; 
		$ny         = $_POST['ny']; 
		$id         = $_POST['id']; 
		$name       = $_POST['name']; 
		$cust       = $_POST['cust']; 
		$width      = $_POST['width']; 
		$height     = $_POST['height']; 
        $Schrift    = $_POST['Schrift'];
		$Ssel = "S".$nx;	$Zsel = "Z".$ny;  $Psel = "P".$papersize; $tmp = $metric;
		${$Ssel}    = " selected";	${$Zsel} = " selected"; ${$Psel} = " selected"; ${$tmp} = " selected";
	}
?>
<html>
	<head>
		<title></title>
<?php 
echo $menu['stylesheets']; 
echo $menu['javascripts']; 
echo $head['CRMCSS']; 
echo $head['JQTABLE'];
echo $head['THEME']; 
echo $head['JQTABLE']; 
?>
	</head>
<body>
<?php echo $menu['pre_content'];?>
<?php echo $menu['start_content'];?>
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Etiketten');">Etiketten-Editor</h1><br>

<table><tr><td style="width:280px" valign='top'>
<form name="defaufkleber" action="aufkleber_def.php" method="post">
<input type="hidden" name="id" value="<?php echo  $id ?>">
<input type="hidden" name="name" value="<?php echo  $name ?>">
<input type="hidden" name="cust" value="<?php echo  $cust ?>">
<table style="width:100%">
	<tr>
		<th colspan="4"><h3>Seitendefinition</h3></th>
	</tr>
	<tr>
		<th colspan="4" class="listtop">Seitengr&ouml;&szlig;e</th>
	</tr>
	<tr>
		<td>Format</td>
		<td>
			<select name="papersize">
				<option value="A4"<?php echo  $PA4 ?>>A4
				<option value="A3"<?php echo  $PA3 ?>>A3
				<option value="A5"<?php echo  $PA5 ?>>A5
				<option value="letter"<?php echo  $Pletter ?>>Letter
				<option value="legal"<?php echo  $Plegal ?>>Legal
			</select>
		</td>
		<td>Metric</td>
		<td>
			<select name="metric">
				<option value="mm"<?php echo  $mm ?>>mm
				<option value="in"<?php echo  $in ?>>in
			</select>
		</td>
	</tr>
	<tr>
		<th colspan="4" class="listtop">Seitenr&auml;nder</th>
	</tr>
	<tr>
		<td>oben</td><td><input type="text" name="margintop" size="6" value="<?php echo  $margintop ?>"></td>
		<td>links</td><td><input type="text" name="marginleft" size="6" value="<?php echo   $marginleft ?>"></td>
	</tr>	
	<tr>
		<th colspan="4" class="listtop">Abst&auml;nde</th>
	</tr>
	<tr>
		<td>Spalten</td><td><input type="text" name="spacex" size="6" value="<?php echo  $spacex ?>"></td>
		<td>Zeilen</td><td><input type="text" name="spacey" size="6" value="<?php echo  $spacey ?>"></td>
	</tr>
	<tr>
		<th colspan="4" class="listtop">Gr&ouml;&szlig;e der Aufkleber</th>
	</tr>
	<tr>
		<td>Breite</td><td><input type="text" name="width" size="6" value="<?php echo  $width ?>"></td>
		<td>H&ouml;he</td><td><input type="text" name="height" size="6" value="<?php echo  $height ?>"></td>
	</tr>
	<tr>
		<th colspan="4" class="listtop">Anzahl der Aufkleber</th>
	</tr>
	<tr>
		<td>Spalten</td>
		<td><select name="nx">
			<option value="1"<?php echo  $S1 ?>>1
			<option value="2"<?php echo  $S2 ?>>2
			<option value="3"<?php echo  $S3 ?>>3
			<option value="4"<?php echo  $S4 ?>>4
			<option value="5"<?php echo  $S5 ?>>5
			<option value="6"<?php echo  $S6 ?>>6
			</select>
		</td>
		<td>Zeilen</td>
		<td><select name="ny">
			<option value="1"<?php echo  $Z1 ?>>1
			<option value="2"<?php echo  $Z2 ?>>2
			<option value="3"<?php echo  $Z3 ?>>3
			<option value="4"<?php echo  $Z4 ?>>4
			<option value="5"<?php echo  $Z5 ?>>5
			<option value="6"<?php echo  $Z6 ?>>6
			<option value="7"<?php echo  $Z7 ?>>7
			<option value="8"<?php echo  $Z8 ?>>8
			<option value="9"<?php echo  $Z9 ?>>9
			<option value="10"<?php echo  $Z10 ?>>10
			<option value="11"<?php echo  $Z11 ?>>11
			<option value="12"<?php echo  $Z12 ?>>12
			</select>
		</td>
	</tr>
	<tr>
		<td colspan="3"></td><td valign="right"></td>
	</tr>
</table>
</td><td width="*" valign='top'>
<table style="width:100%">
	<tr>
		<th class="listelement"><h3>Aktionen</h3></th>
	</tr>
	<tr><td class="listtop">gespeicherte Labels</td></tr>
	<tr><td class="ce">
			<select name="format">
				<option></option>
<?php
	foreach ($ALabels as $data) {
		echo "\t\t\t\t<option value='".$data['id']."'";
		if ($data['id']==$format) echo " selected";
		echo ">";
		if ( isset($data['Cust']) ) echo "C ";
		echo $data['name']."\n";
	}
?>
			</select><input type="submit" name="hole" value="lade">
	</td></tr>
	<tr><td class="ce"><input type="text" name="custname" size="12"></td></tr>
	<tr><td class="ce"><input type="submit" name="csave" value="sichern als Neu"></td>	</tr>
	<tr><td class="ce"><br><input type="submit" name="test" value="testen"></td></tr>
	<tr><td class="ce"><br><input type="submit" name="more" value="mehr Textzeilen"></td></tr>
	<tr><td class="ce"><input type="submit" name="less" value="weniger Textzeilen"></td></tr>
	<tr><td class="ce"><br><input type="submit" name="ok" value="sichern"> </td></tr>
</table>
</td><td valign='top'>
	<form name="adrtxt" method="post">
<table style="width:290px">
	<tr>
		<th colspan="3" class="listelement"><h3>Texte f&uuml;r  Aufkleber</h3></th>
	</tr>
	<tr>
		<th colspan="2" class="listtop">Font</th>
		<th class="listtop">Text</th>
	</tr>
<?php	for ($i=0; $i<$Textzeilen;$i++) { ?>
		<tr><td width="50px"><select name="Schrift[]">
<?php  echo schriften($Schrift[$i]); ?>
			</select>
			</td>
			<td width="*" ><?php echo  $i ?></td><td><input type="text" name="Text[]" size="30" value="<?php echo  $Text[$i] ?>"></td></tr>
<?php } ?>
</table>
</td></tr>

</table>

</form>
</div>
<?php echo $menu['end_content']; ?>
</body>
</html>
