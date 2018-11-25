<?php

namespace SocietyLeadership;

/**
 * ReportController
 */
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


		// Call data model for dynamic view data based on request
		$allUsers = \SocietyLeadership\User::findByCriteria(array());

		$members = '<table id="membersTable"><thead><tr><th>First</th><th>Last</th><th>Username</th><th>Email</th></tr></thead><tbody><tr>';
		$j = 0;
		foreach ($allUsers as $user) {
			$trClass = ($j%2 !== 0) ? 'odd': 'even'; 
			$members .= sprintf('<tr class="%s"><td>%s</td><td>%s</tdr><td>%s</td><td>%s</td></tr>',
			  $trClass, 
			  $user->getAttribute('first'), 
			  $user->getAttribute('last'), 
			  $user->getAttribute('username'), 
			  $user->getAttribute('email')
			);
		}
		$members .= '</tbody></table>';


		// Display all flash messages in the session.
		$this->displayFlashMsgs();

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
			    sprintf('<fieldset><legend>Search Result</legend>Found 
			      user matching <b>%s</b>: <ul><li>%s</li></ul></fieldset>', 
			      $this->request->getPostParameter('search_str'),
			      $foundUser->getAttribute('last')
			    )
			);
		}
		elseif (!empty($this->request->getPostParameter('search_str'))) {
			$this->response->doReplace('{{search_result}}', 
				sprintf('<fieldset><legend>Search Result</legend>No user found for <b>%s</b></fieldset>', 
					$this->request->getPostParameter('search_str')
				)
			); 
		}
		else {
			$this->response->doReplace('{{search_result}}', ''); 
		}
		$this->displayFlashMsgs();
		$this->response = \SocietyLeadership\MenuUtils::welcome($this->response);
		$this->response = \SocietyLeadership\MenuUtils::topMenu($this->response);
		return $this->response->getAttribute('output');
	}
}

/**
 * HomeController
 */
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
		$this->displayFlashMsgs();
		$this->response = \SocietyLeadership\MenuUtils::welcome($this->response);
		$this->response = \SocietyLeadership\MenuUtils::topMenu($this->response);
		return $this->response->getAttribute('output');
	}
}

/**
 * MemberController
 */
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

		// show form
		$signupForm = file_get_contents(__DIR__ . '/_signup_form.html');
		$this->response->doReplace('{{signup_form}}', $signupForm); 

		if ($this->request->hasPostParameters()) {
			$_SESSION['post'] = $this->request->getPostParameters();
			$validator = new \SocietyLeadership\Validator();
			$validator->setAttribute('executed', null);
		
		    // Validate new signup request data - error if incorrect.
		    $candidateUser = new User();
		    $candidateUser->setAttribute('username', $this->request->getPostParameter('username'));
		    $candidateUser->setAttribute('email', $this->request->getPostParameter('email'));

		    //If form not yet validated
		    if (empty($validator->getAttribute('executed'))) {
		      
		      // Validate form and add errors to stack

		      // Indicate field level form errors...
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
		      	$token = sprintf('{{%s}}', $fld);
		      	$this->response->doReplace($token, $_SESSION['post'][$fld]); 
		      }
		    }	

	    	// Only add user if the validation error stack is still empty.
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

				//don't show form
				$this->response->doDelimitedReplace('<!--signup-->', '<!--/signup-->', '');
		      }
		      else {
		        $_SESSION['flash_msgs'][] = 'Error adding user.'; 
		      } 
		    }
		    else {
		    	// Indicate global level form error
		    	$_SESSION['flash_msgs'][] = 'Please correct the form input.';
		    }
		}

		// Default values for sign up form can be empty 
		if (!$this->request->hasPostParameters()) {
			foreach (array('username','first','last','password','email') as $fld) {
				$this->response->doReplace('{{' . $fld . '}}', ''); 
			}	
		}
	
		$this->displayFlashMsgs();
		$this->response = \SocietyLeadership\MenuUtils::welcome($this->response);
		$this->response = \SocietyLeadership\MenuUtils::topMenu($this->response);
	    return $this->response->getAttribute('output');
	}

	/**
	 * Login
	 * @return string
	 */
	public function login() {
		$this->clearSession();
		// Do something with the request - run validators, query DB, etc.
		if ($this->request->hasPostParameters()) {
			$_SESSION['post'] = $this->request->getPostParameters();

			if (empty($_SESSION['authenticated'])) {
				if (isset($_SESSION['post']['username_login']) && isset($_SESSION['post']['username_password'])) {
					$authUser = User::authenticate($_SESSION['post']['username_login'], 
						$_SESSION['post']['username_password']
					);
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

		if (count($_SESSION['login_flash_msgs'])) {
			// Display all login_flash_msgs in the session.
			$this->response->doReplace('{{login_flash_msgs}}',
			        '<ul><li>'
			        . implode('</li><li>', $_SESSION['login_flash_msgs'])
			        . '</li></ul>'
			);
		}
		else {
			$this->response->doReplace('{{login_flash_msgs}}', '');
		}

		$this->response = \SocietyLeadership\MenuUtils::welcome($this->response);
		$this->response = \SocietyLeadership\MenuUtils::topMenu($this->response);

		return $this->response->getAttribute('output');
	}

	/**
	 * Logout
	 * @return string
	 */
	public function logout() {

		$this->displayFlashMsgs();
		$this->response = \SocietyLeadership\MenuUtils::welcome($this->response);
		$this->response = \SocietyLeadership\MenuUtils::topMenu($this->response); 
		return $this->response->getAttribute('output');
	}
}