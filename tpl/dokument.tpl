<html>
    <head><title></title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{THEME}
{FANCYBOX}
    <script type="text/javascript" src="{CRMPATH}inc/dokument.js"></script>
    <script language="JavaScript">
    <!--
    
    $(document).ready(
        function(){
        $( '#delDir' ).hide();
    });

    //-->
    </script>
<body onLoad="dateibaum('left','/'), hidelinks(0); pickup = {PICUP}; tiny = {tiny}; baseurl = '{BASEURL}'; davurl = '{DAVURL}'">
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Dokumente');">.:Documents:.</h1><br>
    <form name="dokument.php" enctype='multipart/form-data' action="{action}" method="post">
    <input type="hidden" id="mandant" value="{mandant}">
    <span id='contentbox2'>

<!-- linker Dateibaum: -->
    <span style="float:left; width:45%; height:90%; min-height:300px; text-align:left; padding:2px; border: 1px solid black; border-bottom: 0px;">
        <button name="onClick=newFile('left')">.:uploadDocument:.   </button>
        <button name="onClick=newDir('left')">.:newDirectory:.      </button>
        <button name="onClick=delDir(pfadleft)" id='delDir'>.:delDirectory:.      </button>
        <button name="onClick=dateibaum('left',pfadleft)">.:reread:.</button>
        <button name="onClick=dateibaum('left','!')" id='davcrm'><span id='buttxt'>WebDav</span></button>
        <br>
        .:current path:.: <span id="path"></span>
        <span id="fbleft"><!-- Platzhalter für den dynamischen Inhalt --></span>
    </span>

<!-- rechter Dateibaum: -->
    <span style="float:left; width:53%; height:90%; min-height:300px; text-align:left; border: 1px solid black; border-bottom: 0px; padding:2px; border-left:0px;">
        <button id="subfilebrowser" name="onClick=dateibaum('right',pfadleft)">.:Filebrowser:.</button>
        <button id="subdownload"    name="onClick=download();">.:download:.                   </button>
        <button id="subdelete"      name="onClick=deletefile();">.:delete:.                   </button>
        <button id="submove"        name="onClick=movefile();">.:move:.                       </button> 
        <button id="subedit"        name="onClick=editattribut();">.:edit attribute:.         </button>
        <button id="lock"           name="onClick=lockFile();">.:lock file:.                  </button>
        <button id="picupbut"       name='onClick=picup();'>.:picup:.                         </button>
        <br>
        <span id="fbright"><!-- Platzhalter für den dynamischen Inhalt --></span>
    </span>

<!-- Neues Verzeichnis  -->
    <div id="newwindir" title=".:newDirectory:.">
        <p valign="center"><input type="hidden" name="seite" id="seite">
        <input type="text" name="subdir" id="subdir" size="26"><br /><br />
        <input type="button" name="sdok" value=".:create:." onClick="mkDir();"><br />
        <br />
        <center><button name="close" onClick="$('#newwindir').dialog('close');">.:close:.</button></center>
    </div>

<!-- Datei upload  -->
    <div id="uploadfr" title=".:uploadDocument:.">
        <iframe id="frupload" name="frupload" src="upload.php" frameborder="0" width="100%" height="80%"></iframe>
        <center><button name="close" onClick="$('#uploadfr').dialog('close');">.:close:.</button></center>
    </div>

<!-- Dateiattribute ändern  -->
    <div id="attribut" title=".:edit attribute:.">
        <input type="hidden" name="docid"      id="docid" value="">
        <input type="hidden" name="wvid"       id="wvid" value="">
        <input type="hidden" name="docoldname" id="docoldname" value="">
        <input type="hidden" name="docpfad"    id="docpfad" value="">
        <center>
        <table >
        <tr><td ><textarea name="docdescript" id="docdescript" cols="65" rows="8"></textarea></td></tr>
        <tr><td class="klein">.:Description:.</td></tr>
        <tr><td ><input type="text" name="docname" id="docname" size="45" value=""></td></tr>
        <tr><td class="klein">.:Filename:.</td></tr>
        <!--tr><td class="klein"><input type="text" name="iwvdate" id="wvdate" size="15" value=""></td></tr>
        <tr><td class="mini">.:wvdate:.</td></tr-->
        <tr><td class="re"><input type="button" name="saveAtr" value=".:save:." onClick="saveAttribut();"></td></tr>
        </table>
        <button name="close" onClick="$('#attribut').dialog('close');">.:close:.</button>
        </center>
    </div>

<!-- Datei löschen -->
    <div id="fileDel" title=".:delete:.">
        <p><center>
        <span class="fett" id="delname"></span><br />
        <br />
        <a href="javascript:filedelete();"><img src="image/eraser.png" border="0">.:Really:.</a><br />
        <br />
        <a href="javascript:deletefile();"><img src="image/fileclose.png" border="0">.:Better not:.</a><br />
        <br />
        <br />
        <button name="close" onClick="$('#fileDel').dialog('close');">.:close:.</button></center>
    </div>

    </span>
    </form>
    <form name='someform' id='someform'><input type='hidden' id='DownLoadFile' name='DownLoadFile' value=''></form>
</div>
{END_CONTENT}
</body>
</html>
