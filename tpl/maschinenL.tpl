<html>
	<head><title></title>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
<body >
{PRE_CONTENT}
{START_CONTENT}
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('MaschinenEingebenEditieren');">Maschinen Trefferliste: {search}</h1><br>
<form name="formular"  action="{action}" method="post">
<input type="hidden" name="MID" value="{MID}">
<select name="{fldname}" size="25" style='width:70%'>
<!-- BEGIN Sernumber -->
    <option value="{number}">{pnumber} {description}
<!-- END Sernumber -->
</select>
<input type="submit" name="search" value="übernehmen">
<a href='{action}'><input type="button" name="reset" value="neue Suche"></a>
{END_CONTENT}
</body>
</html>
	
