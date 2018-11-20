<?php 
namespace SocietyLeadership;

define('DB_DSN',  'mysql:host=127.0.0.1;dbname=societyleadership');
define('DB_USER', 'societyleadershi');
define('DB_PASS', 'societyleadershi');

/**
 * @package SocietyLeadership
 * 
 * society_lib.php
 */
class SocietyDB 
{
   
    private static $objInstance;
   
    /**
     * Class Constructor - Create a new database connection if one doesn't exist
     * Set to private so no-one can create a new instance via ' = new SocietyDB();'
     */
    private function __construct() {
    }
   
    /**
     * Like the constructor, we make __clone private so nobody can clone the instance
     */
    private function __clone() {
    }
   
    /**
     * Returns DB instance or create initial connection
     * @param
     * @return $objInstance;
     */
    public static function getInstance() {
           
        if(!self::$objInstance){
            self::$objInstance = new \PDO(DB_DSN, DB_USER, DB_PASS);
            self::$objInstance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
       
        return self::$objInstance;
   
    }

    /**
     * Passes on any static calls to this class onto the singleton PDO instance
     * @param $chrMethod, $arrArguments
     * @return $mix
     */
    final public static function __callStatic( $chrMethod, $arrArguments ) {
           
        $objInstance = self::getInstance();
       
        return call_user_func_array(array($objInstance, $chrMethod), $arrArguments);
       
    } 
}



class User {

	private $username, 
		$password, 
		$first, 
		$last, 
		$email;

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	/**
	 * @param array
	 */
	public function load(array $data) {
		foreach ($data as $k => $v) {
			$this->$k = $v;
		}
	}
	/**
	 * @param array
	 * @return array
	 */
	public function findByCriteria(array $criteria = array()) {
		$pdo = \SocietyLeadership\SocietyDB::getInstance();
		//Find by username
		//$where = ' WHERE username = :username';
		$stmt = $pdo->prepare('SELECT * FROM user');
		//$stmt->bindValue(':username', $criteria['username'], PDO::PARAM_STR);
		$stmt->execute();
		$users = array();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$users[$row['username']] = new User($row);
		}
		return $users;
	}
}




/**
 * Replace template placeholders with dynamic values
 */
function preprocess_view() {
  $ini_array = parse_ini_file(__DIR__ . '/society_leadership_config.ini', true);
  $pdo = \SocietyLeadership\SocietyDB::getInstance();
  $allUsers = \SocietyLeadership\User::findByCriteria(array(), true);
  $members = '<table><thead><tr><th>First</th><th>Last</th><th>Username</th><th>Email</th></tr></thead><tbody><tr>';
  foreach ($allUsers as $user) {
  	$members .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', $user->getAttribute('first'), $user->getAttribute('last'), $user->getAttribute('username'), $user->getAttribute('email'));
  }
  $members .= '</tbody></table>';

  $output = get_view();
  $output = str_replace('{{members}}', $members, $output);

  return $output;
}
/**
 * Send HTML header and render page
 */
function render_view() {
  echo preprocess_view();
}
/**
 * Read HTML template from file system into variable for processing
 */
function get_view() {
  ob_start();
  $is_admin = TRUE;
  if($is_admin) {
  	include(__DIR__ . '/view.html'); 
  }
  else {
  	//Insufficient Privileges
  	include(__DIR__ . '/view.html'); 
  }
  return ob_get_clean();
}

/**

	Create a user login page where an existing user can log in using their username and
	password. Also allow for the users to log out.

 */