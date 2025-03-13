<?php
require_once('inc/stdLib.php');
$popup = ( isSetVar($_GET['popup']) )?1:( isSetVar($_POST['popup']) )?1:0;

function getAllPostIt($id) {
	$sql = 'select * from postit where employee='.$id.' order by date';
	$rs  = $GLOBALS['db']->getAll($sql);
	return $rs;
}
function getOnePostIt($id) {
	$sql = 'select * from postit where id='.$id;
	$rs  = $GLOBALS['db']->getAll($sql);
	if ($rs) {
		$data=$rs[0];
		$data['notes']=stripslashes($data['notes']);
		return $data;
	} else {
		return false;
	}
}
function savePostIt($data) {
	if ( !$data['id'] ) {
		$newID = uniqid (rand());
        $rc    = $GLOBALS['db']->begin();
        $sql   = 'INSERT INTO postit (employee,date,cause) VALUES ('.$_SESSION['loginCRM'].',now(),\''.$newID.'\')';
        $rc    = $GLOBALS['db']->query($sql);
		if ( $rc ) {
           	$sql = "select id from postit where cause = '$newID'";
	        $rs  = $GLOBALS['db']->getOne($sql);
            if ( $rs ) {
                $rc = $GLOBALS['db']->commit();
                $data['id'] = $rs['id'];
            } else {
                $rc = $GLOBALS['db']->rollback();
                return false;
            }
		} else {
            $rc = $GLOBALS['db']->rollback();
			return false;
		};
    };
    $sql = "UPDATE postit SET notes = '".addslashes($data['notes'])."',cause='".addslashes($data['cause'])."' WHERE id = ".$data['id'];
    $rc  = $GLOBALS['db']->query($sql);
    return $rc;
}

function DelPostIt($id) {
	$sql = 'delete from postit where id='.$id;
	$rc  = $GLOBALS['db']->query($sql);
	return $rc;
}

$data = array('cause'=>'','notes'=>'','id'=>'');

if ( isSetVar($_POST['save']) ) {
	if ($_POST['cause']) $rc = savePostIt($_POST);
	if ( !$rc ) $data = $_POST;
} else if ( isSetVar($_GET['hole']) ) {
	$data = getOnePostIt($_GET['hole']);
} else if ( isSetVar($_POST['delete']) ) {
	if ($_POST['id']) $rc = delPostIt($_POST['id']);
} 

$menu  = $_SESSION['menu'];
$head  = mkHeader();
?>
<html xmlns='http://www.w3.org/1999/xhtml'>
	<head><title><?php echo  translate('.:LxO:.','work'); ?> <?php echo  translate('.:postit:.','work'); ?></title>
<?php 
echo $menu['stylesheets'];
echo $menu['javascripts']; 
echo $head['CRMCSS']; 
echo $head['JQTABLE'];
echo $head['THEME'];
?>
	<script language='JavaScript'>
	<!--
	function PopUp() {
		f1=open('postit.php?popup=1','PostIt','width=700,height=450');
	}
	//-->
	</script>
    <script>
       $(document).ready(function() {
           $('#memos').tablesorter({widthFixed: false, widgets: ['zebra']})
       });
	</script>
	</head>
<body onLoad='if (1==<?php echo  $popup ?>) window.resizeTo(600,400);'>
<?php if ( $popup != 1 ) {
echo $menu['pre_content'];
echo $menu['start_content'];
};?>

<div class='ui-widget-content' style='height:722px; border:0px;'>
<h1 class="toplist  ui-widget  ui-corner-all tools content1" onClick="help('Postit');"><?php echo  translate('.:notes:.','work'); ?></h1><br>

    <table id='memos' class='tablesorter' style='width:auto; min-width:400px;'>
        <thead>
            <tr><th>Datum</th><th>Memo</th></tr>
        </thead>
        <tbody>
<?php
$liste = getAllPostIt($_SESSION['loginCRM']);
if ( $liste ) foreach( $liste as $row ) {
	echo "\t<tr><td>";
	echo db2date(substr($row['date'],0,10)).' '.substr($row['date'],11,5);
	echo "</td><td>&nbsp;[<a href='postit.php?popup=$popup&hole=".$row['id']."'>".$row['cause']."</a>]</td></tr>\n";
};
?>
        </tbody>
    </table>
    <form  name='postit' method='post' action='postit.php'>
    <input type='hidden' name='id'    value='<?php echo  $data['id'] ?>'>
    <input type='hidden' name='popup' value='<?php echo  $popup ?>'>
    <input type='text'   name='cause' size='60' maxlength='100' value='<?php echo   $data['cause'] ?>'><br />
    <font size='-1'>Schlagzeile</font><br>
    <textarea class='normal' rows='7' cols='75' name='notes'><?php echo  $data['notes'] ?></textarea><br />
    <font size='-1'>Langtext</font><br>
    <input type='submit' name='save'   class='sichern'    value='<?php echo  translate('.:save:.','work'); ?>'>&nbsp;
    <input type='submit' name='clear'  class='clear'      value='<?php echo  translate('.:clear:.','work'); ?>'>&nbsp;
    <input type='submit' name='delete' class='sichernneu' value='<?php echo  translate('.:delete:.','work'); ?>'>&nbsp;
<?php if ( $popup==1) { ?>
    <input type='button' name='ppp' value='<?php echo  translate('.:close:.','work'); ?>' onCLick='self.close();'>
<?php }  else { ?>
    <input type='button' name='ppp' value='<?php echo  translate('.:popup:.','work'); ?>' onCLick='PopUp();'>
<?php } ?>
    </form>
</div>
<?php if ( $popup != 1 ) echo $menu['end_content']; ?>
</body>
</html>
