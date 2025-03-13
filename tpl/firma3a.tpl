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
	function showP (id,nr) {
		if (id!='') {
			Frame=eval("parent.main_window");
			f1=open("rechng.php?Q={Q}&id="+id+"&nr="+nr,"rechng","width=700,height=420,left=10,top=10,scrollbars=yes");
		}
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
        })
	</script>    
<body>
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Stamm');">.:detailview:. {FAART} <span title=".:important note:.">{Cmsg}&nbsp;</span></h1><br>

<br />
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

<div style="position:absolute; left:1em; top:5em; width:45em;text-align:center;" class="normal">
.:SalesOrder:. .:Month:. {Monat}
	<table id="ums" class="tablesorter" width="100%">
		<thead><tr>
			<th style="width:6em">.:date:.</th>
			<th>.:number:.</th>
			<th>.:netto:.</th>
			<th>.:brutto:.</th>
			<th width="4em"></th>
			<th>.:art:.</th>
			<th>.:OP:.</th>
		</tr></thead><tbody style='cursor:pointer'>
<!-- BEGIN Liste -->
		<tr class="klein bgcol{LineCol}" onClick="showP('{Typ}{RNid}','{RNr}');">
			<td >{Datum}</td>
			<td >&nbsp;{RNr}&nbsp;</td>
			<td class='re'>{RSumme}&nbsp;&nbsp;</td>
			<td class='re'>{RBrutto}&nbsp;</td>
			<td >{Curr}</td>
			<td >&nbsp;{Typ}</td>
			<td >&nbsp;{offen}</td>
		</tr>
<!-- END Liste -->
		<tr><td colspan="7"><b>R</b>).:invoice:., <b>A</b>).:quotation:., <b>L</b>).:orders:.</td></tr>
		<tr><td colspan="7"><b>o</b>).:open:., <b>c</b>).:closed:., <b>+</b>).:paid:., <b>-</b>).:not_paid:.</td></tr>
	</tbody></table>
</div>	
</span>
{END_CONTENT}
</body>
</html>
