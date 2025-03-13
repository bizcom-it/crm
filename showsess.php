<?php
        include("inc/stdLib.php");
        $menu = $_SESSION['menu'];
?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head><title></title>
    <?php echo $menu['stylesheets'].$menu['javascripts']; ?>
</head>
<body>
<?php
echo $menu['pre_content'];
echo $menu['start_content']; 
if ( isset($_GET['ok']) && $_GET["ok"]) {
	$x = $_SESSION['menu'];
    $y = preg_replace( "^>^","&gt;",$x);
    $y = preg_replace( "^<^","&lt;",$y);
    $y = preg_replace( "^\n^","<br>",$y);
    $_SESSION['menu'] = $y;
	echo "<pre>";
    print_r($_SESSION);
    echo "COOKIE\n";
	print_r($_COOKIE);
	echo "</pre>";
	echo "<form name='x' action='showsess.php' method='post'>";
	echo "<input type='submit' name='del' value='Session l&ouml;schen'>";
	echo "</form>";
    $_SESSION['menu'] = $x;
} else {
	while( list($key,$val) = each($_SESSION) ) {
		unset($_SESSION[$key]);
	};
    echo '<script type="text/javascript">window.location.href="status.php";</script>';
};
echo $menu['end_content'];
?>
</body>
