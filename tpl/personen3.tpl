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
                for ( var i=0; i<felder.length; i++ ) {
                    tmp = felder[i].split(':');
                    if ( tmp[0] == 'phone' || tmp[0] == 'mobile' ) tmp[0] += '1';
                    if ( tmp[0] == 'sw' ) tmp[0] = 'stichwort';
                    if ( tmp[0] == 'greeting' ) {
                       continue; // Muß noch wg. Übersetzung gemacht werden.
                    } else {
                        $("input[name='cp_"+tmp[0]+"']").val(tmp[1]);
                    }
                }
            }
        }
        function goFld() {
            if ({BgC}) document.formular.{Fld}.style.backgroundColor = "red";
               document.formular.{Fld}.focus();
        }
        function suchFa() {
            val=document.formular.name.value;
            f1=open("suchFa.php?nq=1&name="+val,"suche","width=350,height=200,left=100,top=100");
        }
        function vcard() {
            f1=open("vcard.php?src=P","vcard","width=350,height=200,left=100,top=100");
        }
        function chkPflicht() {
        if ( $( '#cp_givenname' ).val() != '' ) 
            if ( $( '#cp_name' ).val() != '' ) 
                       return true;
        alert('Bitte alle Pflichtfelder ausfüllen');
        return false;
    }

    $(document).ready(
        function(){
            $( "#maintab" ).tabs({ heightStyle: "auto" });
            $( "#cp_birthday" ).datepicker($.datepicker.regional[ "de" ]);
            $(function(){
                $("#company_name").autocomplete({                          
                    source: "jqhelp/autocompletion.php?case=name&src=cv",                            
                    minLength: '2',
                    select: function(e,ui) {               
                        $("#cp_cv_id").val(ui.item.id);
                    }
                });
            });
            chkcookie();
            if ( !cok ) $('#Copy').hide();
        });
    //-->
    </script>
<body onLoad="goFld();">
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('PersonenEingebenEditieren');">.:persons:. .:keyin:./.:edit:.</h1><br>


<!-- Beginn Code ------------------------------------------->
    <div id="maintab">
        <ul>
        <li><a href="#tab1">.:person:.</a></li>
        <li><a href="#tab2">.:Company:.</a></li>
        <li><a href="#tab3">.:misc:.</a></li>
        </ul>
 
        <form name="formular" enctype='multipart/form-data' action="{action}" method="post" onSubmit='return chkPflicht();'>
        <input type="hidden" name="PID" value="{PID}">
        <input type="hidden" name="mtime" value="{mtime}">
        <input type="hidden" name="FID1" value="{FID1}">
        <input type="hidden" name="Quelle" value="{tabelle}">
        <input type="hidden" name="employee" value="{employee}">
        <input type="hidden" name="IMG_" value="{IMG_}">
        <input type="hidden" name="nummer" value="{nummer}">
        <input type="hidden" name="cp_cv_id" id="cp_cv_id" size="7" maxlength="10" value="{FID}" tabindex="32">
 
        <span id="tab1">
            <div class="zeile2">
                <span class="label2 klein">.:gender:.</span>
                <span class="feld">
                        <select name="cp_gender" tabindex="2" style="width:9em;">
                            <option value="m" {cp_genderm}>.:male:.
                            <option value="f" {cp_genderf}>.:female:.
                        </select>
                </span>
                <span class="label klein">.:phone:. 1</span>
                <span class="feld"><input type="text" id="phone" name="cp_phone1" size="25" maxlength="75" value="{cp_phone1}" tabindex="12"></span>
            </div>
            <div class="zeile2">
                <span class="label2 klein">.:salutation:.</span>
                <span class="feld"><select name="cp_salutation" tabindex="3" style="width:15em;">
                            <option value="">
<!-- BEGIN briefanred -->
                            <option value="{BAid}" {BAsel}>{BAtext}
