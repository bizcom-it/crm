<html>
    <head><title>.:usersettings:.</title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{BOXCSS}
{JQTABLE}
{THEME}    
{JQFILEDOWN}
{JQBOX}
<style type="text/css">
    input.b0 { width:50px; }
    input.b1, select.b1 { width: 200px;  }
    .selectboxit-container .selectboxit-options {width: 170px;}
    #selportSelectBoxItContainer.selectboxit-container .selectboxit-options {width: 10px;}
    #termbeginSelectBoxItContainer.selectboxit-container .selectboxit-options {width: 10px;}
    #termendSelectBoxItContainer.selectboxit-container .selectboxit-options {width: 10px;}
    .selectboxit-container span, .selectboxit-container .selectboxit-options a {height: 22px; line-height: 22px;}
    .inp-checkbox+label {
        margin: .5em;
        width:16px; 
        height:16px; 
        vertical-align:middle;
    }   
    li.ui-state-default.ui-state-hidden[role=tab]:not(.ui-tabs-active) {
    display: none;
    }
</style>
<script language="javascript" type="text/javascript" src="translation/telco.lng"></script>
<script language="JavaScript">
(function ($) {
    $.fn.disableTab = function (tabIndex, hide) {

        // Get the array of disabled tabs, if any
        var disabledTabs = this.tabs("option", "disabled");

        if ($.isArray(disabledTabs)) {
            var pos = $.inArray(tabIndex, disabledTabs);

            if (pos < 0) {
                disabledTabs.push(tabIndex);
            }
        }
        else {
            disabledTabs = [tabIndex];
        }

        this.tabs("option", "disabled", disabledTabs);

        if (hide === true) {
            $(this).find('li:eq(' + tabIndex + ')').addClass('ui-state-hidden');
        }

        // Enable chaining
        return this;
    };

    $.fn.enableTab = function (tabIndex) {
                $(this).find('li:eq(' + tabIndex + ')').removeClass('ui-state-hidden');
        this.tabs("enable", tabIndex);
        return this;
        
    };


})(jQuery);
    function showItem(Q,id) {
	    F1=open("getCall.php?hole="+id+Q,"Caller","width=800, height=650, left=100, top=50, scrollbars=yes");
    }
    function Mailonoff() {
        var Q, p, email;
        var content = '';
        $('#mailtable tbody').empty();
        $.ajax({
            url: 'jqhelp/firmaserver.php?task=usermail&uid={uid}',
            dataType: 'json',
            success: function(data){
                $.each(data, function(i, row) {
                    if ( row.cp_mail != null ) {
                        email = row.cp_email;
                        Q     = '&Q=XC&pid='+row.pid;
                    } else if ( row.cemail != null ) {
                        email = row.cemail;
                        Q     = '&Q=C&pid='+row.cid;
                    } else if ( row.vemail != null ) {
                        email = row.vemail;
                        Q     = '&Q=C&pid='+row.vid;
                    } else {
                        Q = '&Q=XX';
                        p = row.cause.indexOf('|');
                        if ( p>=0 ) {
                            email = row.cause.substring(p+1);
                            row.cause = row.cause.substring(0,p);
                        } else {
                            email = '--------';
                        }
                    }
                    content += '<tr onClick="showItem(\''+Q+'\','+row.id+');"><td>'+row.datum+' '+row.zeit+'</td><td>'+email+'</td><td>'+row.cause+'</td></tr>';
                });
                $('#mailtable tbody').append(content);
                $("#mailtable").trigger('update');
                $("#mailtable")
                    .tablesorter({widthFixed: true, widgets: ['zebra'] })
                    .tablesorterPager({container: $("#pager"), size: 15, positionFixed: false})
            }
        })
    }
    function kal(fld) {
        f=open("terminmonat.php?datum={DATUM}&fld="+fld,"Name","width=410,height=390,left=200,top=100");
        f.focus();
    }
    function expkal() {
        start = $('#start').val();
        stop  = $('#stop').val();
        ext   = $('#icalext').val();
        dest  = $('#icaldest').val();
        art   = $('#icalart option:selected').val();
        $.ajax({
            url: 'jqhelp/mkics.php?start='+start+'&stop='+stop+'&icalext='+ext+'&icaldest='+dest+'&icalart='+art,
            dataType: 'json',
            success: function(data){
                if ( data.rc == 0 ) {
                       $('#msgkal').empty().append(data.msg);
                } else if ( data.rc == 1 ) {
                       $('#downloadfile').attr('href',data.file);
                       $('#downloadfile').text('Download');
                       $.fileDownload(data.file);
                       $('#msgkal').empty().append(data.msg);
                       $('#msgkal').append(' '+data.cnt);
                } else if ( data.rc == 2 ) {
                       $('#msgkal').empty().append(data.msg);
                       $('#msgkal').append(' '+data.cnt);
                } else if ( data.rc == 3 ) {
                       $('#msgkal').empty().append(data.msg);
                       $('#msgkal').append(' '+data.cnt);
                } else if ( data.rc == 4 ) {
                       $('#msgkal').empty().append(data.msg);
                       $('#msgkal').append(' '+data.cnt);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(xhr.status);
                alert(thrownError);
            }
        });
    }
    function destchg() {
        art   = $('#icalart option:selected').val();
        if ( art == 'mail' ) {
            $('#icaldest').prop('readonly',false);
            $('#icaldest').css({'background-color' : '#ffffff'});
        } else {
            $('#icaldest').prop('readonly',true);
            $('#icaldest').css({'background-color' : '#aaaaaa'});
        }
    }
    function selPort() {
        po = document.user.selport.selectedIndex;
        document.user.port.value=document.user.selport.options[po].value;
    }
    function importVC() {    //Ausgewählte Daten in die DB übernehmen
        console.log('Import');
        var update = [];
        var insert = [];
        var upd = $( 'input[name^=update]:checked' );
        var ins = $( '[name^=neu] option:selected' );
        $( 'input[name^=update]:checked' ).each( function() { update.push( $(this).val() ); } );
        $( '[name^=neu] option:selected' ).each( function() { if ( $(this).val() != '-' ) insert.push( $(this).val() ); } );
        console.log(update);
        console.log(insert);
        $.ajax({
            type: 'POST',
            url:  'jqhelp/serien.php?task=importvc',
            data: {'task':'importvc','update':update,'insert':insert},
            succsess: function(rc) {
                console.log('ok'+rc);
            },
            error:    function(rc) {
                console.log('Error'+rc);
            },
        }).done(function(rc) {
                console.log('Done');
                console.log(rc);
                $( '#output' ).append('<br>'+rc);

        })

    }
    $(document).ready(function(){
        $("#dialog_saved, #noThemeFile, #cantEditBase, #syncput" ).dialog({ 
            autoOpen: false,
            modal: true,
            width: 400,
            position: { my: "center top", at: "center center" }
        });          
        $("#syncget" ).dialog({ 
            autoOpen: false,
            modal: true,
            width: 800,
            position: { my: "center top", at: "center top" }
        });          
        var language = kivi.myconfig.countrycode;
        $('.lang').each( function(){
            var key = $( this ).attr( "data-lang" );
            $( this ).text( typeof( langData[language][key] ) != 'undefined' ? langData[language][key] : 'LNG ERR'  );
        });
        $( "#asterisk" ).change( function() {
           if ( $( "#asterisk" ).is(':checked') ) {
              $( '#maintab' ).enableTab(3);
           } else {
              $( '#maintab' ).disableTab(3, true);
           }
        });
        //$( "#maintab" ).tabs({ heightStyle: "auto" });
        $( "#maintab" ).tabs({ overflow: "auto" });
        $( "#maintab" ).tabs({ activate: function( event,ui ) {
                    var current = ui.newTab.index().toString();
                    if ( current < 3 ) { $('#SAVEUSR').show();  }
                    else { $('#SAVEUSR').hide(); };
                }
        });
        $( "#edit_theme" ).button().click(function( event ) {
            event.preventDefault();
            var theme = $("#theme").val()
            $.ajax({
                type: "POST",
                url:  "jqhelp/getThemeUrl.php",
                data: {theme: theme},
                success: function(result){ 
                    if( result == "noThemeFile" ) $("#noThemeFile").dialog( "open" );
                    else if( result == "base" ) $("#cantEditBase").dialog( "open" );                
                    else window.open(result);
                }   
            })
            return false; 
        });
        $( "#PUTCARD" ).button().click(function(event) {  //Markierte Datensätze zum Syncserver übertragen
            event.preventDefault();
            $( '#syncput' ).dialog('open');
            $( '#syncimg').html("<img src='image/waitingwheel.gif'><br>");
            $( '#syncmsg').html('Daten werden nun übertragen.<br>Bitte warten.');
            console.log('Senden');
            $.ajax({
                url:  'jqhelp/serien.php?task=putcard',
                dataType: 'json',
                succsess: function(rc) {
                    console.log('ok'+rc);
                        $( '#syncimg').html(rc.cnt+' Daten erfolgreich übertragen.');
                        $( '#syncmsg').html(rc.add+' neue Addressen<br>' +
                                            rc.upd+' Adressen aktuallisiert');
                },
                error:    function(rc) {
                    console.log('Error'+rc);
                    $( '#syncimg').html('Fehler: ' + rc.msg);
                    $( '#syncmsg').html('letzte ID: ' + rc.id);
                },
            }).done(function(rc) {
                    console.log('Done');
                    console.log(rc);
                    $( '#syncimg' ).html('<button class=\'button\' onClick=\'$( "#syncput" ).dialog( "close" );\'>.:close:.</button><br>');
                    $( '#syncmsg' ).html(rc.add + ' neue Adressen <br>' +
                                         rc.upd + ' Addressen aktualisiert');
            })
            return false;
        });
        $( "#GETCARD" ).button().click(function(event) {  //VCards vom Syncserver holen
            event.preventDefault();
            console.log('Holen');
            $( '#syncget' ).dialog('open');
            $( '#output' ).empty().html('Daten werden geholt. Bitte warten.');
            $.ajax({
                url:  'jqhelp/serien.php?task=getcard',
                //dataType: 'json',
                succsess: function(rc) {
                    console.log('ok'+rc);
                        //$( '#syncimg').html(rc.cnt+' Daten erfolgreich übertragen.');
                        //$( '#syncmsg').html(rc.found+' Addressen automatisch zugeordnet<br>' +
                        //                    rc.more+' Adressen unbekannt');
                    $( '#output' ).html(rc);
                },
                error:    function(rc) {
                    console.log('Error'+rc);
                    //$( '#syncimg').html('Fehler: ' + rc.msg);
                    //$( '#syncmsg').html('letzte ID: ' + rc.id);
                    $( '#output' ).html(rc);
                },
            }).done(function(rc) {
                    console.log('Done');
                    console.log(rc);
                    $( '#output' ).html(rc);
            })
            return false;
            
        });
        $( "#SAVEUSR" ).button().click(function(event) {
            event.preventDefault();
            console.log('SaveUserData');
            $.ajax({
                type: "POST",
                url: "jqhelp/saveUserData.php",
                data: { task: 'usrsave', form: $("#userform").serialize()} ,
                success: function(res) {
                    console.log(res);
                    $("#dialog_saved").dialog( "open" );
                    setTimeout("$('#dialog_saved').dialog('close')",1100);
                }
            });
            return false;
        });
        $('#streetview_default').click(function() {
            var $this = $(this);
            if ($this.is(':checked') ) {
                $("#streetview,#planspace").hide()  
            } else {
                $("#streetview,#planspace").show()           
            }
        });
        if( $('#streetview_default').is(':checked') ){
            $("#streetview,#planspace").hide()   
        }
        $('#external_mail').click(function() {
            var $this = $(this);
            if ($this.is(':checked') ) {
                $("#mails_button").hide()  
            } else {
                $("#mails_button").show()           
            }
        });
        if( $('#external_mail').is(':checked') ){
            $("#mails_button").hide()   
        }
        $("select").selectBoxIt({
            theme:       "jqueryui",
            autoWidth:   true,
            //hideCurrent:  true,
            
        })
        $( "td#mansig,span#proto,td#ssl,td#caltype" ).buttonset();
        $(".inp-checkbox").button({ text: false})
            .click(function(e) {
                $(this).button("option", {
                    icons: {
                        primary: $(this)[0].checked ? "ui-icon-check" : ""
                    }
                });
           
            });
        $( "#p1{streetview_default}{preon}{tinymce}{angebot_button}{auftrag_button}{rechnung_button}{asterisk}"
         + "{liefer_button}{zeige_extra}{zeige_karte}{zeige_bearbeiter}{zeige_etikett}{zeige_tools}{mobiletel}"
         + "{zeige_lxcars}{feature_unique_name_plz}{external_mail}{sql_error}{php_error}" ).click(); 
        var on = $( "#asterisk" ).is(':checked') ;
        if ( ! on ) $( '#maintab' ).disableTab(3, true);
        $( '#TEST' ).button().click( function(event) {
                event.preventDefault();
                var TS = $( '#TelcoServer' ).val();
                var TC = $( '#TelCommand' ).val();
                var AK = $( '#AuthKey' ).val();
                if ( TS == '' && TC == '' ) { 
                    alert('Host oder Kommando muss angegeben werden');
                    return;
                }
                $( '#MSG' ).html('Test gestartet');
                $.ajax({
                    url: 'jqhelp/telco.php',
                    type: "POST",
                    data: { task:  'test', TelcoServer: TS, TelCommand: TC, AuthKey: AK },
                }).done(function(rc) {
                    console.log(rc);
                    $( '#MSG' ).html(rc);
                });
            }); //end Button test
        $( '#CALL' ).button().click( function(event) {
                event.preventDefault();
                var FROM = $( '#FromNr' ).val();
                var TO   = $( '#ToNr' ).val();
                console.log('Call '+FROM+' '+TO);
                $( '#MSG' ).html('Anruf gestartet');
                $.ajax({
                    url: 'jqhelp/telco.php',
                    type: "POST",
                    data: { task:  'call', From: FROM, To: TO },
                }).done(function(rc) {
                    console.log(rc);
                    $( '#MSG' ).html(rc);
                });
            }); //end Button call        
    });
</script>
<body>
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style=" border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('User');">.:usersettings:.  {login} : {uid}</h1><br>

    <div id="dialog_saved" title=".:usersettingscrm:.">
        <p>.:usersettingssaved:.</p>
    </div>

    <div id="noThemeFile" title="Theme wechseln">
        <p>.:nothemefilefound:.</p>
    </div>

     <div id="cantEditBase" title="Theme bearbeiten">
        <p>.:basecannotbechanged:.</p>
    </div>   


<div id="maintab">
    <ul>
        <li><a href="#tab1">.:users:.</a></li>
        <li><a href="#tab2">CRM</a></li>
        <li><a href="#tab3">.:sync:.</a></li>
        <li><a href="#tab6">Asterisk</a></li>
        <li><a href="#tab4">.:sales volume:.</a></li>
        <li><a href="#tab5" onclick='Mailonoff();'>.:email:.</a></li>
    </ul>

<!-- E-Mail Tab  -->
    <span id="tab5">    
        <table id="mailtable" class="tablesorter">
        <thead>
            <tr><th>.:date:.</th><th>.:emailaddress:.</th><th>.:subject:.</th></tr>
        </thead>
        <tbody id='mtablebody'>
        </tbody>
        </table>
            <div id="pager" class="pager b1">
                <img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/first.png" class="first"/>
                <img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/prev.png" class="prev"/>
                <img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/next.png" class="next"/>
                <img src="{CRMPATH}jquery/plugin/tablesorter-master/addons/pager/icons/last.png" class="last"/>
                <select class="pagesize b1" id='pagesize'>
                    <option value="10">10</option>
                    <option value="15" selected>15</option>
                    <option value="20">20</option>
                    <option value="25">25</option>
                    <option value="30">30</option>
                </select>
         </div>
    </span>
        
    <form name="user" id="userform" action="user1.php" method="post">
        <input type="hidden" name="uid" value="{uid}">
        <input type="hidden" name="login" value="{login}">
    
<!-- User Tab  -->
    <span id="tab1">
        <table border="0">
        <tr><td class="norm">.:searchtab:.</td>
            <td>
                <select class="b1" name="searchtab" data-size="39">
                    <option value="1" {searchtab1}>.:fastsearch:.
                    <option value="2" {searchtab2}>.:Company:.
                    <option value="3" {searchtab3}>.:persons:.
                </select></td>
            <td class="norm">.:searchtable:.</td>
            <td><input type='radio' name='searchtable' id='stC' value='C' {stC}><label for="stC">.:Customer:.</label>
                <input type='radio' name='searchtable' id='stV' value='V' {stV}><label for="stV">.:Vendor:.</label>
                <input type='radio' name='searchtable' id='stV' value='B' {stB}><label for="stB">.:both:.</label>
            </td></tr>
        <tr><td class="norm">.:kdviewli:.</td>
            <td>
                <select class="b1" name="kdviewli">
                    <option value="1" {kdviewli1}>.:shipto:.
                    <option value="2" {kdviewli2}>.:remarks:.
                    <option value="3" {kdviewli3}>.:variablen:.
                    <option value="4" {kdviewli4}>.:financial:.
                    <option value="5" {kdviewli5}>.:miscInfo:.
                </select></td>
            <td class="norm">.:label:.</td>
            <td class="norm">
                <select class="b1" name="etikett">
<!-- BEGIN SelectboxB -->
                     <option value="{LID}"{FSel}>{FTXT}</option>
<!-- END SelectboxB -->
                </select>
            </td></tr>
        <tr><td class="norm">.:kdviewre:.</td>
            <td>
                <select class="b1" name="kdviewre">
                    <option value="1" {kdviewre1}>.:contact:.
                    <option value="2" {kdviewre2}>.:quotations:.
                    <option value="3" {kdviewre3}>.:orders:.
                    <option value="4" {kdviewre4}>.:invoices:.
                </select></td>
            <td class="norm">.:substitute:.</td>
            <td class="norm">
                <select class="b1" name="vertreter">
                    <option value=""></option>
<!-- BEGIN Selectbox -->
                    <option value="{vertreter}" {Sel}>{vname}</option>
<!-- END Selectbox -->
                </select>
            </td></tr>
        <tr><td class="norm">.:name:.</td>
            <td><input class="b1 ui-widget-content ui-corner-all" type="text" name="name" value="{name}" maxlength="75"></td>
            <td class="norm">.:department:.</td>    
            <td ><input class="b1 ui-widget-content ui-corner-all" type="text" name="abteilung" value="{abteilung}" maxlength="75"></td></tr>
        <tr><td class="norm">.:street:.</td>
            <td><input class="b1 ui-widget-content ui-corner-all" type="text" name="addr1" value="{addr1}" maxlength="75"></td>
            <td class="norm">.:position:.</td>
            <td><input class="b1 ui-widget-content ui-corner-all" type="text" name="position" value="{position}" maxlength="75"></td></tr>
        <tr><td class="norm">.:zipcode:. .:city:.</td>
            <td><input class="b0 ui-widget-content ui-corner-all" type="text" name="addr2" value="{addr2}" size="6" maxlength="10"> 
                <input class="b0 ui-widget-content ui-corner-all" style="width:145px;" type="text" name="addr3" value="{addr3}"  maxlength="75"></td>
            <td class="norm">.:email:.</td>
            <td><input class="b1 ui-widget-content ui-corner-all" type="text" name="email" value="{email}" size="30" maxlength="125"></td></tr>
        <tr><td class="norm">.:privatephone:.</td>
            <td><input class="b1 ui-widget-content ui-corner-all" type="text" name="homephone" value="{homephone}" maxlength="30"></td>
            <td class="norm">.:officephone:.</td>
            <td><input class="b1 ui-widget-content ui-corner-all" type="text" name="workphone" value="{workphone}" maxlength="30"></td></tr>
        <tr><td class="norm">.:remark:.</td>
            <td><textarea name="notes" cols="37" rows="5" class="ui-widget-content ui-corner-all" >{notes}</textarea></td>
            <td class="norm">.:email:.<br>.:signature:.</td>
            <td><textarea name="mailsign" cols="37" rows="5" class="ui-widget-content ui-corner-all" >{mailsign}</textarea></td></tr>
        <tr><td class="norm">Mandantensignatur</td>
            <td><input type="radio" id="mandsig0" name="mandsig" value='0' {mandsig0}><label for="mandsig0">ignorieren</label>
                <input type="radio" id="mandsig1" name="mandsig" value='1' {mandsig1}><label for="mandsig1">nur diese</label>
                <input type="radio" id="mandsig2" name="mandsig" value='2' {mandsig2}><label for="mandsig2">voran stellen</label>
                <input type="radio" id="mandsig3" name="mandsig" value='3' {mandsig3}><label for="mandsig3">anhängen</label></td>
            <td></td>
            <td></td></tr>
        <tr>
            <td class="norm">.:member:.</td>
            <td><a href="user2.php" >{GRUPPE}</a></td>
            <td></td>
            <td></td></tr>
        </table>
    </span>

<!-- CRM Tab  -->
    <span id="tab2">    
        <table>
        <tr><td class="norm">.:emailserver:.</td><td><input class="b1 ui-widget-content ui-corner-all" type="text" name="msrv" value="{msrv}"  maxlength="75"></td>
            <td class="norm">.:emailuser:.</td>  <td class="norm"><input class="b1 ui-widget-content ui-corner-all" type="text" name="mailuser" value="{mailuser}" size="25" maxlength="75"></td></tr>
        <tr><td class="norm">.:emailbox:.</td>   <td class="norm"><input class="b1 ui-widget-content ui-corner-all" type="text" name="postf" value="{postf}" size="10" maxlength="75"></td>
            <td class="norm">.:password:.</td>   <td class="norm"><input class="b1 ui-widget-content ui-corner-all" type="password" name="kennw" value="{kennw}" maxlength="75"></td></tr>
        <tr><td class='norm'>.:emaildone:.</td>  <td class='norm'><input class="b1 ui-widget-content ui-corner-all" type="text" name="postf2" value="{postf2}" size="10"> </td><td></td></tr>
            
        <tr><td class="norm">.:protocol:.</td>
            <td>
                <input type="radio" name="proto" value="f" {protopop}>.:POP:. <input type="radio" name="proto" value="t" {protoimap}>.:IMAP:.
                .:port:. <input style="width:28px;" type="text" name="port" value="{port}" size="4" maxlength="6">
                <select class='b1' name="selport" id='selport' onChange="selPort();">
                    <option value=""></option>
                    <option data-selectedtext=" " value="110">110</option>
                    <option data-selectedtext=" " value="143">143</option>
                    <option data-selectedtext=" " value="993">993</option>
                    <option data-selectedtext=" " value="995">995</option>
                </select></td>
            <td class="norm">SSL</td>
            <td id="ssl">
                <input type="radio" id="ssln" name="ssl" value="n" {ssln}><label for="ssln">.:notls:.</label> 
                <input type="radio" id="sslt" name="ssl" value="t" {sslt}><label for="sslt">ssl</label> 
                <input type="radio" id="sslf" name="ssl" value="f" {sslf}><label for="sslf">tls</label>        
            </td></tr>
        <tr><td class="norm">.:theme:.</td>
            <td>
                <select class="b1 ui-widget-content ui-corner-all" style="width:115px;" name="theme" id="theme">
                <!-- BEGIN Theme -->
                    <option value="{themefile}" {TSel}>{themename}
                <!-- END Theme -->
               </select> <button id="edit_theme">.:edit:.</button></td>
            <td class="norm">.:tinymce:.</td>
            <td><input class="inp-checkbox" type='checkbox' name='tinymce' id='tinymce'  value='t'><label for="tinymce"></label></td></tr>
        <tr><td class="norm">.:deadlines:.</td>
            <td>
                .:from_t:. <select class='b1' id="termbegin" name="termbegin">{termbegin}</select> 
                .:to_t:. <select class='b1' id="termend" name="termend">{termend}</select> .:uhr:.</td>
            <td class="norm">.:deadlinespacing:.</td><td><input style="width:30px;" class="b1 ui-widget-content ui-corner-all" type="text" name="termseq" value="{termseq}" size="3" maxlength="2"> .:minutes:.</td></tr>

        <tr><td class="norm">.:calendartyp:.</td>
             <td id='caltype'>
                <input type="radio" id="caltypemonth"       name="caltype" value="month"       {caltypemonth}>      <label for="caltypemonth">.:Month:.</label> 
                <input type="radio" id="caltypeagendaWeek"  name="caltype" value="agendaWeek"  {caltypeagendaWeek}> <label for="caltypeagendaWeek">.:week:.</label> 
                <input type="radio" id="caltypebasic2Weeks" name="caltype" value="basic2Weeks" {caltypebasic2Weeks}><label for="caltypebasic2Weeks">.:2week:.</label> 
                <input type="radio" id="caltypeagendaDay"   name="caltype" value="agendaDay"   {caltypeagendaDay}>  <label for="caltypeagendaDay">.:day:.</label> 
             </td>
            <td class="norm">.:interval:.</td>
            <td><input style="width:30px;" class="b1 ui-widget-content ui-corner-all" type="text" name="interv" value="{interv}" size="4" maxlength="5">.:sec.:. &nbsp;&nbsp; </td></tr>
        <tr><td class="normal">.:presearch:. </td><td><input style="width:30px;" class="b1 ui-widget-content ui-corner-all" type="text" name="pre" value="{pre}" size="10"></td>
            <td class="norm">.:awpre:.</td><td><input class="inp-checkbox" type="checkbox" value='t' name="preon" id="preon"><label for="preon"></label>.:yes:.</td></tr>
        <tr><td class="normal">.:asterisksrv:. </td>  <td><input class="inp-checkbox" type="checkbox" name="asterisk"  id="asterisk"  value='t'><label for="asterisk"></label>.:yes:.</td>
            <td class="normal">.:mobiletellink:. </td><td><input class="inp-checkbox" type="checkbox" name="mobiletel" id="mobiletel" value='t'><label for="mobiletel"></label>.:yes:.</td>
        </tr>
        <tr><td class="normal">.:imagesize:. </td><td colspan='2'>
            .:width:. :<input style="width:80px;" class="b1 ui-widget-content ui-corner-all" type="text" name="iwidth" value="{iwidth}" size="10"> &nbsp;
            .:height:. :<input style="width:80px;" class="b1 ui-widget-content ui-corner-all" type="text" name="iheight" value="{iheight}" size="10"></td></tr>
        <tr><td class="norm">.:mapservice:.</td>
            <td colspan="4">
                 <input style="width:750px;" class="b1 ui-widget-content ui-corner-all" type="text" name="streetview" id="streetview" size="80" value='{streetview}'>
                 <input class="inp-checkbox" type="checkbox" name="streetview_default" id="streetview_default"  value='t'><label for="streetview_default"></label>.:mandant:.    
            </td></tr>
        <tr><td class="norm">.:spacecharsubst:.</td>
            <td colspan="4">
                 <input style="width:30px;" class="b1 ui-widget-content ui-corner-all" type="text" name="planspace" id="planspace"size="3" value='{planspace}'></td></tr>
        <tr><td class="norm">.:autocompletion:.</td>
            <td colspan="4">
                 .:minentry:.: 
                 <input style="width:20px;" class="b1 ui-widget-content ui-corner-all" type="text" name="feature_ac_minlength"  value='{feature_ac_minlength}'>&nbsp;&nbsp; .:delay:.: 
                 <input style="width:40px;" class="b1 ui-widget-content ui-corner-all" type="text" name="feature_ac_delay" size="3" value='{feature_ac_delay}'>.:ms:.</td></tr>
        <tr><td class="norm">.:firmabuttons:.</td>
            <td colspan="4">
                <input class="inp-checkbox" type="checkbox" name="angebot_button" id="angebot_button" value='t'><label for="angebot_button"></label>.:quotation:.&nbsp;&nbsp; 
                <input class="inp-checkbox" type="checkbox" name="auftrag_button" id="auftrag_button" value='t'><label for="auftrag_button"></label>.:order:.&nbsp;&nbsp;  
                <input class="inp-checkbox" type="checkbox" name="rechnung_button"id="rechnung_button"value='t'><label for="rechnung_button"></label>.:invoice:.&nbsp;&nbsp; 
                <input class="inp-checkbox" type="checkbox" name="liefer_button"  id="liefer_button"  value='t'><label for="liefer_button"></label>.:delivery order:.&nbsp;&nbsp;
                <input class="inp-checkbox" type="checkbox" name="zeige_extra"    id="zeige_extra"    value='t'><label for="zeige_extra"></label>.:extra:.&nbsp;&nbsp;
                <input class="inp-checkbox" type="checkbox" name="zeige_karte"    id="zeige_karte"    value='t'><label for="zeige_karte"></label>.:map:.&nbsp;&nbsp;
                <input class="inp-checkbox" type="checkbox" name="zeige_bearbeiter" id="zeige_bearbeiter" value='t'><label for="zeige_bearbeiter"></label>.:employee:.&nbsp;&nbsp;
                <input class="inp-checkbox" type="checkbox" name="zeige_etikett"  id="zeige_etikett"  value='t'><label for="zeige_etikett"></label>.:label:.&nbsp;&nbsp;
                <input class="inp-checkbox" type="checkbox" name="zeige_tools"    id="zeige_tools"    value='t'><label for="zeige_tools"></label>.:tools:.&nbsp;&nbsp;
                <input class="inp-checkbox" type="checkbox" name="zeige_lxcars"   id="zeige_lxcars"   value='t'><label for="zeige_lxcars"></label>LxCars&nbsp;&nbsp;
                <div id="p1"></div></td></tr>
        <tr><td class="norm">.:createmultiuser:.</td>
            <td >
                 <input class="inp-checkbox" type="checkbox" name="feature_unique_name_plz" id="feature_unique_name_plz" value='t'><label for="feature_unique_name_plz"></label>.:disallow:.</td>    
            <td class="norm">.:external_mail:.</td>
            <td colspan="4">
                 <input class="inp-checkbox"type="checkbox" id="external_mail" name="external_mail" value='t'><label for="external_mail"></label>.:use:.</td></tr>
        <tr><td class="norm">.:show errors:.</td>
            <td colspan="4">
                <input class="inp-checkbox" type="checkbox" name="sql_error" id="sql_error" value='t'><label for="sql_error"></label>.:sqlerror:.&nbsp;&nbsp; 
                <input class="inp-checkbox" type="checkbox" name="php_error" id="php_error" value='t'><label for="php_error"></label>.:phperror:.&nbsp;&nbsp;</td></tr>        
        </table>
    </span>

<!-- Sync TAB  -->
    <span id="tab3">    
        <table>
            <tr><td class="norm">.:syncservice:.</td><td colspan="5">.:protocol:. / .:adress:.</td></tr>
            <tr><td class="norm">.:adress:.</td><td colspan="5">- - - - - - - - - - - - - - -</td></tr>
            <tr><td class="norm">.:davsrv:.</td>    
                <td colspan="5">
                     <input style="width:550px;" class="b1 ui-widget-content ui-corner-all" type="text" name="cardsrv" id="cardsrv" size="80" value='{cardsrv}'></td></tr>
            <tr><td class="norm">.:name:.</td><td><input class="b1 ui-widget-content ui-corner-all" type="text" name="cardname" value="{cardname}" maxlength="75"></td>
                <td class="norm">.:password:.</td><td ><input class="b1 ui-widget-content ui-corner-all" type="password" name="cardpwd" value="{cardpwd}" maxlength="75"></td></tr>
            <tr><td class="norm">.:okerror:.</td><td>
                     <input style="width:100px;" class="b1 ui-widget-content ui-corner-all" type="text" name="cardsrverror" id="cardsrverror" size="5" value='{cardsrverror}'></td>
                <td class='norm'>Status nach import</td>
                <td colspan="3"><input type='radio' name='syncstat' value='0' {syncstat0}>Nein
                                <input type='radio' name='syncstat' value='1' {syncstat1}>nur senden
                                <input type='radio' name='syncstat' value='2' {syncstat2}>beide Richtungen
                </td></tr>
            <tr><td class="norm">.:calender:.</td><td colspan="5">- - - - - - - - - - - - - - -</td></tr>
            <tr><td class="norm">.:davsrv:.</td>    
                <td colspan="5">
                     <input style="width:550px;" class="b1 ui-widget-content ui-corner-all" type="text" name="calsrv" id="calsrv" size="80" value='{calsrv}'></td></tr>
            <tr><td class="norm">.:name:.</td><td><input class="b1 ui-widget-content ui-corner-all" type="text" name="calname" value="{calname}" maxlength="75"></td>
                <td class="norm">.:password:.</td><td ><input class="b1 ui-widget-content ui-corner-all" type="password" name="calpwd" value="{calpwd}" maxlength="75"></td></tr>
            <tr><td class="norm">.:okerror:.</td>    
                <td colspan="5">
                     <input style="width:100px;" class="b1 ui-widget-content ui-corner-all" type="text" name="calsrverror" id="calsrverror" size="5" value='{calsrverror}'></td></tr>
            <tr><td class="norm">.:sendcard:.</td>
                <td colspan="5">
                     <input type="radio" name="sendadrcard" value="L" {sendadrcardL}>.:local:. 
                     <input type="radio" name="sendadrcard" value="R" {sendadrcardR}>.:remote:. </td></tr>
            <tr><td colspan="6">&nbsp;</td></tr>
            <tr><td>.:exportcal:.:</td>
                <td><input type="text" class="b1 ui-widget-content ui-corner-all" style="width:80px;" size="10" id="start" name="start">
                        <img src='image/date.png' border='0' align='middle' onClick="kal('start')";></td>
                <td><input type="text" class="b1 ui-widget-content ui-corner-all" style="width:80px;" size="10" id="stop" name="stop">
                        <img src='image/date.png' border='0' align='middle' id='triggerStop' onClick="kal('stop')";></td>
                <td><select class='b1' id='icalart' name="icalart" onChange='destchg();'>
                        <option value="server" {icalartserver}>ok-Server
                        <option value="webdav" {icalartwebdav}>.:putcalsrv:.
                        <option value="mail"   {icalartmail}>.:email:.
                        <option value="client" {icalartclient}>.:browser:.
                    </select>
                </td>
                <td><input type="text" class="b1 ui-widget-content ui-corner-all" size="4" style="width:40px;" id="icalext"  name="icalext" value="{icalext}"></td>
                <td><input type="text" class="b1 ui-widget-content ui-corner-all" size="30"  id="icaldest"  name="icaldest" value="{icaldest}"> <a href="#" onClick="expkal()">.:go:.</a></td>
            </tr>
            <tr>
                <td></td>
                <td class="klein">.:from_t:.</td>
                <td class="klein">.:to_t:.</td>
                <td class="klein">.:type:.</td>
                <td class="klein">.:fileextention:.</td>
                <td class="klein">.:destination:. .:email:.</td>
            </tr>
            <tr><td colspan='6'><span id='msgkal'></span></td></tr>
            <tr><td colspan='6'><span id='download'><a id='downloadfile' href='' target='_blank'></a></span></td></tr>
            <tr><td>.:getcard:.</td><td colspan='5'><button type='button' id="GETCARD" class="lang buttons" data-lang="GET" ></button> <button type='button' id="PUTCARD" class="lang buttons" data-lang="PUT" ></td></tr>
        </table>
    </span>

<!-- Umsatz Tab -->
    <span id="tab4">    
        <img src="{IMG}" width="{iwidth}" height="{iheight}" title="Netto sales over 12 Month">
    </span>

<!-- Asterisk -->
    <span id="tab6">
        <table>
        <!--tr><td><span data-lang='TELCOSERVER' class='lang'></span></td>   <td><input type='text' size='50' id='TelcoServer' value=''></td></tr>
        <tr><td><span data-lang='TELCOMMAND' class='lang'></span></td>    <td><input type='text' size='50' id='TelCommand' value=''></td></tr>
        <tr><td><span data-lang='AUTHKEY' class='lang'></span></td>       <td><input type='text' size='30' id='AuthKey' value=''></td></tr>
        <tr><td><span data-lang='LOCALCONTEXT' class='lang'></span></td>  <td><input type='text' size='30' id='LocalContext' value=''></td></tr>
        <tr><td><span data-lang='EXTERNCONTEXT' class='lang'></span></td> <td><input type='text' size='30' id='ExternContext' value=''></td></tr>
        <tr><td><span data-lang='VORZEICHEN' class='lang'></span></td>    <td><input type='text' size='3' id='Vorzeichen' value=''></td></tr-->
        <tr><td><span data-lang='FROM' class='lang'></span></td>          <td><input type='text' size='20' id='FromNr' value='{workphone}'></td></tr>
        <tr><td><span data-lang='TO' class='lang'></span></td>            <td><input type='text' size='20' id='ToNr' value=''></td></tr>
        <tr><td><!--button id="TEST"  class="lang buttons" data-lang="TEST"></button--></td>            
            <td><button id="CALL"  class="lang buttons" data-lang="CALL"></button></td></tr>
        </table>
        <div id="MSG"></div>
    </span>

</div> <!-- maintab -->
    <button id="SAVEUSR" name='SAVEUSER' type='button' class="lang buttons" data-lang="SAVE" ></button>
    </form>
<div id="syncput">
    <h4>Daten zum Syncserver senden</h4>
    <span id='syncimg'></span>
    <span id='syncmsg'></span>
</div>    
<div id="syncget">
<span id='output'></span>
</div>    
</div> <!-- ui-widget-content -->
{END_CONTENT}
</body>
</html>

