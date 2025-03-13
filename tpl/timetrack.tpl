<!DOCTYPE HTML>
<html>
<head><title></title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{JQDATE}
{THEME}
{JQTABLE}
{AUTOCOMPLETE}
    <script language="JavaScript">
    <!--
    function doPrint() {
        var datas = new Array();
        datas['tid'] = $( '#tid' ).val();
        datas['fid'] = $( '#fid' ).val();
        datas['was'] = $( "input[name='open']:checked" ).val()
        datas['von'] = $( '#prtvon' ).val();
        datas['bis'] = $( '#prtbis' ).val();
        $.ajax({
            url: 'jqhelp/prtTimetrack.php',
            method : 'post',
            data   : 'tid='+datas['tid']+'&fid='+datas['fid']+'&was='+datas['was']+'&von='+datas['von']+'&bis='+datas['bis'],
            async  : false,
            //contentType : "application/octet-stream",
            dataType : 'json',
            //headers  : {
            //             'Content-Type':'application/pdf','X-Requested-With':'XMLHttpRequest'
            //           },
            //processData: false,
            succsess: function(pdf) {
                 console.log('Return');
                 var url = URL.createObjectURL(pdf);
                 var $a = $('<a />', {
                                'href': url,
                                'download': 'document.pdf',
                                'text': "click"
                           }).hide().appendTo("body")[0].click();
            },
            error: function(ret) {
                console.log('Fehler'+ret);
                $.each( ret, function(i,v) { 
                                console.log(i+':'+v); } 
                ); 
                if ( ret.statusText == 'OK' ) {
                    var url = URL.createObjectURL(ret.responseText);
                    var $a = $('<a />', {
                                'href': url,
                                'download': 'document.pdf',
                                'text': "click"
                           }).hide().appendTo("body")[0].click();
                }
            }
        })
    }
    function editrow(id) {
        $.ajax({
               url:  'jqhelp/firmaserver.php?task=editTevent&id='+id,
               dataType: 'json',
               success: function(data){
                  $('#startd').val(data.startd);
                  $('#startt').val(data.startt);
                  $('#stopd').val(data.stopd);
                  $('#stopt').val(data.stopt);
                  $('#cleared').val(data.t.cleared);
                  $('#eventid').val(id);
                  $('#ttevent').empty().append(data.t.ttevent);
                  $('#parts').empty();
                  $.each(data.p, function( index, part ) {
                       line = part.qty+'|'+part.parts_id+'|'+part.parts_txt;
                       $("<option/>").val(line).text( part.qty+' * '+part.parts_txt).appendTo("#parts");
                  });
                  if (data.t.cleared > 0) {
                      $('#savett').hide();
                      $('#pdel').hide();
                      $('#psearch').hide();
                  } else {
                      $('#savett').show();
                      $('#pdel').show();
                      $('#psearch').show();
                  }
              }
        });
    }
    function getEventListe() {
        id = document.formular.id.value
        fid = document.formular.fid.value
        if ( fid < 0 ) fid=0;
        console.log('getEventListe',id,fid);
        $.ajax({
               url:  'jqhelp/firmaserver.php?task=geteventlist&tab={tab}&fid='+fid+'&id='+id,
               dataType: 'json',
               success: function(data){
                  console.log(data.liste);
                  console.log(data.buttons);
                  $('#eliste tbody').empty().append(data.liste);
                  $('#eliste' ).trigger('update');
                  $('#eventbutton').empty().append(data.buttons);
                  $('#summtime').empty().append(data.use);
                  $('#summtime').append(data.rest);
                  $('#printliste').show();
              }
        });
    }
    function doit(was) {
        document.formular.action.value=was; 
        document.formular.submit();
    }
    function chktime(wo) {
        var timeval = document.getElementById(wo).value;
        if ( timeval == '' ) return;
        var ausdruck = /(\d+)[:,\.-]?(\d*)/;
        erg = ausdruck.exec(timeval)
        if ( erg == null ) {
            alert('Fehlerhafter Ausdruck ('+timeval+')');
            document.getElementById(wo).value = '';
            return;
        }
        if (erg[1]*1 < 0 || erg[1]*1 > 24) {
            alert('Fehlerhafter Ausdruck:' + erg[1]);
            document.getElementById(wo).value = '';
            return;
        }
        if ( erg[2] == '' ) erg[2] = '0';
        if (erg[2] < 0 || erg[2] > 59) {
            alert('Fehlerhafter Ausdruck:' + erg[2]);
            document.getElementById(wo).value = '';
            return;
        }
        document.getElementById(wo).value = erg[1]+':'+erg[2];
    }
    function check_right_date_format(fld) {
        var datum = fld.value;
        if ( datum == '' ) return;
        datum = datum.replace(/[-\\\/]/g,'.');
        if ( datum.match(/\d+\.\d+\.\d+/)) {
            fld.value = datum;
        } else {
            alert("Fehlerhaftes Datumsformat: " + datum);
        }
    }
    function psuche(){  
        var part = document.getElementById('partnr').value;
        f1 = open('suchPart.php?part='+part,"suche","width=650,height=250,left=100,top=100");
    }
    function pdelete() {
        nr = document.getElementById('parts').selectedIndex;
        document.getElementById('parts').options[nr] = null
    }
    function fehler(txt) {
        alert(txt);
        return false;
    }
    function saveTT() {
        var start =  $('#startchk').is(':checked');
        var stop  =  $('#stopchk').is(':checked');
        if ( $('#startd').val() == '' && !start ) return fehler('kein Startdatum'); 
        if ( $('#startt').val() == '' && !start ) return fehler('keine Startzeit'); 
        if ( $('#ttevent').val() == '' ) return fehler('kein Arbeitstext '); 
        if ( $('#stopd').val() != '' && ( $('#stopt').val() == '' && !stop) ) return fehler('keine Stopzeit'); 
        var data = new Array();
        for ( i=0; i < document.getElementById('parts').length; i++) {
            data[data.length]=document.getElementById('parts').options[i].value;
        }
        document.getElementById('parray').value = data.join('###');
        return true;
    }
    function doReset() {
        $('#savett').show();
        $('#pdel').show();
        $('#psearch').show();
        $('#parts').empty();
        //$('#printliste').hide();
    }
    //-->
    </script>
	<script>
        $(function() {
            $( "#eliste" ).tablesorter({widthFixed: true, widgets: ['zebra'], headers: {
                0: { sorter: false }, 1: { sorter: false }, 2: { sorter: false }, 3: { sorter: false }, 4: { sorter: false } } 
            });
            $( "#START" ).datepicker($.datepicker.regional[ "de" ]);
            $( "#STOP" ).datepicker($.datepicker.regional[ "de" ]);
            $( "#startd" ).datepicker($.datepicker.regional[ "de" ]);
            $( "#stopd" ).datepicker($.datepicker.regional[ "de" ]);
            $( "#prtvon" ).datepicker($.datepicker.regional[ "de" ]);
            $( "#prtbis" ).datepicker($.datepicker.regional[ "de" ]);
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
                    console.log(ui.item);
                    $( '#fid' ).val(ui.item.id);
                    $( '#tab' ).val(ui.item.src);
                    //$( '#name' ).val(ui.item.value);
                }
            });
        });
        </script>
