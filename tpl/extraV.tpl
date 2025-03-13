<html>
<head><title>Zusatzdaten</title>
<!-- Von hier -->
        {STYLESHEETS}
        {JAVASCRIPTS}
        {CRMCSS}
        {THEME}
<!--bis hier nicht verändern -->
	<script langage="JavaScript">
		// sind in den Funktionen geschweifte Klammern drin, dann als extra File laden
		// da die sonst von der TemplateEngie gelöscht wird
		function checkfelder() {
            if ( '#{SUCHE}' == '#yes' ) return true;
            //var suche = /^[0-9]+$/;
            //var value = document.test.plz.value;
            //var test  = value.match(suche);
			if (document.test.name.value == '') { alert ("Bitte einen Namen eingeben"); return false; };
			if (!document.test.plz.value.match(/^[0-9]+$/)) { alert ("Fehlerhafte PLZ"); return false; };
			return true;
		}
	</script>
</head>
<body {POPUP}>
{PRE_CONTENT}   <!--Nicht verändern -->
{START_CONTENT} <!--Nicht verändern -->
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Extrafelder');"><span title=".:important note:.">Extrafelder für Lieferanten&nbsp;</span></h1><br>
<center>
<h2>Ein Test</h2>
<!-- die nächsten 2 Zeilen übernehmen -->
<form name="test" action="{ZIEL}" method="post" onSubmit="return checkfelder();">
<input type="hidden" name="owner" value="{owner}">
<input type="hidden" name="suche" value="">
<table>
<tr><td>Name</td><td><input type="text" name="name" size="30" value="{name}"></td></tr>
<tr><td>Strasse</td><td><input type="text" name="strasse" size="30" value="{strasse}"></td></tr>
<tr><td>Plz/Ort</td><td><input type="text" name="plz" size="5" value="{plz}"><input type="text" name="ort" size="25" value="{ort}"></td></tr>
<tr><td>Test</td><td><select name="test">
<option value="" {test}>
<option value="1" {test1}>Wert1
<option value="2" {test2}>Wert2
<option value="3" {test3}>Wert3
<option value="4" {test4}>Wert4
<option value="5" {test5}>Wert5
<option value="6" {test6}>Wert6
</select></td></tr>
<tr><td>JaNein</td><td><input type="radio" name="radiofld"  value="1" {radiofld_1}>Ja <input type="radio" name="radiofld"  value="2" {radiofld_2}>Nein</td></tr>
<tr><td>Checkit</td><td><input type="checkbox" name="chkfld1"  value="1" {chkfld1_1}>A 
			<input type="checkbox" name="chkfld2"  value="2" {chkfld2_2}>B
			<input type="checkbox" name="chkfld3"  value="2" {chkfld3_2}>C</td></tr>

</table>
<input type="submit" name="save" value="sichern" {visiblesichern}>&nbsp;&nbsp;&nbsp;<input type="button" onClick="self.close();" value="close" {visiblesichern}>

{msg} <input type="button" name="such" value="suchen"  {visiblesuchen} onClick="document.test.suche.value=1; submit()">
</form>
</center>
<!--Ab Hier nichts verändern -->
{END_CONTENT} 
</body>
</html>
