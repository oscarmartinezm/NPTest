<?php

namespace Libs;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * DataBase class
 *
 * This class file is the DataBase connector helper
 * @author Oscar Martinez M.
 */
class DataBase {

    private $connection;
    private static $user;
    private static $password;
    private static $schema;
    private static $server;
    
    private static $singleton;

    /**
     * Creates a new database instance
     * @param string $user      User name of the database
     * @param string $password  Password of the database
     * @param string $schema    Schema to connect
     * @param string $server    Server to connect
     */
    public function __construct($user = null, $password = null, $schema = null, $server = '127.0.0.1') {
        //self::loadConfig();
        if (is_null($user) || is_null($password)) {
            $user = self::$user;
            $password = self::$password;
        }
        if (is_null($schema)) {
            $schema = self::$schema;
        }
        if (is_null($server)) {
            $server = self::$server;
        }
        try {
            $this->connection = new \mysqli($server, $user, $password, $schema);
            mysqli_query($this->connection, "SET NAMES 'utf8'");
            if (mysqli_connect_errno()) {
                throw new \Exception('Database connection error: ' . $this->connection->error);
            }
        } catch (\Exception $exc) {
            if (mysqli_connect_errno()) {
                throw new \Exception('Database connection error: ' . $this->connection->error);
            }
        }
    }

    /**
     * detroy the instance with the database     
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * Close the instance with the database     
     */
    public function close() {
        if ($this->connection) {
            try {
                $this->connection->close();
                $this->connection = null;
            } catch (\Exception $ex) {
                
            }
        }
    }

