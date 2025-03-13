	<script language="JavaScript">
	<!--
    $(document).ready(
        function(){
            var  destdir = 'tmp';
            var  url = 'jqhelp/uploader.php';
            $.widget("custom.catcomplete", $.ui.autocomplete, {
                _renderMenu: function(ul,items) {
                    var that = this;
                    $.each( items, function( index, item ) {
                        that._renderItemData(ul,item);
                    });
                }
            });     
            $("#CC").catcomplete({                          
                source: "jqhelp/autocompletion.php?case=employee",                            
                minLength: {feature_ac_minlength},
                delay: {feature_ac_delay},
                disabled: false, 
                select: function(e,ui) {
                    console.log(ui.item.id);
                }
            });

            $("#resultsCC").css('height',50);

            uploadButton = $('<button/>')
                .prop('disabled', true)
                .text('Processing...')
                .on('click', function () {
                    var $this = $(this),
                        data = $this.data();
                    data.formData = {rename: 1};
                    $this
                        .off('click')
                        .text('Abort')
                        .on('click', function () {
                            $this.remove();
                            data.abort();
                         });
                    data.submit().always(function () {
                        $this.remove();
                    });
            });
            removeButton = $('<button/>')
                .prop('disabled', true)
                .prop('id', 'REMOVE')
                .on('click', function () {
                   $('#files').empty();
                   $('#Datei').empty();
                   $('#Size').empty();
                   $('#Typ').empty();
                });
            $('#fileupload').fileupload({
                url: url,
                dataType: 'json',
                autoUpload: false,
                acceptFileTypes: /\.(jpe?g)|(png)|(gif)|(pdf)|(txt)|(od[tspgf])|(docx?)$/,
                maxFileSize:'2000000' ,
                add: function (e, data) {
                    console.log(data);
                    var file = data.files[0];
                    console.log(file);
                    $('#Datei').empty().val(file.name);
                    $('#Size').empty().val(file.size);
                    $('#Typ').empty().val(file.type);
                    $('#files').empty().text(file.name+' '+file.size).append(removeButton.clone(true).data(data).text('Entfernen').prop('disabled', !!data.files.error));
                    $("#progress .bar").css('width','0%');
                    $('#senden').one('click',function (e) {
                        e.preventDefault();
                        data.formData = {rename: 1};
                         $('#msg').empty().append('Uploading...');
                        data.submit();
                    });
                    console.log(data);
                    data.context = $('<div/>').appendTo('#files');
                },
                error: function(err,x) {
                   console.log('Error Upload ');
                   console.log(err.responseText);
                   alert('Fehler');
                },
                done: function (e,data) {
                    var file = data.files[0];
                    $.each(data.result.files, function (index, file) {
                        if ( file.error != undefined ) { alert(file.error); return; };
                        $('#msg').empty();
                        $('#files').empty().append(file.name+' done');
                    })
                },
                progressall: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress .bar').css(
                        'width',
                        progress + '%'
                    );
                },
            });
            
            $("#vorlagen").change(function(){
                var mid = $("#vorlagen option:selected").val();
                if (mid > 0) {
    	            $.ajax({
    	                url: "jqhelp/mailvorlage.php?case=get&to=&template="+mid,
    	                dataType: 'json',
    	                success: function(data){
    		            $("#Subject").val(data.subject);
    		            $("#BodyText").val(data.bodytxt);
                        $("#MID").val(mid);
                        sign();
    	                }
    	            });
                }
            })
        });
        function versenden() {
            console.log('versenden');
			subj = $("#Subject").val();
			if (subj == "") {
                $("#SENDTXT").html("Kein Betreff angegeben");
				return false;
			};
			CC    = $("#CC").val();
			body  = $("#BodyText").val();
			datei = $("#Datei").val();
			size  = $("#Size").val();
			type  = $("#Typ").val();
            console.log('CC' + CC);
            console.log('DATEI' + datei);
            $.ajax({
               url:  'jqhelp/sendsermail.php',
               dataType: 'json',
               method : 'post',
               data   : {'Subject':subj, 'CC':CC, 'BodyText':body, 'Datei':datei, 'Size':size, 'Typ':type},
               success: function(rc){
                  console.log(rc);
                  if ( !rc.rc ) {
                      $("#SENDTXT").html(rc.msg);
                      return;
                  } else {
                      $("#SENDTXT").html(rc.msg);
                      fx = open('sendsermail.php?offset=1','sendmail','width=300,height=100');
                  }
              },
              error : function(rc) {
                  console.log('ERROR',rc);
              }
            });
        }
		function suchMail() {
			var val = $('#CC').val();
			var f1  = open("suchMail.php?name="+val+"&adr=CC","suche","width=450,height=200,left=100,top=100");
		}
		function upload() {
			var f1 = open("mailupload.php","suche","width=350,height=200,left=100,top=100");
		}
		function sign(){
            var txt = $("#BodyText").val();
            var sig = "{Sign}".replace(/<br>/g,"\n");
            $("#BodyText").val(txt+"\n\n"+sig);
		}
		function setcur(textEl) {
			if (textEl.selectionStart || textEl.selectionStart == '0') {
		     		textEl.selectionStart=0;
     				textEl.selectionEnd=0;
			}
		}
	//-->
	</script>


