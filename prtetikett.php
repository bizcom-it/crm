<?php
    require_once("inc/stdLib.php");
    require_once("inc/crmLib.php");
    include_once("inc/UserLib.php");
    $menu = $_SESSION['menu'];
    $head = mkHeader();
    $ALabels = getLableNames();
    $usr = getUserStamm($_SESSION["loginCRM"]);
    if ( isset($_GET['etikett']) ) { $etikett = $_GET['etikett']; }
    else if ( isset($_POST['etikett']) ) { $etikett = $_POST['etikett']; }
    else { $etikett = $usr['etikett']; };
    $label = getOneLable($etikett);
    if ( isset($_POST['action']) && $_POST['action'] == '1' ) {
        $lableformat=array("paper-size"=>$label["papersize"],'name'=>$label["name"], 'metric'=>$label["metric"],
                            'marginLeft'=>$label["marginleft"], 'marginTop'=>$label["margintop"],
                            'NX'=>$label["nx"], 'NY'=>$label["ny"], 'SpaceX'=>$label["spacex"], 'SpaceY'=>$label["spacey"],
                            'width'=>$label["width"], 'height'=>$label["height"], 'font-size'=>6);
        require_once('inc/PDF_Label.php');
        $tmp=explode(":",$_POST["xy"]);
        $SX=substr($tmp[0],1);
        $SY=substr($tmp[1],1);
        $pdf = new PDF_Label($lableformat, $label["metric"], $SX, $SY);
        $pdf->Open();
        unset($tmp);
        if ($SX<>1 or $SY<>1)    $pdf->AddPage();
        foreach ($label["Text"] as $row) {
            preg_match_all("/%([A-Z0-9_]+)%/U",$row["zeile"],$ph, PREG_PATTERN_ORDER);
            if ($ph) {
                $first=true;
                $oder=strpos($row["zeile"],"|");
                $ph=array_slice($ph,1);
                if ($ph[0]) { foreach ($ph as $x) {
                    foreach ($x as $u) {
                        $y=$_POST[$u];
                        //echo "!$y!$u!<br>";
                        if ($y <>"" and $first) {
                            $y=utf8_decode($y);
                            $row["zeile"]=str_replace("%".$u."%",$y,$row["zeile"]);
                            if ($oder>0) $first=false;
                        } else {
                            $row["zeile"]=str_replace("%".$u."%","",$row["zeile"]);
                        }
                    }
                }
            };
            if ($oder>0) $row["zeile"]=str_replace("|","",$row["zeile"]);
            if ($row["zeile"]<>"!") {
                if ($row["zeile"][0]=="!") {
                            $text=substr($row["zeile"],1);
                        } else {
                            $text=$row["zeile"];
                        }
                        $tmp[]=array("text"=>utf8_decode($text),"font"=>$row["font"]);
                    }
            }
        };
        $pdf->Add_PDF_Label2($tmp);
        $pdf->Output();
        exit;
    } // Ende $_POST['prt']
?>
<html>
<head><title></title>
<?php
    echo $menu['stylesheets'];
    echo $menu['javascripts'];
    echo $head['CRMCSS'];
    echo $head['JQTABLE'];
    echo $head['THEME'];
?>
<script>
    $(document).ready(
        function(){
            $("#etikett").selectmenu({
                change: function( event, ui ) {
                    $('#prtform').submit();
                }
            });
            $("#prt").button().click(function() {
                $('#action').val('1');
                $('#prtform').submit();
            });
    });
</script>
</head>
<body>
<?php
echo $menu['pre_content'];
echo $menu['start_content']; ?>
<div class="ui-widget-content" style='border:0px;' >
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Etikett');">Etikett</h1><br>
<form name='prtform' method='post' action='prtetikett.php' id='prtform' >
<input type='hidden' name='seletikett' value='<?php echo $etikett; ?>'>
<input type='hidden' name='action' id='action' value=''>

<table><tr><td>
    <table>
    <tr><td>Etikett:</td><td>
        <select name='etikett' id='etikett' style="margin-top:0.5em; min-width:150px;" >
<?php foreach ($ALabels as $data) { ?>
            <option value='<?php echo  $data["id"]?>'<?php echo  ($data["id"]==$etikett)?" selected":"" ?>><?php echo  $data["name"] ?>
<?php } ?>
        </select></td></tr>
<?php
$line = '<tr><td>%s</td><td><input type="text" name="%s" size="20"></td></tr>';
if ( $label ) foreach ( $label['Text'] as $row ) {
    if ( preg_match_all('/%[^%]+%/',$row['zeile'],$hits) ) {
        foreach ( $hits[0] as $fld ) echo sprintf($line,substr($fld,1,-1),substr($fld,1,-1));
    }
}
echo '  </table></td><td>';
    $sel="checked";
    for ($y=1; $y<=$label["ny"];$y++) {
        for ($x=1; $x<=$label["nx"];$x++) {
            echo "<input type='radio' name='xy' value='x$x:y$y' $sel>";
            $sel="";
        }
        echo "<br>\n";
    }
?>
<br>
<button id='prt'>drucken</button>
<!--input type='submit' name='prt' id='prt' value='drucken'-->
</td></tr></table>
</form>
</div>
<?php echo $menu['end_content']; ?>
</body>
</html>