    /**
     * Escape string for security
     * @param string $string    string to esccape
     */
    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }

    //************** EXECUTE SENTENCES **************\\

    /**
     * Make the query
     * @param  string $query           querry to execute
     * @param  array  $prepStmtValues  querry params
     * @return array
     */
    public function query($query, $prepStmtValues = null, $parmsTypeDefinition = null) {
        //die($query);
        if (!$query || !is_string($query)) {
            throw new \Exception('String query is empty or invalid');
        }
        try {
            if (is_array($prepStmtValues)) {
                $result = $this->queryPrepStmt($query, $prepStmtValues, $parmsTypeDefinition);
            } else {
                $result = $this->connection->query($query, MYSQLI_USE_RESULT);
            }
            if (!$result) {
                throw new \Exception($this->errorMessage('Invalid query: ', $query));
            }
            $resultSet = self::loadResultValues($result);
            $result->close();
        } catch (\Exception $exc) {
            throw $exc;
        }
        return $resultSet;
    }

    /**
     * Prepare the query
     * @param  string $query           query to execute
     * @param  array  $prepStmtValues  query params
     * @return array
     */
    private function queryPrepStmt($query, $prepStmtValues, $parmsTypeDefinition = null) {
        if (is_null($parmsTypeDefinition)) {
            $parmsTypeDefinition = '';
            for ($i = 0; $i < count($prepStmtValues); ++$i) {
                $parmsTypeDefinition .= 's';
            }
        }
        $statement = $this->connection->prepare($query);
        if (!empty($prepStmtValues)) {
            $parameters_references = array();
            foreach ($prepStmtValues as $key => $parameter) {
                $parameters_references[] = &$prepStmtValues[$key];
            }
            call_user_func_array('mysqli_stmt_bind_param', array_merge(array($statement, $parmsTypeDefinition), $parameters_references));
        }
        $statement->execute();
        return $statement->get_result();
    }

    /**
     * Execute the query
     * @param  string $query           querry to execute
     * @param  array  $prepStmtValues  querry params
     * @return array
     */
    public function execute($query, $prepStmtValues = null, $parmsTypeDefinition = null) {
        //die($query);
        if (!$query || !is_string($query)) {
            throw new \Exception('String query is empty or invalid');
        }
        try {
            if (!is_null($prepStmtValues)) {
                $result = $this->executePrepStmt($query, $prepStmtValues, $parmsTypeDefinition);
            } else {
                $result = $this->connection->query($query);
                if (!$result) {
                    throw new \Exception($this->errorMessage('Invalid query: ', $query));
                }
            }
        } catch (\Exception $exc) {
            throw $exc;
        }
        return $this->connection->affected_rows;
    }

    /**
     * Execute the querry wtih values
     * @param  string $query           querry to execute
     * @param  array  $prepStmtValues  querry params
     * @return array
     */
    private function executePrepStmt($query, $prepStmtValues, $parmsTypeDefinition = null) {
        try {
            if (is_null($parmsTypeDefinition)) {
                $parmsTypeDefinition = '';
                for ($i = 0; $i < count($prepStmtValues); ++$i) {
                    $parmsTypeDefinition .= 's';
                }
            }
            $statement = $this->connection->prepare($query);
            if (!$statement) {
                throw new \Exception($this->errorMessage('Invalid query: ', $query));
            }
            if (!empty($prepStmtValues)) {
                $parameters_references = array();
                foreach ($prepStmtValues as $key => $parameter) {
                    $parameters_references[] = &$prepStmtValues[$key];
                }
                call_user_func_array('mysqli_stmt_bind_param', array_merge(array($statement, $parmsTypeDefinition), $parameters_references));
            }
            $statement->execute();
            return $statement->get_result();
        } catch (\Exception $exc) {
            throw $exc;
        }
    }

    //************** TRANSACTION MANAGER **************\\

    /**
     * Begins a database transaction
     * @return void
     */
    public function transactionBegin() {
        if (!$this->connection->autocommit(false)) {
            throw new \Exception("DataBase Transaction: begin transaction not successful");
        }
    }

    /**
     * Commits and ends a database transaction
     * @return void
     */
    public function transactionCommit() {
        if (!$this->connection->commit()) {
            throw new \Exception("DataBase transaction error: commit not successful");
        }
        $this->connection->autocommit(true);
    }

    /**
     * Rollback and ends a database transaction
     * @return void
     */
    public function transactionRollback() {
        if (!$this->connection->rollback()) {
            throw new \Exception("DataBase transaction error: rollback not successful");
        }
        $this->connection->autocommit(true);
    }

    //************** EXECUTE SENTENCES **************\\

    /**
     * Check if a value exist doing a query     
     * @param  string   $query  query for execution
     * @return boolean
     */
    public function exists($query, $prepStmtValues = null, $parmsTypeDefinition = null) {
        $exists = false;
        if (!$query || !is_string($query)) {
            throw new \Exception('String query is empty or invalid');
        }
        try {
            if (is_array($prepStmtValues)) {
                $result = $this->queryPrepStmt($query, $prepStmtValues, $parmsTypeDefinition);
            } else {
                $result = $this->connection->query($query, MYSQLI_USE_RESULT);
            }
            if (!$result) {
                throw new \Exception($this->errorMessage('Invalid query: ', $query));
            }
            while ($result->fetch_array(MYSQLI_ASSOC)) {
                $exists = true;
                break;
            }
            $result->close();
        } catch (\Exception $exc) {
            throw $exc;
        }
        return $exists;
    }

    /**
     * Get a specific value of the query
     * @param  string   $query  query for execution
     * @return int
     */
    public function getValue($query) {
        try {
            $value = $this->queryValue($query);
        } catch (\Exception $exc) {
            throw $exc;
        }
        return $value;
    }

    /**
     * Get a specific number of the query
     * @param  string   $query  query for execution
     * @return int
     */
    public function itemCount($query) {
        try {
            $value = $this->queryValue($query);
        } catch (\Exception $exc) {
            throw $exc;
        }
        return $value;
    }

    /**
     * Get a specific number of a table of the query
     * @param  string   $tabe  table to count
     * @return int
     */
    public function itemCountTable($tabla) {
        try {
            $value = $this->queryValue("SELECT COUNT(*) FROM $tabla");
        } catch (\Exception $exc) {
            throw $exc;
        }
        return $value;
    }

    /**
     * Get last inserted Id 
     */
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }

    public function convertToSimpleQuery($query, $values) {
        $offset = 0;
        foreach ($values as $value) {
            $pos = strpos($query, '?', $offset);
            if (!is_null($value)) {
                $query = substr_replace($query, "'$value'", $pos, 1);
            } else {
                $query = substr_replace($query, 'null', $pos, 1);
            }
            $offset = $pos;
        }
        return $query;
    }

    /**
     * Get a specific value of the query
     * @param  string   $query  the query to be executed
     * @return string
     */
    private function queryValue($query) {
        $result = $this->connection->query($query, MYSQLI_USE_RESULT);
        if (!$result) {
            throw new \Exception($this->errorMessage('Invalid query: ', $query));
        }
        $value = '';
        while ($row = $result->fetch_row()) {
            $value = $row[0];
        }
        return $value;
    }

    /**
     * Load the values result of a query
     * @param  array   $result  result of the query execution
     * @return array
     */
    private static function loadResultValues($result) {
        $rows = array();
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            array_push($rows, $row);
        }
        return $rows;
    }

    /**
     * Error message for a query execution
     * @param  string   $message     message  showed
     * @param  string   $query       query executed 
     * @return array
     */
    private function errorMessage($message, $query = '') {
        if (defined('DEBUG_DB_SHOW_ERROR') && DEBUG_DB_SHOW_ERROR === true) {
            $message .= $this->connection->error;
        }
        if (defined('DEBUG_DB_SHOW_QUERY') && DEBUG_DB_SHOW_QUERY === true) {
            if (strlen($query) > 2048) {
                $query = '';
            }
            $message .= "<br>$query";
        }
        return $message;
    }
    
    public static function get($user = null, $password = null, $schema = null, $server = '127.0.0.1'){
        if(is_null(self::$singleton)){
            self::$singleton = new DataBase();
        }
        return self::$singleton;
    }
    

}

?>