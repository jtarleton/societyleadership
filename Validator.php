<?php 
namespace SocietyLeadership;

class Validator {
	private $executed;

	public function __construct() {
		$this->executed = false;
	}
	public function setAttribute($attr, $value) {
		$this->$attr = $value;

	}
	/**
	 * @return mixed
	 */
	public function getAttribute($attr) {
		return $this->$attr;
	}
	/**
	 * @param string
	 * @return boolean
	 */
	public function validateStringEmail($email) {
		$this->executed = true;
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) { 
			return true;
		}
		return false;
	}

	/**
	 * @param string
	 * @return bool
	 */
	public function validateStringNotEmpty($string) {
		$this->executed = true;
		$string = trim($string);
		if (!empty($string) && is_string($string)) {
			return true;
		}
		
		return false;
	}

	public function validateStringLength($string, $limit = 6) {
		$this->executed = true;
		// only pass if the string is at least $limit 
		// characters in length
		if (strlen($string) >= (int) $limit) {
			return true;
		}

		return false;
	}

	/**
	 * @param Object
	 * @return boolean
	 */
	public function validateUserNoneExists(User $user) {
		$this->executed = true;
		// check the user does not already exist in the DB

		$foundUsers = \SocietyLeadership\User::findByCriteria(
			array('username' => $user->getAttribute('username'))
		);
		if (!empty($foundUsers)) {
			return false;
		}

		$foundUsers = \SocietyLeadership\User::findByCriteria(
			array('email' => $user->getAttribute('email'))
		);

		if (!empty($foundUsers)) {
			return false;
		}

		// if not found by either username or email
		return true; 

	}
}
