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
    // Find all 
    $sql = 'SELECT * FROM user';
		
    // Or find by a single criteria (username OR email).
    // (Presently this method accepts but 
    // one criterion at a time.)
    // To search multiple criteria
    // please revise this prepared statment and 
    // remove calls to key() and current()

    if (!empty($criteria)) {
      $criteria['field'] = current($criteria);
		  $sql   .= ' WHERE ';
      $field  = key($criteria);
      $sql   .= sprintf('%s = :field', key($criteria)); 
    }
		
    $stmt = $pdo->prepare($sql);
		if (!empty($criteria)) {
      $stmt->bindValue(':field', $criteria[$field], \PDO::PARAM_STR);
    }
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

  public static function authenticate($username, $password) {
    global $ini_array;
    $password = $ini_array['first_section']['admin_config']['password'];
    $user = new User();
    $user->setAttribute('username', $username);
    $user->setAttribute('password', $password);
    if ($user->getAttribute('username') ===  base64_decode($ini_array['first_section']['admin_config']['username'])
      && $user->getAttribute('password') === base64_decode($ini_array['first_section']['admin_config']['password'])
    ) {
      return $user;
    }
    return false;
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
      $stmt->bindValue(':username', $this->username, \PDO::PARAM_STR);
      $stmt->bindValue(':first', $this->first, \PDO::PARAM_STR);
      $stmt->bindValue(':last', $this->last, \PDO::PARAM_STR);
      $stmt->bindValue(':password', $this->password, \PDO::PARAM_STR);
      $stmt->bindValue(':email', $this->email, \PDO::PARAM_STR);
      $stmt->bindValue(':role', 'user', \PDO::PARAM_STR);
      $stmt->bindValue(':created', date('Y-m-d H:i:s'), 
        \PDO::PARAM_STR);
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
  global $ini_array;
  //clear session values.
  $_SESSION['flash_msgs'] = null;
  $_SESSION['post'] = null;

  $validator = new \SocietyLeadership\Validator();
  $validator->setAttribute('executed', null);


  // Preprocess template/view placeholders with dynamic values
  $output = get_view();

  // Do something with the request - run validators, query DB, etc.
  $req = new \stdClass;

  // Always filter raw request data
  foreach ($_POST as $k => $v) {
    $v = trim($v);
    $req->post[$k] = strip_tags($v);
  }

  if (!empty($req->post)) {
    $_SESSION['post'] = $req->post;
    $_SESSION['post']['username_login'] =$req->post['username_login'];
    $_SESSION['post']['username_password'] = $req->post['username_password'];
    if (empty($_SESSION['authenticated'])) {
      if (
        !empty($_SESSION['post']['username_login']) 
          && !empty($_SESSION['post']['username_password'])
      ) {
        $authUser = User::authenticate($_SESSION['post']['username_login'], 
          $_SESSION['post']['username_password']);
        if ($authUser instanceof User) {
          $_SESSION['authenticated'] = true;
          $_SESSION['authenticated']['authUser'] = serialize($authUser);
         
        }
      }
    }

    // Validate request data - error if incorrect.

    $candidateUser = new User();
    $candidateUser->setAttribute('username', $req->post['username']);
    $candidateUser->setAttribute('email', $req->post['email']);
    if (empty($validator->getAttribute('executed'))) {
      if (!$validator->validateStringEmail($req->post['email'])) {
        $_SESSION['flash_msgs'][] = 'Invalid email.';
      } 

      if (!$validator->validateStringLength($req->post['password'])) {
        $_SESSION['flash_msgs'][] = 'Invalid password length. Password should contain a minimum of six characters.';
      }

      if (!$validator->validateUserNoneExists($candidateUser)) {
        $_SESSION['flash_msgs'][] = sprintf('Invalid user input. The user <b>%s</b> or email <b>%s</b> already exists.', 
          $candidateUser->getAttribute('username'), $candidateUser->getAttribute('email')
        );
      }

      if (!$validator->validateStringNotEmpty($req->post['username'])) {
        $_SESSION['flash_msgs'][] = 'Username is a required field.';
      } 

      if (!$validator->validateStringNotEmpty($req->post['first'])) {
        $_SESSION['flash_msgs'][] = 'First name is a required field.';
      }

      if (!$validator->validateStringNotEmpty($req->post['last'])) {
        $_SESSION['flash_msgs'][] = 'Last name is a required field.';
      } 

      // Display request as default values if validation fails.
      $output = str_replace('{{username}}', $_SESSION['post']['username'], $output);
      $output = str_replace('{{first}}', $_SESSION['post']['first'], $output);
      $output = str_replace('{{last}}', $_SESSION['post']['last'], $output);
      $output = str_replace('{{password}}', $_SESSION['post']['password'], $output);
      $output = str_replace('{{email}}', $_SESSION['post']['email'], $output);
    }
    
    // Only add user if the validation error array is still empty.
    if (empty($_SESSION['flash_msgs'])) {
      //Add new user by calling saveNew on a User instance
      if (User::doInsert(
          array(
            'username' => $req->post['username'],
            'first' => $req->post['first'],
            'last' => $req->post['last'],
            'password' => $req->post['password'],
            'email' => $req->post['email']
          )
        )
      ) {
        $_SESSION['flash_msgs'][] = sprintf('Added user <b>%s</b>.', $req->post['username']); 
        $output = str_replace('{{username}}', '', $output);
        $output = str_replace('{{first}}', '', $output);
        $output = str_replace('{{last}}', '', $output);
        $output = str_replace('{{password}}', '', $output);
        $output = str_replace('{{email}}', '', $output);
      }
      else {
        $_SESSION['flash_msgs'][] = 'Error adding user.'; 
      } 
    }
    
    // Search result by email
    $foundUsers = \SocietyLeadership\User::findByCriteria(
      array('email' => $req->post['search_str'])
    );
    
    if (!empty($req->post['search_str'])) {
      if (!empty($foundUsers)) {
        $_SESSION['flash_msgs'][] = sprintf('Found user matching %s.', $req->post['search_str']);
      }    
    }
  }

  // Call data model for dynamic view data based on request
  $allUsers = \SocietyLeadership\User::findByCriteria(array());

  $members = '<table><thead><tr><th>First</th><th>Last</th><th>Username</th><th>Email</th></tr></thead><tbody><tr>';

  foreach ($allUsers as $user) {
  	$members .= sprintf('<tr><td>%s</td><td>%s</tdr><td>%s</td><td>%s</td></tr>', $user->getAttribute('first'), $user->getAttribute('last'), $user->getAttribute('username'), $user->getAttribute('email'));
  }
  $members .= '</tbody></table>';
  

  // Display all flash messages in the session.
  $output = str_replace('{{flash_msgs}}', implode('<br />', $_SESSION['flash_msgs']), $output);

  // Display member table
  $output = str_replace('{{members}}', $members, $output);

  // Display search result by user email.
  if (!empty($foundUsers)) {
    $foundUser = current($foundUsers);
    $output = str_replace('{{search_result}}', 
        sprintf('Found 
          user matching <b>%s</b>: <ul><li>%s</li></ul>', 
          $req->post['search_str'],
          $foundUser->getAttribute('last')
        ), $output);
  }
  elseif (!empty($req->post['search_str'])) {
    $output = str_replace('{{search_result}}', sprintf('No user found for <b>%s</b>', $req->post['search_str']), $output); 
  }
  else {
    $output = str_replace('{{search_result}}', '', $output); 
  }

  // Default values for sign up 
  // form can be empty except on submission.
  $output = str_replace('{{username}}', '', $output);
  $output = str_replace('{{first}}', '', $output);
  $output = str_replace('{{last}}', '', $output);
  $output = str_replace('{{password}}', '', $output);
  $output = str_replace('{{email}}', '', $output);

  $authUser = unserialize($_SESSION['authenticated']['authUser']);

  $output = (!empty($_SESSION['authenticated']['authUser'])) 
    ? str_replace('{{loggedin_user}}', 
      'You are logged in. Welcome.' . $authUser->getAttribute('last')
      , $output)
    : str_replace('{{loggedin_user}}', '', $output);
  return $output;
}

/** ->getAttribute('last'), $output)
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
  case '/member/logout':
    $_SESSION['authenticated'] = null;
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
                case '/member/logout':
                        include(__DIR__ . '/view.html');
                        break;
                case '/member/login':
                        include(__DIR__ . '/login.html');
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