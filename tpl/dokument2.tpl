<html>
	<head><title></title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{THEME} 
	<script>
     $(document).ready(
        function(){
            $( "input[type=reset]")
            .button().click(function( event ) { 
                 event.preventDefault();
                 document.location.href = this.getAttribute('name');
            });
        }); 
	</script>
<body>
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist ui-widget ui-corner-all tools content1" onClick="help('Vorlage');">Dokumentvorlagen</h1>
    <form name="firma4" enctype='multipart/form-data' action="dokument2.php" method="post">
        <input type="reset" name="dokument1.php" value='Dokumente'>
        <input type="reset" name="{Link2}" value='neue Vorlage'>
        <input type="reset" name="{Link3}" value='Felder'>
        <input type="hidden" name="did" value="{did}">
        <input type="hidden" name="file_" value="{file}">
        <br><br>
        <input type="text" name="vorlage" value="{vorlage}" size="40" maxlength="80"><br>Bezeichnung<br>
        <textarea name="beschreibung" cols="52" rows="3">{beschreibung}</textarea><br>Beschreibung<br>
        <b>{file}</b><br>
        <input type="file" name="file" size="30"><br>Vorlage<br><br>
        Erlaubte Dokumententypen: sxw, rtf, xls, tex<br>{msg}<br>
        <input type="submit" name="ok" value="sichern"> <input type="submit" name="del" value="l&ouml;schen">
    </form>
</div>
{END_CONTENT}
</body>
</html>
