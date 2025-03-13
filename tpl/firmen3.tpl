<html>
    <head><title></title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{THEME}
    <script language="JavaScript">
    <!--
    var cok = false;
    function chkcookie() {
        var allcookies = document.cookie;
        var cookiearray  = allcookies.split(';');
        for ( var i=0; i<cookiearray.length; i++ ){
            name = cookiearray[i].split('=')[0];
            value = cookiearray[i].split('=')[1];
            if ( name == 'text' ) {
                cok = true;
                break;
            }
        }
    } 
    function c2m() {
        if ( cok ) {  // passendes Cookie gefunden
            var felder = value.split('|');
            var vname  = '';
            for ( var i=0; i<felder.length; i++ ) {
                tmp = felder[i].split(':');
                if ( tmp[0] == 'stichwort' ) tmp[0] = 'sw';
                if ( tmp[0] == 'givenname' ) { vname = tmp[1] ; continue; };
                if ( tmp[0] == 'greeting' ) tmp[0] += '_';
                if ( tmp[0] == 'title' ) {
                   continue; // 
                } else {
                    $("input[name='"+tmp[0]+"']").val(tmp[1]);
                }
            }
            if ( vname != '' ) {
                var nname = $("input[name='name']").val();
                if ( nname != '' ) $("input[name='name']").val(nname+', '+vname);
                if ( $("input[name='contact']").val() == '' ) $("input[name='contact']").val(vname+' '+nname)
            }
        }
        return false;
    }    
    function vcard() {
            f1=open("vcard.php?src=F","vcard","width=350,height=200,left=100,top=100");
        }
    function suchFa() {
            val=document.neueintrag.konzernname.value;
            if (val=="") val="%";
            f1=open("suchFa.php?tab={Q}&konzernname="+val,"suche","width=350,height=200,left=100,top=100");
        }
    function chkPflicht() {
        if ( $( '#name' ).val() != '' ) 
            if ( $( '#steet' ).val() != '' ) 
               if ( $( '#zipcode' ).val() != '' ) 
                   if ( $( '#city' ).val() != '' ) 
                       return true;
        alert('Bitte alle Pflichtfelder ausfüllen');
        return false;
    }
    //-->
    </script>
    <script>
    $(document).ready(
        function(){
            $("#country").blur(function(){
               var country = $("#country").val();
               $.ajax({
                   url: "jqhelp/firmaserver.php?task=bland&land="+country,
                   dataType: 'json',
                   success: function(items){
                       $("#bland").empty();
                       $.each(items, function( index, item ) {
                           $("<option/>").val(item.id).text(item.val).appendTo("#bland");
                       })
                   }
               })
            })
        });
    $(document).ready(
        function(){
            $("#shiptocountry").blur(function(){
               var country = $("#shiptocountry").val();
               $.ajax({
                   url: "jqhelp/firmaserver.php?task=bland&land="+country,
                   dataType: 'json',
                   success: function(items){
                       $("#shiptobland").empty();
                       $.each(items, function( index, item ) {
                           $("<option/>").val(item.id).text(item.val).appendTo("#shiptobland");
                       })
                   }
               })
            })
        });
    $(document).ready(
        function(){
            $("#shiptoadress").change(function(){
                id = $("#shiptoadress option:selected").val();
                Q = $("#Q").val();
                $.ajax({
                    url: "jqhelp/firmaserver.php?task=shipto&id="+id+"&Q="+Q,
                    dataType: 'json',
                    success: function(data){
                        $('#shipto_id').val(data.trans_id);
                        $('#shiptoname').val(data.shiptoname);
                        $('#shiptodepartment_1').val(data.shiptodepartment_1);
                        $('#shiptodepartment_2').val(data.shiptodepartment_2);
                        $('#shiptostreet').val(data.shiptostreet);
                        $('#shiptocountry').val(data.shiptocountry);
                        $('#shiptozipcode').val(data.shiptozipcode);
                        $('#shiptocity').val(data.shiptocity);
                        $('#shiptophone').val(data.shiptophone);
                        $('#shiptofax').val(data.shiptofax);
                        $('#shiptoemail').val(data.shiptoemail);
                        $('#shiptocontact').val(data.shiptocontact);
                        $('#shiptobland').val(data.shiptobland);
                    }
                })
            })
            $( "#maintab" ).tabs({ heightStyle: "auto" });
            chkcookie();
            if ( !cok ) $('#Copy').hide();
        });
    </script>
