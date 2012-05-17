<?
/**
Session Variables:

**/

//require_once 'fct/Predis/Autoloader.php'; // https://github.com/nrk/predis
//Predis\Autoloader::register();


if (isset($_COOKIE['PHPSESSID'])) {
	session_id($_COOKIE['PHPSESSID']);
}
// Code for Session Cookie workaround for swf uploaders
else if (isset($_REQUEST["PHPSESSID"])) {
	session_id($_REQUEST["PHPSESSID"]);
}
session_start();
require_once "class.db.php";

class Session
{
	private $db;
	private $salt = "001ff7dc5e819102b607da4f6f340640";
	
	public	$id;
	public $domain = "";
	public $cookie = array();
	 
  	// Class constructor 
	function __construct(){
		global $database;  //The database connection
		$this->db = $database;
		
		$this->domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
		ini_set('session.cookie_domain',$this->domain);
		ini_set('session.use_only_cookies','1');
		
		$this->id = (isset($_COOKIE['PHPSESSID'])) ? $_COOKIE['PHPSESSID'] : 0;
		
		//if(!isset($_SERVER['HTTPS'])) putenv('HTTPS=off');
		
		$this->redis = new Predis\Client(
			array(
				'host' => '127.0.0.1',
				'port' => 6379
			),
			array('prefix' => 'session:')
		);
		
		$data = $this->redis->get($this->id);
		
		if (!$data) {
			$this->create();	
		} else {
			$json = json_decode($data);
			foreach ($json as $key => $value) {
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
		$this->cookie["USER_ID"] = 0;
		//$this->cookie[""] = 0;
		
		$this->save();
	}
	
	function save() {
		$this->redis->set($this->id, json_encode($this->cookie));	
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
		
		$query = "SELECT * FROM users WHERE ( user_name = '{{user_name}}' OR user_email = '{{user_email}}' ) AND user_password = '{{user_password}}'";
		$result = $this->db->query($query, array('user_name' => $user, 'user_email' => $user, 'user_password' => $this->generateHash($pass)));
		if (!$result) return false;	// user / pass combo not found
		
		$r = mysql_fetch_assoc($result);
		
		// load usesr data
		$this->cookie["USER_ID"] = $r['user_ID'];
		$this->cookie["USER_NAME"] = $r['user_name'];
		
		$this->load();
		$this->save();
		return true;
	}
	
	function logout() {
		$this->clear();
		
		//set_cookie_fix_domain('PHPSESSID', session_id(), time()-COOKIE_EXPIRE, COOKIE_PATH, $this->domain);
	}
	
	function generateHash($plainText) {
		return sha1($this->salt . $plainText);
	}

	
};


$session = new Session;

?>