<?
//Henry Margies <h.margies@maxina.de>
//Holger Lindemann <hli@lx-system.de>
/**
 * Returns ID of a partgroup (or adds a new partgroup entry)
 * \db is the database
 * \value is the partgroup name
 * \add if true and partgroup does not exist yet, we will add it automatically
 * \returns partgroup id or "" in case of an error
 */
function getPartsgroupId($value, $add) {
	$sql = "select id from partsgroup where partsgroup = '$value'";
	$rs  = $GLOBALS['db']->getAll($sql);
	if ( empty($rs[0]["id"] ) && $add ) {
		$sql = "insert into partsgroup (partsgroup) values ('$value')";
		$rc  = $GLOBALS['db']->query($sql);
		if ( !$rc ) return "";
		return getPartsgroupId($GLOBALS['db'], $value, 0);
	}
	return $rs[0]["id"];
}

function getMakemodel($herstellerid,$model,$partsid,$cost,$newvendor,$add=true) {
    if ( !$herstellerid ) return 'f';
    $sql  = "select * from makemodel where make = $herstellerid ";
    $sql .= "and parts_id = $partsid and model = '$model' order by sortorder";
    $rs   = $GLOBALS['db']->getAll($sql);
    if ( empty($rs[0]["id"]) && $add  ) {
        if ( $newvendor ) { 
            $sql = 'SELECT count(*) FROM makemodel WHERE parts_id = '.$parts_id;
            $rs  = $GLOBALS['db']->one($sql);
            // Hier jetzt weiter
        }
        $sql = "insert into makemodel (parts_id,make,model,lastcost,sortorder,lastupdate) values ($partsid,$herstellerid,'$model',$cost,0,now())";    
        $rc  = $GLOBALS['db']->query($sql);
        if ( !$rc ) return "f";
        $sql  = 'UPDATE makemodel SET sortorder = sortorder+1 WHERE ';
        $sql .= "parts_id = $partsid and make = $herstellerid";
        $rc   = $GLOBALS['db']->query($sql);
        return getMakemodel($GLOBALS['db'],$herstellerid,$model,$partsid,$newvendor,false);
    }
    if ( $rs[0]["parts_id"] == $partsid ) { return "t"; }
    else { return "f"; }
}
function updMakemodel($herstellerid,$model,$partsid,$cost) {
    if ( !$herstellerid ) return 'f';
    $sql  = 'UPDATE makemodel SET sortorder = sortorder+1 WHERE ';
    $sql .= "parts_id = $partsid and model = '$model'";
    $rc=$GLOBALS['db']->query($sql);
    $sql  = "UPDATE makemodel SET sortorder = 1,lastcost=$cost,lastupdate=now() WHERE ";
    $sql .= "make = $herstellerid AND parts_id = $partsid and model = '$model'";
    $rc=$GLOBALS['db']->query($sql);
    if ( $rc ) { return -1; }
    else { return -99; };
}

