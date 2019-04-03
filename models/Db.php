<?php namespace Model\Core;

use PDO;

class DbCmd
{

    private $dbconn;
    private $d;
    private $sql;

    public function __construct( DbConnect $dbconn, $sql ){
        $this->dbconn = $dbconn;
        $this->sql = $sql;
        $this->d = $dbconn->getPDO()->prepare( $sql );
    }

    public function __call($name, $arguments)
    {
        $resp = null;
        $retry = false;

        if( $name == 'bindParam' ){
            $name = 'bindValue';
        }

        try{
            $cmd = call_user_func_array(array($this->d, $name), $arguments);
            $resp = $cmd;
        }catch( \Exception $e ){
            if( strstr($e->getMessage(),'2006 MySQL server has gone away')  ){
                $retry = true;
            }else{
                //MESSAGE SOMEONE ABOUT DODGY SQL
            }
        }

        if( $retry ){
            try{
                $this->dbconn->reconnect();
                $this->d = $this->dbconn->getPDO()->prepare( $this->sql );
                call_user_func_array(array($this->d, $name), $arguments);
            }catch( \Exception $e ){}
        }


        return $resp;
    }

}

class DbConnect
{

    private $pdo;
    private $write;
    private $db;
    private $db_user;
    private $db_pass;


    public function reconnect(){
        $servers = array();
        $write = $this->write;
        $db = $this->db;
        $db_user = $this->db_user;
        $db_pass = $this->db_pass;
        if( gethostname() == \Config::getLocalMachine() ){
            $servers[] = array( \Config::getDbDevHost() , \Config::getDbDevPort() );
        }else {
            if ($this->write) {
                $servers[] = array( \Config::getDbWriteHost() ,\Config::getDbWritePort() );
            } else {
                $servers[] = array( \Config::getDbReadHost() ,\Config::getDbReadPort() );
            }
        }
        $connected = false;
        $connection = false;
        if( gethostname() == 'homestead' ){
            $write = true;
        }
        $errors = array();
        foreach( $servers as $server ){
            if( !$connected ) {
                try {
                    $conn = new \PDO('mysql:host=' . $server[0] . ';port='.$server[1].';dbname=' . $db, $db_user, $db_pass, array(
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_TIMEOUT => ( ( gethostname() == 'homestead' ) ? '5' : '2' ),
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                    ));
                    if( $write ){
                        $connected = true;
                        $connection = $conn;
                    }else{
                        $connected = true;
                        $connection = $conn;
                    }
                }catch ( \Exception $e ){
                    $errors[] = $e->getMessage();
                }
            }
        }
        if( !$connection ){
            $error_message = array('Database Error, please try again later');
            $ip = (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
            if( strstr( \Config::getDbErrorIP(), $ip) ) {
                $error_message[] = $errors;
                $error_message[] = $_SERVER;
            }
            \Output::error( $error_message,500);
        }
        $this->pdo = $connection;
    }

    public function __construct($write, $db, $db_user, $db_pass ){
        $this->write = $write;
        $this->db = $db;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->reconnect();
    }

    /**
     * @return PDO
     */
    public function getPDO(){
        return $this->pdo;
    }

    public function __call($name, $arguments)
    {
        $cmd = null;
        if( $name == 'prepare' ){
            $cmd = new DbCmd( $this , $arguments[0] );
        }else {
            $cmd = call_user_func_array(array($this->pdo, $name), $arguments);
        }
        return $cmd;
    }

}

class Db {

    static private $read = '';
    static private $write = '';

    /**
     * @return PDO
     */
    static public function read(){
        if( gettype(self::$read) == 'string' ) {
            self::$read = new DbConnect( false, \Config::getDbDataBase(),\Config::getDbReadUser(),\Config::getDbReadPass());
        }
        return self::$read;
    }

    public static function closeAll(){
        self::$read = null;
        self::$write = null;
    }

    /**
     * @return PDO
     */
    static public function write(){
        if( gettype(self::$write) == 'string' ) {
            self::$write = new DbConnect( true,  \Config::getDbDataBase(),\Config::getDbWriteUser(),\Config::getDbWritePass());
        }
        return self::$write;
    }


}