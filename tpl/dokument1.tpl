<html>
	<head><title></title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{THEME} 
{JQTABLE}

	<script language="JavaScript">
	<!--
	function showD (id) {
		if (id>0) {
			uri="dokument2.php?did=" + id;
			window.location.href=uri;
		}
	}
	//-->
	</script>
	<script>
     $(document).ready(
        function(){
            $('#liste').tablesorter({widthFixed: true, widgets: ['zebra'], headers: { 0: { sorter: false }, 1: { sorter: false } } });
            $( "input[type=reset]")
            .button().click(function( event ) { 
                 event.preventDefault();
                 document.location.href = this.getAttribute('name');
            });
        }); 
	</script>
    </head>    
<body>
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Dokumentvorlagen');">Dokumentvorlagen</h1><br>

<form>
	<input type="reset" name="dokument1.php" value='Dokumente'>
	<input type="reset" name="dokument2.php" value='neue Vorlage'>
</form>
<br>
<table class="tablesorter" id="liste" style="width:300px">
<thead><tr><th>Bezeichnung</th><th>Typ</th></tr></thead><tbody>
<!-- BEGIN Liste -->
	<tr onClick="showD({did});">
		<td>{Bezeichnung}</td><td>{Appl}</td>
	</tr>
<!-- END Liste -->
</tbody></table>
</div>
{END_CONTENT}
</body>
</html>