<body>
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('FirmenEingebenEditieren');"> {FAART} .:keyin:./.:edit:.</h1><br>

<div id="maintab">
    <ul>
    <li><a href="#tab1">.:address:.</a></li>
    <li><a href="#tab2">.:shipto:.</a></li>
    <li><a href="#tab3">.:bank/tax:.</a></li>
    <li><a href="#tab4">.:misc:.</a></li>
    <li><a href="#tab5">.:variablen:.</a></li>
    </ul>
    <form name="neueintrag" enctype='multipart/form-data' action="{action}" method="post" onSubmit='return chkPflicht();'>
    <input type="hidden" name="id" value="{id}">
    <input type="hidden" name="Q" id="Q" value="{Q}">
    <input type="hidden" id="shipto_id" name="shipto_id" value="{shipto_id}">
    <input type="hidden" name="customernumber" value="{customernumber}">
    <input type="hidden" name="vendornumber" value="{vendornumber}">
    <input type="hidden" name="employee" value="{employee}">
    <input type="hidden" name="grafik" value="{grafik}">
    <input type="hidden" name="mtime" value="{mtime}">

    <span id='tab1'>
        <br />
        <div class="zeile2">
            <span class="label klein">.:greeting:.</span>
            <span class="feldxx"> <input type="text" name="greeting_" size="15" maxlength="75" value="{greeting_}" tabindex="1">
                    <select name="greeting" id="greeting" tabindex="2">
                        <option value="">
<!-- BEGIN anreden -->
                        <option value="{Aid}" {Asel}>{Atext}
<!-- END anreden -->
                    </select>
            </span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:name:. *</span>
            <span class="feldxx"> <input type="text" name="name" id='name' size="35" maxlength="75" value="{name}" tabindex="3"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:department:. 1</span>
            <span class="feldxx"><input type="text" name="department_1" size="35" maxlength="75" value="{department_1}" tabindex="4"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:department:. 2</span>
            <span class="feldxx"><input type="text" name="department_2" size="35" maxlength="75" value="{department_2}" tabindex="5"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:street:. *</span>
            <span class="feldxx"><input type="text" name="street" id='street' size="35" maxlength="75" value="{street}" tabindex="6"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:country:. / .:zipcode:. *</span>
            <span class="feldxx">
                <input type="text" name="country" id="country" size="2" maxlength="75" value="{country}" tabindex="7" >/
                <input type="text" id="zipcode" name="zipcode" id='zipcode' size="5" maxlength="10" value="{zipcode}" tabindex="8">
                <select name="bland" id="bland" tabindex="9" style="width:150px;">
                    <option value=""></option>
<!-- BEGIN buland -->
                    <option value="{BLid}" {BLsel}>{BLtext}</option>
