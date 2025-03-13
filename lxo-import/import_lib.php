<?
/*
Funktionsbibliothek für den Datenimport in Lx-Office ERP

Copyright (C) 2005
Author: Holger Lindemann
Email: hli@lx-system.de
Web: http://lx-system.de
*/


$address = array(
	"name"          => "Firmenname",
	"department_1"  => "Abteilung",
	"department_2"  => "Abteilung",
	"street"        => "Strasse + Nr",
	"zipcode"       => "Plz",
	"city"          => "Ort",
	"country"       => "Land",
	"contact"       => "Ansprechpartner",
	"phone"         => "Telefon",
	"fax"           => "Fax",
	"homepage"      => "Homepage",
	"email"         => "eMail",
	"notes"         => "Bemerkungen",
	"discount"      => "Rabatt (nn.nn)",
	"taxincluded"   => "incl. Steuer? (t/f)",
	"terms"         => "Zahlungsziel (Tage)",
	"customernumber" => "Kundennummer",
	"vendornumber"  => "Lieferantennummer",
	"taxnumber"     => "Steuernummer",
	"ustid"         => "Umsatzsteuer-ID",
	"account_number" => "Kontonummer",
	"bank_code"     => "Bankleitzahl",
	"bank"          => "Bankname",
	"branche"       => "Branche",
	//"language" => "Sprache (de,en,fr)",
	"sw"            => "Stichwort",
	"creditlimit"   => "Kreditlimit (nnnnnn.nn)"); /*,
	"hierarchie" => "Hierarchie",
	"potenzial" => "Potenzial",
        "ar" => "Debitorenkonto",
        "ap" => "Kreditorenkonto",
        "matchcode" => "Matchcode",
	"customernumber2" => "Kundennummer 2"); 
	Kundenspezifisch */
        
$shiptos = array(
	"shiptoname"         => "Firmenname",
	"shiptodepartment_1" => "Abteilung",
	"shiptodepartment_2" => "Abteilung",
	"shiptostreet"       => "Strasse + Nr",
	"shiptozipcode"      => "Plz",
	"shiptocity"         => "Ort",
	"shiptocountry"      => "Land",
	"shiptocontact"      => "Ansprechpartner",
	"shiptophone"        => "Telefon",
	"shiptofax"          => "Fax",
	"shiptoemail"        => "eMail",
	"customernumber"     => "Kundennummer",
	"vendornumber"       => "Lieferantennummer");

$parts = array( 
	"partnumber"    => "Artikelnummer",
	"description"   => "Artikeltext",
	"unit"          => "Einheit",
	"ean"           => "Barcodenummer",
	"weight"        => "Gewicht in Benutzerdefinition",
	"notes"         => "Beschreibung",
	"lastcost"      => "letzter EK",
	"listprice"     => "Listenpreis",
	"sellprice"     => "Verkaufspreis",
	"partsgroup"    => "Warengruppenbezeichnung",
	"image"         => "Pfad/Dateiname",
	"drawing"       => "Pfad/Dateiname",
	"microfiche"    => "Pfad/Dateiname",
	"make"          => "Hersteller",
	"model"         => "Modellbezeichnung",
	"assembly"      => "St&uuml;ckliste (Y/N); wird noch nicht unterst&uuml;tzt",
	"bin_id"        => "Lagerort-ID",
    "warehouse_id"  => "Lagerplatz-ID",
	"onhand"        => "Lagerbestand",
	"rop"           => "Mindestbestand",
	"inventory_accno" => "Bestandskonto",
	"income_accno"  => "Erl&ouml;skonto",
	"expense_accno" => "Konto Umsatzkosten",
	"obsolete"      => "Gesperrt (Y/N)",
	"shop"          => "Shopartikel (Y/N)",
	"art"           => "Ware/Dienstleistung (*/d), mu&szlig; vor den Konten kommen"
	);
	
$contactscrm = array(
	"customernumber"    => "Kundennummer",
	"vendornumber"      => "Lieferantennummer",
	"cp_cv_id"          => "FirmenID in der db",
	"firma"             => "Firmenname",
	"cp_abteilung"      => "Abteilung",
	"cp_position"       => "Position/Hierarchie",
	"cp_greeting"       => "Männlich/Weiblich (m/f)",
	"cp_title"          => "Titel",
	"cp_givenname"      => "Vorname",
	"cp_name"           => "Nachname",
	"cp_email"          => "eMail",
	"cp_phone1"         => "Telefon 1",
	"cp_phone2"         => "Telefon 2",
	"cp_mobile1"        => "Mobiltelefon 1",
	"cp_mobile2"        => "Mobiltelefon 2",
	"cp_homepage"       => "Homepage",
	"cp_street"         => "Strasse",
	"cp_country"        => "Land",
	"cp_zipcode"        => "PLZ",
	"cp_city"           => "Ort",
	"cp_privatphone"    => "Privattelefon",
	"cp_privatemail"    => "private eMail",
	"cp_notes"          => "Bemerkungen",
	"cp_stichwort1"     => "Stichwort(e)",
	"katalog"           => "Katalog",
	"inhaber"           => "Inhaber",
	"contact_id"        => "Kontakt ID"
	);

