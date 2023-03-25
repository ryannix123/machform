<?php

/*
session table:

CREATE TABLE `session_handler_table` (
`id` varchar(255) NOT NULL,
`data` mediumtext NOT NULL,
`timestamp` int(255) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/

/**
* A PHP session handler to keep session data within a MySQL database
*
* @author 	Manuel Reinhard <manu@sprain.ch>
* @link		https://github.com/sprain/PHP-MySQL-Session-Handler
*/

/* Fork this class to use PDO MySQL */

class MySqlSessionHandler{

    /**
     * a database PDO MySQL connection resource
     * @var resource
     */
    protected $dbConnection;
    
    /**
     * the name of the DB table which handles the sessions
     * @var string
     */
    protected $dbTable;


    public function setDbDetails()
    {
        try {
            $this->dbConnection = new PDO('mysql:host='.MF_DB_HOST.';dbname='.MF_DB_NAME, MF_DB_USER, MF_DB_PASSWORD,
                                    array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true)
                                    );
            $this->dbConnection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $this->dbConnection->query("SET NAMES utf8");
            $this->dbConnection->query("SET sql_mode = ''");
        } catch(PDOException $e) {
            die("Error connecting to the database: ".$e->getMessage());
        }
    }

    /**
     * Inject DB connection from outside
     * @param 	object	$dbConnection	expects PDO MySQL object
     */
    public function setDbConnection($dbConnection)
    {
        $this->dbConnection = $dbConnection;
    }

    /**
     * Inject DB connection from outside
     * @param 	object	$dbConnection	expects PDO MySQL object
     */
    public function setDbTable($dbTable)
    {
        $this->dbTable = $dbTable;
    }

    /**
     * Open the session
     * @return bool
     */
    public function open()
    {
        //delete old session handlers
        $limit = time() - (3600 * 24);

        $query = "DELETE FROM " . $this->dbTable . " WHERE timestamp < " . $limit;
        $sth = $this->do_query($query,array(),$this->dbConnection);
        
        if ($sth === false)
            return false;
        else 
            return true;
    }

    /**
     * Close the session
     * @return bool
     */
    public function close()
    {
        //make PDO conenction null 
        $this->dbConnection = null;
        return true;
    }

    /**
     * Read the session
     * @param int session id
     * @return string string of the sessoin
     */
    public function read($id)
    {
        $query = "SELECT data FROM " . $this->dbTable . " WHERE id = '" . $id . "'";
        $sth = $this->do_query($query,array(),$this->dbConnection);
        $row = $this->do_fetch_result($sth);
        
        if (!empty($row)) {
            return $row['data'];
        } else {
            return '';
        }
    }

    /**
     * Write the session
     * @param int session id
     * @param string data of the session
     */
    public function write($id, $data)
    {

        $query = "REPLACE INTO `". $this->dbTable ."` VALUES('" . $id . "', '" . $data . "', '" . time() . "')";        
        $sth = $this->do_query($query,array(),$this->dbConnection);

        if ($sth === false)
            return false;
        else 
            return true;
    }

    /**
     * Destoroy the session
     * @param int session id
     * @return bool
     */
    public function destroy($id)
    {
        $query = "DELETE FROM `".$this->dbTable."` WHERE `id` = '". $id ."'";
        $sth = $this->do_query($query,array(),$this->dbConnection);

        return $sth;
    }

    /**
     * Garbage Collector
     * @param int life time (sec.)
     * @return bool
     * @see session.gc_divisor      100
     * @see session.gc_maxlifetime 1440
     * @see session.gc_probability    1
     * @usage execution rate 1/100
     *        (session.gc_probability/session.gc_divisor)
     */
    public function gc($max)
    {
        $query = "DELETE FROM `{$this->dbTable}` WHERE `timestamp` < '". (time() - intval($max))."'";
        $sth = $this->do_query($query,array(),$this->dbConnection);

        if ($sth === false)
            return false;
        else 
            return true;
    }


    private function do_query($query,$params,$dbh){
		$sth = $dbh->prepare($query);
		try{
			$sth->execute($params);
		}catch(PDOException $e) {
            error_log("MySQL Error. Query Failed: ".$e->getMessage());
            
            $sth->debugDumpParams();
			echo("Query Failed: ".$e->getMessage());
            return false;            
		}
		
		return $sth;
    }

    private function do_fetch_result($sth){
		return $sth->fetch(PDO::FETCH_ASSOC);	
	}
   
}
?>