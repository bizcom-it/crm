<html>
	<head><title></title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{THEME}
{FANCYBOX}
	<script language="JavaScript">
	<!--
    function chkfld() {
<!-- BEGIN RegEx -->
                if ( !$('{typ}[name="{fld}"]').val().match(/^{regul}*$/) ) { alert("{fldname}"); return false; };
<!-- END RegEx -->
                return true;
    }
    //-->
	</script>
	<script>

	</script>    

<body>
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Vorlage');">.:generate document:.</h1><br>
<div id="myser">
<form name="firma4" id="firma4" method="post" onsubmit="return chkfld();">
    <input type="hidden" name="did" value="{DOCID}">
    <input type="hidden" name="fid" value="{FID}">
    <input type="hidden" name="pid" value="{PID}">
    <input type="hidden" name="tab" value="{TAB}">
    <input type="hidden" name="pfad" value="{PFAD}">
    <input type="hidden" name="erstellen" value="1">
    {Beschreibung}<br><p>
    <table>
    <!-- BEGIN Liste -->
        <tr><td>{Feldname} </td><td title=".:keyin:. {Feldname}">&nbsp;{EINGABE}</td></tr>
    <!-- END Liste -->
    </table></p>
   <input type="submit"  id="erstellen" value="erstellen">
</form>
</div>
</body>
</html>
