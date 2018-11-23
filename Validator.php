<?php 
namespace SocietyLeadership;

class Validator {
	
	/**
	 * @param string
	 * @return boolean
	 */
	public function validateStringEmail($email) {
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
		$string = trim($string);
		if (!empty($string) && is_string($string)) {
			return true;
		}
		return false;
	}

	public function validateStringLength($string, $limit = 6) {
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
		// check the user does not already exist in the DB
		
		$foundUsers = \SocietyLeadership\User::findByCriteria(
			array('username' => $user->username)
		);
		if (!empty($foundUsers)) {
			return false;
		}

		$foundUsers = \SocietyLeadership\User::findByCriteria(
			array('email' => $user->email)
		);

		if (!empty($foundUsers)) {
			return false;
		}

		// if not found by either username or email
		return true; 

	}
}
