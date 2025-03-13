<html>
	<head><title></title>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{THEME}
	<script language="JavaScript">
	<!--
		function drucke(nr)  {
			f=open("prtWVertrag.php?aid="+nr,"drucke","width=10,height=10,left=10,top=10");
		}	
	//-->
	</script>
<body >
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('WVEingebenEditieren');">Vertr&auml;ge auswerten</h1><br>

    <table width="99%" border="0"><tr><td>
    <form name="formular" enctype='multipart/form-data' action="{action}" method="post">
    <input type="hidden" name="vid" value="{VID}">
    Auswertung {jahr} f&uuml;r Vertrag [<a href="vertrag3.php?vid={VID}">{VertragNr}</a>]  [<a href="firma1.php?id={FID}">{Firma}</a>]
    <table style="width:550px">
	<tr><th class="norm" width="200">Maschine</th><th class="norm">Auftrag</th><th class="norm">Dauer</th><th class="norm">Summe</th><th class="norm">Gesamtsumme</th></tr>
<!-- BEGIN Liste -->
	<tr><td class="norm" nowrap>{MID}</td><td width="100" class="norm ce">{RID}</td><td width="100" class="norm re">{DAUER}</td><td width="100" class="norm re">{BETRAG}</td><td width="100" class="norm re">{SUMME}</td></tr>
<!-- END Liste -->
    </table>
    <div  class="norm">
        Errechnete (nicht tats&auml;chliche) Einnahmen: {einnahme}<br>
        Aufgelaufene Kosten: <b>{kosten}</b> &nbsp;ohne Arbeitszeit&nbsp;&nbsp; <b>{diff} &euro;</b>
    </div>
    </form>
    </td></tr></table>
</div>
{END_CONTENT}
</body>
</html>
