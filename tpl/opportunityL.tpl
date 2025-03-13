<html>
    <head><title>{TITLE}</title>
{STYLESHEETS}
{JAVASCRIPTS}
{CRMCSS}
{THEME}    
{JQTABLE}    
    <script language="JavaScript">
    <!--
        function showO(id) {
            self.location="opportunity.php?id="+id
        }
    //-->
    </script>
    <script>
        $(document).ready(
            function() {
                $("#oppliste").tablesorter({widthFixed: true, widgets: ['zebra']});
            });
    </script>    
<body>
{PRE_CONTENT}
{START_CONTENT}
<div class="ui-widget-content" style="height:722px; border:0px;">
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Auftragschance');">.:opportunity:.</h1><br>

<table id='oppliste' class='tablesorter' style='margin:0px; cursor:pointer;'>
    <thead>
    <tr>
        <th>.:company:.</th>
        <th>.:order:.</th>
        <th style="width:20;text-align:right">%</th>
        <th style="width:80;text-align:center">&euro;</th>
        <th>.:status:.</th>
        <th>.:targetdate:.</th>
        <th>.:employee:.</th>
        <th>.:changed:.</th>
    </tr>
    </thead><tbody>
<!-- BEGIN Liste -->
    <tr onClick="showO({id});">
        <td>{firma}</td>
        <td>{title}</td>
        <td style="width:20;text-align:right">{chance}</td>
        <td style="width:80;text-align:right"> {betrag}</td>
        <td>{status}</td>
        <td style="width:60;text-align:right"> {datum}</td>
        <td>{user}</td>
        <td>{chgdate}</td>
    </tr>
<!-- END Liste -->
    </tbody>
</table>
</div>
{END_CONTENT}
</body>
</html>