function chkPartNumber($number,$check) {
	if ($number<>"") {
		$sql = "select * from parts where partnumber = '$number'";
		$rs=$GLOBALS['db']->getAll($sql);
	}
	if ( $rs[0]["id"] > 0 or $number == "" ) {
		if ( $check ) return "check";
		$rc  = $GLOBALS['db']->query("BEGIN");
		$sql = "select  articlenumber from defaults";
		$rs  = $GLOBALS['db']->getAll($sql);
		if ( $rs[0]["articlenumber"] ) {
			preg_match("/([^0-9]+)?([0-9]+)([^0-9]+)?/", $rs[0]["articlenumber"] , $regs);
			$number = $regs[1].($regs[2]+1).$regs[3];
		}
		$sql = "update defaults set articlenumber = '$number'";
		$rc  = $GLOBALS['db']->query($sql);
		$rc  = $GLOBALS['db']->query("COMMIT");
		$sql = "select * from parts where partnumber = '$number'";
		$rs  = $GLOBALS['db']->getAll($sql);
		if ( $rs[0]["id"]>0 ) return "";
	}
	return $number;
}
function chkPartNumberUpd($sellprice,$lastcost,$listprice,$partnumber,$descript,$note,$image,$weight,$pgid,$newvendor,$vendor,$model,$check){
	if ( $partnumber == "" ) {
		$nummer = chkPartNumber($partnumber,$check);
		if ( $nummer == "" ) { return -99; }
		else { return $nummer; };
	}
	$sql = "select * from parts where partnumber = '$partnumber'";
	$rs  = $GLOBALS['db']->getAll($sql);
	if ( $rs[0]["id"] > 0 ) {
		$sql = "update parts set ";
        if ( isset($sellprice) ) $sql .= "sellprice = $sellprice,";
        if ( isset($lastcost) )  $sql .= "lastcost = $lastcost,";
        if ( isset($listprice) ) $sql .= "listprice = $listprice,";
		if ( $weight > 0 ) $sql .= " weight=$weight,";
		if ( $pgid>0 )     $sql .="partsgroup_id=$pgid,";
		if ( $descript )   $sql .="description=E'$descript',notes=E'$note',"; 
        if ( isset($image) ) $sql .= "image = '$image',";
        $sql = substr($sql,0,-1);
		$sql .=" where partnumber = '$partnumber'";
		$rc = $GLOBALS['db']->query($sql);
        if ( ! $rc ) return -99;
        if ( $vendor > 0 ) {
            if ( getMakemodel($vendor,$model,$partsid,$lastcost,$newvendor,$add=false) == 't' ) {
                $mod = updMakemodel($vendor,$model,$partsid,$lastcost);
            } else {
                $mod = getMakemodel($vendor,$model,$partsid,$lastcost,$newvendor,$add=true);
            }            
		    return $mod;
        } else {
            return -1;
        }
	} else {
    	$nummer = chkPartNumber($partnumber,$check);
	    if ( $nummer == "" ) { return -99; }
    	else { return $nummer; };
    }
}

function getAccnoId($accno) {
	$sql = "select id from chart where accno='$accno'";
	$rs  = $GLOBALS['db']->getAll($sql);
	return $rs[0]["id"];
}

function getBuchungsgruppe($income, $expense, $inventory=false) {
	$sql  = "select id from buchungsgruppen where ";
	$sql .= "income_accno_id_0 = $income and ";
	$sql .= "expense_accno_id_0 = $expense ";
    if ( $inventory ) $sql .= 'and inventory_accno_id = '.$inventory;
	$rs = $GLOBALS['db']->getAll($sql);
	return $rs[0]["id"];
}


function getFromBG($bg_id, $name) {
	$sql = "select $name from buchungsgruppen where id='$bg_id'";
	$rs  = $GLOBALS['db']->getAll($sql);
	return $rs[0][$name];
}

function existUnit($value) {
	$sql = "select name from units where name = '$value'";
	$rs  = $GLOBALS['db']->getAll($sql);
	if ( empty($rs[0]["name"]) ) return FALSE;
	return TRUE;
}

function show($show, $things) {
	if ( $show )
		echo $things;
}

function getStdUnit($type) {
	$sql = "select * from units where type='$type' order by sortkey limit 1";
	$rs  = $GLOBALS['db']->getAll($sql);
	if ( empty($rs[0]["name"]) ) return "Stck";
	return $rs[0]["name"];
}

