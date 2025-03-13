<html>
<head><title>CRM Firma:{Fname1}</title>
{STYLESHEETS}
{CRMCSS}
{JAVASCRIPTS}
{THEME}
{JQTABLE}
{FANCYBOX}
{QRCODE}

<script language="JavaScript" type="text/javascript">
    function c2c() {
            document.cookie = 'text=' + $('#c2c').text();        
    }
    function showCall() {
        $('#calls tr[group="tc"]').remove();
        $.ajax({
           url: 'jqhelp/firmaserver.php?task=showCalls&firma=1&id={FID}',
           dataType: 'json',
           success: function(data){
                        var content;
                        $.each(data.items, function(i) {
                             content = '';
                             content += '<tr class="verlauf" group="tc" onClick="showItem('+data.items[i].id+');">'
                             content += '<td>' + data.items[i].calldate + '</td>';
                             content += '<td>' + data.items[i].id + '</td>';
                             content += '<td nowrap>' + data.items[i].kontakt;
                             if (data.items[i].inout == 'o') {
                                  content += ' &gt;</td>';
                             } else if (data.items[i].inout == 'i') {
                                  content += ' &lt;</td>';
                             } else {
                                  content += ' -</td>';
                             }
                             if ( data.items[i].new == 1 ) {
                                 content += '<td><b>' + data.items[i].cause + '</b></td>';
                             } else {
                                 content += '<td>' + data.items[i].cause + '</td>';
                             }
                             content += '<td>' + data.items[i].cp_name + '</td></tr>';
                             $('#calls tr:last').after(content);
                        })
                        $("#calls").trigger('update');
                    }
        });
        return false;
    }
    function extra(){
            f1=open("extrafelder.php?owner={Q}{FID}","Zusatzinfo","width=750, height=600, left=50, top=50, scrollbars=yes");

    }  
    function showItem(id) {
        F1=open("getCall.php?Q={Q}&fid={FID}&bezug="+id,"Caller","width=770, height=680, left=100, top=50, scrollbars=yes");
    }
    function doTelCall() {
        console.log('doTelCall');
        $( "#dialogcall" ).dialog( "option", "maxWidth", 400 );
        $( "#dialogcall" ).dialog( "option", "maxHeight", 200 );
        $( "#dialogcall" ).dialog( { title: "Telefonanruf" } );
        $( "#dialogtext" ).html('Anruf wird initialisiert');
        $( "#dialogcall" ).dialog( "open" );
        $.ajax({
           url: 'jqhelp/telco.php',
           type: 'POST',
           data: { task:'call', From:'{userphone}', To:'{callphone}' },
        }).done(function(rc) {
             $( "#dialogtext" ).html('Local abgenommen oder abgewiesen');
        });
    }
    function anschr(A) {
        $( "#dialogwin" ).dialog( "option", "maxWidth", 400 );
        $( "#dialogwin" ).dialog( "option", "maxHeight", 600 );
        $( "#dialogwin" ).dialog( { title: "Adresse" } );
        if (A==1) {
            $('#iframe1').attr('src', 'showAdr.php?Q={Q}&fid={FID}&nojs=1');
        } else {
            sid = document.getElementById('SID').firstChild.nodeValue;
            if ( sid )
                $('#iframe1').attr('src', 'showAdr.php?Q={Q}&sid='+sid+'&nojs=1');
        }
        $( "#dialogwin" ).dialog( "open" );
    }
    function notes() {
            F1=open("showNote.php?fid={FID}","Notes","width=400, height=400, left=100, top=50, scrollbars=yes");
    }
    var shiptoids = new Array({Sids});
    var sil = shiptoids.length;
    var sid = 0;
    function nextshipto(dir) {
        if ( dir == 'o' ) {
            sid = 0;
        } else {
            if (sil<2) return;
            if (dir=="-") {
                if (sid>0) {
                    sid--;
                } else {
                    sid = (sil - 1);
                }
            } else {
                if (sid < sil - 1) {
                    sid++;
                } else {
                    sid=0;
                }
            }
        }
        $.ajax({
           url: "jqhelp/firmaserver.php?task=showShipadress&id="+shiptoids[sid]+"&Q={Q}&fid={FID}&cnt="+sil,
           dataType: 'json',
           success: function(data){
                        var adr = data.adr;
                        $('#SID').empty().append(adr.shipto_id);
                        $('#shiptoname').empty().append(adr.shiptoname);
                        $('#shiptodepartment_1').empty().append(adr.shiptodepartment_1);
                        $('#shiptodepartment_2').empty().append(adr.shiptodepartment_2);
                        $('#shiptostreet').empty().append(adr.shiptostreet);
                        $('#shiptocountry').empty().append(adr.shiptocountry);
                        $('#shiptobland').empty().append(adr.shiptobland);
                        $('#shiptozipcode').empty().append(adr.shiptozipcode);
                        $('#shiptocity').empty().append(adr.shiptocity);
                        $('#shiptocontact').empty().append(adr.shiptocontact);
                        $('#shiptophone').empty().append(adr.shiptophone);
                        $('#shiptofax').empty().append(adr.shiptofax);
                        $('#shiptoemail').empty().append(data.mail);
                        $('#karte2').attr("href",data.karte);
                    }
        })
    }
    var f1 = null;
    function showOP(was) {
                F1=open("op_.php?Q={Q}&fa={Fname1}&op="+was,"OP","width=950, height=450, left=100, top=50, scrollbars=yes");
        }
    function surfgeo() {
        if ({GEODB}) {
            F1=open("surfgeodb.php?plz={Plz}&ort={Ort}","GEO","width=550, height=350, left=100, top=50, scrollbars=yes");
        } else {
            alert("GEO-Datenbank nicht aktiviert");
        }
    }
    function doOe(type) {//Angebot / Auftrag
      window.location.href = '../oe.pl?action=add&vc={CuVe}&{CuVe}_id={FID}&type=' + type;
    }
    function doDo() { //neuer Lieferschein
      var type = '{Q}' == 'C' ? 'sales_delivery_order' : 'purchase_delivery_order';
      window.location.href = '../do.pl?action=add&vc={CuVe}&{CuVe}_id={FID}&type=' + type;
    }
    function doIr() { //neue Rechnung
      var file = '{Q}' == 'C' ? '../is.pl' : '../ir.pl';
      window.location.href = file + '?action=add&type=invoice&vc={CuVe}&{CuVe}_id={FID}';
    }
    function doIb() { //neuer Brief
      window.location.href = '../letter.pl?action=add';
    }
    function doLxCars() {
        uri='lxcars/lxcmain.php?owner={FID}&task=1'
        window.location.href=uri;
    }
    $(document).ready(
        function(){
            if ( sil == 0 ) $( '#Sprt' ).hide();
            $("#shipleft").click(function(){ nextshipto('-'); })
            $("#shipright").click(function(){ nextshipto('+'); })
            nextshipto('o'); 
            $('button').button().click( 
            function(event) {
                event.preventDefault();
                name = this.getAttribute('name');
                if ( name == 'ks' ) {
                    var sw = $('#suchwort').val();
                    F1=open("suchKontakt.php?suchwort="+sw+"&Q=C&id={FID}","Suche","width=400, height=400, left=100, top=50, scrollbars=yes");
                } else if ( name == 'reload' ) {
                    showCall();
                } else {
                    document.location.href = name;
                }
            });
            $("#fasubmen").tabs({ 
                heightStyle: "auto",
                active: {kdviewli}
                });
            $(function() {
                $( "#right_tabs" ).tabs({
                    cache: true, //helpful?
                    active: {kdviewre},
                    beforeLoad: function( event, ui ) {
                        ui.jqXHR.error(function() {
                        ui.panel.html(
                            ".:Couldn't load this tab.:." );
                        });
                    }
                });
            });
            $("#dialogwin").dialog({
                autoOpen: false,
                show: {
                    effect: "blind",
                    duration: 300
                },
                hide: {
                    effect: "explode",
                    duration: 300
                },
            });
            $("#dialogcall").dialog({
                autoOpen: false,
                show: {
                    effect: "blind",
                    duration: 300
                },
                hide: {
                    effect: "explode",
                    duration: 300
                },
                buttons: [ {
                    text: "Ok",
                    icons: { primary: "ui-icon-heart" },
                    click: function() { $( this ).dialog( "close" ); }
                }]
            });
            $(".firmabutton").button().click(
            function( event ) {
                if ( this.getAttribute('name') != 'extra' && this.getAttribute('name') != 'karte' && this.getAttribute('name') != 'lxcars') {
                    event.preventDefault();
                };
            });
           $("#kdhelp").selectmenu({
                change: function( event, ui ) {
                            link = $('#kdhelp option:selected').val();
                            if ( $('#kdhelp').prop("selectedIndex") > 0 ) {
                                  f1=open("wissen.php?kdhelp="+link,"Wissen","width=750, height=600, left=50, top=50, scrollbars=yes");
                                  $('#kdhelp option')[0].selected = true;
                            }
                        }
           });
           $("#actionmenu").selectmenu({
                change: function( event, ui ) {
                             if ($('#actionmenu option:selected').attr('id') < 20) {
                                 window.location.href = $('#actionmenu option:selected').val();
                             } else if ($('#actionmenu option:selected').attr('id') > 90) {
                                 F1=open($('#actionmenu option:selected').val(),"CRM","width=350, height=400, left=100, top=50, scrollbars=yes");
                             } else {
                                 if ( $('#actionmenu option:selected').val() == 'c2c' ) {
                                     c2c();
                                 } else if ( $('#actionmenu option:selected').val() == 'invoice' ) {
                                     doIr();
                                 } else if ( $('#actionmenu option:selected').val() == 'delivery_order' ) {
                                     doDo();
                                 } else if ( $('#actionmenu option:selected').val() == '{sales}_order' ) {
                                     doOe($('#actionmenu option:selected').val())
                                 } else if ( $('#actionmenu option:selected').val() == '{request}_quotation' ) {
                                     doOe($('#actionmenu option:selected').val())
                                 }
                             }
                        }
           });
           // --------   QR Code wird durch Jquery erstellt
           $('#logo').click(function(event) {
               $('#logourl').fancybox().trigger('click');
           });
           $("#qrbutt").button().click(
              function( event ) {
                $.ajax({
                      type: "GET",
                      url: "vcardexp.php?Q={Q}&fid={FID}",
                       success: function(strResponse){
                         $("#qrcode").qrcode({
                             "mode": 0,
                              "size": 250,
                              "color": "#3a3",
                              "text": strResponse
                        });
                       }
                 });
                 $(".fancybox").trigger('click');
                 $(".fancybox").empty();
            });
           $( "input[name='autosync']" ).change(
               function() {
                   console.log(this.value);
                   $.ajax({
                       url: 'jqhelp/firmaserver.php?task=setsync&Q={Q}&id={FID}&val='+this.value,
                       success: function(rc){ console.log(rc); },
                       error: function(rc)  { console.log(rc); }
                   });
               }
           );
           $("#syncbutt").button().click(
                function( event ) {
                $( "#dialogcall" ).dialog( "option", "maxWidth", 400 );
                $( "#dialogcall" ).dialog( "option", "maxHeight", 600 );
                $( "#dialogcall" ).dialog( { title: "Sync" } );
                $( "#dialogtext" ).html('<i>Adresse {FID} wird gesendet.</i><br><b>Bitte warten.</b>');
                $( "#dialogcall" ).dialog( "open" );
                console.log('Sync');
                $.ajax({
                      type: "GET",
                      url: "vcardexp.php?Q={Q}&fid={FID}&sync=1",
                      success: function(strResponse){
                           console.log('ok'+strResponse);
                           $( "#dialogtext" ).html('<br><center><b>'+strResponse+'</b></center>');
                      }
                }).done(function(rc) { console.log('Sync done:'+rc); });
            });
            $(".fancybox").fancybox({
				'transitionIn'	: 'elastic',
				'transitionOut'	: 'elastic',
                'autoDimensions': true
            });
       }
    );
