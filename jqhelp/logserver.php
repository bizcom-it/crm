<?php

        $x = @exec('tail -5 /var/log/apache2/error.log $2> /dev/null',$status1); 
        $x = @exec('tail -5 /var/log/apache2/access.log $2> /dev/null',$status2); 
        if (empty($status1)) {
            $status = "Error:<br />Logfile ist leer oder nicht lesbar.";  
        } else {
            $status = "Error:<br />".implode("<br />",$status1);  
        }
        $status .= "<br />------------------------------------<br />";
        if (empty($status2)) {
            $status .= "Access:<br />Logfile ist leer oder nicht lesbar.";  
        } else {
            $status .= "Access:<br />".implode("<br />",$status2);  
        }
        if (trim($status)=="") $status="Keine Meldung";
        echo $status;


?>
