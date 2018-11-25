<?php

namespace SocietyLeadership;

interface FlashMessage {
	public function displayFlashMsgs();
}

class Request  {
	/**
	 * @var $post array
	 */
	private $post;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->createFromGlobals();
	}
	/**
	 * @return Bool
	 */
	public function hasPostParameters() {
		return !empty($this->post);
	}
	/**
	 * @return array
	 */
	public function getPostParameters() {
		return $this->post;
	}
	/**
	 * @param string
	 * @return mixed
	 */
	public function getPostParameter($param) {
		return $this->post[$param];
	}
	/**
	 * @return object
	 */
	public function createFromGlobals() {
		// Always filter raw request data
		foreach ($_POST as $k => $v) {
			$v = trim($v);
			$this->post[$k] = strip_tags($v);
		}
		return $this;
	}
}

class Response {
	/**
	 * @var string
	 */
	private $output; //the response output will be a string.
	
	/**
	 * Constructor
	 */
	public function __construct() {
		if (!isset($this->output)) {
			$this->output = get_view(); 
		}
	}
	/**
	 * @param string
	 * @return mixed
	 */
	public function getAttribute($attr) {
		return $this->$attr;
	}
	/**
	 * @param string
	 * @return object
	 */
	public function doReplace($tokenName, $replaceWith){
		$this->output = str_replace($tokenName, $replaceWith, $this->output);
		return $this;
	} 
	// 
}

class MenuUtils {

	/**
	 * Add welcome text to response.
	 */
	public static function welcome(Response $response) {
		session_start();
		if (!empty($_SESSION['authenticated'])) {
	    $loginForm = '';
	    if (!empty($_SESSION['authUser'])) {
	      $userObj = unserialize($_SESSION['authUser']); 
	      $name = '';
	      if($userObj instanceof User) {
	        $name = $userObj->getFullname();
	      }
	    }
	    $response->doReplace('{{loggedin_user}}', "You are logged in. Welcome $name.");
	    $response->doReplace('{{login_form}}', 'You are logged in.');
	  }
	  else {
	    $loginForm = file_get_contents(__DIR__ . '/_login_form.php');
	    $response->doReplace('{{loggedin_user}}', '');
	    $response->doReplace('{{login_form}}', $loginForm);
	  }
	  return $response;	
	}

	/**
	 * top menu
	 */
	public static function topMenu(Response $response) {
		$topMenuItems = array(
			'members'=> '<a href="/report/members">Members List</a>',
			'signup'=> '<a href="/member/signup">Sign-up user</a>',
			'login'=> '<a href="/member/login">Login</a>',
			'logout'=> '<a href="/member/logout">Logout</a>'
		);

		if (!empty($_SESSION['authenticated'])) {
			unset($topMenuItems['login']);
		}
		else {
			unset($topMenuItems['logout']);
		}

		$topMenu = sprintf('<ul><li>%s</li></ul>', implode('</li><li>', $topMenuItems));

		$response->doReplace('{{topmenu}}', $topMenu);
		return $response;
	} 
}

class BaseController implements \SocietyLeadership\FlashMessage {
	protected $request, $response;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->request = new Request();
		$this->response = new Response();
	}

	/**
	 * clearSession
	 * @return void
	 */
	public function clearSession() {
		//clear session values.
		$_SESSION['flash_msgs'] = null;
		$_SESSION['login_flash_msgs'] = null;
		$_SESSION['post'] = null;
	}

	/**
	 * displayFlashMessages
	 * @return void
	 */
	public function displayFlashMsgs() {
		// Display all flash messages in the session.
		$this->response->doReplace('{{flash_msgs}}', 
			implode('<br />', $_SESSION['flash_msgs'])
		);
		$_SESSION['flash_msgs'] = null;
	}
}

class DeniedController extends BaseController {
		/**
	 * Controller
	 */
	public function __construct() {
		global $ini_array;
		parent::__construct();
	}
} 

class ReportController extends BaseController {
	/**
	 * Controller
	 */
	public function __construct() {
		global $ini_array;
		parent::__construct();
	}

