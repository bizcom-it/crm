<?php
class logging {

    var $lf = false;

    #public function __construct() {
    #    date_default_timezone_set("Europe/Berlin"); 
    #}

    public function logging() {
        $this->lf = fopen('/tmp/android.log','a');
        date_default_timezone_set("Europe/Berlin"); 
        fputs($this->lf,'Start debug: '.date("Y-m-d H:i:s")."\n");
    }

    public function write($txt) {
        date_default_timezone_set("Europe/Berlin"); 
        fputs($this->lf,date("Y-m-d H:i:s ->")."\n");
        fputs($this->lf,$txt."\n");
    }
    
    public function close() {
        date_default_timezone_set("Europe/Berlin"); 
        fputs($this->lf,'Stop debug: '.date("Y-m-d H:i:s")."\n");
        fclose($this->lf);

    }
}
?>
