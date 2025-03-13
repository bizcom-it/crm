<?php
//http://www.html-world.de/program/phpex_13.php
class webdav {

var $server = '';


function webdav($server) {
    $this->server = $server;
}

function __connect( $param = array() )
{
 $fp = fsockopen($this->server, 80, $errno, $errstr, 5);
 if(!$fp)
 {
  return "$errno -> $errstr<br>";
 }
 else
 {
  fwrite($fp, $param['content']);
  $output_array = array();
  while(!feof($fp))
  {
   array_push($output_array,fgets($fp));
  }
  fclose($fp);
  return $output_array;
 }
}

public function check_webdav( $param=array() )
{
 $content = "HEAD / HTTP/1.1 \r\n";
 $content .= "Host: $this->server \r\n";
 $content .= "Connection: Close\r\n";
 $content .= "\r\n";
 $output = $this->__connect( array('content'=>$content) );

 foreach($output as $line)
 {
  if( preg_match("/Server:/",$line) )
  {
   if( !preg_match("/DAV*/",$line) )
   {
    return "1";
   }
  }
 }
}

public function show_content( $param=array() )
{
 $user = $param['user'];
 //$pwd  = $param['pwd'];

 $content = "PROPFIND /files/$user/ HTTP/1.1 \r\n";
 $content .= "Host: $this->server \r\n";
 $content .= "Depth: 1\r\n";
 $content .= "Content-Type: text/xml\r\n";
 $content .= "Connection:close\r\n";
 $content .= "Content-Length: 0\r\n";
 $content .= "\r\n";
 $content .= "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
 $content .= "<D:propfind xmlns:D=\"DAV:\">\r\n";
 $content .= "<D:allprop/>\r\n";
 $content .= "</D:propfind>\r\n";

 $output = $this->__connect( array('content'=>$content) );
 array_pop($output);
 $key = array_search("\r\n",$output);
 $output = array_slice($output,($key+1));
}

public function file_upload( $param = array() )
{
 $user = $param['user'];
 $tmp_filename = $param['tmpfile'];
 $filename = $param['file'];

 if(copy($tmp_filename, "D:/Webdav/" . $user ."/" . $filename) )
 {
  $content = "<script type=text/javascript>
  alert(\"Datei $filename wurde hochgeladen\");
  document.location.href=\"index.php\";
 </script>";
 }
 else
 {
  $content = "<script type=text/javascript>
  alert(\"Datei $filename konnte nicht hochgeladen werden\");
  document.location.href=\"index.php\";
 </script>";
 }
 return $content;
}

public function delete_file( $param = array() )
{
 $query_string = $param['query_string'];
 $query_param = split("&",$query_string);

 $filename = split("=",$query_param[1]);
 $file_tmp = str_replace("%3CD:href%3E","",$filename[1]);
 $file = str_replace("%3C/D:href%3E","",$file_tmp);

 $file_param = pathinfo($file);
 $path = $file_param['dirname'] . "/";
 $file = $file_param['basename'];

 $content = "DELETE $path/$file HTTP/1.1 \r\n";
 $content .= "Host: $this->server \r\n";
 $content .= "Connection: Close\r\n";
 $content .= "Content-length:0\r\n";
 $content .= "Destroy:NoUndelete\r\n";
 $content .= "\r\n";

 $output = $this->__connect( array('content'=>$content) );

 if(preg_match("/204/",$output[0]))
 {
  $content = "<script type=text/javascript>
  alert('Datei $file wurde gelöscht');
  document.location.href=\"index.php\";
 </script>";
 }
 else
 {
  $content = "<script type=text/javascript>
  alert('Datei $file konnte nicht gelöscht werden');
  document.location.href=\"index.php\";
 </script>";
 }
 return $content;
}

public function create_new_folder( $param = array() )
{
 $user = $param['user'];
 $folder = $param['folder'];

 $content = "MKCOL /files/$user/$folder HTTP/1.1 \r\n";
 $content .= "Host: $this->server \r\n";
 $content .= "Connection: Close\r\n";
 $content .= "\r\n";

 $output = $this->__connect( array('content'=>$content) );
 if(preg_match("/201/",$output[0]))
 {
  $content = "<script type=text/javascript>
  alert('Ordner $folder wurde angelegt');
  document.location.href=\"index.php\";
 </script>";
 }
 else
 {
  $content = "<script type=text/javascript>
  alert('Der Ordner $folder konnte nicht angelegt werden.');
  document.location.href=\"index.php\";
 </script>";
 }
 return $content;
}

}
?>