</script>
</head>
<body onLoad=" showCall(0);">
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Stamm');">.:detailview:. {FAART} <span title=".:important note:.">{Cmsg}&nbsp;</span></h1><br>

<div id="qrcode" class="fancybox" rel="group"><img src="" alt="" /></div>

<br>
<div id='menubox1' > <!-- Reiter, Selectboxen, Tools -->
    <form>
    <span style="float:left;" valign="bottom">
        <button name="firma1.php?Q={Q}&id={FID}">.:Custombase:.</button>
        <button name="firma2.php?Q={Q}&fid={FID}">.:Contacts:.</button>
        <button name="firma3.php?Q={Q}&fid={FID}">.:Sales:.</button>
        <button name="firma4.php?Q={Q}&fid={FID}">.:Documents:.</button>
    </span>
    <span style="float:left; vertical-alig:bottom; padding-left:0.4em;">
        <select id="kdhelp" style="margin-top:0.5em; visibility:{chelp}; " >
<!-- BEGIN kdhelp -->
          <option value="{cid}">{cname}</option>
<!-- END kdhelp -->
        </select>&nbsp;
        <select id="actionmenu" style="margin-top:0.5em; padding-left:0.4em;">
            <option>Aktionen</option>
            <option id='1'  value='firmen3.php?Q={Q}&id={FID}&edit=1'>.:edit:.</option>
            <option id='2'  value='timetrack.php?action=search&tab={Q}&fid={FID}&name={Fname1}'>.:timetrack:.</option>
            <option id='90' value='extrafelder.php?owner={Q}{FID}'>.:extra data:.</option>
            <option id='4'  value='vcardexp.php?Q={Q}&fid={FID}'>VCard</option>
            <option id='20' value='c2c'>Copy to Cookie</option>
            <option id='6'  value='vcardexp.php?qr=1&Q={Q}&fid={FID}'>QR-Code</option>
            <option id='7'  value='karte.php?Q={Q}&fid={FID}'>.:register:. .:develop:.</option>
            <option id='21' value='{request}_quotation'>.:quotation:. .:develop:.</option>
            <option id='22' value='{sales}_order'>.:order:. .:develop:.</option>
            <option id='23' value='delivery_order'>.:delivery order:. .:develop:.</option>
            <option id='24' value='invoice'>.:invoice:. .:develop:.</option>
        </select>
    </span>
    </form>