<!-- END buland -->
                </select>
            </span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:city:. *</span>
            <span class="feldxx"><input type="text" id="city" name="city" size="35" maxlength="75" value="{city}" tabindex="10"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:phone:.</span>
            <span class="feldxx"><input type="text" id="phone" name="phone" size="35" maxlength="30" value="{phone}" tabindex="11"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:fax:.</span>
            <span class="feldxx"><input type="text" name="fax" size="35" maxlength="30" value="{fax}" tabindex="12"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:email:.</span>
            <span class="feldxx"><input type="text" name="email" size="35" maxlength="125" value="{email}" tabindex="13"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:Contacts:.</span>
            <span class="feldxx"><input type="text" name="contact" size="35" maxlength="125" value="{contact}" tabindex="14"></span>
        </div>
        <div class="zeile2">
            <span class="klein">.:Remarks:.</span><br>
            <textarea name="notes" cols="70" rows="3" tabindex="15">{notes}</textarea><br />
        </div>
        <span style="position:absolute; left:35em; top:5em;text-align:left;">
            <div class="zeile2">
                <span class="labelxx klein">Logo</span>
                <span class="feldxx">
                    <input type="file" name="Datei" size="20" maxlength="125" accept="Image/*" tabindex="16">
                </span>
            </div>
            <div class="zeile2" id='sync'>
                <span class="labelxx klein"></span>
                <span class="feldxx klein">
                     Adresse synchronisieren
                </span>
            </div>
            <div class="zeile2" id='sync'>
                <span class="labelxx klein"></span>
                <span class="feldxx klein">
                     <input type="radio" name="sync" value='0' {sync0} tabindex="16">nie
                     <input type="radio" name="sync" value='1' {sync1} tabindex="16">nur senden
                     <input type="radio" name="sync" value='2' {sync2} tabindex="16">beide Richtungen
                </span>
            </div>
            <div class="zeile2" id='KEY' style='visibility:hidden;'>
                <span class="labelxx klein">Key</span>
                <span class="feldxx">
                    <input type="text" name="key" size="20" maxlength="125" tabindex="16">
                </span>
            </div>
            <div class="zeile2" id='REV' style='visibility:hidden;'>
                <span class="labelxx klein">REV</span>
                <span class="feldxx">
                    <input type="text" name="revision" size="20" maxlength="125" tabindex="16">
                </span>
            </div>
            <div class="zeile2" id='UID' style='visibility:hidden;'>
                <span class="labelxx klein">UID</span>
                <span class="feldxx">
                    <input type="text" name="uid" size="20" maxlength="125" tabindex="16">
                </span>
		<br><br>
            </div>
            <div class="zeile2" id='UID'>
                <span class="feldxx">
                {IMG}
                </span>
                <span id="geosearchR" class="feldxx"></span>
            </div>
        </span>
    </span>
<!-- Ende tab1 -->
    <span id='tab2'>
        <div class="zeile2">
            <span class="label klein"></span>
            <span class="feldxx"><select name="shiptoadress" id="shiptoadress" style="width:19em;" tabindex="1">
                    <option value=""></option>
<!-- BEGIN shiptos -->
                    <option value="{STid}">{STtext}</option>
<!-- END shiptos -->
            </select></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:name:.</span>
            <span class="feldxx"><input type="text" id="shiptoname" name="shiptoname" size="35" maxlength="75" value="{shiptoname}" tabindex="2"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:department:. 1</span>
            <span class="feldxx"><input type="text" id="shiptodepartment_1" name="shiptodepartment_1" size="35" maxlength="75" value="{shiptodepartment_1}" tabindex="3"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:department:. 2</span>
            <span class="feldxx"><input type="text" id="shiptodepartment_2" name="shiptodepartment_2" size="35" maxlength="75" value="{shiptodepartment_2}" tabindex="4"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:street:.</span>
            <span class="feldxx"><input type="text" id="shiptostreet" name="shiptostreet" size="35" maxlength="75" value="{shiptostreet}" tabindex="5"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:country:. / .:zipcode:.</span>
            <span class="feldxx">
                <input type="text" id="shiptocountry" name="shiptocountry" size="2" value="{shiptocountry}" tabindex="6" >/
                <input type="text" id="shiptozipcode" name="shiptozipcode" size="5" maxlength="10" value="{shiptozipcode}" tabindex="7">
                <select id="shiptobland" name="shiptobland" id="shiptobland" tabindex="8" style="width:12em;">
                    <option value=""></option>
<!-- BEGIN buland2 -->
                    <option value="{BSid}" {BSsel}>{BStext}</option>
