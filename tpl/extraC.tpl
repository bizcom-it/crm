<html>
<head><title>Zusatzdaten</title>
<!--Von hier -->        
        {STYLESHEETS}
        {JAVASCRIPTS}
        {CRMCSS}
        {THEME}
<!-- bis hier nicht verändern -->        
	<script langage="JavaScript">
		// sind in den Funktionen geschweifte Klammern drin, dann Leerzeichen nach und vor die Klammer 
		// da die sonst von der TemplateEngie gelöscht werden
		function checkfelder() {
            if ( '#{SUCHE}' == '#yes' ) return true;
			if (!document.extra.plz.value.match(/^[0-9]+$/)) { alert ("Fehlerhafte oder fehlende PLZ"); return false; };
			return true;
		}
	</script>
</head>
<body {POPUP}>
{PRE_CONTENT}   <!--Nicht verändern -->
{START_CONTENT} <!--Nicht verändern -->
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Extrafelder');"><span title=".:important note:.">Extrafelder für Kunden&nbsp;</span></h1><br>

<center>
<h2>Bauvorhaben</h2>{msg}
<h4>Informationen &uuml;ber einen Interessenten der ein Haus bauen m&ouml;chte</h4>
<!-- die 2 nächsten Zeilen übernehmen -->
<form name="extra" action="{ZIEL}" method="post" onSubmit="return checkfelder();">
<input type="hidden" name="owner" value="{owner}">
<table>
<tr><td>Bauvorhaben</td><td><select name="bauvorhaben">
		<option value="EFH" {bauvorhabenEFH}>EFH
		<option value="DHH" {bauvorhabenDHH}>DHH
		<option value="MFH" {bauvorhabenMFH}>MFH
		<option value="Bungalow" {bauvorhabenBungalow}>Bungalow
		<option value="Gewerbe" {bauvorhabenGewerbe}>Gewerbe
	</select></td>
	<td>Art des Bauvorhabens</td></tr>
<tr><td>Grundst&uuml;ck vorhanden</td><td><input type="radio" name="grundstueck" {grundstueck_J} value="J">Ja <input type="radio" name="grundstueck" value="N" {grundstueck_N}>Nein</td>
	<td></td></tr>
<tr><td>Plz / Ort</td><td nowrap><input type="text" name="plz" size="4" value="{plz}"><input type="text" name="ort" size="25" value="{ort}"></td>
	<td>An welchem Ort soll das Grundst&uuml;ck sein</td></tr>
<tr><td>Gr&ouml;&szlig;e</td><td><input type="text" name="groesse" size="10" value="{groesse}"> qm</td>
	<td>Gr&ouml;&szlig;e des Grundst&uuml;cks</td></tr>
<tr><td>Grundst&uuml;ckspreis</td><td><input type="text" name="kosten_grund" size="10" value="{kosten_grund}"> Euro</td>
	<td>Was darf das Grundst&uuml;ck kosten</td></tr>
<tr><td>Kosten Bauvorhaben</td><td><input type="text" name="kosten_bau" size="10" value="{kosten_bau}"> Euro</td>
	<td>Was darf das Bauvorhaben kosten</td></tr>
<tr><td>Vertragsdatum</td><td><input type="text" name="vertragsdatum" size="10" value="{vertragsdatum}"></td>
	<td>Datum des Vertragsabschlusses</td></tr>
<tr><td>Baubegin</td><td><input type="text" name="baubeginn" size="10" value="{baubeginn}"></td>
	<td>Datum des Baubeginns</td></tr>
<tr><td>Fertigstellung</td><td><input type="text" name="fertigstellung" size="10" value="{fertigstellung}"></td>
	<td>Datum der Fertigstellung</td></tr>
</table>
<!-- {visiblesichern} + {visiblesuchen} steuern wann die Buttons angezeigt werden -->
<input type="submit" name="save"  value="sichern" {visiblesichern}>&nbsp;&nbsp;&nbsp;<input type="button" onClick="self.close();" value="close" {visiblesichern}>
{msg} <input type="submit" name="suchen" value="suchen" {visiblesuchen}>
</form>
</center>
<!--Ab Hier nichts verändern -->
{END_CONTENT}
</body>
</html>
