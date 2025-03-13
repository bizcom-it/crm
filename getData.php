<?php
    ob_start(); 
    require_once('inc/stdLib.php');
    include('crmLib.php');
    $menu = $_SESSION['menu'];
    $head = mkHeader();
    if ( !isset($_SESSION['searchtab']) ) {
          $tmpdata = getUserEmployee(array('feature_ac_minlength','feature_ac_delay','searchtab','searchtable','listLimit'));
          $tmpdata['listLimit'] = ($tmpdata['listLimit']>0)?$tmpdata['listLimit']:200;
          $_SESSION = array_merge($_SESSION,$tmpdata);
    };
?>
<html>
<head><title></title>
<?php 
echo $menu['stylesheets'];
echo $menu['javascripts'];
echo $head['CRMCSS']; 
echo $head['JQTABLE'];
echo $head['JQFILEUP'];
echo $head['THEME']; 
echo $head['JUI-DROPDOWN']; 
echo $head['AUTOCOMPLETE']; 
?>
    <style>
        #jui_dropdown {
            height: 400px;
        }
        #jui_dropdown button {
            padding: 3px !important;
        }
        #jui_dropdown ul li {
            background: none;
            display: inline-block;
            list-style: none;
        }
        .drop_container {
            margin: 10px 10px 10px 10px ;
            display: inline-block;
        }
        .menu {
            position: absolute;
            width: 240px !important;
            margin-top: 3px !important;
        }
    </style>
    <script language="JavaScript">
        function waiton() {
            $( '#wait' ).dialog('open');
        }
        function waitoff() {
            $( '#wait' ).dialog('close');
        }    
        $(document).ready(function() {
            console.log('Ready');
            $("#wait" ).dialog({
                autoOpen: false,
                modal: true,
                width: 310,
                position: { my: "center", at: "center", of: window }
            });             
            $( "#tabs" ).tabs({
                active: <?php echo $_SESSION["searchtab"] - 1;?>,
                beforeLoad: function( event, ui ) {
                    ui.jqXHR.error(function() {
                        ui.panel.html(".:Couldn't load this tab.:." );
                    });
                }       
            });
            $.widget("custom.catcomplete", $.ui.autocomplete, {
                _renderMenu: function(ul,items) {
                    var that = this,
                    currentCategory = "";
                    $.each( items, function( index, item ) {
                        if ( item.category != currentCategory ) {
                            ul.append( "<li class=\'ui-autocomplete-category\'>" + item.category + "</li>" );
                            currentCategory = item.category;
                        }
                        that._renderItemData(ul,item);
                    });
                }
            });      
            $("#ac0").catcomplete({                          
                source: "jqhelp/autocompletion.php?case=name",                            
                minLength: <?php echo $_SESSION['feature_ac_minlength']; ?>,
                delay: <?PHP echo $_SESSION['feature_ac_delay']; ?>,
                disabled: false, 
                select: function(e,ui) {                    
                    showD(ui.item.src,ui.item.id);
                }
            });
            $("#dialog_no_sw,#dialog_viele,#dialog_keine").dialog({ autoOpen: false });  
         
            
            $("#results").css('height',300);
            
            $.ajax({
                url: "jqhelp/getHistory.php",
                context: $('#menu'),
                success: function(data) {
                    $(this).html(data);
                    $("#drop").jui_dropdown({
                        launcher_id: 'launcher',
                        launcher_container_id: 'launcher_container',
                        menu_id: 'menu',
                        containerClass: 'drop_container',
                        menuClass: 'menu',
                        launchOnMouseEnter:true,
                        onSelect: function(event, data) {
                            showD(data.id.substring(0,1), data.id.substring(1));
                        }
                    });
                }
            });
 
            $("#adress").button().click(function() {
                waiton();
                $.ajax({
                    type: "POST",
                    url: "jqhelp/getDataResult.php",
                    data: "swort=" + $("#ac0").val() + "&submit=adress",
                    success: function(res) {
                        $('#ac0').catcomplete('close');                    
                        $("#results").html(res).focus();
                    }
                }).done(function() { waitoff(); });
                return false;
            });
            $("#kontakt").button().click(function() {
                $.ajax({
                    type: "POST",
                    url: "jqhelp/getDataResult.php",
                    data: "swort=" + $("#ac0").val() + "&submit=kontakt",
                    success: function(res) {
                        $('#ac0').catcomplete('close');
                        $("#results").html(res);
                    }
                });
                return false;   
            });
            $('#ac0').focus().val('<?php echo (isset($_SESSION['swort'])?preg_replace("#[ ].*#",'',$_SESSION['swort']):""); ?>').select();
            $("#suchfelder_C").load('jqhelp/getCompanies1.php');    
            $("#suchfelder_P").load('jqhelp/getPersons1.php'); 
            console.log('Ready END');
        });
    
