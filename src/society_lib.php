<?php 
namespace SocietyLeadership;

/** 
 * Send HTML header and render page
 */
function render() {
  session_start();
  $requestedRoute = $_SERVER['REQUEST_URI'];
  switch ($requestedRoute) {
    case '/member/signup':
      $actions = new \SocietyLeadership\MemberController();
      echo $actions->signup();
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
 *
 * The View's job is to translate data into a visual rendering for response to the Client (ie. web browser or other consumer). 
 * The data will be supplied primarily by the Controller 
 *   Red Flags: My View architecture might be going bad if:
 *   The View contains business logic.
 *   The View contains session logic. 
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