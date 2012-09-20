<?
/**
Session Variables:

**/

//cooking variables
if (!defined("COOKIE_EXPIRE")) define("COOKIE_EXPIRE", 60*60*2*1); //expire in 2 hour (in sec)
if (!defined("COOKIE_PATH")) define("COOKIE_PATH", "/");  // Avaible in whole domain

// PHPSESSION
if (isset($_COOKIE['PHPSESSID'])) {
	session_id($_COOKIE['PHPSESSID']);
}
// Code for Session Cookie workaround for swf uploaders
else if (isset($_REQUEST["PHPSESSID"])) {
	session_id($_REQUEST["PHPSESSID"]);
}


session_start();
require_once "class.db.php";
require_once "class.redis.php";

class Session {
	private $db;
	private $redis;
	
	public $id;
	public $domain = "";
	public $cookie = array();
	 
  	// Class constructor 
	function __construct(){
		global $database;  //The database connection
		$this->db = $database;
		$this->redis = new Redis('session:');
		
		$this->domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
		ini_set('session.cookie_domain',$this->domain);
		ini_set('session.use_only_cookies','1');
		
		$this->id = (isset($_COOKIE['PHPSESSID'])) ? $_COOKIE['PHPSESSID'] : 0;
		
		//if(!isset($_SERVER['HTTPS'])) putenv('HTTPS=off');
		
		
		
		$data = $this->redis->get($this->id);
		
		if (!$data) {
			$this->create();	
		} else {
			foreach ($data as $key => $value) {
				$this->cookie[$key] = $value;
			}
		}
		$this->load();
		
		//if(isset($_SESSION['URL'])) $_SESSION['LAST_URL'] = $_SESSION['URL'];
		//if(getenv("REQUEST_URI") != '/login') $_SESSION['URL'] = getenv("REQUEST_URI");
		//echo $_SESSION['LAST_URL']." = ".$_SESSION['URL'];
  	}
  
	function __destruct() {
		  
  	}
	
	function create() {
		$this->cookie["user_ID"] = 0;
		$this->cookie["company_ID"] = 0;
		$this->cookie["user_level"] = 0;
		
		$this->save();
	}
	
	function save() {
		$this->redis->set($this->id, $this->cookie);	
	}
	
	function load() {
		foreach ($this->cookie as $key => $value) {
			if (!defined($key)) define($key, $value);
		}
	}
	
	function clear() {
		$this->cookie = array();
		$this->create();
	}
	
	// reset session_ID for security
	function login($user, $pass) {
		
		$query = "SELECT * FROM users WHERE user_email = '{{user_email}}' LIMIT 0,1";
		$result = $this->db->query($query, array('user_email' => $user));
		if (!$result) return false;	// user / pass combo not found
		
		$r = mysql_fetch_assoc($result);
		
		if (!bcrypt_check($pass, $r['password'])) {
			return false;	// pass doesn't match
		}
		
		// load usesr data
		$return = array();
		$return["user_ID"] 				= $this->cookie["user_ID"] 		= $r['user_ID'];
		$return["company_ID"] 			= $this->cookie["company_ID"] 	= $r['company_ID'];
		$return["user_name"] 			= $r['user_name'];
		$return["password_timestamp"] 	= $r['password_timestamp'];
		
		$this->cookie["user_level"] 	= $r['user_level'];
		
		$this->load();
		$this->save();
		return $return;
	}
	
	function logout() {
		$this->clear();
		
		set_cookie_fix_domain('PHPSESSID', session_id(), $_SERVER['REQUEST_TIME']-COOKIE_EXPIRE, COOKIE_PATH, $this->domain);
	}
	

	
};

$session = new Session;


?>