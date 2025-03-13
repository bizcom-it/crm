<html>
	<head><title>User Stamm</title>
    {STYLESHEETS}
    {JAVASCRIPTS}
    {CRMCSS}
    {THEME}

	<script language="JavaScript">
	<!--
		function doit() {
			document.grp2.submit();
		}
		function subusr() {
            var nr = document.getElementById('grpuser').selectedIndex;
            document.getElementById('grpuser').options[nr] = null;
		}
		function addusr() {
			var nr  = document.grp2.users.selectedIndex;
			var val = document.grp2.users.options[nr].value;
			var txt = document.grp2.users.options[nr].text;
			NeuerEintrag = new Option(txt,val,false,true);
            document.getElementById('grpuser').options[document.getElementById('grpuser').length] = NeuerEintrag;
		}
		function selall() {
            var len = document.getElementById('grpuser').length;
            document.getElementById('grpuser').multiple = true;
			for ( i = 0; i<len; i++) {
				document.grp2.getElementById('grpuser').options[i].selected = true;
			}
		}
        function editgrp() {
			var nr    = document.grp2.gruppe.selectedIndex;
			var grpid = document.grp2.gruppe.options[nr].value;
			var txt   = document.grp2.gruppe.options[nr].text;
            var length = txt.length;
            var recht = txt.charAt(length-1);
            document.gruppe.grpid.value = grpid;
            document.gruppe.name.value = txt.substring(0,length-2);
            document.getElementById('rechte'+recht).checked=true;
        }
	//-->
	</script>
</head>
<body>
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style=" border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Gruppen');">Gruppen {msg}</h1><br>

<span style='visibility:{hide}'>
<br>
Zugriffbeschr&auml;nkungen f&uuml;r Kunden und Personen durch Gruppen einrichten<br><br>
	<form name="gruppe" action="user2.php" method="post">
		<input type="hidden" name="id" value="{UID}">
		<input type="hidden" name="grpid" value="">
		Neue Gruppe: <input type="text" name="name"  size="20" maxlength="40">
		<input type="radio" name="rechte" id="rechtel" value="l" checked>lesen
		<input type="radio" name="rechte" id="rechtes" value="s">schreiben
		<input type="radio" name="rechte" id="rechter" value="r">Ressourcen
		<input type="submit" name="newgrp" value="eintragen">
	</form>
	<br><br>
	<form name="grp2" action="user2.php" method="post">
		<input type="hidden" name="id" value="{UID}">
		Gruppen: <select name="gruppe">
<!-- BEGIN Selectbox -->
			<option value="{GRPID}"{SEL}>{NAME} - {RECHT}</option>
<!-- END Selectbox -->
		</select>
		<input type="submit" name="selgrp" value="holen">
		<input type="button" name="modgrp" value="edit" onClick="editgrp()">
		<input type="submit" name="delgrp" value="l&ouml;schen"><br>
<table>
	<tr>
		<td width="45%" class="norm ce">
			Mitglieder:<br>
			<select name="grpusr[]" id="grpuser" size="10" style='min-width:200px;'>
<!-- BEGIN Selectbox2 -->
				<option value="{USRID}">{USRNAME}</option>
<!-- END Selectbox2 -->
			</select>
		</td>
		<td width="10%" class="norm ce">
			<input type="button" name="left" value="<--" onClick="addusr()">
			<br><br>
			<input type="button" name="right" value="-->" onClick="subusr()">
		</td>
		<td width="45%" class="norm ce">
			User:<br>
			<select name="users" id="users" size="10" style='min-width:200px;'>
<!-- BEGIN Selectbox3 -->
				<option value="{USRID}">{USRNAME}</option>
<!-- END Selectbox3 -->
			</select>
		</td>
	</tr>
	<tr><td class="norm ce"><input type="submit" name="usrgrp" value="sichern" onClick="selall()"></td><td colspan="2"></td></tr>
</table>
</form>
</span>
</div>
{END_CONTENT}
</body>
</html>
