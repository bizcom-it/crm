<?php
    require_once('inc/stdLib.php');
    include_once('wvLib.php');
    $menu =  $_SESSION['menu'];
    $head = mkHeader();
    $msg  = '';
    $vid  = false;

    if ( isSetVar($_POST['ok']) ) {
        $vid = suchVertrag($_POST['vid']);
        if ( !$vid ) {
            $msg = 'Kein Vertrag gefunden';
        } else if ( count($vid)==1 ) {
            header('location:vertrag3.php?vid='.$vid[0]['cid']);
        }
    }    
?>
<html>
    <head><title></title>
<?php echo $menu['stylesheets']; 
      echo $menu['javascripts']; 
      echo $head['CRMCSS']; 
      echo $head['THEME']; 
      echo $head['JQTABLE']; 
?>
    <script>
    $(function() {
        $("#treffer")
            .tablesorter({widthFixed: true, widgets: ['zebra']})
    });    
    </script>

<body >
<?php echo $menu['pre_content'];
      echo $menu['start_content'];?>
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('WVEingebenEditieren');">Wartungsvertr&auml;ge suchen</h1><br>
<form name="formular" enctype='multipart/form-data' action="vertrag1.php" method="post">
<input type="text" name="vid" size="20" value="" tabindex="1"> &nbsp; 
<input type="submit" name="ok" value="suchen"><br>Vertragsnummer
</form>
<?php  echo $msg; ?><br>
<table id='treffer' class='tablesorter'>
<thead><tr ><th>Vertragsnummer</th><th>Kunde</th></tr></thead>
<tbody>
<?php
        if ( count($vid)>1 ) {
            foreach( $vid as $nr ) {
                echo "<tr><td>[<a href=vertrag3.php?vid=".$nr["cid"].">".$nr["contractnumber"]."</a>]</td><td>".$nr["name"]."</td></tr>\n";
            }
        }
?>
</tbody>
</table>
</div>
<?php echo $menu['end_content'];?>
</body>
</html>
