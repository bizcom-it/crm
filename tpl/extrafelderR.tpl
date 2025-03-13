<html>
<head><title>Zusatzdaten</title>
        {STYLESHEETS}
        {JAVASCRIPTS}
        {CRMCSS}
        {THEME}
        {JQTABLE}
<script language="JavaScript">
	function showK (id,tab) {
		if (id) {
            if ( tab == 'P' ) {
  			    uri = "firma2.php?id=" + id;
            } else {
  			    uri = "firma1.php?Q=" + tab + "&id=" + id;
            }
			location.href = uri;
		}
	}
    $(document).ready(function() {
        $( "#modify_search_C" ).button().click(function() {
            location.href='getData.php';
            return false;
        });
        $( "#sercontent_C" ).dialog({
            autoOpen: false,
            show: {
                effect: "blind",
                duration: 300
            },
            hide: {
                effect: "explode",
                duration: 300
            },
            //position: { my: "center top", at: "center", of: null } 
        });
        $( "#butetikett_C" ).button().click(function() {
            $( "#sercontent_C" ).dialog( "option", "maxWidth", 400 );
            $( "#sercontent_C" ).dialog( "open" );
            $( "#sercontent_C" ).dialog( { title: "Etiketten" } );
            $( "#sercontent_C" ).load("etiketten.php?src=F");
            return false;
        });
        $( "#butvcard_C" ).button().click(function() {
            $( "#sercontent_C" ).dialog( "option", "maxWidth", 400 );
            $( "#sercontent_C" ).dialog( "open" );
            $( "#sercontent_C" ).dialog( { title: "V-Cards" } );
            $( "#sercontent_C" ).load("servcard.php?src=F");
            return false;
        });
        $( "#butbrief_C" ).button().click(function() {
            $( "#sercontent_C" ).dialog( "option", "minWidth", 600 );
            $( "#sercontent_C" ).dialog( "open" );
            $( "#sercontent_C" ).dialog( { title: "Serienbrief" } );
            $( "#sercontent_C" ).load("serdoc.php?src=F");
            return false;
        });
        $( "#butsermail_C" ).button().click(function() {
            $( "#sercontent_C" ).dialog( "option", "minWidth", 800 );
            $( "#sercontent_C" ).dialog( "open" );
            $( "#sercontent_C" ).load("sermail.php?src=F");
            return false;
        });
        //$( "input[type=button]" ).button();
        $("#treffer_C")
            .tablesorter({widthFixed: true, widgets: ["zebra"]})
            .tablesorterPager({container: $("#pager_C"), size: 20, positionFixed: false});
    });
</script>
</head>
<body {POPUP}>
{PRE_CONTENT}   <!--Nicht verändern -->
{START_CONTENT} <!--Nicht verändern -->
<h1 class="toplist  ui-widget  ui-corner-all tools content1">.:search result:. .:{FAART}:.</h1><br>
<table id="treffer_C" class="tablesorter">  
    <thead>
		<tr>
			<th>Kd-Nr</th>
			<th>Name</th>
			<th>Plz</th>
			<th>Ort</th>
			<th>Strasse</th>
			<th>Telefon</th>
			<th>E-Mail</th>
			<th>.:obsolete:.</th>
		</tr>
	</thead>
	<tbody style='cursor:pointer'>
<!-- BEGIN Liste -->
    <tr onClick="showK({ID},'{tab}');">
		<td>{tab} {KdNr}</td><td>{Name}</td><td>{Plz}</td><td>{Ort}</td><td>{Strasse}</td><td>{Telefon}</td><td>{eMail}</td><td>{obsolete}</td></tr>
<!-- END Liste -->
	</tbody>
</table>
<span id="pager_C" class="pager">
	<form>
		<img src="{CRMPATH}jquery/plugin/Table/addons/pager/icons/first.png" class="first"/>
		<img src="{CRMPATH}jquery/plugin/Table/addons/pager/icons/prev.png" class="prev"/>
		<input type="text" class="pagedisplay"/>
		<img src="{CRMPATH}jquery/plugin/Table/addons/pager/icons/next.png" class="next"/>
		<img src="{CRMPATH}jquery/plugin/Table/addons/pager/icons/last.png" class="last"/>
		<select class="pagesize">
			<option value="10">10</option>
			<option value="20" selected>20</option>
			<option value="30">30</option>
			<option value="40">40</option>
		</select>
    <button id="modify_search_C" >.:modify search:.</button>&nbsp;
	<button id="butetikett_C" >.:label:.</button>&nbsp;
	<button id="butbrief_C" >.:serdoc:.</button>&nbsp;
	<button id="butvcard_C" >.:servcard:.</button>&nbsp;
	<button id="butsermail_C" >.:sermail:.</button>&nbsp;
	</form>
</span>
<div id="sercontent_C"></div>
{END_CONTENT} 
</body>
</html>
