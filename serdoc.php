<?php
    require_once("inc/stdLib.php");

?>

    <style>
    .progress { position:relative; width:400px; border: 1px solid #bbb; padding: 1px; border-radius: 3px; }
    .bar { background-color: #B4F5B4; width:0%; height:20px; border-radius: 3px; }
    .percent { position:absolute; display:inline-block; top:3px; left:48%; }
    </style>

<script>
$(document).ready(
$(function () {
    'use strict';
    var url = 'crm/jqhelp/uploader.php?SER=<?php echo $Ziel; ?>',
        uploadButton = $('<button/>')
            .prop('disabled', true)
            .text('Processing...')
            .on('click', function () {
                var $this = $(this),
                    data = $this.data();
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

    $('#fileupload').fileupload({
        url: url,
        dataType: 'json',
        autoUpload: false,    
        acceptFileTypes: /\.(tex)|(rtf)|(swf)|(sxw)$/,
        add: function (e, data) {
            data.context = $('<div/>').appendTo('#files');
            $.each(data.files, function (index, file) {
                var node = $('<p/>').append($('<span/>').text(file.name+' '+file.size));
                if (!index) { node.append(uploadButton.clone(true).data(data).text('Hochladen und generieren').prop('disabled', !!data.files.error)); }
                node.appendTo(data.context);
            })
            /*$('#uplfile').empty().append(data.files[0].name+' ');
            $('#uplfile').append(data.files[0].size+' ');
            $('#progress .bar').css('width','0%');
            $('#uplfile').append($('<button/>').text('Upload')
                                   .click(function () {
                                       $('#msg').empty().append('Uploading...');
                                       data.submit();
                                   })
            );*/
        },
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                if ( file.error != undefined ) { alert(file.error); return; };
                $('#uplfile').empty().append(file.name+' done');
                $.ajax({
                    url: 'jqhelp/serien.php',
                    dataType: 'json',
                    type: 'post',
                    data : { 'datum': $('#formdate').val(), 'subject': $('#subject').val(), 
                             'body': $('#body').val(), 'src': $('#src').val(), 
                             'filename':file.name, 'task': 'brief' },
                    success: function(rc){
                            if ( !rc.rc ) {
                                alert(rc.msg);
                                return;
                            } else {
                                f1=open('mkserdocs.php?src='+$('#src').val(),'SerDoc','width=600,height=100')
                            }
                    }
                })
            });
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .bar').css(
                'width',
                progress + '%'
            );
            //$('#percent').replaceWith(progress+' %');
            
        },
    });
})
);
</script>
<script>    
        $(function() {
            $( "#formdate" ).datepicker($.datepicker.regional[ "de" ]);
        });
</script>    
<h1 class="listtop" onClick="help('SerDoc');">Seriendokument erzeugen<font color="red"></font></h1>

Daten f&uuml;r den Serienbrief:<br />
<form name="serdoc" method="post">
<input type="hidden" name="src" id="src" value='<?php echo $_GET["src"]; ?>'>
<table>
<tr><td>Datum:</td><td><input type="text" name="formdate" id="formdate" size="12" value=""></td></tr>
<tr><td>Betreff:</td><td> <input type="text" name="subject" id="subject" size="30" value=""></td></tr>
<tr><td>Zusatztext:</td><td></tr>
<tr><td colspan="2"><textarea name="body" id="body" cols="50" rows="8"></textarea></td></tr>
</table>
Datei: <input id="fileupload" type="file" name="files[]" ></td></tr>
</form>
<div id="progress" class="progress" >
    <div class="bar" id='bar' style="width: 0%;"></div>
</div>
<div id="files"><div>
<div id="msg"><div>
