<?php
    require_once("inc/stdLib.php");
    include_once("template.inc");
    include_once("FirmenLib.php");
    include_once("UserLib.php");
    $edit    = false;
    $saveneu = false;
    $save    = false;
    $show    = false;
    if ( isset($_POST['Q']) ) {
        $Q = $_POST["Q"];
        if (isset($_POST["save"]))         { $save    = true; }
        else if (isset($_POST["saveneu"])) { $saveneu = true; }
        else if (isset($_POST["show"]))    { $show    = true; };
    } else {
        $Q = $_GET["Q"];
        $edit = (isset($_GET["edit"]))?true:false;
    };
    $t = new Template($base);
    doHeader($t);
    $t->set_file(array("fa1" => "firmen3.tpl"));
    if ( $saveneu ) {
        $_POST["customernumber"] = false;
        $_POST["vendornumber"]   = false;
        $rc = saveNeuFirmaStamm($_POST,$_FILES,$Q);
        if ( $rc[0]>0 ) { header("location:firmen3.php?Q=$Q&id=".$rc[0]."&edit=1");}
        else { $msg="Fehler beim Sichern (".($rc[1]).")"; };
        $btn1=""; $btn2=""; $_POST["id"]="";
        vartpl ($t,$_POST,$Q,$msg,$btn1,$btn2,3);
    } else if ( $save ) {
        if ( $_POST["id"] ) {
            $tabelle = ($Q=="C")?"customer":"vendor";
            if (chkTimeStamp($tabelle,$_POST["id"],$_POST["mtime"])) {
                $rc = saveFirmaStamm($_POST,$_FILES,$Q);
                if ( $rc[0]>0 ) {
                    $msg   = "Daten gesichert.";
                    $_POST = getFirmenStamm($rc[0],false,$Q);
                } else {
                    $msg   = "Fehler beim Sichern ( ".$rc[1]." )";
                };
            } else {
                $msg   = "Daten wurden inzwischen modifiert";
                $rc[0] = -1;
            }
        } else {
            $rc[0]=-1; $rc[1]="Kein Bestandskunde";
        }
        $btn1 = "<input type='submit' class='sichern' name='save' value='sichern' tabindex='90'>";
        $btn2 = "<input type='submit' class='anzeige' name='show' value='zur Anzeige' tabindex='91'>";
        vartpl ($t,$_POST,$Q,$msg,$btn1,$btn2,3);
    } else if ( $show ) {
        header("location:firma1.php?Q=$Q&id=".$_POST["id"]);
    } else if ( $edit ) {
        $daten = getFirmenStamm($_GET["id"],false,$Q);
        $msg   = "Edit: <b>".$_GET["id"]."</b>";
        $btn1  = "<input type='submit' class='sichern' name='save' value='sichern' tabindex='90'>";
        $btn2  = "<input type='submit' class='anzeige' name='show' value='zur Anzeige' tabindex='91'>";
        vartpl ($t,$daten,$Q,$msg,$btn1,$btn2,3);
    } else {
        leertpl($t,3,$Q,"Neueingabe");
    }
    $t->set_unknowns('remove');
    $t->Lpparse("out",array("fa1"),$_SESSION['countrycode'],"firma");
?>