</div>  <!-- Ende Reiter .... -->

<div id='contentbox'> 
    <!-- Box Fa-Stammdaten -->
    <div style="position:relative; float:left; width:49%; text-align:center; border: 1px solid lightgray; border-bottom: 0px;" >
          <!-- oberer Teil -->
          <div style='border: 0px solid black; height:270px;'>
          <!-- oben, linke Hälfte -->
          <span class="gross" style="position:relative; width:52%; float:left; text-align:left; border: 0px solid red; padding:0.2em;" >
            <span class="fett">{Fname1}</span><br />
            {Fdepartment_1} {Fdepartment_2}<br />
            {Strasse}<br />
            <span class="mini">&nbsp;<br /></span>
            <span onClick="surfgeo()">{Land}-{Plz} {Ort}</span><br />
            <span class="klein">{Bundesland}</span>
            <span class="mini"><br />&nbsp;<br /></span>
            {Fcontact}
            <span class="mini"><br />&nbsp;<br /></span>
            <font color="#444444"> .:tel:.:</font> {Telefon} {asterisk}<br />
            <font color="#444444"> .:fax:.:</font> {Fax}<br />
            <span class="mini">&nbsp;<br /></span>
            &nbsp;[<a href="{mail_pre}{eMail}{mail_after}">{eMail}</a>]<br />
            &nbsp;<a href="{Internet}" target="_blank">{Internet}</a><br />
          </span> <!-- Ende linke Hälfte -->
          <!-- oben, rechte Hälfte -->
          <span style="position:relative; float:right; width:45%; text-align:right; border: 0px solid blue; padding:0.2em;">
            <span valign='top'><span class="fett">{kdnr}</span> <img src="image/kreuzchen.gif" title=".:locked address:." style="visibility:{verstecke};" > {verkaeufer}
            <span style="visibility:{zeige_bearbeiter};"> / <b>{bearbeiter}</b></span>
            <br />
            <br class='mini'>
               {ANGEBOT_BUTTON}
               {AUFTRAG_BUTTON}
               {LIEFER_BUTTON}
               {RECHNUNG_BUTTON}<br />
            <br class='mini'>
               {EXTRA_BUTTON}
               {QR_BUTTON}
               {KARTE_BUTTON}
               {ETIKETT_BUTTON}<br />
            <br class='mini'>
               {SYNC_BUTTON}
               {BRIEF_BUTTON}
               {LxCars_BUTTON}<br />
            <br class='mini'>
            {IMG}<a href="{IMGURL}" id='logourl' style='visibility:hidden'></a>
          </span> <!-- Ende rechte Hälfte -->
        <!-- Ende oberer Teil -->
          </div>

        <!-- Box Zusatzdaten mit Reitermenü, unterer Teil-->
        <div id="fasubmen" style='position:relative; width:*;' >
            <ul>
                <li><a href="#lie">.:shipto:. </a></li>
                <li><a href="#not">.:notes:. </a></li>
                <li><a href="#var">.:variablen:. </a></li>
                <li><a href="#fin">.:financial:.</a></li>
                <li><a href="#inf">.:miscInfo:. </a></li>
            </ul>
            <div id="lie" class="klein" style='text-align:left;'>
                <span class="fett" id="shiptoname"></span> &nbsp;&nbsp;&nbsp;&nbsp;
                .:shipto count:.:{Scnt} <img src="image/leftarrow.png" id='shipleft' border="0">
                <span id="SID"></span> <img src="image/rightarrow.png" id='shipright' border="0">&nbsp; &nbsp;
                <a href="#" onCLick="anschr();"><img src="image/brief.png" id='Sprt' alt=".:print label:." border="0"/></a>&nbsp; &nbsp;
                <a href="" id='karte2' target="_blank"><img src="image/karte.gif" alt="karte" title=".:city map:." border="0"></a><br />
                <span id="shiptodepartment_1"></span> &nbsp; &nbsp; <span id="shiptodepartment_2"></span> <br />
                <span id="shiptostreet"></span><br />
                <span class="mini">&nbsp;<br /></span>
                <span id="shiptocountry"></span>-<span id="shiptozipcode"></span> <span id="shiptocity"></span><br />
                <span id="shiptobundesland"></span><br />
                <span class="mini">&nbsp;<br /></span>
                <span id="shiptocontact"></span><br />
                .:tel:.: <span id="shiptophone"></span><br />
                .:fax:.: <span id="shiptofax"></span><br />
                <span id="shiptoemail"></span>
            </div> <!-- Ende lie -->
            <div id="not">
                <table class="tablesorter" width="50%" style='margin:0px; cursor:pointer;'>
                <thead></thead>
                <tbody>        
                    <tr><td width="20%" >.:Catchword:.</td><td>{sw}</td></tr>
                    <tr><td width="20%" >.:Remarks:.</td><td>{notiz}</td></tr>
                </tbody>
                </table>                
            </div> <!-- Ende not -->
            <div id="var" >
                <table class="tablesorter" width="50%" style='margin:0px; cursor:pointer;'>
                <thead></thead>