<body {chkevent}>
{PRE_CONTENT}
{START_CONTENT}
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('TimeTrack');">.:timetracker:.</h1><br>

<span style="position:absolute; left:1em; top:2.5em; width:95%;">
<form name="formular" action="timetrack.php" method="post">
<input type="hidden" name="clear" value="{clear}">
<input type="hidden" name="id"  id='tid' value="{id}">
<input type="hidden" name="tab" id='tab' value="{tab}">
<input type="hidden" name="fid" id='fid' value="{fid}">
<input type="hidden" name="backlink" value="{backlink}">
<span style='display:{visible}'>
    <select name="tid">
<!-- BEGIN Liste -->
    <option value='{tid}'>{ttn}</option>
<!-- END Liste -->
    </select><input type="submit" name="getone" value="ok">
</span>
<font color="red"><b>{msg}</b></font>
    <div class="zeile">
        <span class="label klein">.:name:.</span>
            <input type="text" size="60" name="name" id='name' value="{name}" class="ui-widget-content ui-corner-all title"  autocomplete="on"> 
    </div>
    <div class="zeile">
        <span class="label klein">.:project:.</span>
        <input type="text" size="60" name="ttname" value="{ttname}" > 
    </div>
    <div class="zeile">
        <span class="label klein">.:description:.</span>
        <textarea cols="60" rows="5" name="ttdescription">{ttdescription}</textarea> 
    </div>
    <div class="zeile">     
        <span class="label klein"></span>
        <span class="klein">.:startdate:.
        <input type="text" size="10" name="startdate" id="START" value="{startdate}" > </span> &nbsp; &nbsp;
        <span class="klein">.:stopdate:.
        <input type="text" size="10" name="stopdate" id="STOP" value="{stopdate}" > </span>
    </div>
    <div class="zeile">
        <span class="label klein"></span>
        <span class="klein">.:aim:.</span>
        <input type="text" size="5" name="aim" value="{aim}" >.:hours:. &nbsp; &nbsp;
        <span class="klein">.:active:.</span>
        <input type="radio" value="t" name="active" {activet}>.:yes:.
        <input type="radio" value="f" name="active" {activef}>.:no:.
    </div>
    <div class="zeile">
        <span class="label klein"></span>
        <span class="klein">.:budget:.</span>
        <input type="text" size="9" name="budget" value="{budget}" >{cur} &nbsp; &nbsp;
    </div>
    <div class="zeile">
        <span class="label"></span>
        <span style="visibility:{noown}">
        <input type="hidden" name="action" value="">
        <img src="image/save_kl.png"   alt='.:save:.'   title='.:save:.'   name="save"   value=".:save:."   onclick="doit('save');"> &nbsp;
        <img src="image/cancel_kl.png" alt='.:delete:.' title='.:delete:.' name="delete" value=".:delete:." onclick="doit('delete');" style="visibility:{delete};"> &nbsp;
        </span>
        <span style="visibility:{blshow}">
        <a href='{backlink}'><img src="image/firma.png" alt='.:back:.' title='.:back:.' border="0"/></a>&nbsp;
        </span>
        <span>
        <img src="image/neu.png"    alt='.:new:.'    title='.:new:.'    name="clear"  value=".:new:."    onclick="doit('clear');"> &nbsp;
        <img src="image/suchen.png" alt='.:search:.' title='.:search:.' name="search" value=".:search:." onclick="doit('search');"> &nbsp;
        </span>
        <span id="summtime"></span>
    </div>

