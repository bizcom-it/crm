<html>
	<head><title>User Stamm</title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{THEME}
    </head>
<body>
{PRE_CONTENT}
{START_CONTENT}
<p class="ui-state-highlight ui-widget-header ui-widget ui-corner-all content1" onClick="help('User');">Stammdaten</p>
<br>
<table>
	<tr><td class="norm">User ID</td>	<td class="norm">: {uid}</td></tr>
	<tr><td class="norm">Login</td>		<td class="norm">: {login}</td></tr>
	<tr><td class="norm">Name</td>		<td class="norm">: {name}</td></tr>
	<tr><td class="norm">Strasse</td>	<td class="norm">: {addr1}</td></tr>
	<tr><td class="norm">Plz Ort</td>	<td class="norm">: {addr2} {addr3}</td></tr>
	<tr><td class="norm">Telefon gesch.&nbsp;</td><td class="norm">: {workphone}</td></tr>
	<tr><td class="norm">Telefon priv.       </td><td class="norm">: {homephone}</td></tr>
	<tr><td class="norm">E-Mail</td>	<td class="norm">: <a href='mail.php?TO={email}'>{email}</a></td></tr>
	<tr><td class="norm">Abteilung</td>	<td class="norm">: {abteilung}</td></tr>
	<tr><td class="norm">Vertreter</td>	<td class="norm">: {vertreter}</td></tr>
	<tr><td class="norm">Bemerkung</td>	<td class="norm">: {notes}</td></tr>
</table>
{END_CONTENT}
</body>
</html>