function del_parts($file) {
	$delartikel = file($file);
	$i=0; $f=0;
	foreach ($delartikel as $artikel) {
		$artikel=trim($artikel);
		if ($artikel[0]=='"') $artikel=substr($artikel,1,-1);
		//if (strlen($artikel)==6) {
			$sql="update parts set shop='f' where partnumber = '$artikel'";
			echo $sql;
			$rc=$GLOBALS['db']->query($sql);	
			if (!$rc) { $f++; } 
			else { $i++; };
		//};
	};
	show (true,"$i Artikel deaktiviert ($f Fehler)");	
}
function mktext($text,$to,$from) {
    if ( $from != $to ) $text = mb_convert_encoding($text,$to,$from);
    $text = preg_replace('/""[^ ]/',	'"',$text);
    $text = preg_replace("/'/",'\\\''	,$text);
    return $text;
}
function import_parts($file, $trenner, $trennzeichen, $fields, $check, $insert, $show,$maske) {
	$destcode  = "UTF-8";
	$srccode   = "UTF-8";
    $newvendor = ($maske['newvendor'] == '2')?true:false;
	$m         = 0;	/* line */
	$errors    = 0;	/* number of errors detected  */
	$precision = $maske["precision"];
	$quotation = $maske["quotation"];
	$quottype  = $maske["quottype"];
	$UpdText   = ($maske["TextUpd"]=="1")?true:false;
	$Update    = ($maske["update"]=="U")?true:false;
	if ( $quottype == "P" ) $quotation = ($quotation+100)/100;
	if ( $trenner == "other" ) { 
        $trenner = trim($trennzeichen); 
	    if ( substr($trenner,0,1) == "#" ) { 
            if ( strlen($trenner) > 1 ) $trenner = chr(substr($trenner,1)); 
        };
    };

	/* field description, -1 nicht im CSV - also erst einmal alle */
	$parts_fld = array_keys($fields);
    $parts_fld[] = 'inventory_accno_id';
    $parts_fld[] = 'income_accno_id';
    $parts_fld[] = 'expense_accno_id';
    $j = 0;
    foreach ($parts_fld as $fld) { ${'pos'.$fld} = -1; $j++;  };

	/* open csv file */
	$f=fopen($_SESSION['erppath']."crm/tmp/$file.csv","r");
	
	show( $show, "<table border='1'><tr><td>#</td>\n");

	/* read first line with table descriptions */
	$infld = fgetcsv($f,1200,$trenner);
    $j = 0;
    $used_fld = array();
	foreach ($infld as $fld) {
		$fld = strtolower(trim(strtr($fld,array("\""=>"","'"=>""))));
		if (in_array($fld,$parts_fld)) {
            ${'pos'.$fld} = $j;
            if ( $fld == 'partsgroup' ) {
                $used_fld[] = 'partsgroup_id';
            } else {
                $used_fld[] = $fld;
            }
			show( $show, "<td>$fld</td>\n");
		};
        $j++;
	}
	if ( !in_array("unit",$infld) ) {
        //Unit ist nicht im CSV-File
		$stdunitW = getStdUnit("dimension");
		$stdunitD = getStdUnit("service");
		$unit     = true;
		show( $show, "<td>unit</td>\n");
	};
	show( $show, "<td>Bugru</td>\n");

	while ( ($zeile=fgetcsv($f,120000,$trenner)) != FALSE) {
        $keys = array();
        $vals = array();
        $inventory_accno_id = 0;
        $income_accno_id    = 0;
        $expense_accno_id   = 0;

		$i = 0;	/* column */
        $m++;	/* increase line */
        if ( $posmake > -1 ) {
            $vendorID = 0;
            $model    = '';
            $vendornr = $zeile[$posmake];
            if ( $vendornr != '' ) {
                $vtmp = $GLOBALS['db']->getOne("SELECT id FROM vendor WHERE vendornumber = '$vendornr'");
                if ( $vtmp['id'] )  $vendorID = $vtmp['id'];
            };
            if ( $posmodel > -1 ) $model = preg_replace('/\'/',' ',$zeile[$posmodel]);
        };
		if ( $possellprice > -1 ) { $sellprice =$zeile[$possellprice];    
                                    $sellprice = str_replace(",", ".", $sellprice); 
		                            if ($quotation<>0) if ( $quottype == "A" ) { $sellprice += $quotation; }
                                                       else { $sellprice = $sellprice * $quotation; };
		                            if ( $precision >= 0 ) $sellprice = round($sellprice,$precision);
                                    $keys['sellprice'] = $sellprice;
        } else { $sellprice = '0.00'; };
		if ( $poslistprice > -1 ) { $listprice =$zeile[$poslistprice];    
                                    $listprice = str_replace(",", ".", $listprice); 
		                            if ($quotation<>0) if ( $quottype == "A" ) { $listprice += $quotation; }
                                                       else { $listprice = $listprice * $quotation; };
		                            if ( $precision >= 0 ) $listprice = round($listprice,$precision);
                                    $keys['listprice'] = $listprice;
        } else { $listprice = '0.00'; };
		if ( $poslastcost > -1 ) { 
                                    $lastcost = $zeile[$poslastcost]; 
                                    $lastcost = str_replace(",", ".", $lastcost);
                                    $keys['lastcost'] = $lastcost;
        }   else { $lastcost  = '0.00'; };
	    if ( $pospartsgroup > -1 )  {
            $pgname =  $zeile[$pospartsgroup];
    		$pgid = getPartsgroupId( $pgname, $insert);
            $keys['partsgroup_id'] = $pgid;
        };
        if ( $posdescription > -1 )         { $description = mktext($zeile[$posdescription],$destcode,$srccode); $keys['description'] = $description;
                                            } else { $descriotion = ''; }
        if ( $posnotes > -1 )               { $notes = mktext($zeile[$posnotes],$destcode,$srccode); $keys['notes'] = $notes;
                                            } else { $notes = ''; }
        if ( $pospartnumber > -1 )          { $partnumber = $zeile[$pospartnumber]; $keys['partnumber'] = $partnumber; };
        if ( $posunit > -1 )                { $unit = $zeile[$posunit]; if ( existUnit($unit) ) $keys['unit'] = $unit; }; 
        if ( $posweight > -1 )              { $weight = $zeile[$posweight] * 1; $keys['weight'] = $weight; }; 
        if ( $posean > -1 )                 { $ean  = $zeile[$posean]; $keys['ean'] = $ean; } else { $ean = ''; }; 
        if ( $posimage  > -1 )              { $image  = $zeile[$posimage]; $keys['image'] = $image; } else { $image = ''; }; 
        if ( $posdrawing  > -1 )            { $keys['drawing'] = $zeile[$posdrawing]; }; 
        if ( $posmicrofiche  > -1           ) { $keys['microfiche'] = $zeile[$posmicrofiche]; }; 
        if ( $posassembly  > -1 )           { $keys['assembly'] = (strtoupper($zeile[$posassembly]) == 'Y')?'t':'f'; }; 
        if ( $posbin_id  > -1 )             { $keys['bin_id'] = $zeile[$posbin_id]; }; 
        if ( $poswarehouse_id  > -1 )       { $keys['warehouse_id'] = $zeile[$poswarehouse_id]; }; 
        if ( $posrop  > -1 )                { $keys['rop'] = $zeile[$posrop]; }; 
        if ( $posobsolete  > -1 )           { $keys['obsolete'] = (strtoupper($zeile[$posobsolete]) == 'Y')?'t':'f'; }; 
        if ( $posinventory_accno  > -1 )    { $inventory_accno_id = getAccnoId($zeile[$posinventory_accno]); }; 
        if ( $posincome_accno  > -1 )       { $income_accno_id    = getAccnoId($zeile[$posincome_accno]);    }; 
        if ( $posexpense_accno  > -1 )      { $expense_accno_id   = getAccnoId($zeile[$posexpense_accno]);   }; 
        if ( $posinventory_accno_id  > -1 ) { $inventory_accno_id = $zeile[$posinventory_accno]; }; 
        if ( $posincome_accno_id  > -1 )    { $income_accno_id    = $zeile[$posincome_accno];    }; 
        if ( $posexpense_accno_id  > -1 )   { $expense_accno_id   = $zeile[$posexpense_accno];   }; 
        if ( $posshop  > -1 )               { $key['shop'] = ( strtoupper($zeile[$posshop] == 'Y') )?'t':'f'; }; 
		if ($Update) {
			if ($UpdText) {
				$rc=chkPartNumberUpd($sellprice,$lastcost,$listprice,$partnumber,$description,$note,$image,$weight,$pgid,$newvendor,$vendorID,$model,$check);
			} else {
				$rc=chkPartNumberUpd($sellprice,$lastcost,$listprice,$partnumber,false,false,false,$weight,$pgid,$newvendor,$vendorID,$model,$check);
			}
			if ($rc==-1) {
				show($show,"<tr><td>Update </td><td>$partnumber:$sellprice</td></tr>\n");
				continue;
			} else if ($rc==-99) {
				show($show,"<tr><td>Fehler Zeile $m</td></tr>\n");
				continue;
			} else {
				$keys['partnumber'] = $rc;
			}
		};
		show( $show, "<tr><td>$m</td>\n");


		$artikel        = false;
        if ( $posart  > -1  and $maske['ware'] == 'G')      { $artikel = ($zeile[$posart] == 'D')?false:true; }
        else if ( $maske['ware'] == 'D' ) { $artikel = false; }
        else { $artikel = true; }

        if ( ! in_array('unit',$keys) ) $keys['unit'] = ( $artikel )?$stdunitW:$stdunitD;

		if ( $maske["bugrufix"] == 1 ) { $keys['buchungsgruppe_id'] = $maske["bugru"]; }
        else {
	        /* search for buchungsgruppe */
            $keys['income_accno_id']  = ( $income_accno_id > 0 )?$income_accno_id:1; 
            $keys['expense_accno_id'] = ( $expense_accno_id > 0 )?$expense_accno_id:1;
            if ( $artikel ) {
                $keys['inventory_accno_id'] = ( $inventory_accno_id > 0)?$inventory_accno_id:1;
                if ( $income_accno_id > 0 and $expense_accno_id > 0 )
                    $bg = getBuchungsgruppe($keys['income_accno_id'],$keys['expense_accno_id_0'],$keys['inventory_accno_id'] );
            } else {
                if ( $income_accno_id > 0 and $expense_accno_id > 0 )
				    $bg = getBuchungsgruppe($keys['income_accno_id'],$keys['expense_accno_id_0'] );
            };
            if ($bg == "" and $maske["bugrufix"]==2 and $maske["bugru"]<>"") {
                //Bugru nicht gefunden. Fall-Back benutzen
				$keys['buchungsgruppen_id'] = $maske["bugru"];
			} else if ($maske["bugru"]<>"" and $maske["bugrufix"]==2) {
                //Kann nicht gefunden werden. Fall-Back
				$keys['buchungsgruppen_id'] = $maske["bugru"];
			} else {
				/* nothing found? user must create one */
				echo "Error in line $m: ";
				echo "Keine Buchungsgruppe gefunden für <br>";
				echo "Erlöse Inland: $income_accno<br>";
				echo "Bitte legen Sie eine an oder geben Sie eine vor.<br>";
				echo "<br>";
				$errors++;
                continue;
            };
        }

        if ( ! $keys['shop'] ) $keys['shop'] = $maske['shop'];
        foreach ( $used_fld as $fld ){
            if ( $fld == 'notes' and strlen($keys[$fld])>25 ) {
                show( $show, "<td>".substr($keys[$fld],0,25)." . . . ".htmlentities(substr($keys[$fld],-25))."</td>");
            } else if ( $fld == 'partsgroup_id' ) {
                show( $show, "<td>".$pgname.':'.$keys[$fld]."</td>");
            } else {
                show( $show, "<td>".$keys[$fld]."</td>");
            }
        }
        show( $show, "<td>".$keys['buchungsgruppen_id']."</td>");

		if ($insert) {
			show( $show, "<td>");
			if ($keys['buchungsgruppen_id']>0) {
			    $rc = $GLOBALS['db']->insert('parts',array_keys($keys),array_values($keys));
			    //echo "!!!$rc!!!";
			    if (!$rc) {
				    echo "SQL-Fehler";
				    $error++;
			    } else {
                    $rs = $GLOBALS['db']->getOne("SELECT id FROM parts WHERE partnumber = '$partnumber'");
                    if ( $rs ) {
                        $partsid = $rs['id'];
                        $mod = getMakemodel($vendor,$model,$partsid,$lastcost,$add=true) ;
                    }
                }
			} else {
			    echo "Fehler BG";
			    $error++;
			};
			show( $show, "</td>");
		}
		show( $show, "</tr>\n");
	}

	show( $show, "</table>\n");
	fclose($f);
	echo "$m Zeilen bearbeitet. ($error : Fehler) ";
	return $errors;
}

?>