<!-- END buland2 -->
                </select>
            </span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:city:.</span>
            <span class="feldxx"><input type="text" id="shiptocity" name="shiptocity" size="35" maxlength="75" value="{shiptocity}" tabindex="9"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:phone:.</span>
            <span class="feldxx"><input type="text" id="shiptophone" name="shiptophone" size="35" maxlength="30" value="{shiptophone}" tabindex="10"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:fax:.</span>
            <span class="feldxx"><input type="text" id="shiptofax" name="shiptofax" size="35" maxlength="30" value="{shiptofax}" tabindex="11"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:email:.</span>
            <span class="feldxx"><input type="text" id="shiptoemail" name="shiptoemail" size="35" maxlength="125" value="{shiptoemail}" tabindex="12"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:Contacts:.</span>
            <span class="feldxx"><input type="text" id="shiptocontact" name="shiptocontact" size="35" maxlength="75" value="{shiptocontact}" tabindex="13"></span>
        </div>
        <br><br>
        <br><br>
        <span style="position:absolute; left:35em; top:3em;text-align:left;">
            <div class="zeile2">
                <span id="geosearchL" class="feldxx"></span>
            </div>
        </span>
    </span>
<!-- Ende tab2 -->
    <span id='tab3'>
        <div class="zeile2">
            <span class="label klein">UStId</span>
            <span class="feldxx"><input type="text" name="ustid" size="35" maxlength="15" value="{ustid}" tabindex="5"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:taxnumber:.</span>
            <span class="feldxx"><input type="text" name="taxnumber" size="35" maxlength="35" value="{taxnumber}" tabindex="6"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:bankname:.</span>
            <span class="feldxx"><input type="text" id="cv_bank" name="bank" size="35" maxlength="55" value="{bank}" tabindex="7"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:bankcode:.</span>
            <span class="feldxx"><input type="text" id="cv_bank_code" name="bank_code" size="35" maxlength="10" value="{bank_code}" tabindex="8">
            </span>
            <span id="blzsearch" style="text-align:left;"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:account:.</span>
            <span class="feldxx"><input type="text" name="account_number" size="35" maxlength="15" value="{account_number}" tabindex="9"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:iban:.</span>
            <span class="feldxx"><input type="text" name="iban" size="35" maxlength="25" value="{iban}" tabindex="10"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:bic:.</span>
            <span class="feldxx"><input type="text" id='cv_bic' name="bic" size="35" maxlength="15" value="{bic}" tabindex="11"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:directdebit:.</span>
            <span class="feldxx"><input type="radio" value="t" name="direct_debit" {direct_debitt} tabindex="12">.:yes:.
                    <input type="radio" value="f" name="direct_debit" {direct_debitf} {direct_debit} tabindex="13">.:no:.
            </span>
        </div>
    </span>
<!-- Ende tab3 -->
    <span id='tab4'>
        <div class="zeile2">
        <span class="label klein">.:Industry:.</span>
            <span class="feldxx"><input type="text" name="branche_" size="15" maxlength="25" value="{branche_}" tabindex="1">
                    <select name="branche" id="branche" tabindex="2" style="width:11em;">
                        <option value="">
<!-- BEGIN branchen -->
                        <option value="{BRid}" {BRsel}>{BRtext}
<!-- END branchen -->
                    </select>
            </span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:Catchword:.</span>
            <span class="feldxx"><input type="text" name="sw" size="35" value="{sw}" maxlength="50" tabindex="3"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:homepage:.</span>
            <span class="feldxx"><input type="text" name="homepage" size="35" maxlength="75" value="{homepage}" tabindex="4"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:leadsource:.</span>
            <span class="feldxx">
                <select name="lead" id"lead" tabindex="10" style="width:10em;">
<!-- BEGIN LeadListe -->
                    <option value="{LLid}" {LLsel}>{LLtext}</option>
<!-- END LeadListe -->
                </select>
                <input type="text" name="leadsrc" size="15" maxlength="15" value="{leadsrc}" tabindex="11">
            </span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:{Q}Business:.</span>
            <span class="feldxx">
                <select name="business_id" id="business_id"  tabindex="12">
<!-- BEGIN TypListe -->
                    <option value="{BTid}" {BTsel}>{BTtext}</option>
