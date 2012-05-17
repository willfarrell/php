<?php

/**
 * MySQL Database Class
 *
 * PHP Version 5
 *
 * @category  N/A
 * @package   N/A
 * @author    will Farrell <will.farrell@gmail.com>
 * @copyright 2001 - 2012 willFarrell.ca
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version   GIT: <git_id>
 * @link      http://willFarrell.ca
 */

/*

Additional Notes:
- Make sure the DB_USER only has permission for command your using

*/


define('DB_SERVER','localhost');
define('DB_NAME','stockbet_db');
define('DB_USER','root');
define('DB_PASS','localhost');

/**
 * Database
 *
 * @category  N/A
 * @package   N/A
 * @author    Original Author <author@example.com>
 * @author    Another Author <another@example.com>
 * @copyright 2001 - 2011 willFarrell.ca
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version   Release: <package_version>
 * @link      http://willFarrell.ca
 */

class Database
{
    var $connection; //The MySQL database connection

    /**
     * Constructor
     */
    function __construct()
    {
        $this->_connect();
    }

    /**
     * Destructor
     */
    function __destruct()
    {
        $this->_close();
    }

    /**
     * create db connection
     *
     * @return null
     * @access private
     */
    private function _connect()
    {
        $this->connection = mysql_connect(DB_SERVER, DB_USER, DB_PASS) or die(mysql_error());
        mysql_select_db(DB_NAME, $this->connection) or die(mysql_error());
    }
    
    /**
     * close db connection
     *
     * @return null
     * @access private
     */
    private function _close()
    {
         mysql_close($this->connection);
    }

    /**
     * Connection check
     * pings mysql service to see if still running,
     * if not, connects again
     *
     * @return true
     * @aceess puiblic
     */
    function ping()
    {
        if (!mysql_ping($this->connection)) {
            $this->_connect();
            $this->ping();
        } else {
            return true;
        }
    }


    /**
     *    MySQL Query
     *    runs the query through the MyQSL service
     *
     * @param string $query - MySQL Query
     *
     * @return MySQL Object
     *    @aceess    puiblic
     */
    private function _run($query)
    {
        $return = mysql_query($query, $this->connection);

        if (mysql_error() && DEBUG) {
            echo $query."<br>";
            echo "<b>".mysql_error()."</b><br>";
        }
        return $return;
    }

    /**
     *    checks the output of a MySQL query
     *
     * @param object $result MySQL Object
     *
     * @return MySQL Object
     *    @aceess    puiblic
     */
    function resultCheck($result)
    {
        if (!$result || (mysql_num_rows($result) < 1)) {
            return null;
        }
        return $result;
    }

    //** Functions **//
    /**
     * cleans all values of SQL injections
     *

     * @param array $array array of values to interact with the DB
     *
     * @return array of cleaned values
     * @aceess puiblic
     */
    function cleanArray($array)
    {
        $array = array_map('trim', $array);
        $array = array_map('stripslashes', $array);
        $array = array_map('mysql_real_escape_string', $array);
        return $array;
    }

    /**
     * FUTURE: cleans all values of SQL injections
     *
     * @param string $query - MySQL query
     *
     * @return cleaned query
     * @aceess puiblic
     */
    function cleanQuery($query)
    {
        //echo $q;
        //preg_match_all("/[;]?(DROP TABLE|TRUNCATE)[\s]+/", $q, $matches);
        //print_r($matches);
        //if (count($matches) > 2) return null;
        return $query;
    }

    /**
     * Custom Query
     * runs a templates written query
     *l
     * @param string $query       MySQL Query
     * @param array  $value_array replace {{$key}} with $value in custom query
     *
     * @return object $object MySQL Object
     * @aceess    puiblic
     */
    function query($query, $value_array = null)
    {
        if ($value_array && is_array($value_array)) {
            $query = preg_replace("/{{([\w]+)}}/e", "\$value_array['\\1']", $query);
            /*foreach ($value_array as $key => $value) {
                $query = preg_replace("/{{".$key."}}/i", $value, $query);
            }*/
        } else {
            //$q = $this->cleanQuery($q);
        }
        $check = (substr($query, 0, 6) == 'SELECT')?true:false;
        $result = $this->_run($query);
        $result = ($check)?$this->resultCheck($result):$result;
        return $result;
    }

