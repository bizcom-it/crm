<html>
	<head><title></title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{THEME}
    <script language="javascript">
        function pgselect() {
             txt = document.katalog.pglist.options[document.katalog.pglist.selectedIndex].value;
             document.katalog.partsgroup.value = txt;
        }
    </script>
        
</head>
<body >
{PRE_CONTENT}
{START_CONTENT}
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Katalog');">.:catalog:. <font color='red'>{msg}</font></h1>
<table >
<tr><td valign='top'>
<form name="katalog" action="katalog.php" method="post">
<div class="zeile">
    <span class="label"></span>
    <span class="leftfeld">Mehrere Bedingungungen (AND-Verknüpft)</span>
</div>
<div class="zeile">
    <span class="label">Artikelbezeichnung</span>
    <span class="leftfeld"><input type="text" name="description" value='{description}' size="20"></span>
</div>
<div class="zeile">
    <span class="label">Artikelnummer (mehrere durch Komma getrennt)</span>
    <span class="leftfeld"><input type="text" name="partnumber" value='{partnumber}' size="20"> </span>
</div>
<div class="zeile">
    <span class="label">EAN </span>
    <span class="leftfeld"><input type="text" name="ean" value='{ean}' size="20"> </span>
</div>
<div class="zeile">
    <span class="label">Warengruppe (enthält)</span>
    <span class="leftfeld"><input type="text" name="partsgroup" value='{partsgroup}' size="20"> </span>
</div>
<div class="zeile">
    <span class="label">angelegte Warengruppen</span>
    <span class="leftfeld"><select name="pglist" onChange="pgselect()">
                           {pglist}
                          </select></span>
</div>
<div class="zeile">
    <span class="label">Preis </span>
    <span class="leftfeld"><select name='preise'>
<!-- BEGIN Preise -->
        <option value='{preisid}' {select}>{preis}
<!-- END Preise -->
    </select>
    </span>
</div>
<div class="zeile">
    <span class="label">Nur Shopartikel</span>
    <span class="leftfeld"><input type="checkbox" name="shop" value="1" {shop}>.:yes:.</span>
</div>
<div class="zeile">
    <span class="label">Nur mit Bild</span>
    <span class="leftfeld"><input type="checkbox" name="image" value="1" {image}>.:yes:.</span>
</div>
<div class="zeile">
    <span class="label">Steuer aufschlagen </span>
    <span class="leftfeld"><input type="checkbox" name="addtax" value="1" {addtax}>.:yes:.</span>
</div>
<div class="zeile">
    <span class="label">Ab-/Aufschlag </span>
    <span class="leftfeld"><input type="text" name="prozent" value='{prozent}' size="6">% 
                          <input type="radio" name="pm" value="+" {pm+}>+ <input type="radio" name="pm" value="-" {pm-}>-</span>
</div>
<div class="zeile">
    <span class="label">Order by </span>
    <span class="leftfeld"><select name='order'>
        <option value='PG.partsgroup,partnumber' {PG.partsgroup,partnumbersel}>Warengruppe, Artikelnummer
        <option value='partnumber,PG.partsgroup' {partnumber,PG.partsgroupsel}>Artikelnummer, Warengruppe
        <option value='ean' {eansel}>EAN
        <option value='description,partnumber'   {description,partnumbersel}>Artikelbezeichnung, Artikelnummer
        <option value='spezial' {spezialsel}>Artikelnummern (Eingabe)
    </select>
    </span>
</div>
<!--div class="zeile">
    <span class="label">Warengruppenwechsel</span>
    <span class="leftfeld"><input type="checkbox" name="pgchange" value="1" {pgchange}>.:yes:.</span>
</div-->
<div class="zeile">
    <span class="label">Vorlage </span>
    <span class="leftfeld"><select name="vorlage">
<!-- BEGIN vorlagen -->
         <option value='{VORLAGE}' {VSEL}>{VORLAGE}
<!-- END vorlagen -->
    </select></span>
</div>
</td><td valign='top'>
<div class="zeile">
    <span class="label"></span>
    <span class="leftfeld">Nur eine Auswahl!! (cVars)</span>
</div>
<!-- BEGIN cvarListe -->
        <div class="zeile">
                <span class="label">{varlable}</span>
                <span class="leftfeld">{varfld}</span>
        </div>
<!-- END cvarListe -->
</td></tr>
</table>
<input type="submit" name="ok" value="erstellen"> <a href='{link}'>{link}</a>  <a href='{linklog}'>{linklog}</a><br><br>
    <input type="text" name="maskename" value="" size="20">  <input type="submit" name="save" value="Maske sichern">
<br>
<select name="maskem">
<!-- BEGIN maskList -->
         <option value='{MASKNAME}'>{MASKNAME}
<!-- END maskList -->
</select>
<input type="submit" name="load" value="Maske laden">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="del" value="Maske löschen">

</form>
{END_CONTENT}
</body>
</html>
