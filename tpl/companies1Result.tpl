<script type="text/javascript" src="{CRMPATH}js/tablesorter.js"></script>
<script language="JavaScript">
	function showK (id,tab) {
		if (id) {
			uri = "firma1.php?Q=" + tab + "&id=" + id;
			location.href = uri;
		}
	}
    $(document).ready(function() {
        $( "#synccontent_C" ).dialog({
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
        $( "#modify_search_C" ).button().click(function() {
            $( "#suchfelder_C").show();
            $( "#companyResults_C").hide();
            $( "#nameC" ).focus();
            return false;
        });
        $( "#butetikett_C" ).button().click(function() {
            $( "#sercontent_C" ).dialog( "option", "maxWidth", 400 );
            $( "#sercontent_C" ).dialog( "open" );
            $( "#sercontent_C" ).dialog( { title: "Etiketten" } );
            $( "#sercontent_C" ).load("etiketten.php?src=F");
            return false;
        });
        $( "#butsync_C" ).button().click(function() {
            console.log('Synce');
            $( "#synccontent_C" ).dialog( "option", "maxWidth", 400 );
            $( "#synccontent_C" ).dialog( "open" );
            $( "#synccontent_C" ).dialog( { title: "Sync" } );
            $( '#syncimg').html("<img src='image/waitingwheel.gif'>");
            $( '#syncmsg').html('Daten werden nun übertragen.<br>Bitte warten.');
            $.ajax({
                url : 'jqhelp/serien.php?task=sync',
                dataType : 'json',
                success: function(rc) {
                   console.log('Success');
                   console.log(rc);
                   $( '#syncmsg' ).html(rc.add + ' neue Adressen <br>' +
                                        rc.upd + ' Addressen aktualisiert');
                },
                error: function(rc) {
                   console.log('Error');
                   console.log(rc);
                   $( '#syncimg' ).html('<h4>Achtung, Fehler!</h4>');
                   $( '#syncmsg' ).html(rc.msg + '<br>Zuletzt bearbeitet: ' + rc.last + 
                                        rc.add + ' neue Adressen <br>' +
                                        rc.upd + ' Addressen aktualisiert');
                }
            }).done(function(rc) {
                    console.log('Done');
                    console.log(rc);
                    $( '#syncimg' ).html('<button class=\'button\' onClick=\'$( "#synccontent_C" ).dialog( "close" );\'>.:close:.</button><br>');
                    $( '#syncmsg' ).html(rc.add + ' neue Adressen <br>' +
                                         rc.upd + ' Addressen aktualisiert');
            })
            return false;
        });
        $( "#butsersync_C" ).button().click(function() {
            $( "#sercontent_C" ).dialog( "option", "maxWidth", 400 );
            $( "#sercontent_C" ).dialog( "open" );
            $( "#sercontent_C" ).dialog( { title: "Syncstatus ändern" } );
            var stat = $( "input[name='autosync']:checked" ).val();
            $( "#sercontent_C" ).load("sersync.php?src=F&status="+stat);
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
            $( "#sercontent_C" ).dialog( "option", "minWidth", 700 );
            $( "#sercontent_C" ).dialog( "open" );
            $( "#sercontent_C" ).dialog( { title: "Serienbrief" } );
            $( "#sercontent_C" ).load("serdoc.php?src=F");
            return false;
        });
        $( "#butsermail_C" ).button().click(function() {
            $( "#sercontent_C" ).dialog( "option", "minWidth", 850 );
            $( "#sercontent_C" ).dialog( "open" );
            $( "#sercontent_C" ).dialog( { title: "Serienmail" } );
            $( "#sercontent_C" ).load("sermail.php?src=F");
            return false;
        });
        //$( "input[type=button]" ).button();
        $("#treffer_C")
            .tablesorter({widthFixed: true, widgets: ["zebra"]})
            .tablesorterPager({container: $("#pager_C"), size: 20, positionFixed: false});
    });
</script>

<p class="ui-state-highlight ui-widget-header ui-widget ui-corner-all content1">.:search result:. .:{FAART}:.</p>
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
		</select>
    <br>
    <br>
    <button id="modify_search_C" >.:modify search:.</button>&nbsp;
	<button id="butetikett_C" >.:label:.</button>&nbsp;
	<button id="butbrief_C" >.:serdoc:.</button>&nbsp;
	<button id="butvcard_C" >.:servcard:.</button>&nbsp;
	<button id="butsync_C" >.:syncit:.</button>&nbsp;
	<button id="butsermail_C" >.:sermail:.</button>&nbsp;
    <span class="ui-state-highlight ui-widget-header ui-widget ui-corner-all content1">
    <input type='radio' name='autosync' value='0' checked>.:no:.
    <input type='radio' name='autosync' value='1' >.:senddir:.
    <input type='radio' name='autosync' value='2' >.:bothdir:.
	<button id="butsersync_C" >.:AutoSync:.</button>&nbsp;
    </span>
	</form>
</span>
{report}

<div id="synccontent_C">
    <h4>Daten zum Syncserver senden</h4>
    <span id='syncimg'></span>
    <span id='syncmsg'></span>
</div>

<div id="sercontent_C"> 

