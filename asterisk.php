<?php
$cmd = '/usr/sbin/asterisk';
$f = fopen('/tmp/asterisk.log','a');

if ( isset($_GET) ) {
    foreach ( $_GET as $key => $val ) { ${$key} = $val; };
    fputs($f,print_r($_GET,true));
} else {
    echo -1;
    exit();
};
$key = trim(file_get_contents('/etc/asterisk/okauth'));
if ( md5($key) != $auth ) {
    echo -2;
    exit();
}
if ( isset( $action ) ) { 
    fputs($f,$action."\n");
    switch( $action ) {
    case 'test' : $rc = shell_exec($cmd." -rx 'core show sysinfo'");
                  break;
    case 'call' : $commando = $cmd." -rx 'originate Local/$from extension $to'";
                  fputs($f,"Remote: $commando\n");
                  $rc = shell_exec($commando);
                  fputs($f,"$rc\n");
                  break;
    default     : $rc = -3;
    }
} else {
    $rc = 'Funktion nicht definiert';
}
echo $rc;

?>
