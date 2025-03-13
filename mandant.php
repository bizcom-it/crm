<?php
require_once( "inc/stdLib.php" );
$keys = array(
    'ttpart',
    'tttime',
    'ttround',
    'ttclearown',
    'GEODB',
    'BLZDB',
    'CallDel',
    'CallEdit',
    'MailFlag',
    'MailDelete',
    'logmail',
    'dir_group',
    'dir_mode',
    'sep_cust_vendor',
    'listLimit',
    'logfile',
    'streetview_man',
    'planspace_man',
);
/*if ( isset($_POST['save']) ) {
    foreach ( $keys as $value ) {
        $_SESSION[$value] = $value == 'dir_mode' ? octdec( $_POST[$value] ) : $_POST[$value]; 
    }
};*/
if ( $_POST['save'] ) {
    $save = true;
    if ( isset( $_POST['ttpart'] ) && $_POST['ttpart'] != '' ) {
        $sql = "SELECT count(*) as cnt FROM parts WHERE partnumber = '".$_POST['ttpart']."'";
        $rs = $GLOBALS['db']->getOne( $sql );
        if ( $rs['cnt'] == 0 ) {
            $msg = "Artikel nicht gefunden";
            $save = false;
        }
        elseif ( $rs['cnt'] > 1 ) {
            $msg = "Artikelnummer nicht eindeutig";
            $save = false;
        }
    };
    if ( isset( $_POST['MailFlag'] ) ) {
        $mailflag = array_keys($_POST['MailFlag']);
        $json     = json_encode($mailflag);
        print_r($mailflag); echo $json;
        $_POST['MailFlag'] = $json;
    }
    if ( $save ) {
        $last = $GLOBALS['db']->getOne( 'SELECT max(id) as id FROM crmdefaults' );
        $insert = "INSERT INTO crmdefaults (key,val,grp,employee) VALUES (?,?,'mandant',".$_SESSION['loginCRM'].")";
        $data = array( );
        foreach ( $keys as $row ) {
            $data[] = array(
                $row,
                $_POST[$row],
            );
        };
        $rc = $GLOBALS['db']->executeMultiple( $insert, $data );
        if ( $rc ) {
            $msg = 'Sichern erfolgt';
            if ( $last['id'] ) {
                $sql = "DELETE FROM crmdefaults WHERE grp = 'mandant' and id <= ".$last['id'];
                $rc = $GLOBALS['db']->query( $sql );
            }
            $GLOBALS['db']->commit( );
        }
        else {
            $GLOBALS['db']->rollback( );
            $msg = 'Sichern fehlgeschlagen';
        }
    }
}
$sql = "SELECT * FROM crmdefaults WHERE grp = 'mandant'";
$rs = $GLOBALS['db']->getAll( $sql );
$data = array( );
if ( $rs ) 
    foreach ( $rs as $row ) 
        $data[$row['key']] = $row['val'];
foreach ( $keys as $row ) {
    if ( !isset( $data[$row] ) ) {
        if ( isset( $ { $row } ) ) {
            $data[$row] = $ { $row };
        }
        else {
            $data[$row] = '';
        };
    };
};
include( "inc/template.inc" );
$t = new Template( $base );
doHeader( $t );
$t->set_file( array( "mand" => "mandant.tpl" ) );
if ( $_SESSION['CRMTL'] != 1 ) {
    $t->set_var( array( 'msg' => 'Diese Aktion ist nicht erlaubt. </ br>Sie sind nicht Mitglied der Gruppe CRMTL.', 'hide' => 'hidden' ) );
} else {
    $flags = json_decode($data['MailFlag']);
    if ( $flags ) foreach ( $flags as $flag ) $t->set_var( array( $flag => 'checked', ) );
    $t->set_var( array( 'GEODB' => ( $data['GEODB'] == 't' ) ? 'checked' : '', 
                        'BLZDB' => ( $data['BLZDB'] == 't' ) ? 'checked' : '', 
                        'CallEdit' => ( $data['CallEdit'] == 't' ) ? 'checked' : '', 
                        'CallDel' => ( $data['CallDel'] == 't' ) ? 'checked' : '', 
                        'del'.$data['MailDelete'] => 'selected', 
                        'logmail' => ( $data['logmail'] == 't' ) ? 'checked' : '', 
                        'streetview_man' => $data['streetview_man'], 
                        'planspace_man' => $data['planspace_man'], 
                        'ttpart' => $data['ttpart'], 
                        'tttime' => $data['tttime'], 
                        'ttround' => $data['ttround'], 
                        'ttclearown' => ( $data['clearown'] == 't' ) ? 'checked' : '', 
                        'dir_group' => $data['dir_group'], 
                        'dir_mode' => $data['dir_mode'], 
                        'sep_cust_vendor' => ( $data['sep_cust_vendor'] == 't' ) ? 'checked' : '', 
                        'listLimit' => $data['listLimit'], 
                        'showErr' => ( $data['showErr'] == 't' ) ? 'checked' : '', 
                        'logfile' => ( $data['logfile'] == 't' ) ? 'checked' : '', 
                        'erppath' => $_SESSION['erppath'], 
                        'msg' => $msg, ) );
}
$t->pparse( "out", array( "mand" ) );
?>
