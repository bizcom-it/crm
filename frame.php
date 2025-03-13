<?php
require_once("inc/stdLib.php");
$menu = $_SESSION['menu'];
$head = mkHeader();

$parameter = array();
$error = false;

if ( isset($_GET) ) while ( list($key,$val) = each ($_GET) ) {
    if ( $key == 'action' ) {
        if ( $val != 'section_menu' ) $link = $val;
    } else if ( $key == 'level' ) {
		continue;
    } else if ( $key == 'dir' ) {
        $dir  = 'crm/'.$val.'/';
	} else {
        $parameter[] = $key.'='.$val;
	};
} else {
	exit(1);
} 
if ( $dir == '' ) $dir = 'peppershop';
if ( $link == '' ) $error = true;
$parameter = join("&",$parameter);

if ( substr($link,-5) != '.html' ) $link .= '.php';
    
echo '<html>
<head><title></title>';
echo $menu['stylesheets'];
echo $menu['javascripts'];
echo $head['CRMCSS'];
echo $head['THEME'];
echo $head['JQTABLE'];
echo '</head>
<body>';
echo $menu['pre_content'];
echo $menu['start_content'];
echo "<span class='tools'></span>";
if ( $error ) { echo "Ungültige Parameter !$dir!$link!"; } 
else {
?>

<iframe  id="shop" name="shop" src="<?php echo $_SESSION['baseurl'].$dir.$link.'?'.$parameter ?>" frameborder="0" width="100%" height="100%"></iframe>

<?php }; 
echo $menu['end_content']; ?>
</body>
</html>
