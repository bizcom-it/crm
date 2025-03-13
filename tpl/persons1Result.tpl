<script type="text/javascript" src="{CRMPATH}js/tablesorter.js"></script>
<script language="JavaScript">

	function showK (id,tbl) {
		{no}
		uri="firma2.php?Q="+tbl+"&id=" + id;
		location.href=uri;
	}
	function showK__ (id) {
		{no}
		uri="kontakt.php?id=" + id;
		location.href=uri;
	}
    $(function() {
		$("#treffer_pers")
			.tablesorter({widthFixed: true, widgets: ['zebra']})
			.tablesorterPager({container: $("#pager_pers"), size: 20, positionFixed: false});

        $( "#sercontent_pers" ).dialog({
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
        //$( "input[type=button]" ).button();
        $( "#modify_search_pers" ).button().click(function() {
            $( "#suchfelder_pers").show();
            $( "#results_pers").hide();
            $( "#name_pers" ).focus();
            return false;
        });
        $( "#butetikett_pers" ).button().click(function() {
            $( "#sercontent_pers" ).dialog( "option", "maxWidth", 400 );
            $( "#sercontent_pers" ).dialog( "open" );
            $( "#sercontent_pers" ).dialog( { title: "Etiketten" } );
            $( "#sercontent_pers" ).load("etiketten.php?src=P");
            return false;
        });
        $( "#butvcard_pers" ).button().click(function() {
            $( "#sercontent_pers" ).dialog( "option", "maxWidth", 400 );
            $( "#sercontent_pers" ).dialog( "open" );
            $( "#sercontent_pers" ).dialog( { title: "V-Cards" } );
            $( "#sercontent_pers" ).load("servcard.php?src=P");
            return false;
        });
        $( "#butbrief_pers" ).button().click(function() {
            $( "#sercontent_pers" ).dialog( "option", "minWidth", 600 );
            $( "#sercontent_pers" ).dialog( "open" );
            $( "#sercontent_pers" ).dialog( { title: "Serienbrief" } );
            $( "#sercontent_pers" ).load("serdoc.php?src=P");
            return false;
        });
        $( "#butsersync_pers" ).button().click(function() {
            $( "#sercontent_pers" ).dialog( "option", "maxWidth", 400 );
            $( "#sercontent_pers" ).dialog( "open" );
            $( "#sercontent_pers" ).dialog( { title: "Syncstatus ändern" } );
            var stat = $( "input[name='autosync']:checked" ).val();
            $( "#sercontent_pers" ).load("sersync.php?src=P&status="+stat);
            return false;
        });        
        $( "#email_pers" ).button().click(function() {
            $( "#sercontent_pers" ).dialog( "option", "minWidth", 800 );
            $( "#sercontent_pers" ).dialog( "open" );
            $( "#sercontent_pers" ).load("sermail.php?src=F");
            return false;
        });
    });
</script>
    
<p class="ui-state-highlight ui-corner-all content1">.:search result:. .:Contacts:.</p>
<table id="treffer_pers" class="tablesorter">  
    <thead>
		<tr>
			<th>.:name:.</th>
			<th>.:zipcode:.</th>
			<th>.:city:.</th>
			<th>.:phone:.</th>
			<th>.:email:.</th>
			<th>.:company:.</th>
			<th></th>
		</tr>
	</thead>
	<tbody style='cursor:pointer'>
<!-- BEGIN Liste -->
	<tr onClick='{js}'>
		<td>{Name}</td><td>&nbsp;{Plz}</td><td>{Ort}</td><td>&nbsp;{Telefon}</td><td>&nbsp;{eMail}</td><td>{table}&nbsp;{Firma}</td><td>&nbsp;{insk}</td></tr>
<!-- END Liste -->
   </tbody>
</table>
<span id="pager_pers" class="pager">
	<form>
		<img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/first.png" class="first"/>
		<img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/prev.png" class="prev"/>
		<input type="text" class="pagedisplay"/>
		<img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/next.png" class="next"/>
		<img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/last.png" class="last"/>
		<select class="pagesize">
			<option value="10">10</option>
			<option value="20" selected>20</option>
			<option value="30">30</option>
			<option value="40">40</option>
		</select><br>
    <button id="modify_search_pers"  >.:modify search:.</button>&nbsp;
	<button id="butetikett_pers" >.:label:.</button>&nbsp;
	<button id="butbrief_pers" >.:serdoc:.</button> &nbsp;
	<button id="butvcard_pers" >.:servcard:.</button>&nbsp;
	<button id="email_pers" >.:sermail:.</button>&nbsp;
    <span class="ui-state-highlight ui-widget-header ui-widget ui-corner-all content1">
    <input type='radio' name='autosync' value='0' checked>.:no:.
    <input type='radio' name='autosync' value='1' >.:senddir:.
    <input type='radio' name='autosync' value='2' >.:bothdir:.
	<button id="butsersync_pers" >.:AutoSync:.</button>&nbsp;
    </span>
	</form>
</span>
{report}
<div id="sercontent_pers"> 
    
