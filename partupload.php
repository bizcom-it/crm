<?php
    require_once("inc/stdLib.php");
    $head = mkHeader();
    $fs = ini_get('post_max_size');
    if ( substr($fs,-1) == 'M' )      { $size = 1048576 * substr($fs,0,-1); }
    else if ( substr($fs,-1) == 'G' ) { $size = 1073741824 * substr($fs,0,-1); }
    else if ( substr($fs,-1) == 'K' ) { $size = 1024 * substr($fs,0,-1); }
    else { $size = '2000000'; };
    $uploaddir = $_GET['PART'];
    if ( $uploaddir == 'image' ) {
        $filetype = '(jpe?g)|(png)|(gif)';
    } else {
        $filetype = '(jpe?g)|(png)|(gif)|(pdf)|(od[tspgf])|(docx?)';
    }
    $Ziel = 'parts/'.$uploaddir.'/'.$_SESSION['manid'].'/';
    if ( ! isset($_GET['id']) or $_GET['id'] == '' ) {
        $id = false;
    } else {
        $id = $_GET['id'];
    }


?>
<?php
//echo $head['JQFILEUP']; 
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
    var dest = '<?php echo $uploaddir; ?>';
    var mand = '<?php echo $_SESSION['manid']; ?>';
    var ziel = '<?php echo $Ziel; ?>';
    var url = 'crm/jqhelp/uploader.php?PART=<?php echo $Ziel; ?>',
        uploadButton = $('<button/>')
            .prop('disabled', true)
            .text('Processing...')
            .on('click', function () {
                var $this = $(this),
                    data = $this.data();
                    var ren = ($('#rename').is(':checked')?1:0);
                    data.formData = {rename: ren};
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
        formDate: {rename: '0'},
        autoUpload: false,
        acceptFileTypes: /\.<?php echo $filetype; ?>$/,
        maxFileSize: <?php echo $size; ?>,
        add: function (e, data) {
            data.context = $('<div/>').appendTo('#files');
            $.each(data.files, function (index, file) {
                var node = $('<p/>')
                    .append($('<span/>').text(file.name+' '+file.size));
                if (!index) { node.append(uploadButton.clone(true).data(data).text('Upload').prop('disabled', !!data.files.error)); }
                node.appendTo(data.context);
            })
        },
	error: function(err,x) {
           console.log('Error Upload ');
           console.log(err.responseText);
           //console.log(err.statusText);
           //for (var p in err) { console.log(p+':'+e.p); };
           //console.log(x);
           alert('Fehler');
        },
        done: function (e,data) {
            $.each(data.result.files, function (index, file) {
                $('#msg').empty();
                $('#files').empty().append(file.name+' done');
                $('[name="part.'+dest+'"]').val(ziel+file.name);
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
})
);
</script>

<?php
    if ( !$id ) {
            echo "Artikel noch nicht gespeichert. <br>";
    };
    echo "<h2>Upload nach: ".$uploaddir."</h2>";
?>
        Vorhandene Datei überschreiben <input type='checkbox' name='rename' id='rename'><br>
        <span class="fileinput-button">
        </span>
	        <input id='fileupload' type='file' name='files[]' multiple>
	    <div id="progress" class="progress" >
	        <div class="bar" id='bar' style="width: 0%;"></div>
	    </div>
	    <div id="files"></div>
	    <div id="msg"></div>
	    <br>
	    <h3>Es können Dateien mit einer Größe von maximal <?php echo $size; ?> Byte zum Server übertragen werden.</h3>
	    <h3>Erlaubt sind Dateien mit der Endung: <?php echo $filetype; ?></h3>
<?php    
?>
<br>
