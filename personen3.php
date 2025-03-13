<?php
    require_once("inc/stdLib.php");
    include_once("template.inc");
    include_once("laender.php");
    include_once("crmLib.php");
    include_once("UserLib.php");
    include_once("persLib.php");
    include_once("FirmenLib.php");
    $t = new Template($base);
    $menu = $_SESSION['menu'];
    doHeader($t);
    $Q = (isset($_GET["Quelle"]))?$_GET["Quelle"]:$_POST["Quelle"];    
    
    if ( isset( $_POST["show"] ) ) {
        header("location:firma2.php?Q=$Q&id=".$_POST["PID"]);
    } else if ( $_POST["save"]||$_POST["neu"] ) {
        if ( isset( $_POST["neu"] ) ) { 
            $_POST["PID"] = 0;
            $rc = savePersonStamm($_POST,$_FILES);
        } else {
            if ( chkTimeStamp("contacts",$_POST["PID"],$_POST["mtime"]) ) {
                $rc = savePersonStamm($_POST,$_FILES);
            } else {
                $rc = -10;
            }
        }
        if ( preg_match('/^[0-9]+$/',$rc) ) {
            $msg = "Daten gesichert.";
            $daten = getKontaktStamm(($_POST["PID"])?$_POST["PID"]:$rc);
            $daten["Quelle"]=$Q;
            $btn3 = "<input type='submit' class='sichern' name='save' value='.:save:. .:update:.' tabindex='25'>";
            $btn1 = "<input type='submit' class='anzeige' name='show' value='.:view:.'>";
            $btn2 = "<input type='submit' class='sichernneu' name='neu' value='.:save:. .:new:.'>";
            vartplP ($t,$daten,$msg,$btn1,$btn2,$btn3,"cp_givenname","white",0,3);
        } else {
            if ( $_POST["PID"] ) {
                $_POST["cp_id"] = $_POST["PID"];
                $btn3 = "<input type='submit' class='sichern' name='save' value='.:save:. .:update:.' tabindex='25'>";
                $btn1 = "<input type='submit' class='anzeige' name='show' value='.:view:.'>";
            } else {
                $btn1 = "";
                $btn3 = "";
            }
            if ( $rc == -10 ) {
                $msg = "Daten wurden inzwischen modifiziert";
            } else {
                $msgtmp = explode('::',$rc);
                $rc = $msgtmp[1];
                $msg = ".:error:. .:save:. ($msgtmp[0])";
            };
            $btn2 = "<input type='submit' class='sichernneu' name='neu' value='.:save:. .:new:.'>";            
            vartplP ($t,$_POST,$msg,$btn1,$btn2,$btn3,$rc,"red",1,3);
        }
    } else if ( $_POST["edit"] > 0 || $_GET["edit"] > 0 ) {
        if ( isset($_POST['id']) && $_POST["id"] ) {
            $id = $_POST["id"];
        } else {
            $id = $_GET["id"];
        }
        if (!$id) header("location:".$_SESSION['baseurl']."crm/personen1.php?Q=$Q");
        $daten = getKontaktStamm($id);
        $daten["Quelle"] = $Q;
        $daten["tbl"] = $Q;
        $msg  = "Edit: <b>$id</b>";
        $btn3 = "<input type='submit' class='sichern' name='save' value='.:save:. .:update:.' tabindex='25'>";
        $btn1 = "<input type='submit' class='anzeige' name='show' value='.:view:.'>";
        $btn2 = "<input type='submit' class='sichernneu' name='neu' value='.:save:. .:new:.'>";
        vartplP ($t,$daten,$msg,$btn1,$btn2,$btn3,"cp_givenname","white",0,3);
    } else {
        $msg = ".:person:. .:new:.";
        leertplP($t,$_GET["fid"],$msg,3,true,$Q);
    }
    $t->Lpparse("out",array("pers1"),$_SESSION['countrycode'],"firma");
?>