<!-- END TypListe -->
                </select>
            </span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:taxzone:. *</span>
            <span class="feldxx">
                <select name="taxzone_id" id="taxzone_id"  tabindex="13">
                    <option value="4" {txid0}>Inland
                    <option value="1" {txid1}>EU mit UStID
                    <option value="2" {txid2}>EU ohne UStID
                    <option value="3" {txid3}>Ausland
                </select> 
            </span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:currency:.</span>
            <span class="feldxx">
                <select name="currency_id" id="currency_id" tabindex="13">
<!-- BEGIN currency -->
                    <option value="{Cid}" {Csel}>{Ctext}</option>
<!-- END currency -->
                </select>
            </span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:payment_terms:.</span>
            <span class="feldxx">
                <select name="payment_id" id="payment_id" tabindex="12">
<!-- BEGIN payment -->
                    <option value="{Pid}" {Psel}>{Ptext}</option>
<!-- END payment -->
                </select>
            </span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:headcount:.</span>
            <span class="feldxx"><input type="text" name="headcount" size="5" maxlength="5" value="{headcount}" tabindex="14"></span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:Concern:.</span>
            <input type="hidden" name="konzern" value="{konzern}">
            <span class="feldxx"><input type="text" name="konzernname" size="30" value="{konzernname}" maxlength="50" tabindex="14">{konzern}<input type="button" name="suche" value="suchen" onClick="suchFa();"></span>
        </div>
 
        <div class="zeile2">
            <span class="label klein">.:salesman:.</span>
            <span class="feldxx">
                <select name="salesman_id" id="salesman_id" tabindex="15">
                    <option value=""></option>
<!-- BEGIN SalesmanListe -->
                    <option value="{SMid}" {SMsel}>{SMtext}</option>
<!-- END SalesmanListe -->
                </select>
            </span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:language:.</span>
            <span class="feldxx">
                <select name="language_id" id="language_id" tabindex="15">
                    <option value=""></option>
<!-- BEGIN LAnguage -->
                    <option value="{LAid}" {LAsel}>{LAtext}</option>
<!-- END LAnguage -->
                </select>
            </span>
        </div>
        <div class="zeile2">
            <span class="label klein">.:authority:.</span>
            <span class="feldxx">
                <select name="owener" id="owener" tabindex="16">
<!-- BEGIN OwenerListe -->
                    <option value="{OLid}" {OLsel}>{OLtext}</option>
<!-- END OwenerListe -->
                </select> &nbsp; <span class="klein">{init}</span>
            </span>
        </div>
    </span>
    <span id='tab5'>
<!-- BEGIN cvarListe -->
        <div class="zeile">
                <span class="label">{varlable1}</span>
                <span class="leftfeld">{varfld1}</span>
                <span class="label">{varlable2}</span>
                <span class="leftfeld">{varfld2}</span>
        </div>
<!-- END cvarListe -->
    </span>
</span>
<span id="buttonrow" style="position:relative; text-align:left;width:48%; left:0.8em;">
        Felder mit * sind Pflichtfelder<br>
        {Btn1} &nbsp;{Btn2} &nbsp; 
        <input type="submit" class="sichernneu" name="saveneu" value=".:save:. .:new:." tabindex="97"> &nbsp;
        <input type="submit" class="clear" name="reset" value=".:clear:." tabindex="98"> &nbsp;
        <input type="button" name="VCard" value="VCard" onClick="vcard()" tabindex="99"> &nbsp;
        <input type="button" name="Copy" id='Copy' value="insert"  onClick="c2m()"   tabindex="99">
        <span style='font-size:1.4em; font-weight:bold; color:red;'>{Msg}</span>
</span>
</form>
    <{GEO1}script type='text/javascript' src='inc/geosearchF.js'></script>
    <script type='text/javascript' src='inc/geosearch.js'></script{GEO2}>
    <{BLZ1}script type='text/javascript' src='inc/blzsearch.js'></script{BLZ2}>
</div>
{END_CONTENT}
</body>
</html>
            
