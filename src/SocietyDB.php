<?php 

namespace SocietyLeadership;

define('DB_DSN',  'mysql:host=127.0.0.1;dbname=societyleadership');
define('DB_USER', 'societyleadershi');
define('DB_PASS', 'societyleadershi');
/**
 * @package SocietyLeadership
 * 
 * SocietyDB.php
 */
class SocietyDB {
   
    private static $objInstance;
   
    /**
     * Class Constructor - Create a new database connection if one doesn't exist
     * Set to private so no-one can create a new instance via ' = new SocietyDB();'
     */
    private function __construct() {
    }
   
    /**
     * Like the constructor, we make __clone private so nobody can clone the instance
     */
    private function __clone() {
    }
   
    /**
     * Returns DB instance or create initial connection
     * @param
     * @return $objInstance;
     */
    public static function getInstance() {
           
        if (!self::$objInstance) { 
            self::$objInstance = new \PDO(DB_DSN, DB_USER, DB_PASS);
            self::$objInstance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
       
        return self::$objInstance;
   
    }

    /**
     * Passes on any static calls to this class onto the singleton PDO instance
     * @param $chrMethod, $arrArguments
     * @return $mix
     */
    final public static function __callStatic( $chrMethod, $arrArguments ) {
           
        $objInstance = self::getInstance();
       
        return call_user_func_array(array($objInstance, $chrMethod), $arrArguments);
       
    } 
}