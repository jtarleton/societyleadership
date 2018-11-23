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


/**
 * User
 */
class User {

	private $pdo, 
    $username, 
		$password, 
		$first, 
		$last, 
		$email;

	/**
	 * Constructor
	 */
	public function __construct() {
    $this->pdo = \SocietyLeadership\SocietyDB::getInstance();
	}

	/**
	 * @param array
	 */
	public function load(array $data) {
		foreach ($data as $k => $v) {
			$this->$k = $v;
		}
	}

  public function setAttribute($attr, $value) {
    $this->$attr = $value;
  }

	/**
	 * @param string
	 */
	public function getAttribute($attr) {
		return $this->$attr;
	}

	public function factoryCreate($row) {
		$obj = new User();
		$obj->load($row);
		return $obj;
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
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$users[$row['username']] = User::factoryCreate($row);
		}
		return $users;
	}

  /**
   * @param array
   * @return bool
   */
  public static function doInsert($data) {
    $user = new User();
    foreach ($data as $k=>$v) {
      $user->setAttribute($k, $v);
    }
    return $user->saveNew();
  }

  /**
   * @return Bool
   */
  public function saveNew() {
  
      $stmt = $this->pdo->prepare('INSERT INTO user (username, 
        first, 
        last, 
        password, 
        email, 
        role, 
        created
        ) VALUES(:username,
        :first,
        :last,
        :password,
        :email,
        :role,
        :created
      )');
      $stmt->bindValue(':username', $this->username, PDO::PARAM_STR);
      $stmt->bindValue(':first', $this->first, PDO::PARAM_STR);
      $stmt->bindValue(':last', $this->last, PDO::PARAM_STR);
      $stmt->bindValue(':password', $this->password, PDO::PARAM_STR);
      $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
      $stmt->bindValue(':role', 'user', PDO::PARAM_STR);
      $stmt->bindValue(':created', date('Y-m-d H:i:s'), 
        PDO::PARAM_STR);
      $stmt->execute();
      $inserted = $stmt->rowCount();
      return ($inserted > 0);
  }
}


/**
 * Controller
 * The Controller's job is to translate incoming requests into outgoing responses. In order to do this, the controller must take request * data and pass it into the Service layer. The service layer then returns data that the Controller injects into a View for 
 * rendering. This view might be HTML for a standard web request; or, it might be something like JSON  
 * for a RESTful API request.
 * Red Flags: My Controller architecture might be going bad if:
 *   The Controller makes too many requests to the Service layer.
 *   The Controller makes a number of requests to the Service layer that don't return data.
 *   The Controller makes requests to the Service layer without passing in arguments. 
 */
function preprocess_view() {
  $_SESSION['flash_msgs'] = null;
  $ini_array = parse_ini_file(__DIR__ . '/society_leadership_config.ini', true);
  // Get a DB connection represented by a PDO instance.
  $pdo = \SocietyLeadership\SocietyDB::getInstance();

  // Do something with the request - run validators, query DB, etc.
  $req = new \stdClass;
  $req->request = $_REQUEST;
  $req->get = $_GET;
  $req->post = $_POST;

  $validator = new \SocietyLeadership\Validator();
  if (!empty($req->post)) {
    // Validate request data - error if incorrect.
    if (!$validator->validateStringEmail($req->post['email'])) {
      $_SESSION['flash_msgs'][] = 'Invalid email.';
    } 

    //Add new user by calling saveNew on a User instance
    if (User::doInsert(
        array(
          'username'=>$req->post['username'],
          'first'=>$req->post['first'],
          'last'=>$req->post['last'],
          'password'=>$req->post['password'],
          'email'=>$req->post['email']

        )
      )
    ) {
      $_SESSION['flash_msgs'][] = 'Added user.'; 
    }
    else {
      $_SESSION['flash_msgs'][] = 'Error adding user.'; 
    }
  }

  // Call data model for dynamic view data based on request
  $allUsers = \SocietyLeadership\User::findByCriteria(array(), true);
  $members = '<table><thead><tr><th>First</th><th>Last</th><th>Username</th><th>Email</th></tr></thead><tbody><tr>';
  foreach ($allUsers as $user) {
  	$members .= sprintf('<tr><td>%s</td><td>%s</tdr><td>%s</td><td>%s</td></tr>', $user->getAttribute('first'), $user->getAttribute('last'), $user->getAttribute('username'), $user->getAttribute('email'));
  }
  $members .= '</tbody></table>';
  
  // Preprocess template/view placeholders with dynamic values
  $output = get_view();
  $output = str_replace('{{flash_msgs}}', implode('<br />', $_SESSION['flash_msgs']), $output);
  $output = str_replace('{{members}}', $members, $output);

  return $output;
}

/**
 * Send HTML header and render page
 * The View's job is to translate data into a visual rendering for response to the Client (ie. web browser or other consumer). 
 * The data will be supplied primarily by the Controller 
 *   Red Flags: My View architecture might be going bad if:
 *   The View contains business logic.
 *   The View contains session logic. 
 */
function render_view() {
  session_start();
  $requestedRoute = $_SERVER['REQUEST_URI'];
  switch ($requestedRoute) {
    case '/member/sign-up':
		echo preprocess_view();
		break;
	case '/report/members':
	default:
		echo preprocess_view();
		break;
  }
}

/**
 * Read HTML template from file system into variable for processing
 * 
 *	index.php?report/members
 * 	index.php?member/sign-up
 *
 *      Please note  .htaccess in web root:
 *
 *      	Options +FollowSymLinks -MultiViews
 *      	# Turn mod_rewrite on
 *      	RewriteEngine On
 *
 *      	RewriteCond %{REQUEST_FILENAME} !-f
 *      	RewriteCond %{REQUEST_FILENAME} !-d
 *      	RewriteRule ^(.*)$ /index.php?/$1 [L]
 */
function get_view() {
  $requestedRoute = $_SERVER['REQUEST_URI'];
  ob_start();
  $is_admin = TRUE;
  if ($is_admin) {
        switch($requestedRoute) {
                case '/member/sign-up':
                        include(__DIR__ . '/signup.html');
                        break;
                case '/report/members':
                default:
                        include(__DIR__ . '/view.html');
                        break;
        }
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