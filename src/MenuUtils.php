<?php 
namespace SocietyLeadership;

/**
 * MenuUtils
 */
class MenuUtils {

	/**
	 * Add welcome text to response.
	 */
	public static function welcome(Response $response) {
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
	    $loginForm = file_get_contents(__DIR__ . '/_login_form.html');
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
