<?php
namespace SocietyLeadership;

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
		return !empty($_POST) ? true : false;
	}
	/**
	 * @return array
	 */
	public function getPostParameters() {
		if (empty($this->post)) {
			$this->createFromGlobals();
		}
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