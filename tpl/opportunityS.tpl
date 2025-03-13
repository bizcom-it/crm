<html>
    <head><title>{TITLE}</title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{THEME}
{DATEPICKER}
{JQTABLE}
{JUI-DROPDOWN}
{AUTOCOMPLETE}
	<script language="JavaScript">
	<!--
    function hide(nr) {
		document.getElementById(nr).style.display="none";
	}
    function show(nr) {
        var elStyle = document.getElementById(nr).style;
        elStyle.display = (elStyle.display == "inline")?'none':'inline'
	}
	function toggle(was1,was2) {
		document.getElementById(was1).style.display="none";
		document.getElementById(was2).style.display="block";
	}
	function sichern() {
		document.getElementById("ok").style.display="block";
	}
	function quotation(nr) {
		if (nr>0) {
			f=open("rechng.php?id=L{auftrag}","Auftrag","width=650,height=400,left=100,top=100,scrollbars=yes");
		} 
	}
	//-->
	</script>
	<script>
        $(function() {
            $( "#zieldatum" ).datepicker($.datepicker.regional[ "de" ]);
            $( "#history" ).tablesorter({widthFixed: true, widgets: ['zebra'], headers: { 
                0: { sorter: false }, 1: { sorter: false }, 2: { sorter: false }, 3: { sorter: false }, 4: { sorter: false }, 
                5: { sorter: false }, 6: { sorter: false }, 7: { sorter: false }, 8: { sorter: false } } 
            });
            $.widget("custom.catcomplete", $.ui.autocomplete, {
                _renderMenu: function(ul,items) {
                    var that = this,
                    currentCategory = "";
                    $.each( items, function( index, item ) {
                        if ( item.category != currentCategory ) {
                            ul.append( "<li class=\'ui-autocomplete-category\' >" + item.category + "</li>" );
                            currentCategory = item.category;
                        }
                        that._renderItemData(ul,item);
                    });
                }
            });
            $("#name").catcomplete({
                source: "jqhelp/autocompletion.php?case=name",                            
                minLength: {feature_ac_minlength},
                delay: {feature_ac_delay},
                disabled: false,
                select: function(e,ui) {
                    console.log(ui);
                    $( '#fid' ).val(ui.item.id);
                    $( '#tab' ).val(ui.item.src);
                    $( '#firma' ).val(ui.item.value);
                    sichern();
                    //return false;
                }
            });

        });
        </script>
   <style>

        .ui-tabs {
          position: relative;
          left: 5px;
          width: 45em;
          top: 0px;
          border-width: 0px;
          padding: 0;
}
   </style>
<body>
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Auftragschance');">.:opportunity:.</h1><br>

    <span style="position:absolute; left:1em; top:3.3em; width:95%;">
        <form name="formular" action="opportunity.php" method="post">
        <input type="hidden" name="id" value="{id}">
        <input type="hidden" name="oppid" value="{oppid}">
        <input type="hidden" id='tab' name="tab" value="{tab}">
        <input type="hidden" id='fid' name="fid" value="{fid}">
        <input type="hidden" id='firma' name="firma" value="{firma}">
        <span style="display:{stamm};">
            <a href="firma1.php?Q={tab}&id={fid}"><img src="image/addressbook.png" border="0" alt=".:masterdata:." title=".:masterdata:."></a>
            <a href="opportunity.php?Q={tab}&fid={fid}"><img src="image/listen.png" border="0" alt=".:opportunitys:." title=".:opportunitys:."></a>
            <a href="opportunity.php?Q={tab}&fid={fid}&new=1"><img src="image/new.png" border="0" alt=".:new:./.:search:." title=".:new:./.:search:."></a>
            <!--a href="opportunity.php?history={oppid}"><img src="image/history.png" border="0" alt=".:history:." title=".:history:."></a-->
            <img src="image/nummer.png" border="0" alt=".:quotation:." title=".:quotation:." onClick="quotation({auftrag});" style="visibility:{auftragshow};">
                .:changed:. {chgdate} .:by:. {user}
            <br /><br />
    </span>				
<div style="position:absolute;  left:1px;  top:3.8em; border: 0px solid black; text-align:center;" >
	<br />
	<div class="zeile">
		<span class="label klein" onClick='toggle("fa1","fa2");'>.:company:.</span>
		<span class="leftfeld pad value" style="width:50em; display:{block}" id="fa2" onClick='toggle("fa2","fa1");'>{firma}</span>
		<span class="leftfeld"           style="width:50em; display:{none};" id="fa1">
			<input type="text" size="50" id="name" name="name" value="{firma}" class="ui-widget-content ui-corner-all title"  autocomplete="on">
		</span>
	</div>
	<div class="zeile">
		<span class="label klein" onClick='toggle("ti1","ti2");'>.:subject:.</span>
		<span class="leftfeld pad value" style="width:50em; display:{block}" onClick='toggle("ti2","ti1");' id="ti2">{title}</span>
		<span class="leftfeld"     style="width:50em; display:{none};" id="ti1">
			<input type="text" size="65" name="title" value="{title}" onChange="sichern()">
		</span>
	</div>
	<div class="zeile">
		<span class="label klein" onClick='toggle("be1","be2");'>.:ordersum:.</span>
		<span class="leftfeld pad value" style="width:50em; display:{block}" onClick='toggle("be2","be1");' id="be2">{betrag} &euro;</span>
		<span class="leftfeld"     style="width:50em; display:{none};" id="be1">
			<input type="text" size="10" name="betrag" value="{betrag}" onChange="sichern()"> &euro;
		</span>
	</div>
	<div class="zeile">
		<span class="label klein" onClick='toggle("zi1","zi2");'>.:targetdate:.</span>
		<span class="leftfeld pad value" style="width:50em; display:{block}" onClick='toggle("zi2","zi1");' id="zi2">{zieldatum}</span>
		<span class="leftfeld"     style="width:50em; display:{none};" id="zi1">
			<input type="text" size="10" name="zieldatum" id="zieldatum" value="{zieldatum}" onChange="sichern()"> tt.mm.jjjj 
		</span>
	</div>
	<div class="zeile">
		<span class="label klein">.:chance:.</span>
		<span class="leftfeld"><select name="chance" onChange="sichern()">
			<option value="" {csel}>---</option>
			<option value="1" {csel1}>10%</option>
			<option value="2" {csel3}>20%</option>
			<option value="3" {csel3}>30%</option>
			<option value="4" {csel4}>40%</option>
			<option value="5" {csel5}>50%</option>
			<option value="6" {csel6}>60%</option>
			<option value="7" {csel7}>70%</option>
			<option value="8" {csel8}>80%</option>
			<option value="9" {csel9}>90%</option>
			<option value="10" {csel10}>100%</option>
			</select>
		</span>
	</div>
	<div class="zeile">
		<span class="label klein">.:status:.</span>
		<span class="leftfeld"><select name="status" onChange="sichern()">
			<option value="" {ssel}>---</option>
