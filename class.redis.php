<?php

/**
 * redis Database Class
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

require_once 'Predis/Autoloader.php'; // https://github.com/nrk/predis
Predis\Autoloader::register();

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

class Redis extends MySQL
{
    var $connection_redis; //The redis database connection
	
    /**
     * Constructor
	 * prefix in teh form of 'prefix:'
     */
    function __construct($prefix = '')
    {
   		$this->connection_redis = new Predis\Client(
			array(
				'host' => '127.0.0.1',
				'port' => 6379
			),
			array('prefix' => $prefix)
		);
    }

    /**
     * Destructor
     */
    function __destruct()
    {
        
    }
    
    //-- Generic --//
    // http://redis.io/commands#generic
    function del($key)
    {
        return $this->connection_redis->del($key);
    }
    
    //-- String --//
    // http://redis.io/commands#string
	
	/*function mget($key_array = array())
    {
        return json_decode($this->connection_redis->get($key));
    }*/
    function get($key)
    {
        return json_decode($this->connection_redis->get($key));
    }
    
    /*function mset($key_value_array = array())
    {
        return $this->connection_redis->set($key, json_encode($value));
    }*/
    function set($key, $value)
    {
        return $this->connection_redis->set($key, json_encode($value));
    }
    
    
    
    //-- Hash --//
    // http://redis.io/commands#hash
    
    function hgetall($hash_key)
    {
        $object = $this->connection_redis->hgetall($hash_key);
        foreach ($object as $key => $value) {
	        $object[$key] = json_decode($value);
        }
        return $object;
    }
    
    /*function hmget($hash_key, $field_array = array())
    {
        $object = $this->connection_redis->hmget($hash_key);
        foreach ($object as $key => $value) {
	        $object[$key] = json_decode($value);
        }
        return $object;
    }*/
    
    function hget($hash_key, $field)
    {
        return json_decode($this->connection_redis->hget($hash_key, $field));
    }
    
    function hmset($hash_key, $field_value_array = array())
    {
        foreach ($field_value_array as $key => $value) {
	        $this->hset($hash_key, $key, json_decode($value));
        }
        return;
    }
    
    function hset($hash_key, $field, $value)
    {
        return $this->connection_redis->hset($hash_key, $field, json_encode($value));
    }
};

?>