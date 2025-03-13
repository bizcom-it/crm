<?php

class MyDebug {
    
   var $file = false;

   function __construct( $name , $mode = 'a') {
        $this->file = fopen('/tmp/'.$name.'.log', $mode);
        $rc = fputs($this->file,'OPEN'."\n");
   }

   function write( $data , $info = '' ) {
       if ( $info != '' ) $rc = fputs($this->file,"$info\n");
       if ( is_array ($data) ) {
           $rc = fputs($this->file,print_r($data,true));
       } else if ( is_object ($data) ) {
           $rc = fputs($this->file,var_dump($data));
       } else {
           $rc = fputs($this->file,$data."\n");
       }
   }
   
   function __destruct() {
       fclose($this->file);
   }
}

?>