<!-- BEGIN vars -->
                    <tr><td width="20%" >{varname}</td><td>{varvalue}</td></tr>
<!-- END vars -->
                </table>
             </div> <!-- Ende var -->
             <div id="inf">
                 <table class="tablesorter" width="50%" style='margin:0px; cursor:pointer;'>
                     <thead></thead>
                     <tbody>
                         <tr><td width="20%">.:Concern:.:</td><td width="25%"><a href="firma1.php?Q={Q}&id={konzern}">{konzernname}</a></td>
                                          <td width="25%"><a href="konzern.php?Q={Q}&fid={FID}">{konzernmember}</a></td><td></td></tr>
                         <tr><td width="20%">.:Industry:. </td><td width="25%">{branche}</td><td width="25%"></td><td></td></tr>
                         <tr><td width="20%">.:headcount:.:</td><td width="25%">{headcount}</td><td width="25%"></td><td></td></tr>
                         <tr><td width="20%">.:language:.:</td><td width="25%">{language} </td><td width="25%"></td><td></td></tr>
                         <tr><td width="20%">.:Init date:.:</td><td width="25%">{erstellt} </td><td width="25%">.:update:.: </td><td>{modify} </td></tr>
                         <tr><td width="20%">.:SyncID:.:</td><td colspan='2'>{uid} </td></tr>
                         <tr><td width="20%">.:AutoSync:.:</td><td colspan='2'><input type='radio' name='autosync' value='0' {autosync0}>.:no:.
                                                                               <input type='radio' name='autosync' value='1' {autosync1}>.:senddir:.
                                                                               <input type='radio' name='autosync' value='2' {autosync2}>.:bothdir:.
                         </td></tr>
                     </tbody>
                 </table>
             </div> <!-- Ende inf -->
             <div id="fin" >
                 <table class="tablesorter" width="50%" style='margin:0px; cursor:pointer;'>
                     <thead></thead>
                     <tbody>
                         <tr><td width="20%">.:Source:.:</td><td width="35%">{lead} {leadsrc}</td><td width="21%">.:Discount:.:</td><td>{rabatt}</td></tr>
                         <tr><td width="20%">.:{Q}Business:.:</td><td width="35%">{kdtyp}</td><td width="21%">.:Price group:.:</td><td>{preisgrp}</td></tr>
                         <tr><td width="20%">.:taxnumber:.:</td><td width="35%">{Taxnumber}</td><td width="21%">.:terms:.:</td><td>{terms} .:days:.</td></tr>
                         <tr><td width="20%">UStId:</td><td width="35%">{USTID}</td><td width="21%">.:creditlimit:.:</td><td>{kreditlim}</td></tr>
                         <tr><td width="20%">.:taxzone:.:</td><td width="35%">{Steuerzone}</td><td width="21%">.:outstanding:. :</td><td></td></tr>
                         <tr><td width="20%">.:bankname:.:</td><td width="35%">{bank}</td><td width="21%">- .:items:.:</td><td>{op}</td></tr>
                         <tr><td width="20%">.:directdebit:.:</td><td width="35%">{directdebit}</td><td width="21%">- .:orders:.:</td><td>{oa}</td></tr>
                         <tr><td width="20%">.:bankcode:.:</td><td width="35%">{blz}</td><td></td><td></td></tr>
                         <tr><td width="20%">.:bic:.:</td><td width="35%">{bic}</td><td></td><td></td></tr>
                         <tr><td width="20%">.:account:.:</td><td width="35%">{konto}</td><td></td><td></td></tr>
                         <tr><td width="20%">.:iban:.:</td><td width="35%">{iban}</td><td></td><td></td></tr>
                     </tbody>
                 </table>
             </div> <!-- Ende fin -->
        </div> <!-- Ende Zusatzdaten, unterer Teil -->
    </div> <!-- Ende Stammdatenbox -->

    <!-- Kontaktthread, Angebote, Aufträge, Rechnungen -->
    <div style="position:relative; float:left; width:50%;text-align:left; border: 1px solid lightgrey; border-left:0px;">
         <div id="right_tabs">
            <ul>
                <li><a href="#contact">.:contact:.</a></li>
                <li><a href="jqhelp/get_doc.php?Q={Q}&fid={FID}&type=quo">.:Quotation:.</a></li>
                <li><a href="jqhelp/get_doc.php?Q={Q}&fid={FID}&type=ord">.:orders:.</a></li>
                <li><a href="jqhelp/get_doc.php?Q={Q}&fid={FID}&type=del">.:delivery order:.</a></li>
                <li><a href="jqhelp/get_doc.php?Q={Q}&fid={FID}&type=inv">.:invoice:.</a></li>    
            </ul>
            <div id="contact">
                <table id="calls" class="tablesorter" width="100%" style='margin:0px; cursor:pointer;'>
                    <thead><tr><th>Datum</th><th>id</th><th class="{ sorter: false }"></th><th>Betreff</th><th>.:contact:.</th></tr></thead>
                    <tbody>
                        <tr onClick="showItem(0)" class='verlauf'><td></td><td>0</td><td></td><td>.:newItem:.</td><td></td></tr>
                    </tbody>
                </table><br>
                <div id="pager" class="pager" style='position:absolute;'>
                    <form name="ksearch" onSubmit="false ks();"> &nbsp;
                        <img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/first.png" class="first">
                        <img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/prev.png" class="prev">
                        <input type="text" id='suchwort' name="suchwort" size="20"><input type="hidden" name="Q" value="{Q}">
                        <button id='ks' name='ks'>.:search:.</button>
                        <button id='reload' name='reload'>reload</button>
                        <img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/next.png" class="next">
                        <img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/last.png" class="last">
                        <select class="pagesize" id='pagesize'>
                            <option value="10">10</option>
                            <option value="15" selected>15</option>
                            <option value="20">20</option>
                            <option value="25">25</option>
                            <option value="30">30</option>
                        </select>
                    </form>
                </div><!-- End Pager -->
                <br/>
            </div><!-- End contact -->
         </div><!-- End right_tabs -->
    </div> <!-- Kontaktthread -->
</div><!-- End contentbox -->

<div id="dialogwin">
  <iframe id="iframe1" width='100%' height='450'  scrolling="auto" border="0" frameborder="0"><img src='image/wait.gif'></iframe>
</div>
<div id="dialogcall">
<center>
<span id='dialogtext'></span>
</center>
</div>
<span id='c2c' style='visibility:hidden'>{C2C}</span><!-- Daten für Cookie, keine Bedeutung für die Anzeige -->

</div>
{END_CONTENT}
</body>
</html>
