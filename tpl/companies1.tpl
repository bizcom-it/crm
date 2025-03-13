<script language="JavaScript" type="text/javascript">

    function sende() {
        var tab = $("input[name='tabelle']:checked").val()
        var felder = '';
        $( '#firma option:selected' ).each(function() {
            felder += tab +'.' + $(this).val() + ',';
        });
        $( '#shipto option:selected' ).each(function() {
            felder += 'S.' + $(this).val() + ',';
        });
        $( '#contacts option:selected' ).each(function() {
            felder += 'P.' + $(this).val() + ',';
        });
        $( '#felder' ).val(felder);
        dialog_report();
        $( 'suchbutton_C' ).click();
    }
    function holeTabellen() {
        var tab = $("input[name='tabelle']:checked").val();
        $.ajax({
            type : 'GET',
            url  : 'jqhelp/getReportTables.php?tab='+tab,
            dataType: 'json',
            success: function(data){
                 $.each(data.tables, function(k , v) {
                      $.each( data.tables[k] , function( key, value ) {
                         $("<option/>").val(value).text(value).appendTo("#" + k);
                      });
                 })
            }
        });
    }

    function dialog_report() {
            holeTabellen();
            if ( $( '#dialog_report' ).dialog('isOpen') ) {
                $( '#dialog_report' ).dialog('close');
            } else {
                $( '#dialog_report' ).dialog('open');
            }
    }

    $(document).ready(function() {
     
        $("#dialog_report" ).dialog({
            autoOpen: false,
            modal: true,
            width: 800,
            position: [100,300]
        });     
        $( "#geo_C" ).button().click(function() {
            if ({GEODB}) {
                fuzzy=(document.erwsuche.fuzzy.checked==true)?1:0;
                plz=document.erwsuche.zipcode.value;
                ort=document.erwsuche.city.value;
                tel=document.erwsuche.phone.value;
                F1=open("surfgeodb.php?ao=and&plz="+plz+"&ort="+ort+"&tel="+tel+"&fuzzy="+fuzzy,"GEO","width=550, height=350, left=100, top=50, scrollbars=yes");
	       } 
	       else alert(".:noGEOdb:.");
	       return false;
        });
        $( "input[name='tabelle']" ).click(function() {
            if (  $("input[name='tabelle']:checked").val() == 'B' ) {
               $( '#extra1' ).hide(); 
               $( '#extra2' ).hide(); 
            } else  if (  $("input[name='tabelle']:checked").val() != 'C' ) {
               $( '#extra2' ).show(); 
               $( '#extra1' ).hide(); 
            } else  if (  $("input[name='tabelle']:checked").val() != 'V' ) {
               $( '#extra2' ).hide(); 
               $( '#extra1' ).show(); 
            }
        });
        $( "#report_C" ).button().click(function(event) {
            event.preventDefault();
            tab = $("input[name='tabelle']:checked").val();
            if ( tab != 'B' ) {
                dialog_report();        
            } else {
                alert('Nur in einer Tabelle suchen');
            }
  	    });
  	    $( ".fett_C" ).click(function() {
            waiton();
            if ( $(this).html() == '#' ) first = '~';
            else first = $(this).html(); 
            tab = $("input[name='tabelle']:checked").val();
            $.ajax({
                type: "POST",
                data: 'first=' + first + '&tabelle=' + tab, 
                url: "jqhelp/getCompanies1.php",
                success: function(res) {
                    $( "#dialog_keine, #dialog_viele, #dialog_no_sw" ).dialog( "close" );
                    if ( !res ) $( "#dialog_keine" ).dialog( "open" );                    
                    else {
                        $( "#suchfelder_C" ).hide();
                        $( "#companyResults_C").html(res); 
                        $( "#companyResults_C").show();
                    }                       
                }
            }).done(function() { waitoff(); });
            return false;
        });
        $( "#suchbutton_C" ).button().click(function() {
            waiton();
            $.ajax({
                type: "POST",
                data: $("#erwsuche_C").serialize() + '&suche=suche', 
                url: "jqhelp/getCompanies1.php",
                success: function(res) {
                    $( "#dialog_keine, #dialog_viele, #dialog_no_sw" ).dialog( "close" );
                    if ( !res ) $( "#dialog_keine" ).dialog( "open" );                    
                    else {
                        $( "#suchfelder_C" ).hide();
                        $( "#companyResults_C" ).html(res); 
                        $( "#companyResults_C" ).show();
                    }                                              
                }
            }).done(function() { waitoff(); });
            return false;
        });
        $( "#reset_C" ).button().click(function() {
            $( "#dialog_keine, #dialog_viele, #dialog_no_sw" ).dialog( "close" );
            $( "#erwsuche_C" ).find(':input').each(function() {
                switch(this.type) {
                    case 'text':
                        $(this).val('');
                    break;
                    case 'checkbox':
                    case 'radio':
                        this.checked = false
                }
            });
            $( "#andorC, #shiptoselC, #fuzzyC, #preC, #obsoleteC" ).click();
            $( "#nameC" ).focus();
            return false;
        });
        $( "#nameC" ).focus();
        $( '#extra2' ).hide(); 
    });	
