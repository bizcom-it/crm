<html>
<head><title></title>
<?php
require_once('../inc/stdLib.php');
require_once('../inc/version.php');
$menu = $_SESSION['menu'];
$head = mkHeader();
echo $menu['stylesheets'];
echo $head['CRMCSS']; 
echo $head['THEME'];
?>
<link rel="stylesheet" type="text/css" href="../css/main.css">
<script type="text/javascript">
    function help(wo) {
        help = open("../hilfe/"+wo+".html","hilfe","width=700,height=400,left=300,top=100,status=no,toolbar=no,menubar=no,location=no,titlebar=no,scrollbars=yes,fullscreen=no");
    }

</script>
</head>
<body>
<div class="ui-widget-content" style="border:0px;">
<h1 class="toplist ui-widget ui-corner-all tools content1" >Online-Hilfe zur CRM <?php echo $VERSION; ?></h1
<p class="ui-state-highlight ui-widget-header ui-widget ui-corner-all tools content1"> Online-Hilfe zur CRM <?php echo $VERSION; ?></p>
<center>
<p>
Die Online-Hilfe ist noch im Aufbau.<br>
Aber auf den meisten Seiten kann man durch einen Klick auf die "Headline", die erste Zeile unter dem Menü,<br>
bereits eine kontextspezifische Hilfe aufrufen.<br>
Die Hilfe wird in einem POP-Up Fenster angezeigt, damit die aktuelle Maske nicht zerstört wird.</p>
<br>
<h3>Einen Überblick über das Menü gibt es <a href='#' onClick="help('AnwenderDokumentationLx-Office');">hier</a>.</h3>
<hr>
Hinweise im Text auf einen:  <span class='reiter'>Reiter</span> <span class='knopf'>Knopf</span> <span class='link'>Link</span><br>
Navigation: [<a name='Anker' class='wikil'>Anker</a>] [<a class='wikil' href='#'>Link</a>]

<hr>
<br>
    <img src="../image/lx-office-crm.png" width="337" height="208" border="0" alt="Lx-Office.org"> <font size="7"><b>&nbsp;=&gt;</b></font> 
    <img src="../../image/openkonto.png" width="337" height="208" border="0" alt="openkonto" >
</center>
</div>
</body>
</html>