<!-- END briefanred -->
                        </select></span>
                 <span class="label klein">2</span>
                 <span class="feld"><input type="text" name="cp_phone2" size="25" maxlength="75" value="{cp_phone2}" tabindex="12"></span>
             </div>
             <div class="zeile2">
                 <span class="label2 klein"></span>
                 <span class="feld"><input type="text" name="cp_salutation_" size="25" maxlength="125" value="{cp_salutation_}" tabindex="5"></span>
                 <span class="label klein">.:mobile:. 1</span>
                 <span class="feld"><input type="text" name="cp_mobile1" size="25" maxlength="75" value="{cp_mobile1}" tabindex="13"></span>
             </div>
             <div class="zeile2">
                 <span class="label2 klein">.:title:.</span>
                 <span class="feld"><input type="text" name="cp_title" size="25" maxlength="75" value="{cp_title}" tabindex="5"></span>
                 <span class="label klein">2</span>
                 <span class="feld"><input type="text" name="cp_mobile2" size="25" maxlength="75" value="{cp_mobile2}" tabindex="13"></span>
             </div>
             <div class="zeile2">
                 <span class="label2 klein">.:givenname:. *</span>
                 <span class="feld"><input type="text" name="cp_givenname" id="cp_givenname" size="25" maxlength="75" value="{cp_givenname}" tabindex="6"></span>
                 <span class="label klein">.:fax:.</span>
                 <span class="feld"><input type="text" name="cp_fax" size="25" maxlength="75" value="{cp_fax}" tabindex="14"></span>
             </div>
             <div class="zeile2">
                 <span class="label2 klein">.:name:. *</span>
                 <span class="feld"><input type="text" name="cp_name" id="cp_name" size="25" maxlength="75" value="{cp_name}" tabindex="7"></span>
                 <span class="label klein">Privat</span>
                 <span class="feld"><input type="text" name="cp_privatphone" size="25" maxlength="75" value="{cp_privatphone}" tabindex="12"></span>
             </div>
             <div class="zeile2">
                 <span class="label2 klein">.:street:.</span>
                 <span class="feld"><input type="text" name="cp_street" size="25" maxlength="75" value="{cp_street}" tabindex="8"></span>
                 <span class="label klein">.:privat:. .:email:. </span>
                 <span class="feld"><input type="text" name="cp_privatemail" size="25" maxlength="125" value="{cp_privatemail}" tabindex="15"></span>
             </div>
             <div class="zeile2">
                 <span class="label2 klein">.:country:. / .:zipcode:.</span>
                 <span class="feld"><input type="text" id="country" name="cp_country" size="2" maxlength="3" value="{cp_country}" tabindex="9"> / 
                           <input type="text" id="zipcode" name="cp_zipcode" size="5" maxlength="10" value="{cp_zipcode}" tabindex="10">
                 </span>
             </div>
             <div class="zeile2">
                 <span class="label2 klein">.:city:.</span>
                 <span class="feld"><input type="text" id="city" name="cp_city" size="25" maxlength="75" value="{cp_city}" tabindex="11"></span>
                           <span id="geosearchP" class="feldxx"></span>
             </div>
             <div class="zeile2">
                 <span class="label2 klein">.:homepage:.</span>
                 <span class="feld"><input type="text" name="cp_homepage" size="25" maxlength="125" value="{cp_homepage}" tabindex="16"></span>
                 <span class="label klein">.:email:. </span>
                 <span class="feld"><input type="text" name="cp_email" size="25" maxlength="125" value="{cp_email}" tabindex="15"></span>
             </div>
             <div class="zeile2" style='visibility:hidden' id='REV'>
                 <span class="label2 klein">REV</span>
                 <span class="feld"><input type="text" name="revision" size="25" maxlength="125" value="" tabindex="16"></span>
                 <span class="label klein">UID </span>
                 <span class="feld"><input type="text" name="uid" size="25" maxlength="125" value="" tabindex="15"></span>
                 <span class="label klein">KEY </span>
                 <span class="feld"><input type="text" name="key" size="25" maxlength="125" value="" tabindex="15"></span>
             </div>
             <br />
        </span> <!-- End tab1 -->
 
        <span id="tab2">
            <div class="zeile2">
                <span class="label klein">.:Company:.</span>
                <span class="feld"><input type="text" name="name" id="company_name" size="25" maxlength="75" value="{Firma}" tabindex="18">
                                   <!--input type="button" name="dst" value=" ? " onClick="suchFa();" tabindex="99"--> </span>
            </div>
            <div class="zeile2">
                <span class="label klein">.:department:.</span>
                <span class="feld"><input type="text" name="cp_abteilung" size="25" maxlength="30" value="{cp_abteilung}" tabindex="19"></span>
            </div>
            <div class="zeile2">
                <span class="label klein">.:position:.</span>
                <span class="feld"><input type="text" name="cp_position" size="25" maxlength="25" value="{cp_position}" tabindex="20"></span>
            </div>
        </span> <!-- End tab2 -->
 
        <span id="tab3">
             <span  style="float:left;">
                 <div class="zeile2">
                     <span class="label klein">.:Catchword:.</span>
                     <span class="feld"><input type="text" name="cp_stichwort1" size="25" maxlength="50" value="{cp_stichwort1}" tabindex="21"></span>
                 </div>
                 <div class="zeile2">
                     <span class="label klein">.:birthday:.</span>
                     <span class="feld"><input type="text" name="cp_birthday" id="cp_birthday" size="10" maxlength="10" value="{cp_birthday}" tabindex="17"><span class="klein"> TT.MM.JJJJ</span></span>
                 </div>
                 <div class="zeile2">
                     <span class="label klein">.:image:.</span>
                     <span class="feld"><input type="file" name="Datei[bild]" size="10" maxlength="75" tabindex="22"></span>
                 </div>
                 <div class="zeile2">
                     <span class="label klein">.:vcard:.</span>
                     <span class="feld"><input type="file" name="Datei[visit]" size="10" maxlength="75" tabindex="23"></span>
                 </div>
                 <div class="zeile2" style="align:left;">
                     <span class="klein">.:Remarks:.</span><br>
                     <span class="feldxx" style="border:0px solid black;"><textarea class="klein" name="cp_notes" cols="55" rows="4" tabindex="25">{cp_notes}</textarea></span>
                 </div>
                 <div class="zeile2">
                     <span class="label klein">.:correlation:.</span>
                     <span class="feld"><input type="text" name="cp_beziehung" size="8" maxlength="10" value="{cp_beziehung}" tabindex="24"></span>
                 </div>
             </span>
             <span style="float:left;">
                 <span class="label">{IMG}{IMG_}<br>
                 {visitenkarte}</span>
             </span>
        </span> <!-- End tab3 -->
        <span class="fett">{Msg}<br /></span>
        <span>
             {Btn3} {Btn1} <input type='submit' class='sichernneu' name='neu' value='.:save:. .:new:.' tabindex="28">
             <input type="submit" class="clear" name="reset" value=".:clear:." tabindex="29"> 
             <input type="button" name="" value="VCard" onClick="vcard()" tabindex="30">
             <input type="button" name="Copy" id='Copy' value="insert" onClick="c2m()" tabindex="30">
             <span class="klein">.:authority:.</span> <select name="cp_owener"  tabindex="31"> 
<!-- BEGIN OwenerListe -->
                <option value="{OLid}" {OLsel}>{OLtext}</option>
<!-- END OwenerListe -->
            </select> {init}
        </span>
</form>
</div>
<!-- End Code ------------------------------------------->
    <script type='text/javascript' src='inc/geosearchP.js'></script>
    <script type='text/javascript' src='inc/geosearch.js'></script>
{END_CONTENT}
</body>
</html>