</script>
<script type='text/javascript' src='inc/help.js'></script>


<div id="dialog_report" title="Report">
    <form name="report" id="formreport" onSubmit='return false;'>
    <table width='300'><tr><th>Firma</th><th>Shipto</th><th>Kontakte</th></tr><tr>
    <td><select id='firma'    name='firma'    size='10' multiple width='90'></select></td>
    <td><select id='shipto'   name='shipto'   size='10' multiple width='90'></select></td>
    <td><select id='contacts' name='contacts' size='10' multiple width='90'></select></td>
    </tr></table>
    <button onClick='sende();'>ok</button>  <button onClick='dialog_report();'>.:close:.</button>
    </form>
</div>

<p class="ui-state-highlight ui-corner-all content1">
<button class="fett_C">A</button> 
<button class="fett_C">B</button> 
<button class="fett_C">C</button> 
<button class="fett_C">D</button> 
<button class="fett_C">E</button> 
<button class="fett_C">F</button> 
<button class="fett_C">G</button> 
<button class="fett_C">H</button> 
<button class="fett_C">I</button> 
<button class="fett_C">J</button> 
<button class="fett_C">K</button> 
<button class="fett_C">L</button> 
<button class="fett_C">M</button> 
<button class="fett_C">N</button> 
<button class="fett_C">O</button> 
<button class="fett_C">P</button> 
<button class="fett_C">Q</button> 
<button class="fett_C">R</button> 
<button class="fett_C">S</button> 
<button class="fett_C">T</button> 
<button class="fett_C">U</button> 
<button class="fett_C">V</button> 
<button class="fett_C">W</button> 
<button class="fett_C">X</button> 
<button class="fett_C">Y</button> 
<button class="fett_C">Z</button> 
<button class="fett_C">#</button> 
</p>

<form name="erwsuche" id="erwsuche_C" enctype='multipart/form-data' action="#" method="post">
<input type="hidden" id='felder' name="felder" value="">
<input type="hidden" id='Q' name="Q" value="C">


	<div class="zeile">
		<span class="label">.:FaNr:.</span>
		<span class="leftfeld"><input type="text" name="customernumber" size="27" maxlength="15" value="{customernumber}" tabindex="1"></span>
		<span class="label">.:Contact:.</span>
		<span class="leftfeld"><input type="text" name="contact" size="27" maxlength="25" value="{contact}" tabindex="21"></span>
	</div>
	<div class="zeile">
		<span class="label">.:company:.</span>
		<span class="leftfeld"><input type="text" name="name" id="nameC" size="27" maxlength="75" value="{name}" tabindex="1"></span>
		<span class="label">.:Industry:.</span>
		<span class="leftfeld"><input type="text" name="branche" size="27" maxlength="25" value="{branche}" tabindex="21"></span>
	</div>
	<div class="zeile">
		<span class="label">.:department:.</span>
		<span class="leftfeld"><input type="text" name="department_1" size="27" maxlength="75" value="{department_1}" tabindex="2"></span>
		<span class="label">.:Catchword:.</span>
		<span class="leftfeld"><input type="text" name="sw" size="27" maxlength="125" value="{sw}" tabindex="22"></span>
	</div>
	<div class="zeile">
		<span class="label">.:street:.</span>
		<span class="leftfeld"><input type="text" name="street" size="27" maxlength="75" value="{street}" tabindex="3"></span>
		<span class="label">.:Remarks:.</span>
		<span class="leftfeld"><input type="text" name="notes" size="27" maxlength="125" value="{notes}" tabindex="23"></span>
	</div>
	<div class="zeile">
		<span class="label">.:country:. / .:zipcode:.</span>
		<span class="leftfeld"><input type="text" name="country" size="2" maxlength="5" value="{country}" tabindex="4"> / 
					<input type="text" name="zipcode" size="7" maxlength="15" value="{zipcode}" tabindex="5"></span>
		<span class="label">.:bankname:.</span>
		<span class="leftfeld"><input type="text" name="bank" size="27" maxlength="50" value="{bank}" tabindex="24"></span>
	</div>
	<div class="zeile">
		<span class="label">.:city:.</span>
		<span class="leftfeld"><input type="text" name="city" size="27" maxlength="75" value="{city}" tabindex="6"></span>
		<span class="label">.:bankcode:.</span>
		<span class="leftfeld"><input type="text" name="bank_code" size="27" maxlength="25" value="{bank_code}" tabindex="26"></span>
	</div>
	<div class="zeile">
		<span class="label">.:phone:.</span>
		<span class="leftfeld"><input type="text" name="phone" size="27" maxlength="75" value="{phone}" tabindex="7"></span>
		<span class="label">.:account:.</span>
		<span class="leftfeld"><input type="text" name="account_number" size="27" maxlength="25" value="{account_number}" tabindex="27"></span>
	</div>
	<div class="zeile">
		<span class="label">.:fax:.</span>
		<span class="leftfeld"><input type="text" name="fax" size="27" maxlength="125" value="{fax}" tabindex="8"></span>
		<span class="label">UStID</span>
		<span class="leftfeld"><input type="text" name="ustid" size="27" maxlength="12" value="{ustid}" tabindex="28"></span>
	</div>
	<div class="zeile">
		<span class="label">.:email:.</span>
		<span class="leftfeld"><input type="text" name="email" size="27" maxlength="125" value="{email}" tabindex="9"></span>
		<span class="label">www</span>
		<span class="leftfeld"><input type="text" name="homepage" size="27" maxlength="125" value="{homepage}" tabindex="29"></span>
	</div>
	<div class="zeile">
		<span class="label">.:Business:.</span>
		<span class="leftfeld">
			<select name="business_id" tabindex="10">