<h1 class="toplist" onClick="help('SerMail');">Serienmail versenden <font color="red">{Msg}</font></h1>
<center>
<table style="width:680px;">
    <div id="Sdialog_no_sw" title="Kein Suchbegriff eingegeben" style='display:none'>
        <p>Bitte geben Sie mindestens ein Zeichen ein.</p>
    </div>
    <div id="Sdialog_viele" title="Zu viele Suchergebnisse" style='display:none'>
        <p>Die Anzahl der Suchergebnisse überschreitet das Listenlimit.<br>Bitte verändern Sie das Suchkriterium.</p>
    </div>
    <div id="Sdialog_keine" title="Nichts gefunden" style='display:none'>
        <p>Dieser Suchbegriff ergibt kein Resultat.<br>Bitte verändern Sie das Suchkriterium.</p>
    </div>
<form name="mailform" action="sermail.php" enctype='multipart/form-data' id='suche' method="post" onSubmit="false">
<INPUT TYPE="hidden" name="MAX_FILE_SIZE" value="2000000">
<INPUT TYPE="hidden" id='kontaktCC' name="kontaktCC" value="">
<INPUT TYPE="hidden" id='Datei' value="">
<INPUT TYPE="hidden" id='Size' value="">
<INPUT TYPE="hidden" id='Typ' value="">
<tr>
	<td class="mini re" width="60px"></td>
	<td class="mini re" width="*x"></td>
	<td class="mini re" width="*"></td>
</tr>
<tr>
	<td class="re">An:</td>
	<td class="">Serienmail<div id='SENDTXT'></div></td>
	<td rowspan="7" class="le" style="vertical-align:middle;"><input type="button" name="ok" id='senden' value="senden" onClick='versenden();'><br><br>{btn}</td>
</tr><tr>
	<td class="re">CC:</td>
	<td class=""><input type="text" class="ui-widget-content ui-corner-all title" size="25" id="CC" autocomplete="on" tabindex="2"><span id="resultsCC"></span></td>
</tr><tr>
	<td class="re">Betreff:</td>
	<td class=""><input type="text" name="Subject" id='Subject' value="{Subject}" size="67" maxlength="125" tabindex="3"></td>
</tr><tr>
 	        <td class="klein re">Vorlagen:</td>
 	        <td class=""><select name="vorlagen" id="vorlagen" tabindex="3" style="width:44em;"> 
 	        	<option value=""></option>
<!-- BEGIN Betreff -->
	            <option value="{MID}">{CAUSE}</option>
<!-- END Betreff -->
            </select></td>
        </tr><tr>
	<td class="re">Text:</td>
	<td class="">
	<textarea name="BodyText" id='BodyText' cols="91" rows="15" tabindex="4" onFocus="setcur(this);">{BodyText}</textarea>
	</td>
</tr><tr>
	<td class="re">Datei:</td>
	<td>
       <span class="fileinput-button">
       </span>
       <input type="file" name="files[]" id='fileupload' size="55" maxlength="125">
       <div id="progress" class="progress" >
             <div class="bar" id='bar' style="width: 0%;"></div>
       </div>
       <div id="files"></div>
       <div id="msg"></div>
    </td>
</tr>

</form>
</table>
</center>
