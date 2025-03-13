<?php

class myDB {

    private $db = false;
    private $lasterror = false;
    private $errfile   = './tmp/lxcrm.err';
    private $logfile   = './tmp/lxcrm.log';

    public function __construct( $conn ){
        if ( $this->db ) return $this->db;
        $this->errfile = $_SESSION['erppath'].'/crm/tmp/lxcrm.err';
        $this->logfile  = $_SESSION['erppath'].'/crm/tmp/lxcrm.log';
        try{
            $this->db = pg_connect( "host=".$conn['dbhost']." port=".$conn['dbport']." dbname=".$conn['dbname']." user=".$conn['dbuser']." password=".$conn['dbpasswd']);
        } catch( Exception $e ){
            echo pg_last_error();
        }
    }
    private function writeLog( $src, $txt, $data = false){
        if ( ! isset($_SESSION['logfile']) || ! $_SESSION['logfile'] ) return;
        file_put_contents($this->logfile,$src.':'.lf, FILE_APPEND );
        if ( is_string($txt) ) {
            file_put_contents($this->logfile,$txt.lf, FILE_APPEND );
        } else if ( is_array($txt) ) {
            foreach ( $txt as $line ) 
                file_put_contents($this->logfile,join(';',$line).lf, FILE_APPEND );
        } else if ( is_object($txt) ) {
            file_put_contents($this->logfile,serialize($txt).lf, FILE_APPEND );
        } else {
            file_put_contents($this->logfile,'Unbekannter Fehler'.lf, FILE_APPEND );
        }
        if ( $data ) {
           if      ( is_string($data) ) { file_put_contents($this->logfile,'String:'.$data.lf, FILE_APPEND ); }
           else if ( is_object($data) ) { file_put_contents($this->logfile,'Object:'.serialize($data).lf, FILE_APPEND ); }
           else if ( is_array($data) )  { file_put_contents($this->logfile,'Array:'.print_r($data,true).lf, FILE_APPEND ); }
           else                         { file_put_contents($this->logfile,'Unbekannte Daten'.lf, FILE_APPEND ); };
        }  else    {
           file_put_contents($this->logfile,'Keine Daten'.lf, FILE_APPEND ); 
        };
        if ( ! isset($_SESSION['debug']) || ! $_SESSION['debug'] ) return;
        file_put_contents($this->logfile,'Backtrace:'.lf, FILE_APPEND );
        foreach ( debug_backtrace() as $debug ) {
            file_put_contents($this->logfile,'File:'.$debug['file'].':'.$debug['line'].lf, FILE_APPEND );
            file_put_contents($this->logfile,(( isset($debug['class']) )?$debug['class'].' -> ':''), FILE_APPEND );
            file_put_contents($this->logfile,$debug['function'].lf, FILE_APPEND );
        }
    }
    public function dbFehler($function, $sql, $data = false ) {
        $this->lasterror = pg_last_error();
        if ( ! isset($_SESSION['errlogfile']) || !$_SESSION['errlogfile'] ) return false;
        file_put_contents($this->errfile,date('Y-m-d H:i:s  ').$function.lf, FILE_APPEND );
        file_put_contents($this->errfile,'SQL:'.$sql.lf, FILE_APPEND );
        file_put_contents($this->errfile,'Msg:'.$this->lasterror.lf, FILE_APPEND );
        if ( $data ) {
           if ( is_string($data) ) { file_put_contents($this->errfile,'String:'.$data.lf, FILE_APPEND ); }
           else if ( is_object($data) ) { file_put_contents($this->errfile,'Object:'.serialize($data).lf, FILE_APPEND ); }
           else if ( is_array($data) ) { file_put_contents($this->errfile,'Array:'.join(';',$data).lf, FILE_APPEND ); }
           else { file_put_contents($this->errfile,'Unbekannter Fehler'.lf, FILE_APPEND ); };
        };
        file_put_contents($this->errfile,'Backtrace:'.lf, FILE_APPEND );
        file_put_contents($this->errfile,print_r(debug_backtrace(),true).lf, FILE_APPEND );
        return false;
    }

    public function query( $sql ){
        $rs   = pg_query( $this->db, $sql);
        $this->writeLog( __FUNCTION__,$sql );
        if( !$rs ) return $this->dbFehler(__FUNCTION__,$sql); 
        return $rs;
    }