<!-- BEGIN TypListe -->	
				<option value="{BTid}" {BTsel}>{BTtext}</option>
<!-- END TypListe -->				
			</select>
		</span>
		<span class="label">.:lang:.</span>
		<span class="leftfeld">	<select name="language_id" tabindex="30">
				<option value="">
<!-- BEGIN LAnguage -->	
				<option value="{LAid}" {LAsel}>{LAtext}
<!-- END LAnguage -->	
			</select>
		</span>
	</div>
	<div class="zeile">
		<span class="label">.:leadsource:.</span>
		<span class="leftfeld">
			<select name="lead" tabindex="11" style="width:110px;">
<!-- BEGIN LeadListe -->	
				<option value="{LLid}" {LLsel}>{LLtext}</option>
<!-- END LeadListe -->				
			</select>
			<input type="text" name="leadsrc" size="5" value="{leadsrc}" tabindex="12">
		</span>
		<span class="label">.:headcount:.</span>
		<span class="leftfeld"><input type="text" name="headcount" size="7" maxlength="7" value="{headcount}" tabindex="32"></span>
	</div>
	<div class="zeile">
        <span class="label">.:sales volume:.</span>
        <span class="leftfeld"><input type="text" name="umsatz" size="7" maxlength="25" value="{umsatz}" tabindex="32"> .:year:. 
			<select name="year" tabindex="11" >
<!-- BEGIN YearListe -->	
				<option value="{YLid}" {YLsel}>{YLtext}</option>
<!-- END YearListe -->				
			</select></span>
	</div>
<!-- BEGIN cvarListe -->	
	<div class="zeile">
		<span class="label">{varlable1}</span>
		<span class="leftfeld">{varfld1}</span>
		<span class="label">{varlable2}</span>
		<span class="leftfeld">{varfld2}</span>
	</div>
<!-- END cvarListe -->	
	<div class="zeile">
            <br>
			.:search:. .:in table:.<input type="radio" name="tabelle"  id="tabelle" value="C" {tabelleC} tabindex="40">.:Customer:. 
			<input type="radio" name="tabelle"  id="tabelle" value="V" {tabelleV} tabindex="40">.:Vendor:. 
			<input type="radio" name="tabelle"  id="tabelle" value="B" {tabelleB} tabindex="40">.:both:. 
            <br>
			.:search:. <input type="radio" name="andor"  id="andor" value="and" checked tabindex="40">.:all:. <input type="radio" name="andor" value="or" tabindex="40">.:some:.<br>

			<input type="checkbox" name="shipto" id="shiptosel" value="1" checked tabindex="40">.:also in:. .:shipto:.<br>

			<input type="checkbox" name="fuzzy" id="fuzzy" value="%" checked tabindex="41">.:fuzzy search:. <input type="checkbox" name="pre" id="preC" value="1" {preon}>.:with prefix:.<br>

			<input type="checkbox" name="employee" value="{employee}" tabindex="42">.:only by own:.<br>
			.:obsolete:. <input type="radio" name="obsolete" value="t" >.:yes:. <input type="radio" name="obsolete" value="f" >.:no:.  <input type="radio" name="obsolete" id="obsoleteC" value="" checked >.:equal:.<br>
			<button id="suchbutton_C" tabindex="43">.:search:.</button>&nbsp;
			<button id="reset_C" tabindex="44">.:clear:.</button> &nbsp;
			<button id="report_C"  tabindex="45">Report</button> &nbsp;
			<button id="geo_C"  tabindex="46" {showGeo}>GeoDB</button> &nbsp;
            <span id='extra1' style='display:{extra1};' ><a href="extrafelderS.php?owner=C"><img src="image/extra.png" alt="Extras" title="Extras" border="0" /></a></span>
            <span id='extra2' style='display:{extra2};' ><a href="extrafelderS.php?owner=V"><img src="image/extra.png" alt="Extras" title="Extras" border="0" /></a></span>
            <br>
			{report}
	</div>
</form>