<!--/div-->
</form>
<br />
<div>
<form name="ttevent" method="post" action="timetrack.php" onSubmit="return saveTT();">
<input type="hidden" name="tid" value="{id}">
<input type="hidden" id="parray" name="parray" value="">
<input type="hidden" name="cleared" id='cleared' value="">
<input type="hidden" name="eventid" id="eventid" value="" >
<span id="work" style="visibility:{noevent}">
	<table>
	<tr><td>.:start work:.</td><td>.:stop work:.</td><td>.:material:. (nur mit Arbeitseintrag)</td>
	</tr>
	<tr><td><input type="text" size="8" name="startd" id="startd" onBlur="check_right_date_format(this)"> 
	    <input type="text" size="4" name="startt" id="startt" onblur="chktime('startt');"><input type="checkbox" id='startchk' name="start" value="1">.:now:.</td>
	    <td><input type="text" size="8" name="stopd"  id="stopd" onBlur="check_right_date_format(this)">  
	    <input type="text" size="4" name="stopt"  id="stopt" onblur="chktime('stopt');"> <input type="checkbox" id='stopchk' name="stop"  value="1">.:now:.</td>
	    <td><input type="text" name="partnr" id="partnr" style='width:19em;'>
	    <input type="button" name="psearch" id="psearch" value=".:psearch:." onClick="psuche();"></td>
	</tr>
	<tr><td colspan="2"><textarea cols="62" rows="5" name="ttevent" id="ttevent"></textarea></td>
	    <td><select name="parts" id="parts" size="5" style='width:19em;'></select>
	    <input type="button" id="pdel" name="pdel" value=".:del:." onClick="pdelete();"></td>
	</tr>
	<tr>
	    <td><input type="reset"  name="resett" value=".:reset:." onClick='doReset();'></td>
	    <td><input type="submit" name="savett" value=".:save:." id='savett' ><!--style='visibility:visible'--></td>
	    <td></td>
	</tr>
	</table>
</span>
</form>
</div>
<div id="eventliste">
    <form name='cleared' method='post' action='timetrack.php'>
    <input type='hidden' name='tid' id='tid' value=''>
<table id='eliste' class='tablesorter' style='margin:0px; cursor:pointer;'>
    <thead>
	        <tr><th></th><th>Start</th><th>Stop</th><th>Dauer min.</th><th>Benutzer</th><th>Subjekt</th><th>Auftrag</th></tr>
    </thead>
    <tbody>
    </tbody>
</table>
</div>
<div id="eventbutton"></div>
<div id='printliste' style="visibility:{noevent}">
	<input type='radio' name='open' value='0' checked> Alle 
	<input type='radio' name='open' value='1'> Nur offene 
	<input type='radio' name='open' value='2'> Nach Datum 
	<input type='text'  name='prtvon' id='prtvon' size='6'>
	<input type='text'  name='prtvon' id='prtbis' size='6'>
	<input type='button' name='print' id='print' value='.:print:.' onClick='doPrint();'>
</div>
</span>
{END_CONTENT}
</body>
</html>
