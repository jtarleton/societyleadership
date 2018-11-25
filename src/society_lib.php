<?php 
namespace SocietyLeadership;

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
  $_SESSION['login_flash_msgs'] = null;
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
  	$members .= sprintf('<tr><td>%s</td><td>%s</tdr><td>%s</td><td>%s</td></tr>', 
      $user->getAttribute('first'), 
      $user->getAttribute('last'), 
      $user->getAttribute('username'), 
      $user->getAttribute('email')
    );
  }
  $members .= '</tbody></table>';
  

  // Display all flash messages in the session.
  $output = str_replace('{{flash_msgs}}', implode('<br />', $_SESSION['flash_msgs']), $output);

  $output = str_replace('{{login_flash_msgs}}', implode('<br />', $_SESSION['login_flash_msgs']), $output);


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


  $output = str_replace('{{topmenu}}', $topMenu, $output);


  if (!empty($_SESSION['authenticated'])) {
    $loginForm = '';
    if (!empty($_SESSION['authUser'])) {
      $userObj = unserialize($_SESSION['authUser']); 
      $name = '';
      if($userObj instanceof User) {
        $name = $userObj->getFullname();
      }
    }
    $output = str_replace('{{loggedin_user}}', "You are logged in. Welcome $name.", $output);
    $output = str_replace('{{login_form}}', 'You are logged in.', $output);
  }
  else {
    $loginForm = file_get_contents(__DIR__ . '/_login_form.php');
    $output = str_replace('{{loggedin_user}}', '', $output);
    $output = str_replace('{{login_form}}', $loginForm, $output);
  }
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
    case '/member/signup':
    $actions = new \SocietyLeadership\MemberController();
    echo $actions->signup();
		//echo preprocess_view();
		break;
  case '/member/logout':
    $_SESSION['authenticated'] = null;
    $_SESSION['authUser'] = null;
    $actions = new \SocietyLeadership\MemberController();
    echo $actions->logout();
    break;
  case '/member/login':
    $actions = new \SocietyLeadership\MemberController();
    echo $actions->login();
    break;
	case '/report/members':
		$actions = new \SocietyLeadership\ReportController();
    echo $actions->members();
		break;
  default:
    $actions = new \SocietyLeadership\HomeController();
    echo $actions->index();
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
  //start output buffering
  ob_start();
  $isAdmin = false;
  
  //Unserialize user from session...check privilege.
  if (!empty($_SESSION['authUser'])) {
    $userObj = unserialize($_SESSION['authUser']); 
  }
 
  if ($userObj instanceof User) {
    $isAdmin = $userObj->isAdmin(); 
  }
  
  if ($isAdmin) {
        switch($requestedRoute) {
                case '/member/signup':
                        include(__DIR__ . '/views/signup.html');
                        break;
                case '/member/logout':
                        include(__DIR__ . '/views/logout.html');
                        break;
                case '/member/login':
                        include(__DIR__ . '/views/login.html');
                        break;
                case '/report/members':
                        include(__DIR__ . '/views/view.html');
                        break;
                default:
                        include(__DIR__ . '/views/home.html');
                        break;
        }
  }
  else {
        switch($requestedRoute) {
                case '/member/sign-up':
                        include(__DIR__ . '/views/signup.html');
                        break;
                case '/member/logout':
                        include(__DIR__ . '/views/logout.html');
                        break;
                case '/member/login':
                        include(__DIR__ . '/views/login.html');
                        break;
                case '/report/members':
                        //Insufficient Privileges
                        include(__DIR__ . '/views/denied.html');
                        break;
                default:
                        include(__DIR__ . '/views/home.html');
                        break;
        }
  }
  return ob_get_clean();
}


/**

	Create a user login page where an existing user can log in using their username and
	password. Also allow for the users to log out.

 */