    /**
     * Select Query
     *
     * @param string $table        table where rows will be deleted
     * @param array  $where_array  `$key` = $value WHERE parameters
     * @param array  $select_array `$key` = $value SELECT parameters
     * @param array  $order_array  `$key` = $value ORDER BY parameters
     *
     * @return ID of affected row
     * @access public
     */
    function select($table, $where_array = null, $select_array = null, $order_array = null)
    {
        $where = '';
        if ($where_array && is_array($where_array)) {
            $where_array = $this->cleanArray($where_array);
            $i = 0;

            foreach ($where_array as $key => $value) {
                $where .= ($i)?"AND ":'';
                $where .= "`$key` = '$value' ";
                $i++;
            }
            $where = ($where)?"WHERE ".$where:'';
        }

        $select = '';
        if ($select_array && is_array($select_array)) {
            $select_array = $this->cleanArray($select_array);
            foreach ($select_array as $value) {
                $select .= ($select)?", ":'';
                $select .= "$value";
            }
            $select = ($select) ? $select : "*";
        } else {
            $select = "*";
        }

        $order = '';
        if ($order_array && is_array($order_array)) {
            $order_array = $this->cleanArray($order_array);
            foreach ($order_array as $value) {
                $order .= ($order)?", ":'';
                $order .= "$value";
            }
            $order = ($order)?"ORDER BY ".$order:'';
        }

        $query = "SELECT $select FROM `$table` $where $order";
        $result = $this->_run($query);
        $result = $this->resultCheck($result);
        return $result;
    }

    /**
     * Insert Query
     *
     * @param string $table        table where rows will be deleted
     * @param array  $set_array    `$key` = $value SET parameters
     * @param array  $update_array `$key` = $value ON DUPLICATE UPDATE parameters
     *
     * @return ID of affected row
     * @access public
     */
    function insert($table, $set_array, $update_array = null)
    {
        if ($set_array && is_array($set_array)) {
            $set_array = $this->cleanArray($set_array);
            $set = '';
            foreach ($set_array as $key => $value) {
                $set .= ($set)?", ":'';
                $set .= "`$key` = '$value' ";
            }
            if ($update_array && is_array($update_array)) {
                $update_array = $this->cleanArray($update_array);
                $update = '';
                foreach ($update_array as $key => $value) {
                    $update .= ($update)?", ":'';
                    $update .= "`$key` = '$value' ";
                }
            } else {
                $update = $set;
            }
            $set = ($set)?"SET ".$set:'';
            $update = ($update)?"ON DUPLICATE KEY UPDATE ".$update:'';
        }

        $query = "INSERT INTO `$table` $set $update";
        $this->_run($query);
        return mysql_insert_id();
    }

    /**
     * Update Query
     *
     * @param string $table       table where rows will be deleted
     * @param array  $set_array   `$key` = $value SET parameters
     * @param array  $where_array `$key` = $value WHERE parameters
     *
     * @return number of affected rows
     * @access public
     */
    function update($table, $set_array, $where_array)
    {
        if ($set_array && is_array($set_array)) {
            $set_array = $this->cleanArray($set_array);
            $i = 0;
            $set = '';
            foreach ($set_array as $key => $value) {
                $set .= ($i)?", ":'';
                $set .= "`$key` = '$value' ";
                $i++;
            }
            $set = ($set)?"SET ".$set:'';
        }

        if ($where_array && is_array($where_array)) {
            $where_array = $this->cleanArray($where_array);
            $i = 0;
            $where = '';
            foreach ($where_array as $key => $value) {
                $where .= ($i)?"AND ":'';
                $where .= "`$key` = '$value' ";
                $i++;
            }
            $where = ($where)?"WHERE ".$where:'';
        }

        $query = "UPDATE `$table` $set $where";
        $this->_run($query);
        return mysql_affected_rows();
    }

    /**
     * Delete Query
     *
     * @param string $table       table where rows will be deleted
     * @param array  $where_array array of `$key` = $value parameters
     *
     * @return number of affected rows
     * @access public
     */
    function delete($table, $where_array)
    {
        if ($where_array && is_array($where_array)) {
            $where_array = $this->cleanArray($where_array);
            $i = 0;
            $where = '';
            foreach ($where_array as $key => $value) {
                $where .= ($i)?"AND ":'';
                $where .= "`$key` = '$value' ";
                $i++;
            }
            $where = ($where)?"WHERE ".$where:'';
        }
        $query = "DELETE FROM `$table` $where";
        $this->_run($query);
        return mysql_affected_rows();
    }

};

$database = new Database;

?>