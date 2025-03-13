<?php
    define ('lf',"\n");

class myDB extends PDO{
    private $showErr = TRUE;  //show errors in browser
    private $logAll  = TRUE;  //log all sql queries
    private $rc      = false;
    private $errfile = "lxcrm.err";
    private $logfile = "lxcrm.log";
    private $db      = false;

    private function writeLog( $src, $txt, $all=true ){
        if ( ! isset($_SESSION['logfile']) ) return;
        file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->logfile,$src.':'.lf, FILE_APPEND );
        if ( is_string($txt) ) {
            file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->logfile,$txt.lf, FILE_APPEND );
        } else if ( is_array($txt) ) {
            foreach ( $txt as $line ) 
                file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->logfile,join(';',$line).lf, FILE_APPEND );
        } else if ( is_object($txt) ) {
            file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->logfile,serialize($txt).lf, FILE_APPEND );
        } else {
            file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->logfile,'Unbekannter Fehler'.lf, FILE_APPEND );
        }
        if ( !$all ) return;
        if ( !empty($this->rc->backtrace[0] )) {
            file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->logfile,'Fehler:'.lf, FILE_APPEND );
            file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->logfile,print_r($this->rc->backtrace[0],true).lf, FILE_APPEND );
            $cnt = count($this->rc->backtrace) - 1;
            file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->logfile,$this->rc->backtrace[$cnt]['line'].':'.$this->rc->backtrace[$cnt]['file'].lf, FILE_APPEND );
        } else {
            file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->logfile,print_r($this->rc,true).lf, FILE_APPEND );
        }
    }
    public function dbFehler($sql,$err) {
            file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->errfile,date('Y-m-d H:i:s').lf, FILE_APPEND );
            file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->errfile,'SQL:'.$sql.lf, FILE_APPEND );
            if ( is_String ($err) ) 
                file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->errfile,'Msg:'.$err.lf, FILE_APPEND );
    }
    public function dbFehler_($sql,$err) {
        //if ( $_SESSION['errlogfile'] ) {
        //if ( 1==1 ) {
            file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->errfile,date("Y-m-d H:i:s \n"), FILE_APPEND );
            file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->errfile,'SQL:'.$sql."\n", FILE_APPEND );
            file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->errfile,'Msg:'.serialize($err)."\n", FILE_APPEND );
        /*    file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->errfile,print_r($this->rc->backtrace[0],true)."\n", FILE_APPEND );
            $cnt=count($this->rc->backtrace);
            for ($i=0; $i<$cnt; $i++) {
                file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->errfile,$this->rc->backtrace[$i]['line'].':'.$this->rc->backtrace[$i]['file']."\n", FILE_APPEND );
            }
            file_put_contents($_SESSION['erppath'].'/crm/tmp/'.$this->errfile,"--------------------------------------------- \n", FILE_APPEND );
        };
        if ( $_SESSION['sql_error'] )
            echo "</td></tr></table><font color='red'><b>$sql : $err</b></font><br>";*/
    }

    public function __construct( $conn ){
        if ( $this->db ) return $this->db;
        try{
            $this->db = new PDO( "pgsql:host=".$conn['dbhost'].";port=".$conn['dbport'].";dbname=".$conn['dbname'].";", 
                                 $conn['dbuser'], $conn['dbpasswd']);
        } catch( PDOException $e ){
            echo $e->getMessage();
        }
    }

    public function query( $sql ){
        $stmt = $this->db->prepare( $sql );
        $this->writeLog( __FUNCTION__,$stmt->queryString );
        $result = $stmt->execute();
        if( !$result ) $this->dbFehler( $stmt->errorInfo() );
        return $result;
    }

    public function getOne( $sql, $json = FALSE ){
        if( $json ) $sql = "SELECT json_agg( json ) FROM (".$sql.") AS json";
        $stmt = $this->db->prepare( $sql );
        $this->writeLog( __FUNCTION__,$stmt->queryString );
        $result = $stmt->execute();
        if( !$result ) $this->dbFehler( $stmt->errorInfo() );
        return  $json ? $stmt->fetch( PDO::FETCH_ASSOC )[json_agg] : $stmt->fetch( PDO::FETCH_ASSOC );
    }

    public function getAll( $sql, $json = FALSE  ){
        if( $json ) $sql = "SELECT json_agg( json ) FROM (".$sql.") AS json";
        $stmt = $this->db->prepare( $sql );
        $this->writeLog( __FUNCTION__,$stmt->queryString );
        $result = $stmt->execute();
        if( !$result ) $this->dbFehler( $stmt->errorInfo() );
        return  $stmt->fetchAll( PDO::FETCH_ASSOC );
    }
    public function getJson($sql) {
        $rs = json_encode($this->getAll($sql));
        return $rs;
    }
    /**********************************************
    * insert - create a new data set
    * IN: $table         - string tablename
    * IN: $fields        - array with fields
    * IN: $values        - array with values
    * IN: $lastInsertId  - string returning last id
    * OUT: last id or TRUE
    **********************************************/
    public function insert( $table, $fields, $values, $lastInsertId = FALSE ){
        $stmt = $this->db->prepare("INSERT INTO $table (".implode(',',$fields).") VALUES (".str_repeat("?,",count($fields)-1)."?) ".( $lastInsertId ? "returning $lastInsertId" : "") );
        $this->writeLog( __FUNCTION__,$stmt->queryString );
        $result = $stmt->execute( $values );
        if( !$result ) $this->dbFehler( $stmt->errorInfo() );
        return $lastInsertId ? $stmt->fetch(PDO::FETCH_ASSOC)[$lastInsertId] : $result; //parent::lastInsertId('id'); doesn't work
    }

    /**********************************************
    * update - modify data set
    * IN: $table  - string name of the table
    * IN: $fields - array with fields
    * IN: $values - array with values
    * IN: $where  - select a data set
    * OUT: true/false
    **********************************************/
    public function update( $table, $fields, $values, $where ){
        $stmt = $this->db->prepare( "UPDATE $table set ".implode( '= ?, ',$fields )." = ? WHERE ".$where );
        $this->writeLog( __FUNCTION__,$stmt->queryString );
        $result = $stmt->execute( $values );
        if( !$result ) $this->dbFehler( $stmt->errorInfo() );
        return $result;
    }

    /*********************************************************
    * IN:  $statement - SQL-String with placeholder (?)
    * IN:  $data      - Array of arrays with values
    * OUT: $result    - boolean with result
    *********************************************************/
    public function executeMultiple( $statement, $data, $transaction = true ){
        //if ( $transaction ) $result = parent::beginTransaction();
        //$this->db->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
        if ( $transaction ) $result = $this->db->beginTransaction();
        $stmt = $this->db->prepare( $statement );
        $this->writeLog( __FUNCTION__,$stmt->queryString );
        $this->writeLog( __FUNCTION__, 'Transaktion:'.$transaction );
        $this->writeLog( __FUNCTION__, $data );
        foreach( $data as $key => $value ){
            if( !$result = $stmt->execute( $value ) ){
                $this->dbFehler( $statement, $stmt->errorInfo() );
                if ( $transaction ) {
                    $this->db->rollback();
                    $this->db->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
                };
                return $result;
            };
        };
        if ( $transaction ) { 
            $this->db->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
            return $this->db->commit(); 
        } else  { return $result; };
    }

    public function begin(){
        $this->writeLog( __FUNCTION__, 'vor Begin' );
        $result = $this->db->beginTransaction();
        $this->writeLog( __FUNCTION__,$result );
        return $result;
    }

    public function commit(){
        $this->writeLog( __FUNCTION__, 'vor Commit' );
        $result = $this->db->commit();
        if( !$result ) $this->dbFehler(  serialize($result) );
        return $result;
    }

    public function rollback(){
        $result = $this->db->rollback();
        $this->writeLog( __FUNCTION__,$result );
        return $result;
    }

}
?>
