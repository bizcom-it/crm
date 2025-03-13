<?php
	require_once("inc/stdLib.php");
    $menu =  $_SESSION['menu'];
	if ($_SESSION["loginCRM"])  {
		$v=($_SESSION["dbname"])?getVersiondb():"";
	}
	
?>
<html>
	<head><title></title>
        <link type="text/css" REL="stylesheet" HREF="<?php echo $_SESSION['baseurl'].'css/'.$_SESSION["stylesheet"]; ?>/main.css"></link>
        <?php 
            echo $menu['stylesheets'];
            echo $menu['javascripts'];
            echo $head['CRMCSS'];
            echo $head['THEME'];
        ?>

<body>
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist ui-widget ui-corner-all tools content1" >Hilfe/Dokumentation</h1
<center><br>
<img src="image/lx-office-crm.png"><br>
<a href="http://lx-office.org" target="_top">http://lx-office.org</a> - <a href="mailto:info@lx-office.org" target="_top">info@lx-office.org</a><br>
&quot;Lx-Office CRM&quot; ist ein Teilprodukt aus dem Lx-Office Paket.<br>

die Software unterliegt der <a href="hilfe/artistic.html" target="_blank">Artistic License</a><br><br>
Verwendete Datenbank: [<?php echo  $_SESSION["dbname"] ?>] Version [<?php echo  $v ?>]  auf Server [<?php echo  $_SESSION["dbhost"] ?>]<br>
Benutzer [<?php echo  $_SESSION["login"] ?>:<?php echo  $_SESSION["loginCRM"] ?>] [<?php echo  session_id() ?>]
<br><br><br>
&gt;<a href="hilfe/index.html">Online-Hilfe</a>&lt;<br>
</center>
</div>
</body>
</html>
