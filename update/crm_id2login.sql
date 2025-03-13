-- @tag: id2login
-- @description: Pfadnamen von ID auf Login umstellen
-- @php: *
$db =  $GLOBALS['db']->getAll('SELECT id,login FROM employee');
$pfad = $_SESSION['erppath'].'crm/dokumente/'.$_SESSION['dbname'].'/';
$return = 1;
if ( $db ) foreach( $db as $row ) {
    echo $pfad.$row['id']." ";
    if ( file_exists($pfad.$row['id']) ) {
        echo $pfad.$row['login'];
        //Altes Verzeichnis gibt es
        if ( file_exists($pfad.$row['login']) ) {
            echo " move<br>";
            //neues auch
            chdir(file_exists($pfad.$row['id']));
            $src = glob('*');
            chdir(file_exists($pfad));
            foreach( $src as $file ) {
                $rc = rename($pfad.$row['id'].'/'.$file,
                             $pfad.$row['login'].'/'.$file);
            };
            if ( $rc ) {
                unlink($pfad.$row['id']);
            } else {
                echo "Verzeichnist konnte nicht korrekt umbenannt werden<br>";
            };
        } else {
            echo " rename<br>";
            //Neues Verzeichnis gibt es nicht, umbenennen
            $rc = rename($pfad.$row['id'],
                         $pfad.$row['login']);
            if ( !$rc ) echo "Verzeichnist konnte nicht angelegt werden<br>";
        }
        $rc = $GLOBALS['db']->query("UPDATE documents SET pfad = '".$row['login']."' WHERE pfad = '".$row['id']."'");
        if ( !$rc ) echo "Probleme beim Update der Pfadnamen<br>";
    } else {
        if ( !file_exists($pfad.$row['login']) ) { 
            //weder altes noch neues Verzeichnis vorhanen
            echo "<br>".getcwd()."<br>";
            echo $pfad.$row['login']."<br>";
            $rc = mkdir($pfad.$row['login']);
            if ( !$rc ) echo "Verzeichnis konnte nicht erstellt werden <br>";
        } else {
            echo "<br>";
        }
    };
    if ( $_SESSION['dir_group'] ) 
        chgrp($pfad.$row['login'], $_SESSION['dir_group']);
    if ( $_SESSION['dir_mode'] ) 
        chmod($pfad.$row['login'],$_SESSION['dir_mode']);
};
return $return;
-- @exec: *
