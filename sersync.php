<?php
require_once("inc/stdLib.php");
     if ( $_GET['status'] == '2' ) { $msg = 'Sync in beide Richtungen'; }
else if ( $_GET['status'] == '1' ) { $msg = 'Sync nur zum Server'; }
else                               { $msg = 'Kein Sync'; }

?>

    <script>
        $.ajax({
            url:      'jqhelp/serien.php?task=sersynstat&tab=<?php echo $_GET['src']; ?>&status=<?php echo $_GET['status']; ?>',
            succsess: function(rc){
                          console.log('ok'+rc)
                          $('#ergebnis').empty().append(rc); 
                      }  
        }).done( function(rc) {
            console.log('done'+rc);
            $('#ergebnis').empty().append(rc); 
        });
    </script>
Sycstatus: <?php echo $msg; ?> wird gesetzt.
<div id="ergebnis"></div>

