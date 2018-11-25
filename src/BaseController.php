<?php 
namespace SocietyLeadership;


interface FlashMessage {
	public function displayFlashMsgs();
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
		if (count($_SESSION['flash_msgs'])) {
			// Display all flash messages in the session.
			$this->response->doReplace('{{flash_msgs}}', 
				'<ul><li>' 
				. implode('</li><li>', $_SESSION['flash_msgs']) 
				. '</li></ul>'
			); 
		}
		else {
			$this->response->doReplace('{{flash_msgs}}', '');
		}
		$_SESSION['flash_msgs'] = null;
	}
}