$contacts = array(
	"customernumber"    => "Kundennummer",
	"vendornumber"      => "Lieferantennummer",
	"cp_cv_id"          => "FirmenID in der db",
	"firma"             => "Firmenname",
	"cp_greeting"       => "Männlich/Weiblich (m/f)",
	"cp_title"          => "Titel",
	"cp_givenname"      => "Vorname",
	"cp_name"           => "Nachname",
	"cp_email"          => "eMail",
	"cp_phone1"         => "Telefon 1",
	"cp_phone2"         => "Telefon 2",
	"cp_mobile1"        => "Mobiltelefon 1",
	"cp_mobile2"        => "Mobiltelefon 2",
	"cp_privatphone"    => "Privattelefon",
	"cp_privatemail"    => "private eMail",
	"cp_homepage"       => "Homepage",
	"katalog"           => "Katalog",
	"inhaber"           => "Inhaber",
	"contact_id"        => "Kontakt ID"
	);


function getKdId($file,$test) {
// die nächste freie Kunden-/Lieferantennummer holen
	if ( $test ) { return "#####"; }
	$sql1 = "select * from defaults";
	$sql2 = "update defaults set ".$file."number = '%s'";
	$GLOBALS['db']->lock();
	$rs = $GLOBALS['db']->getAll($sql1);
	$nr = $rs[0][$file."number"];
	preg_match("/^([^0-9]*)([0-9]+)/",$nr,$hits);
	if ( $hits[2] ) { $nr = $hits[2]+1; $nnr=$hits[1].$nr; }
	else { $nr = $hits[1]+1; $nnr=$nr; };
	$rc = $GLOBALS['db']->query(sprintf($sql2,$nnr));
	if ( $rc ) { 
		$GLOBALS['db']->commit(); 
		return $nnr;
	} else { 
		$GLOBALS['db']->rollback(); 
		return false;
	};
}

function chkKdId($data,$file,$test) {
// gibt es die Nummer schon?
	$sql = "select * from $file where ".$file."number = '$data'";
	$rs  = $GLOBALS['db']->getAll($sql);
	if ( $rs[0][$file."number"] == $data ) {
		// ja, eine neue holen
		return getKdId();
	} else {
		return $data;
	}
}

function getKdRefId($data,$file,$test) {
// gibt es die Nummer schon?
	if ( empty($data) or !$data ) {   
		return false; 
	} 
	$sql = "select * from $file where ".$file."number = '$data'";
	$rs  = $GLOBALS['db']->getAll($sql);
	return $rs[0]["id"];
}

function suchFirma($tab,$data) {
// gibt die Firma ?
	if (empty($data) or !$data) {   
		return false; 
	}
	$data = strtoupper($data);
	$sql  = "select * from $tab where upper(name) like '%$data%'";
	$rs   = $GLOBALS['db']->getAll($sql);
	if ( !$rs ) {
		$org = $data;
		while( strpos($data,"  " ) > 0 ) {
			$data = ereg_replace("  "," ",$data);
		}
	 	$data = preg_replace("/[^A-Z0-9]/ ",".*",trim($data));
		$sql  = "select * from $tab where upper(name) ~ '$data'"; 
		$rs   = $GLOBALS['db']->getAll($sql);
		if ( count($rs) == 1 ) {
			return array("cp_cv_id"=>$rs[0]["id"],"Firma"=>$rs[0]["name"]);
		}
		return false;
	} else {
		return array("cp_cv_id"=>$rs[0]["id"],"Firma"=>$rs[0]["name"]);
	}
}


function mkland($data) {
    $land = array("DEUTSC"=>"D","FRANKR"=>"F","SPANIE"=>"ES","ITALIE"=>"I","HOLLAN"=>"NL","NIEDER"=>"NL",
	              "BELGIE"=>"B","LUXEMB"=>"L","NORWEG"=>"N","FINNLA"=>"","GRIECH"=>"GR","OESTER"=>"A",
                  "SCHWEI"=>"CH","SCHWED"=>"S","AUSTRI"=>"A");
	$data = strtoupper(substr($data,0,6));
	$cntr = $land[$data];
	return (strlen($cntr)>0)?$cntr:substr($data,0,3);
}

//Suche Nach Kunden-/Lieferantenummer
function getFirma($nummer,$tabelle) {
	$nummer = strtoupper($nummer);
	$sql    = "select id from $tabelle where upper(".$tabelle."number) = '$nummer'";
	$rs     = $GLOBALS['db']->getAll($sql);
	if ( !$rs ) {
		$nr  = ereg_replace(" ","%",$nummer);
		$sql = "select id,".$tabelle."number from $tabelle where upper(".$tabelle."number) like '$nr'";
		$rs  = $GLOBALS['db']->getAll($sql);
		if ( $rs ) {
			$nr = ereg_replace(" ","",$nummer);
			foreach ( $rs as $row ) {
				$tmp = ereg_replace(" ","",$row[$tabelle."number"]);
				if ( $tmp == $nr ) return $row["id"];
			}
		} else { 
			return false;
		}
	} else {
		return $rs[0]["id"];
	}
}

function getAllBG() {
	$sql = "select * from buchungsgruppen order by description";
	$rs  = $GLOBALS['db']->getAll($sql);
	return $rs;
}
?>
