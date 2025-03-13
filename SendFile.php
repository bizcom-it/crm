<?php
/* Some verification code that gathers the required data and 
  reports errors back to the target PC via Ajax and 
  then exits with no further output
  If any errors have occurred then this point will never be reached, 
  it is vital that at this point no output has been generated*/
 $file = $_POST['DownLoadFile'];
 if ( file_exists( $file ) ) {
     if ( ! function_exists ( 'mime_content_type' ) ) {
         header( "Content-type: text/plain" );           // Set up a file download
     } else {
         $mime = mime_content_type($file);
         header( "Content-type: $mime" );                // Set up a file download
     };
     header( "Content-Disposition: attachment; filename='$file'" ); // Dump the data
     echo readfile($file);
     exit(0);
 } else {
    echo "File: '$file' nicht gefunden oder lesbar.";
 }
?>
