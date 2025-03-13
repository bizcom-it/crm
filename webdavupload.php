<?php
    require_once("inc/stdLib.php");
    include("inc/crmLib.php");
    $menu = $_SESSION['menu'];
    $head = mkHeader();
    $fs = ini_get('post_max_size');
    if ( substr($fs,-1) == 'M' )      { $size = 1048576 * substr($fs,0,-1); }
    else if ( substr($fs,-1) == 'G' ) { $size = 1073741824 * substr($fs,0,-1); }
    else if ( substr($fs,-1) == 'K' ) { $size = 1024 * substr($fs,0,-1); }
    else { $size = '2000000'; };
if ( ! isset($_GET['id']) or $_GET['id'] == '' ) {
    $rs = false;
} else {
	if ( $_GET['vc'] == 'customer' ) {
	    if ( $_GET['type'] == 'invoice' ) { 
		$sql = 'SELECT invnumber as nr FROM ar WHERE id = '.$_GET['id']; 
		$tpy = 'rechnungen';
	    } else if ( $_GET['type'] == 'sales_order' ) { 
		$sql = 'SELECT ordnumber as nr FROM oe WHERE id = '.$_GET['id']; 
		$tpy = 'bestellungen';
	    } else if ( $_GET['type'] == 'sales_quotation' ) { 
		$sql = 'SELECT quonumber as nr FROM oe WHERE id = '.$_GET['id']; 
		$tpy = 'angebote';
	    } else if ( $_GET['type'] == 'credit_note' ) { 
		$sql = 'SELECT invnumber as nr FROM ar WHERE id = '.$_GET['id']; 
		$tpy = 'gutschriften';
	    }
	} else {
	    if ( $_GET['type'] == 'purchase_invoice' ) { 
		$sql = 'SELECT invnumber as nr FROM ap WHERE id = '.$_GET['id']; 
		$tpy = 'einkaufsrechnungen';
	    } else if ( $_GET['type'] == 'purchase_order' ) { 
		$sql = 'SELECT ordnumber as nr FROM oe WHERE id = '.$_GET['id']; 
		$tpy = 'lieferantenbestellungen';
	    } else if ( $_GET['type'] == 'purchase_quotation' ) { 
		$sql = 'SELECT quonumber as nr FROM oe WHERE id = '.$_GET['id']; 
		$tpy = 'anfragen';
	    }
	};
    $rs = $GLOBALS['db']->getOne($sql);
    if ( $rs ) {
        $uploaddir = $tpy.'/'.$rs['nr'].'/';
        $Ziel = $_SESSION['manid'].'/'.$uploaddir;
    }
}

?>
<html>
<head><title></title>
<?php
echo $menu['stylesheets'];
echo $head['javascripts'];
echo $head['CRMCSS'];
echo $head['JQFILEUP']; 
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
    var url = 'crm/jqhelp/uploader.php?DAV=<?php echo $Ziel; ?>',
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
        acceptFileTypes: /\.(docx?)|(txt)|(xml)|(rtf)|(swx)|(sx[xdw])|(od[tspgf])|(pdf)|(jpe?g)|(png)|(gif)$/,
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
        done: function (e,data) {
            $.each(data.files, function (index, file) {
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
})
);
</script>

</head>
<body>

<?php
    if ( $rs ) {
        echo "<h2>Upload nach: ".$uploaddir."</h2>";
?>
        <span class="fileinput-button">
        </span>
	        <input id='fileupload' type='file' name='files[]' multiple>
	    <div id="progress" class="progress" >
	        <div class="bar" id='bar' style="width: 0%;"></div>
	    </div>
	    <div id="files"></div>
	    <div id="msg"></div>
	    <br>
	    <h3>Es können Dokumente (max. 10) mit einer Größe von maximal <?php echo $size; ?> Byte zum Server übertragen werden.</h3>
	    <h3>Erlaubt sind Dateien mit der Endung: docx? txt xml rtf swx sx[xdw] od[tspgf] pdf jpe?g png gif</h3>
<?php    
	} else {
            echo "Dokument in der Datenbank nicht gefunden <br>";
            echo "oder das Dokument noch nicht gespeichert.<br>";
    };
?>
<br>
</body>