    public function getAll2( $sql, $json = FALSE  ){
        if( $json ) $sql = "SELECT json_agg( json ) FROM (".$sql.") AS json";
        $this->writeLog( __FUNCTION__,$sql,"SQL" );
        $rs   = pg_query( $this->db, $sql);
        if ( !$rs ) return $this->dbFehler(__FUNCTION__,$sql,'Not Found'); 
        return pg_fetch_all($rs);
    }
    public function getAll( $sql, $json = FALSE  ){
        $data = [];
        if( $json ) $sql = "SELECT json_agg( json ) FROM (".$sql.") AS json";
        $this->writeLog( __FUNCTION__,$sql,"SQL" );
        $rs   = pg_query( $this->db, $sql);
        $this->writeLog( __FUNCTION__,count($rs).'','Treffer' );
        if ( !$rs )  return $this->dbFehler(__FUNCTION__,$sql,'Not Found'); 
        if ( $json ) return $rs;
        while ( $row = pg_fetch_assoc($rs) ) {
            $data[] = $row;
        }
        return $data;
    }
    public function getJson($sql) {
        $rs = json_encode($this->getAll($sql));
        return $rs;
    }    
    public function getOne( $sql, $json = FALSE  ){
        if( $json ) $sql = "SELECT json_agg( json ) FROM (".$sql.") AS json";
        $this->writeLog( __FUNCTION__,$sql );
        $rs   = pg_query( $this->db, $sql);
        if( !$rs ) return $this->dbFehler(__FUNCTION__,$sql); 
        return pg_fetch_assoc($rs);
    }
    public function insert( $table, $fields, $values, $lastInsertId = FALSE ){
        $sql = 'INSERT INTO '.$table.' ('.implode(',',$fields).') VALUES ('.('$'.join(',$', range(1,count($fields))) ).') '.( $lastInsertId ? "returning $lastInsertId" : "");
        $this->writeLog( __FUNCTION__,$sql, $values );
        $rs = pg_query_params($this->db, $sql, $values);
        if( !$rs ) return $this->dbFehler(__FUNCTION__,$sql); 
        if ( $lastInsertId ) { list($id) = pg_fetch_row($rs); return $id; } 
        else                 { return true; }
    }
    /*
    * $table == String mit Tabellennamen
    * $fields == Array mit Spaltennamen
    * $values == assoziatives Array mit den Werten
    * $where == String mit Bedingung
    */
    public function update ( $table, $fields, $values, $where ){
        $flds = [];
        for( $i=1; $i<= count($fields); $i++)  $flds[] = $fields[$i-1].' = $'.$i; 
        $sql = "UPDATE $table set ".(implode( ',',$flds ))." WHERE ".$where;
        foreach ( $fields as $key ) if ( !array_key_exists($key, $values) ) $values[$key] = false;
        foreach ( array_keys($values) as $key ) if ( ! in_array($key,$fields) ) unset($values[$key]);
        $this->writeLog( __FUNCTION__,$sql, $values );
        $rs = pg_query_params($this->db, $sql, $values);
        if( !$rs ) return $this->dbFehler(__FUNCTION__,$sql); 
        return true;
    }
    public function updateval ( $table, $values, $where ){
        $flds = [];
        $i = 1;
        foreach ( $values as $key=>$val)   $flds[] = $key.' = $'.$i++; 
        $sql = "UPDATE $table set ".(implode( ',',$flds ))." WHERE ".$where;
        $this->writeLog( __FUNCTION__,$sql, $values );
        $rs = pg_query_params($this->db, $sql, $values);
        if( !$rs ) return $this->dbFehler(__FUNCTION__,$sql); 
        return true;
    }
    public function executeMultiple( $statement, $data ){
        $this->writeLog( __FUNCTION__,$statement, $data );
        $stmt = pg_prepare($this->db, 'multi', $statement);
        foreach ( $data as $row ) {
            try {
                $rs   = @pg_execute($this->db, 'multi', $row);
                if( !$rs ) return $this->dbFehler(__FUNCTION__,$statement, $row); 
            } catch (Exception $e ) {
                $this->lasterror = pg_last_error(); return false; 
            }
        }
        return debug_backtrace();
        //return true;
    }
    public function convert( $table, $data ) {
        $this->writeLog( __FUNCTION__,$table );
        return pg_convert( $this->db, $table, $data); 
    }
    public function begin() {
        return pg_query($this->db, 'BEGIN');
    }
    public function commit() {
        return pg_query($this->db, 'COMMIT');
    }
    public function rollback() {
        return pg_query($this->db, 'ROLLBACK');
    }
}
?>
