<html>
	<head><title></title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{THEME}
{JQTABLE}
{FANCYBOX}
	<script language="JavaScript">
	<!--
	function showM (month) {
		uri="firma3.php?Q={Q}&jahr={JAHR}&monat=" + month + "&fid=" + {FID};
		location.href=uri;
	}
    $(function(){
         $('button')
          .button()
          .click( function(event) { event.preventDefault();  document.location.href=this.getAttribute('name'); });
    });
	//-->
	</script>
    <script>
    $(document).ready(
        function(){
            $("#ums").tablesorter({widthFixed: true, widgets: ['zebra'], headers: { 
                0: { sorter: false }, 1: { sorter: false }, 2: { sorter: false }, 3: { sorter: false }, 4: { sorter: false } } 
            });
            $("#topparts").tablesorter({widthFixed: true, widgets: ['zebra'], headers: { 
                0: { sorter: false }, 1: { sorter: false }, 2: { sorter: false }, 3: { sorter: false }, 4: { sorter: false }, 5: { sorter: false },6: { sorter: false }} 
            });
        })
	</script>
<body>
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Stamm');">.:detailview:. {FAART} <span title=".:important note:.">{Cmsg}&nbsp;</span></h1><br>
<br>
<div id="menubox1">
    <form>
    <span style="float:left;" valign="bottom">
    <button name="{Link1}">.:Custombase:.</button>
    <button name="{Link2}">.:Contacts:.</button>
    <button name="{Link3}">.:Sales:.</button>
    <button name="{Link4}">.:Documents:.</button>
    <button name="opportunity.php?Q={Q}&fid={FID}">.:Opportunitys:.</button>
    </span>
    </form>
</div>
<div id='contentbox' >
<div style="position:absolute; left:2px; width:35em; border:1px solid lightgray; padding:0.4em;">
	<span class="fett">{Name} &nbsp; {kdnr}</span><br />
	{Plz} {Ort}
</div>
<div style="position:relative; left:1em; top:4em; text-align:center;" class="normal">
	<div style="text-align:left; width:99%; border: 0px solid red;" class="fett">
	<center>.:Netto sales over 12 Month:. 
	[<a href='firma3.php?Q={Q}&fid={FID}&jahr={JAHRZ}'>.:earlier:.</a>] {JAHR} [<a href='firma3.php?Q={Q}&fid={FID}&jahr={JAHRV}'>{JAHRVTXT}</a>]</center>
		<img src="{IMG}" width="{width}" height="{height}" title="Netto sales over 12 Month"><br />
	</div>
</div>
<div style="position:relative; left:1em; top:5em; ">
	<div style="float:left; width:45%; text-align:left; border: 0px solid red;" >
		<table id="ums" class="tablesorter" style="width:100%;">
			<thead><tr>
				<th >.:Month:.</th>
				<th></th><th>.:Sales:.</th>
				<th>.:Quotation:.</th><th></th>
			</tr></thead><tbody style='cursor:pointer'>
<!-- BEGIN Liste -->
			<tr onClick="showM('{Month}');">
				<td >{Month}</td>
				<td >{Rcount}</td><td >{RSumme}</td>
				<td >{ASumme}</td><td >&nbsp;{Curr}</td>
			</tr>
<!-- END Liste -->
		</tbody></table>
	</div>  
    <div style="float:left;border: 0px solid blue; width:45%;">
        <table id='topparts' class="tablesorter">
        <thead><tr>
			<th >.:date:.</th>
			<th>.:part:.</th><th>.:qty:.</th>
			<th>.:unit:.</th><th>%</th><th></th><th>.:Sales:.</th>
		</tr></thead><tbody>
<!-- BEGIN TopListe -->
           <tr><td>{transdate}</td><td>{description}</td><td align='right'>{qty}</td><td>{unit}</td><td align='right'>{rabatt}</td><td align='right'>{sellprice}</td><td align='right'>{summe}</td></tr>
<!-- END TopListe -->
        </tbody></table>
    </div>
</div>
</span>
{END_CONTENT}
</body>
</html>