	/**
	 * @return string
	 */
	public function members() {
		$this->response = \SocietyLeadership\MenuUtils::welcome($this->response);
		$this->response = \SocietyLeadership\MenuUtils::topMenu($this->response);

		// Call data model for dynamic view data based on request
		$allUsers = \SocietyLeadership\User::findByCriteria(array());

		$members = '<table><thead><tr><th>First</th><th>Last</th><th>Username</th><th>Email</th></tr></thead><tbody><tr>';

		foreach ($allUsers as $user) {
			$members .= sprintf('<tr><td>%s</td><td>%s</tdr><td>%s</td><td>%s</td></tr>', 
		  $user->getAttribute('first'), 
		  $user->getAttribute('last'), 
		  $user->getAttribute('username'), 
		  $user->getAttribute('email')
		);
		}
		$members .= '</tbody></table>';


		// Display all flash messages in the session.
		$this->response->doReplace('{{flash_msgs}}', 
			implode('<br />', $_SESSION['flash_msgs'])
		);

		// Display members table
		$this->response->doReplace('{{members}}', $members);



	    // Search result by email
	    $foundUsers = \SocietyLeadership\User::findByCriteria(
	      array('email' => $this->request->getPostParameter('search_str'))
	    );
	    
	    if (!empty($this->request->getPostParameter('search_str'))) {
	      if (!empty($foundUsers)) {
	        $_SESSION['flash_msgs'][] = sprintf('Found user matching %s.', 
	        	$this->request->getPostParameter('search_str')
	        );
	      }    
	    }

		// Display search result by user email.
		if (!empty($foundUsers)) {
			$foundUser = current($foundUsers);
			$this->response->doReplace('{{search_result}}', 
			    sprintf('Found 
			      user matching <b>%s</b>: <ul><li>%s</li></ul>', 
			      $this->request->getPostParameter('search_str'),
			      $foundUser->getAttribute('last')
			    )
			);
		}
		elseif (!empty($this->request->getPostParameter('search_str'))) {
			$this->response->doReplace('{{search_result}}', 
				sprintf('No user found for <b>%s</b>', $this->request->getPostParameter('search_str'))
			); 
		}
		else {
			$this->response->doReplace('{{search_result}}', ''); 
		}
		$this->displayFlashMsgs();
		return $this->response->getAttribute('output');
	}
}
class HomeController extends BaseController  {
	/**
	 * Controller
	 */
	public function __construct() {
		global $ini_array;
		parent::__construct();
	}

	/**
	 * Index
	 * @return string
	 */
	public function index() {
		$this->response = \SocietyLeadership\MenuUtils::welcome($this->response);
		$this->response = \SocietyLeadership\MenuUtils::topMenu($this->response);
		$this->displayFlashMsgs();
		return $this->response->getAttribute('output');
	}
}
class MemberController extends BaseController  {
	/**
	 * Controller
	 */
	public function __construct() {
		global $ini_array;
		parent::__construct();
	}

