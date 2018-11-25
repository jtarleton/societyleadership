<?php 
namespace SocietyLeadership;

/** 
 * Send HTML header and render page
 */
function render() {
  session_start();
  $requestedRoute = new Route($_SERVER['REQUEST_URI']);
  switch ($requestedRoute->getAttribute('name')) {
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