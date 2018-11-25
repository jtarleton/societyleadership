<?php 
namespace SocietyLeadership;

class Route {
	private $name;
	/**
	 * Constructor 
	 * @param string
	 */
	public function __construct($name) {
		$this->name = $name;
	}
	/**
	 * @param string
	 */
	public function getAttribute($attr){
		return $this->$attr;
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
	 * @return string
	 */
	public static function getViewByRoute() {
	  $requestedRoute = new Route($_SERVER['REQUEST_URI']);
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
	    switch ($requestedRoute->getAttribute('name')) {
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
	    switch ($requestedRoute->getAttribute('name')) {
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
}