</script>
    <script language="JavaScript">
        function showD (src,id) {
           if      (src=="C") { uri="firma1.php?Q=C&id=" + id }
           else if (src=="V") { uri="firma1.php?Q=V&id=" + id; }
           else if (src=="E") { uri="user1.php?id=" + id; }
           else if (src=="K") { uri="kontakt.php?id=" + id; }
           window.location.href=uri;
        }
        function showItem(id,Q,FID) {
            F1=open("<?php echo $_SESSION['baseurl']; ?>crm/getCall.php?Q="+Q+"&fid="+FID+"&hole="+id,"Caller",
                                                            "width=670, height=600, left=100, top=50, scrollbars=yes");
        }        
    </script>     
</head>
<body>
    <div id="wait" title="Suche" style='background-color:white;'><center>Bitte warten.<br><img src='image/waitingwheel.gif'></center></div>
    <div id="dialog_no_sw" title="Kein Suchbegriff eingegeben" style='display:none'>
        <p>Bitte geben Sie mindestens ein Zeichen ein.</p>
    </div>
    <div id="dialog_viele" title="Zu viele Suchergebnisse" style='display:none'>
        <p>Die Anzahl der Suchergebnisse überschreitet das Listenlimit.<br>Bitte verändern Sie das Suchkriterium.</p>
    </div>
    <div id="dialog_keine" title="Nichts gefunden" style='display:none'>
        <p>Dieser Suchbegriff ergibt kein Resultat.<br>Bitte verändern Sie das Suchkriterium.</p>
    </div>
<?php 
echo '<body onload="$(\'#ac0\').focus().val(\''.(isset($_SESSION['swort'])?preg_replace("#[ ].*#",'',$_SESSION['swort']):"").'\').select();">';
echo $menu['pre_content'];
echo $menu['start_content']; ?>
<div class="ui-widget-content" style="height:722px; border:0px;">
    <h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Suche');"><?php  echo translate('.:address search:.','firma'); ?></h1><br>
    <div id="tabs" style='border:0px;'>
        <ul>
            <li><a href="#tab-1">Schnellsuche</a></li>
            <li><a href="#tab-2">Firmensuche</a></li>
            <li><a href="#tab-4">Personensuche</a></li>
        </ul>
        <div id="tab-1">
            <p class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0.6em;">
                                             <?php echo translate('.:fast search customer/vendor/contacts and contact history:.','firma'); ?> </p>
                <form name="suche" id="suche" action="" method="get">
                <input type="text" class="ui-widget-content ui-corner-all title" size="25" id="ac0" autocomplete="on">  
                <button id="adress"> <?php  echo translate('.:adress:.','firma'); ?> </button>
                <button id="kontakt"><?php  echo translate('.:contact history:.','firma'); ?></button> <br>
                <span class="liste"><?php   echo translate('.:search keyword:.','firma'); ?></span>
                </form>
            <div id="drop">
                <div id="launcher_container">
                    <button id="launcher"><?php echo translate('.:history tracking:.','firma'); ?></button>
                </div>
                <ul id="menu"> </ul>
            </div>
            <div id="results"></div>
        </div>
        
        <div id="tab-2">
            <div id="suchfelder_C"></div>
            <div id="companyResults_C"></div>
        </div>
        <div id="tab-4">
            <div id="suchfelder_P"></div>
            <div id="results_pers"></div>
        </div>
    </div>
</div>    
<?php
ob_end_flush(); 
echo $menu['end_content'];
?>
</body>
</html>
