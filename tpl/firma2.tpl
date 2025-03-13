<html>
        <head><title></title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{THEME}
{JQTABLE}
{FANCYBOX}
{QRCODE}

    <script language="JavaScript">
    <!--
        function c2c() {
            var text = '';
            text  = 'name:'      + $('#cp_name').text() + "|";
            text += 'givenname:' + $('#cp_givenname').text() + "|";
            text += 'greeting:'  + $('#cp_greeting').text() + "|";
            text += 'title:'     + $('#cp_title').text() + "|";
            text += 'street:'    + $('#cp_street').text() + "|";
            text += 'country:'   + $('#cp_country').text() + "|"; 
            text += 'zipcode:'   + $('#cp_zipcode').text() + "|";
            text += 'city:'      + $('#cp_city').text() + "|";
            text += 'phone:'     + $('#cp_phone1').text() + "|"; 
            text += 'mobile:'    + $('#cp_mobile1').text() + "|"; 
            text += 'fax:'       + $('#cp_fax').text() + "|";
            text += 'email:'     + $('#cp_email').text() + "|";
            text += 'homepage:'  + $('#cp_homepage').text() + "|";
            text += 'abteilung:' + $('#cp_abteilung').text() + "|";
            text += 'stichwort:' + $('#cp_stichwort1').text() + "|";
            text += 'notes:'     + $('#cp_notes').text() + "|";
            document.cookie = 'text=' + text;
        }
        function showItem(id) {
            pid = $('#liste option:selected').val();
            F1=open("getCall.php?Q={Q}C&pid="+pid+"&bezug="+id,"Caller","width=770, height=680, left=100, top=50, scrollbars=yes");
        }
        function anschr() {
            pid = $('#liste option:selected').val();
            $( "#dialogwin" ).dialog( "option", "maxWidth",  400 );
            $( "#dialogwin" ).dialog( "option", "maxHeight", 600 );
            $( "#dialogwin" ).dialog( { title: "Adresse" } );
            $('#iframe1').attr('src', 'showAdr.php?Q={Q}&pid='+pid+'&nojs=1'+'{ep}');
            $( "#dialogwin" ).dialog( "open" );
        }
        function notes() {
            pid = $('#liste option:selected').val();
            F1=open("showNote.php?Q={Q}&pid="+pid,"Notes","width=400, height=400, left=100, top=50, scrollbars=yes");
        }
        function extra(){
            pid = $('#liste option:selected').val();
            f1=open("extrafelder.php?owner=P"+pid,"Zusatzinfo","width=750, height=600, left=50, top=50, scrollbars=yes");

        }        
        function vcard(){
            pid = $('#liste option:selected').val();
            document.location.href="vcardexp.php?Q={Q}&pid="+pid;
        }        
        function cedit(ed){
            pid=false;
            if (ed) pid = $('#liste option:selected').val();
            if ( pid == undefined ) return;
            document.location.href="personen3.php?id="+pid+"&edit="+ed+"&Quelle={Q}&fid={FID}";
        }
        function sellist(){                
            pid = $('#liste option:selected').val();
            document.location.href="personen1.php?fid={FID}&Quelle={Q}";
        }
        function doclink(){                
            pid = $('#liste option:selected').val();
            document.location.href="firma4.php?Q={Q}&fid={FID}&pid="+pid;
        }
        var start = 0;
        var max = 0;
        var y = 0;
        function showCall() {
            pid = $('#liste option:selected').val();
            $('#calls tr[group="usr"]').remove();
            $.ajax({
               url: 'jqhelp/firmaserver.php?task=showCalls&firma=0&id='+pid,
               dataType: 'json',
               success: function(data){
                            var content;
                            $.each(data.items, function(i) {
                                 content = '';
                                 content += '<tr group="usr" onClick="showItem('+data.items[i].id+');">'
                                 content += '<td>' + data.items[i].calldate + '</td>';
                                 content += '<td>' + data.items[i].id + '</td>';
                                 content += '<td>' + data.items[i].kontakt;
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
                                $("#calls")
                                    .tablesorter({widthFixed: true, widgets: ['zebra'], headers: { 2: { sorter: false } } })
                                    .tablesorterPager({container: $("#pager"), size: 15, positionFixed: false})
                        }
               });
            return false;
        }
        function showOne(id) {
            //was wollte ich hier?
        }
        function showContact() {
            pid = $('#liste option:selected').val();
            if ( typeof pid == 'undefined' ) return;
            $( '#cp_id' ).html(pid);
            $("input[name='autosync']:checked").removeAttr("checked");
            $.ajax({
                url: "jqhelp/firmaserver.php?task=showContact&id="+pid,
                dataType: 'json',
                success: function(data){
                              if (data.cp_id > 0) {
                                   if ( data.cp_sync == null ) data.cp_sync = 0;
                                   $('#cp_greeting').empty().append(data.cp_greeting);
                                   $('#cp_title').empty().append(data.cp_title);
                                   $('#cp_givenname').empty().append(data.cp_givenname);
                                   $('#cp_name').empty().append(data.cp_name);
                                   $('#cp_street').empty().append(data.cp_street);
                                   $('#cp_country').empty().append(data.cp_country); 
                                   $('#cp_zipcode').empty().append(data.cp_zipcode);
                                   $('#cp_city').empty().append(data.cp_city);
                                   $('#cp_phone1').empty().append(data.cp_phone1); 
                                   $('#cp_phone2').empty().append(data.cp_phone2);
                                   $('#cp_mobile1').empty().append(data.cp_mobile1); 
                                   $('#cp_mobile2').empty().append(data.cp_mobile2);
                                   $('#cp_fax').empty().append(data.cp_fax);
                                   $('#cp_email').empty().append(data.cp_email);
                                   $('#cp_privatemail').empty().append(data.cp_privatemail);
                                   $('#cp_homepage').empty().append(data.cp_homepage);
                                   $('#cp_grafik').empty().append(data.cp_grafik);
                                   $('#cp_birthday').empty().append(data.cp_birthday);
                                   $('#cp_position').empty().append(data.cp_position);
                                   $('#cp_abteilung').empty().append(data.cp_abteilung);
                                   $('#cp_vcard').empty().append(data.cp_vcard);
                                   $('#vcardbutt').css('visibility',(data.cp_vcard)?'visible':'hidden');
                                   $('#cp_stichwort1').empty().append(data.cp_stichwort1);
                                   $('#cp_notes').empty().html(data.cp_notes);
                                   $('#vcardurl').attr('href',data.cp_vcardurl);
                                   $('#grafikurl').attr('href',data.cp_grafikurl);
                                   $( "input[name='autosync']" )[data.cp_sync].checked = true;
                              } else {
                                  $('#cp_name').empty().append(data.cp_name);
                                  $('#cp_greeting').empty();
                                  $('#cp_title').empty();
                                  $('#cp_givenname').empty();
                                  $('#cp_street').empty();
                                  $('#cp_country').empty(); 
                                  $('#cp_zipcode').empty();
                                  $('#cp_city').empty();
                                  $('#cp_phone1').empty(); 
                                  $('#cp_phone2').empty();
                                  $('#cp_mobile1').empty(); 
                                  $('#cp_mobile2').empty();
                                  $('#cp_fax').empty();
                                  $('#cp_email').empty();
                                  $('#cp_privatemail').empty();
                                  $('#cp_homepage').empty();
                                  $('#cp_grafik').empty();
                                  $('#cp_birthday').empty();
                                  $('#cp_position').empty();
                                  $('#cp_abteilung').empty();
                                  $('#cp_vcard').empty();
                                  $('#vcardbutt').css('visibility','hidden');
                                  $('#cp_stichwort1').empty();
                                  $('#cp_notes').empty();
                                  $('#vcardurl').attr('href','');
                                  $('#grafikurl').attr('href','');
                                  $( "input[name='autosync']" )[0].checked = true;
                                  
                              }
                         }
            })
            showCall();
        }
    var f1 = null;

    //-->
    </script>
    <script>
    $(document).ready(
        function(){
           $('#cp_grafik').click(function(event) {
               $('#grafikurl').fancybox().trigger('click');
           });        
           $('#vcardbutt').click(function(event) {
               $('#vcardurl').fancybox().trigger('click');
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
           $("#qrbutt").button().click(
            function( event ) {
                pid = $('#liste option:selected').val();
                $.ajax({
                      type: "GET",
                      url: "vcardexp.php?Q={Q}&pid="+pid,
                       success: function(strResponse){
                         $("#images").qrcode({
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
        $("#syncbutt").button().click(
                function( event ) {
                pid = $('#liste option:selected').val();
                $( "#dialogcall" ).dialog( "option", "maxWidth", 400 );
                $( "#dialogcall" ).dialog( "option", "maxHeight", 600 );
                $( "#dialogcall" ).dialog( { title: "Sync" } );
                $( "#dialogtext" ).html('<i>Adresse {FID} wird gesendet.</i><br><b>Bitte warten.</b>');
                $( "#dialogcall" ).dialog( "open" );
                console.log('Sync');
                $.ajax({
                      type: "GET",
                      url: 'vcardexp.php?Q=P&pid='+pid+'&sync=1',
                      success: function(strResponse){
                           console.log('ok'+strResponse);
                           $( "#dialogtext" ).html('<br><center><b>'+strResponse+'</b></center>');
                      }
                }).done(function(rc) { console.log('Sync done:'+rc); });
            });
        $(".firmabutton").button().click(
            function( event ) {
                if ( this.getAttribute('name') != 'extra' && this.getAttribute('name') != 'karte' ) {
                    event.preventDefault();
                };
            });

           $( "input[name='autosync']" ).change(
               function() {
                   var id = $( '#cp_id' ).html();
                   console.log(this.value + ' ' +id);
                   $.ajax({
                       url: 'jqhelp/firmaserver.php?task=setsync&Q=P&id='+id+'&val='+this.value,
                       success: function(rc){ console.log(rc); },
                       error: function(rc)  { console.log(rc); }
                   });
               }
           );
           $("#kdhelp").selectmenu({
                change: function( event, ui ) {
                            link = $('#kdhelp option:selected').val();
                            if ( $('#kdhelp').prop("selectedIndex") > 0 ) {
                                  f1=open("wissen.php?kdhelp="+link,"Wissen","width=750, height=600, left=50, top=50, scrollbars=yes");
                                  $('#kdhelp option')[0].selected = true;
                            }
                        }
           });        
           $("#liste").selectmenu({
                change: function( event, ui ) {
                            showContact();
                        }
           });        
        $( "#dialogwin" ).dialog({
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
        showContact();
    });
    $(function(){
         $('button')
          .button()
          .click( function(event) { 
              event.preventDefault();  
              name = this.getAttribute('name');
              if ( name == 'ks' ) {
                  var sw = $('#suchwort').val();
                  F1=open("suchKontakt.php?suchwort="+sw+"&Q=C&id={FID}","Suche","width=400, height=400, left=100, top=50, scrollbars=yes");
              } else if ( name == 'reload' ) {
                  showCall();
              } else if ( name == 'Link1' ) {
                  document.location.href = '{Link1}';
              } else if ( name == 'Link2' ) {
                  document.location.href = '{Link2}';
              } else if ( name == 'Link3' ) {
 					   document.location.href = '{Link3}'; 
              } else if ( name == 'null' ) {
                 event.preventDefault();
              } else {
                  var pid = $('#liste option:selected').val();
                  document.location.href = name + pid; 
              }
          });
         $( "input[type=submit]")
          .button()
         .click(function( event ) {
              event.preventDefault();
         });
         $(".fancybox").fancybox({
				'transitionIn'	: 'elastic',
				'transitionOut'	: 'elastic',
                'autoDimensions': true});
    });

    </script>
<body>
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Stamm');">.:detailview:. {FAART} <span title=".:important note:.">{Cmsg}&nbsp;</span></h1><br>

<br/>
<div id="menubox1">
    <form>
    <span style="float:left;" valign="bottom">
        <button name="Link1">.:Custombase:.</button>
        <button name="Link2">.:Contacts:.</button>
        <button name="Link3">.:Sales:.</button>
        <button name="firma4.php?Q={Q}&fid={FID}&pid=">.:Documents:.</button>
    </span>
    <span style="float:left; vertical-alig:bottom">        
        <select id="kdhelp" style="margin-top:0.5em; visibility:{chelp}; " >
<!-- BEGIN kdhelp -->
        <option value="{cid}">{cname}</option>
<!-- END kdhelp -->
        </select>
    </span>
    </form>
</div>

<div id='contentbox'  >
    <div style="float:left; width:49%; height:37em;  border: 1px solid lightgray;" >
        <div style="float:left; width:100%; position:relative; height:4.9em; text-align:left; border-bottom: 1px solid lightgray;">
            <form name="contact">
            <div class="fett" style="border: 0px solid red; width:50%; float:left; padding-left:0.5em;">
                {Fname1} &nbsp; &nbsp; .:KdNr:.: {customernumber}<br />
                {Fdepartment_1}<br />
                {Plz} {Ort} 
            </div>
            <div style="float:right; width:44%; position:relative; border: 0px solid blue; text-align:right;">
                <select name="liste" id="liste" style='min-width:200px;' >
                {kontakte}
                </select><br>
                <span id='cp_id'></span>
            </div>
            </form>
        </div>
        <div style="float:left; width:57%; height:14em; text-align:left; border-bottom: 0px solid lightgray; padding-left:0.5em;" >
            <span id="cp_greeting"></span> <span id="cp_title"></span><br />
            <span id="cp_givenname"></span> <span id="cp_name"></span><br />
            <span id="cp_street"></span><br />
            <span class="mini">&nbsp;<br /></span>
            <span id="cp_country"></span><span id="cp_zipcode"></span> <span id="cp_city"></span><br />
            <span class="mini">&nbsp;<br /></span>
            <img src="image/telefon.gif" style="visibility:{none};" id="phone"> <span id="cp_phone1"></span> <span id="cp_phone2"></span><br />
            &nbsp;<img src="image/mobile.gif" style="visibility:{none};" id="mobile"> <span id="cp_mobile1"></span> <span id="cp_mobile2"></span><br />
            <img src="image/fax.gif" style="visibility:{none};" id="fax"> <span id="cp_fax"></span><br />
            <span id="cp_email"></span><br />
            <span id="cp_homepage"></span><br /><br />
        </div>
        <div style="float:left; width:41%; height:14em; text-align:right; border-bottom: 0px ;" id="cpinhalt2">
            <span id="extraF"></span>
            <a class="firmabutton" href="#" onCLick="vcard();" title='VCard erstellen'><img src="image/vcard2.png" border="0" style="visibility:{none};" id="cpvcard" height='30' width='40'></a> &nbsp; 
            <a class="firmabutton" href="#" id='syncbutt' title='Daten zum Syncserver senden'><img src="image/syncsend.png" border="0" style="visibility:{none};" id="cpsync" height='30' width='40'></a> &nbsp; 
            <a class="firmabutton" href="#" id='qrbutt' title='QR-Code erstellen'><img src="image/qr.png" border="0" style="visibility:{none};" id="cpqr" height='30' width='40'></a><br /><br /> 
            <a class='firmabutton' href='#' id="vcardbutt" title='VCard anzeigen'><span id='cp_vcard'></span></a><a href='' id='vcardurl'></a>&nbsp;
            <a class="firmabutton" href="#" onCLick="extra();" title='Extradaten'><img src="image/extra.png" border="0" style="visibility:{none};" id="cpvcard" height='30' width='40'></a> &nbsp; 
            <a class="firmabutton" href="#" id='anschr' onCLick="anschr();" title='Etikett erstellen'><img src="image/brief.png" border="0" style="visibility:{none};" id="cpbrief" height='30' width='40'></a><br>
            <span id="cp_birthday" style="padding-right:1px;" title='Geburtstag'></span>  <span id="cp_grafik" style="padding-right:1px;"></span><a href='' id='grafikurl'></a></br >
            <span id="cp_abteilung" style="padding-right:1px;"></span> / <span id="cp_position" style="padding-right:1px;"></span><br />
        </div>
        <div style="position:absolute;top:20em; left:0em; width:49%;  text-align:left; border-bottom: 0px;">
            &nbsp;<span id="cp_privatphone"></span> <span id="cp_privatemail"></span><br />
             <hr width="100%">
                &nbsp;<!--input type='submit' value='VCard' onClick="vcard()" -->
                <b>.:Contacts:.:</b> 
                <input type='submit' value='{Edit}' onClick="cedit(1)" >
                <input type='submit' value='.:keyin:.' onClick="cedit(0)" >
                <input type='submit' value='.:fromList:.' onClick="sellist()">
                <input type='submit' value='Cookie' onClick="c2c()" ><br><br>
                &nbsp;&nbsp;.:AutoSync:.:  <input type='radio' name='autosync' value='0'>.:no:.
                                           <input type='radio' name='autosync' value='1'>.:senddir:.
                                           <input type='radio' name='autosync' value='2'>.:bothdir:.

            <hr>
            <span id="cp_stichwort1" class="klein fett" style="width:45em; padding-left:1em;"></span><br />
            <span id="cp_notes" class="klein" style="width:45em; padding-left:1em;"></span>
        </div>
    </div>

    <div style="float:left; width:50%; height:37em; text-align:left; border: 0px solid lightgray; border-left:0px;">
        <table id="calls" class="tablesorter" width="100%" style='margin:0px;'>
        <thead><tr><th>Datum</th><th>id</th><th class="{ sorter: false }"></th><th>Betreff</th><th>.:contakt:.</th></tr></thead>
        <tbody style='cursor:pointer'>
        <tr onClick="showItem(0)"><td></td><td>0</td><td></td><td>.:newItem:.</td><td></td></tr>
        </tbody>
        </table><br>
        <div id="pager" class="pager" style='position:absolute;'>
            <form name="ksearch" onSubmit="return ks();"> &nbsp; 
 	    	  	<img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/first.png" class="first">
                <img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/prev.png" class="prev">
                <input type="text" id="suchwort" name="suchwort" size="20"><input type="hidden" name="Q" value="{Q}">
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
        </div>
    </div>
<div id="dialogwin">
    <iframe id="iframe1" width='100%' height='450'  scrolling="auto" border="0" frameborder="0"><img src='image/wait.gif'></iframe>
</div>
<span id='c2c' style='visibility:hidden'></span>
<div id="dialogcall">
<center>
<span id='dialogtext'></span>
</center>
</div>
<div id="images" class="fancybox" rel="group"></div>
{END_CONTENT}
</body>
</html>
