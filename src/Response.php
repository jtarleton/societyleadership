<?php 
namespace SocietyLeadership;

class Response {
	/**
	 * @var string
	 */
	private $output; //the response output will be a string.
	
	/**
	 * Constructor
	 */
	public function __construct() {
		if (!isset($this->output)) {
			$this->output = \SocietyLeadership\Route::getViewByRoute(); 
		}
	}
	/**
	 * @param string
	 * @return mixed
	 */
	public function getAttribute($attr) {
		return $this->$attr;
	}
	/**
	 * @param string
	 * @return object
	 */
	public function doReplace($tokenName, $replaceWith){
		$this->output = str_replace($tokenName, $replaceWith, $this->output);
		return $this;
	} 

	/**
	 * @param string
	 * @param string
	 * @param string
	 * @return object
	 */
	public function doDelimitedReplace($startDelim, $endDelim, $replaceWith) {
		$snipped = strstr($this->output, $startDelim);
		$delimFragment = strstr($snipped, $endDelim, true);
		if (strpos($this->output, $delimFragment) !== false) { 
			// now use $delimFragment as a token
			$this->doReplace($delimFragment, $replaceWith); 
		}
		return $this;
	}
}