	/**
	 * Signup
	 * @return string
	 */
	public function signup() {
		$this->response = \SocietyLeadership\MenuUtils::welcome($this->response);
		$this->response = \SocietyLeadership\MenuUtils::topMenu($this->response);

		// Default values for sign up 
		// form can be empty except on submission.
 		foreach (array('username','first','last','password','email') as $fld) {
			$this->response->doReplace('{{' . $fld . '}}', ''); 
		}

	if (!empty($this->request->hasPostParameters())) {
		$validator = new \SocietyLeadership\Validator();
		$validator->setAttribute('executed', null);
	
	    // Validate new signup request data - error if incorrect.
	    $candidateUser = new User();
	    $candidateUser->setAttribute('username', $this->request->getPostParameter('username'));
	    $candidateUser->setAttribute('email', $this->request->getPostParameter('email'));
	    if (empty($validator->getAttribute('executed'))) {
	      if (!$validator->validateStringEmail($this->request->getPostParameter('email'))) {
	        $_SESSION['flash_msgs'][] = 'Invalid email.';
	      } 

	      if (!$validator->validateStringLength($this->request->getPostParameter('password'))) {
	        $_SESSION['flash_msgs'][] = 'Invalid password length. Password should contain a minimum of six characters.';
	      }

	      if (!$validator->validateUserNoneExists($candidateUser)) {
	        $_SESSION['flash_msgs'][] = sprintf('Invalid user input. The user <b>%s</b> or email <b>%s</b> already exists.', 
	          $candidateUser->getAttribute('username'), $candidateUser->getAttribute('email')
	        );
	      }

	      if (!$validator->validateStringNotEmpty($this->request->getPostParameter('username'))) {
	        $_SESSION['flash_msgs'][] = 'Username is a required field.';
	      } 

	      if (!$validator->validateStringNotEmpty($this->request->getPostParameter('first'))) {
	        $_SESSION['flash_msgs'][] = 'First name is a required field.';
	      }

	      if (!$validator->validateStringNotEmpty($this->request->getPostParameter('last'))) {
	        $_SESSION['flash_msgs'][] = 'Last name is a required field.';
	      } 

	      // Display request as default values if validation fails.
	      foreach (array('username','first','last','password','email') as $fld) {
	      	$this->response->doReplace('{{' . $fld . '}}', $_SESSION['post'][$fld]); 
	      }
	    }	

    	// Only add user if the validation error array is still empty.
	    if (empty($_SESSION['flash_msgs'])) {
	      //Add new user by calling saveNew on a User instance
	      if (User::doInsert(
	          array(
	            'username' => $this->request->getPostParameter('username'),
	            'first' => $this->request->getPostParameter('first'),
	            'last' => $this->request->getPostParameter('last'),
	            'password' => $this->request->getPostParameter('password'),
	            'email' => $this->request->getPostParameter('email')
	          )
	        )
	      ) {
	        $_SESSION['flash_msgs'][] = sprintf('Added user <b>%s</b>.', 
	        	$this->request->getPostParameter('username')
	        ); 
	        //Clear fields
	 		foreach (array('username','first','last','password','email') as $fld) {
				$this->response->doReplace('{{' . $fld . '}}', ''); 
			}
	      }
	      else {
	        $_SESSION['flash_msgs'][] = 'Error adding user.'; 
	      } 
	    }
	    
	}
	$this->displayFlashMsgs(); 
	    return $this->response->getAttribute('output');
	}

	/**
	 * Login
	 * @return string
	 */
	public function login() {
		$this->response = \SocietyLeadership\MenuUtils::welcome($this->response);
		$this->response = \SocietyLeadership\MenuUtils::topMenu($this->response);
		//$this->clearSession();

		// Do something with the request - run validators, query DB, etc.
		if ($this->request->hasPostParameters()) {
			$_SESSION['post'] = $this->request->getPostParameters();
			$_SESSION['post']['username_login'] = $this->request->getPostParameter('username_login');
			$_SESSION['post']['username_password'] = $this->request->getPostParameter('username_password');
			if (empty($_SESSION['authenticated'])) {
				if (!empty($_SESSION['post']['username_login']) && !empty($_SESSION['post']['username_password'])) {
					$authUser = User::authenticate($_SESSION['post']['username_login'], 
					$_SESSION['post']['username_password']);
				    if ($authUser instanceof User) {
				      $_SESSION['authenticated'] = true;
				      $_SESSION['authUser'] = serialize($authUser);
				    }
				    elseif ($authUser === false) {
				      $_SESSION['login_flash_msgs'][] = 'Invalid credentials.';
				    }
				    else {
				      throw new Exception('Unexpected error in User::authenticate');
				    }
				}
			}
		}
		$this->response->doReplace('{{login_flash_msgs}}', 
			implode('<br />', $_SESSION['login_flash_msgs'])
		);
		$_SESSION['login_flash_msgs'] = null;
		return $this->response->getAttribute('output');
	}

	/**
	 * Logout
	 * @return string
	 */
	public function logout() {
		$this->response = \SocietyLeadership\MenuUtils::welcome($this->response);
		$this->response = \SocietyLeadership\MenuUtils::topMenu($this->response);
		$this->displayFlashMsgs();
		return $this->response->getAttribute('output');
	}
}
