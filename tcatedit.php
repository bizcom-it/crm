<?php
    require_once("inc/stdLib.php");
    require_once("crmLib.php");
    include_once("template.inc");
    if ($_POST["ok"]) {
        $rc = saveTermincat($_POST);
    }

    $tcat = getTermincat(false);
    $i=0;
    $max=0;

    $t = new Template($base);
    doHeader($t);
    $t->set_file(array("cat" => "tcat.tpl"));
    $t->set_block("cat","TKat","Block0");
    //$t->debug = true;
    if ($tcat) foreach ($tcat as $row) {
        $t->set_var(array(
            idx => $i,
            neu => 0,
            cid => $row["catid"],
            cname => $row["catname"],
            order => $row["sorder"],
            ccolor => $row["ccolor"]
        ));
        $t->parse("Block0","TKat",true);
        $i++;
        if ($row["catid"]>$max) $max=$row["catid"];
    };
    $t->set_var(array(
        ERPCSS =>  $_SESSION['baseurl'].'crm/css/'.$_SESSION["stylesheet"],
        idx => $i,
        neu => 1,
        cid => $max+1,
        cname => "",
        order => $row["sorder"]+1,
        ccolor => 'ffffff'
    ));
    $t->parse("Block0","TKat",true);
    $t->Lpparse("out",array("cat"),$_SESSION['countrycode'],"work");
?>