<!-- BEGIN status -->
			<option value="{sval}" {ssel}>{sname}</option>
<!-- END status -->
			</select>
		</span>
	</div>
	<div class="zeile">
		<span class="label klein">.:salesman:.</span>
		<span class="leftfeld"><select name="salesman" onChange="sichern()">
			<option value="" {esel}>---</option>
<!-- BEGIN salesman -->
			<option value="{evals}" {esel}>{ename}</option>
<!-- END salesman -->
			</select>
		</span>
	</div>
	<div class="zeile">
		<span class="label klein">.:quotation:.</span>
		<span class="leftfeld"><select name="auftrag" onChange="sichern()">
			<option value="" {asel}>---</option>
<!-- BEGIN auftrag -->
			<option value="{aval}" {asel}>{aname}</option>
<!-- END auftrag -->
			</select><a href='../oe.pl?action=edit&type=sales_order&vc=customer&id={auftragsnummer}&callback=crm/opportunity.php?id={id}'>{zumauftrag}</a>

		</span>
	</div>
	<div class="zeile">
		<span class="label klein" onClick='toggle("ne1","ne2");'>.:nextstep:.</span>
		<span class="leftfeld pad value" style="width:50em; display:{block}" onClick='toggle("ne2","ne1");' id="ne2">{next}</span>
		<span class="leftfeld"     style="width:50em; display:{none};" id="ne1">
			<input type="text" size="65" name="next" value="{next}" onChange="sichern()">
		</span>
	</div>
	<div class="zeile klein">
		<span class="label" onClick='toggle("no1","no2");'>.:notes:.</span>
		<span class="leftfeld pad value" style="width:50em; display:{block}" onClick='toggle("no2","no1");' id="no2">
			{notxt}
		</span>
		<span class="leftfeld" style="width:45em; display:{none};" id="no1">
			<textarea name="notiz" cols="80" rows="10" onChange="sichern()">{notiz}</textarea>
		</span>
	</div>
	<div class="zeile">
		<span class="label"></span>
		<span class="leftfeld" style="width:350px; display:{none};" id="ok">
            <input type="hidden" name="action" value="">
			<img src="image/suchen_kl.png" alt='.:search:.' title='.:search:.' name="suchen" value=".:search:." style="visibility:{search};" onclick="document.formular.action.value='suchen'; document.formular.submit();"> &nbsp;
			<img src='image/save_kl.png' alt='.:save:.' title='.:save:.' name='save' value='.:new:.' style="visibility:{save};" onclick="document.formular.action.value='save'; document.formular.submit();"> &nbsp; 
			<a href={backlink}><input type='image' src='image/firma.png' alt='.:back:.' title='.:back:.' name='back' value='.:back:.' style="visibility:{blshow};"></a> &nbsp; 
			{msg}
		</span>
	</div>
    </form>
    <table id='history' class="tablesorter" width="100%" style='margin:0px; cursor:pointer;'>
        <thead>
	        <tr><th>.:subject:.</th><th>.:ordersum:.</th><th>.:targetdate:.</th><th>.:chance:.</th>
            <th>.:status:.</th><th>.:quotation:.</th><th>.:nextstep:.</th><th>.:employee:.</th><th>.:changed:.</th></tr>
        </thead>
        <tbody>
<!-- BEGIN Liste --> 
        <tr onClick="show('n{nr}');">
        <td> {histtitle}</td>
        <td style="width:7em;text-align:right"> {histbetrag}</td>
        <td style="width:6em;text-align:right"> {histdatum}</td>
		<td style="width:2em;text-align:right"> {histchance}</td>
		<td style="width:10em;text-align:left"> {histstatus}</td>
		<td> {histauftrag}</td>
		<td> {histnext}</td>
		<td> {user}</td>
		<td style="width:6em;text-align:left">&nbsp;{chgdate}</td></tr>
        <tr onClick="hide('n{nr}');" >
        <!-- Der blöde Firefox kann das nicht mehr ordentlich darstellen -->
        <td style="display:none; font-size:80%; background-color:transparent;"  id='n{nr}'  colspan="9">{histnotiz}</td></tr>
<!-- END Liste -->
        </tbody>
    </table>
</div>
</div>
</span>
{END_CONTENT}
</body>
</html>
