<html>
    <head><title>Mandanten Stamm</title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{THEME}
</head>
<body>
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style="height:722px; border:0px;">
<p class="ui-state-highlight ui-widget-header ui-widget ui-corner-all tools content1" onClick="help('Mandant');">Mandanteneinstellungen {msg}</p>

<table style='visibility:{hide}'>
    <form name="mandant" id="mandant" action="mandant.php" method="post">
    <tr><td colspan='2'><b>externe DBs</b></td></tr>
    <tr><td>Geo-DB</td>                             <td><input type='checkbox' name='GEODB' id='GEODB' value='t' {GEODB}> siehe install.txt</td></tr>
    <tr><td>Blz-DB</td>                             <td><input type='checkbox' name='BLZDB' id='BLZDB' value='t' {BLZDB}> siehe install.txt</td></tr>
    <tr><td colspan='2'><b>Kontakthred</b></td></tr>
    <tr><td>Editieren</td>                          <td><input type='checkbox' name='CallEdit' id='CallEdit' value='t' {CallEdit}></td></tr>
    <tr><td>Löschen</td>                            <td><input type='checkbox' name='CallDel' id='CallDel' value='t' {CallDel}></td></tr>
    <tr><td colspan='2'><b>E-Mail</b></td></tr>
    <!--tr><td>Nur anzeigen wenn nicht:</td><td><select name='MailFlag'>
                                             <option value='Flagged' {Flagged}>Flagged
                                             <option value='Answered' {Answered}>Answered
                                             <option value='Seen' {Seen}>Seen
                                             <option value='Deleted' {Deleted}>Deleted
                                             <option value='Draft' {Draft}>Draft
                                          </select></td></tr-->
    <tr><td>Nur anzeigen wenn nicht gesetzt:</td><td><input type='checkbox' name="MailFlag[Seen]"     id='Seen'     value='t' {Seen}    > Seen 
                                                     <input type='checkbox' name="MailFlag[Flagged]"  id='Flagged'  value='t' {Flagged} > Flagged
                                                     <input type='checkbox' name="MailFlag[Draft]"    id='Draft'    value='t' {Draft}   > Draft
                                                     <input type='checkbox' name="MailFlag[Answered]" id='Answered' value='t' {Answered}> Answered
                                                     <input type='checkbox' name="MailFlag[Deleted]"  id='Deleted'  value='t' {Deleted} > Deleted
                                                     <input type='checkbox' name="MailFlag[Recent]"   id='Recent'   value='t' {Recent}  > Recent
        </td></tr>
    <tr><td>Gelöscht markieren mit: </td><td><select name='MailDelete'>
                                             <option value='Flagged' {delFlagged}>Flagged
                                             <option value='Deleted' {delDeleted}>Deleted
                                             <option value='Draft' {delDraft}>Draft
                                          </select> Wenn Deleted, wird der Mailordner bereinigt</td></tr>
    <tr><td>versendete Mails loggen</td>        <td><input type='checkbox' name='logmail' id='logmail' value='t' {logmail}></td></tr>
    <tr><td colspan='2'><b>Vorgabe Map</b></td></tr>
    <tr><td>Map-URL</td>            <td><input type='text' name='streetview_man' id='streetview_man' value='{streetview_man}' size='60'></td></tr>
    <tr><td colspan='2'>Platzhalter: %TOSTREET% %TOZIPCODE% %TOCITY% %FROMSTREET% %FROMZIPCODE% %FROMCITY%</td></tr>
    <tr><td>Leerzeichenersatz</td>  <td><input type='text' name='planspace_man' id='planspace_man' value='{planspace_man}' size='1' maxlength='1'> (GoYellow: '-', Viamichelin, Google: '+')</td></tr>
    <tr><td colspan='2'><b>Zeiterfassung</b></td></tr>
    <tr><td>Artikelnummer für Arbeitszeit</td>  <td><input type='text' name='ttpart' id='ttpart' value='{ttpart}' size='10'></td></tr>
    <tr><td>Minuten je Einheit</td>             <td><input type='text' name='tttime' id='tttime' value='{tttime}' size='5'></td></tr>
    <tr><td>Ab hier eine Einheit</td>           <td><input type='text' name='ttround' id='ttround' value='{ttround}' size='5'>min.</td></tr>
    <tr><td>Nur eigene Aufträge abrechnen</td>  <td><input type='checkbox' name='ttclearown' id='ttclearown' {ttclearown} value='t'></td></tr>
    <tr><td colspan='2'><b>Benutzerfreundliche Links</b></td></tr>
    <tr><td>Links zu Verzeichnissen</td><td>
             Gruppe: <input type="text" name="dir_group" size="12" value='{dir_group}'>
             &nbsp;&nbsp; Rechte: <input type="text" name="dir_mode" size="4" value='{dir_mode}'>
             <input type="checkbox" name="sep_cust_vendor"  value='t' {sep_cust_vendor}>Kunden/Lieferanten trennen</td>
    <tr><td colspan='2'><b>Diverse</b></td></tr>
    <tr><td>CRM-Pfad</td>                   <td>{erppath}</td></tr>
    <tr><td>Logfile (tmp/lxcrm.err)</td>    <td><input type='checkbox' name='logfile' id='logfile' {logfile} value='t'></td></tr>
    <tr><td>Listenlimit</td>                <td><input type='text' name='listLimit' id='listLimit' value='{listLimit}' size='8'></td></tr>
    <tr><td><input type="submit" name="save" id="save" value="sichern"></td><td></td></tr>
</table>
</form>
</div>
{END_CONTENT}
</body>
</html>

