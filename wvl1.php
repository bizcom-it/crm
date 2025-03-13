<?php
    require_once("inc/stdLib.php");
    include_once("template.inc");
    include_once("crmLib.php");
    include_once("UserLib.php");
    $templ="wvln.tpl";
    $t = new Template($base);
    doHeader($t);
    $t->set_file(array("wvl" => $templ));
    $tmpdata = getUserEmployee(array('interv','feature_ac_minlength','feature_ac_delay'));
    $t->set_var(array(
            'timeout'     => isset($tmpdata['interv'])?$tmpdata['interv']*1000:60000,
            ));
    $sel=$_SESSION["loginCRM"];
    $usr = getAllUser(array(0=>true,1=>"%"));
    $gruppen = getGruppen(true);
    $nouser[0] = array("login" => "-----", "id"=>0 );
    //echo "!".$sel."!<pre>";   print_r($usr); print_r($gruppen); print_r($nouser); echo "</pre>";
    $user = array_merge($nouser,$usr);
    $user = array_merge($user,$gruppen);
    $t->set_block("wvl","Selectbox","Block1");
    if ($user) foreach($user as $zeile) {
        $t->set_var(array(
            'Sel'     => ($sel==$zeile["id"])?" selected":"",
            'UID'     => $zeile["id"],
            'Login'   => ( isset($zeile['name']) and $zeile['name'] != '' )?$zeile['name']:$zeile["login"],
            'feature_ac_minlength'  => $tmpdata['feature_ac_minlength'],
            'feature_ac_delay'      => $tmpdata['feature_ac_delay'],
        ));
        $t->parse("Block1","Selectbox",true);
    }
    $t->Lpparse("out",array("wvl"),$_SESSION["countrycode"],"work");
?>
