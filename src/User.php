<?php 
namespace SocietyLeadership;

/**
 * User
 */
class User {
  private static $pdo;
	private 
    $username, 
		$password, 
		$first, 
		$last, 
		$email;

	/**
	 * Constructor
	 */
	public function __construct() {
    self::$pdo = \SocietyLeadership\SocietyDB::getInstance();
	}

	/**
	 * @param array
	 */
	public function load(array $data) {
		foreach ($data as $k => $v) {
			$this->$k = $v;
		}
	}

  /**
   * @param string
   * @param string
   */
  public function setAttribute($attr, $value) {
    $this->$attr = $value;
  }

	/**
	 * @param string
	 */
	public function getAttribute($attr) {
		return $this->$attr;
	}

  /**
   * @return string
   */
  public function getFullname() {
    return sprintf('%s %s', $this->getAttribute('first'), 
      $this->getAttribute('last')
    );
  }

  public function isAdmin() {
    global $ini_array;

    if (!empty($ini_array)) {
      // Read credentials from INI file values.
      $storedUsername = trim(base64_decode($ini_array['first_section']['admin_config']['username']));
      $storedPassword = trim(base64_decode($ini_array['first_section']['admin_config']['password']));

      return ($this->getAttribute('username') === $storedUsername 
        && $this->getAttribute('role') === 'admin'
      );
    }
    return false;
  }

  /**
   * @param array
   */
	public function factoryCreate($row) {
		$obj = new User();
		$obj->load($row);
		return $obj;
	}

	/**
	 * @param array
	 * @return array
	 */
	public function findByCriteria(array $criteria = array()) {
		$pdo = \SocietyLeadership\SocietyDB::getInstance();
    // Find all 
    $sql = 'SELECT * FROM user';
		
    // Or find by a single criteria (username OR email).
    // (Presently this method accepts but 
    // one criterion at a time.)
    // To search multiple criteria
    // please revise this prepared statment and 
    // remove calls to key() and current()

    if (!empty($criteria)) {
      $criteria['field'] = current($criteria);
		  $sql   .= ' WHERE ';
      $field  = key($criteria);
      $sql   .= sprintf('%s = :field', key($criteria)); 
    }
		
    $stmt = $pdo->prepare($sql);
		if (!empty($criteria)) {
      $stmt->bindValue(':field', $criteria[$field], \PDO::PARAM_STR);
    }
		$stmt->execute();
		$users = array();
		while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
			$users[$row['username']] = User::factoryCreate($row);
		}
		return $users;
	}

  /**
   * @param array
   * @return bool
   */
  public static function doInsert($data) {
    $user = new User();
    foreach ($data as $k=>$v) {
      $user->setAttribute($k, $v);
    }
    return $user->saveNew();
  }

  /**
   * @param string
   * @param string
   * @param mix (false|User)
   */
  public static function authenticate($username, $password) {
    global $ini_array;
    $username = trim($username);
    $password = trim($password);

    // Authenticate credentials in request against INI file values.
    $storedUsername = trim(base64_decode($ini_array['first_section']['admin_config']['username']));
    $storedPassword = trim(base64_decode($ini_array['first_section']['admin_config']['password']));
    $enabledIniAuth = trim($ini_array['first_section']['admin_config']['enable_ini_admin_auth']) === 'true';

    $authAdminFromIni = ($username === $storedUsername
      && $password === $storedPassword);

    // Authenticate credentials in request against DB.

    // Read a DB record as a user object
    $foundUsers = \SocietyLeadership\User::findByCriteria(
      array('username' => $username)
    );
    $user = $foundUsers[$username];

    $dbUsername = ($user instanceof User) 
      ? $user->getAttribute('username') 
      : null;

    /** 
    * @todo salt and hash DB password 
    */
    $dbPassword = ($user instanceof User) 
      ? $user->getAttribute('password') 
      : null;

    $authUser = ($username === $dbUsername
      && $password === $dbPassword);

    if ($user->isAdmin() && $enabledIniAuth) {
      // Special authentication for admin is enabled.
      // Authenticate credentials in request against (both) stored INI and DB values.
      if ($authAdminFromIni && $authUser
      ) {
        //if authenticated, return the instance
        return $user;
      } 
    }
    else {
      // Authenticate credentials in request against only DB values.
      if ($authUser) {
        //if authenticated, return the instance
        return $user;
      } 
    }
    return false;
  }

  /**
   * @return boolean
   */
  public function saveNew() {
      $pdo = \SocietyLeadership\SocietyDB::getInstance();
      $stmt = $pdo->prepare('INSERT INTO user (username, 
        first, 
        last, 
        password, 
        email, 
        role, 
        created
        ) VALUES(:username,
        :first,
        :last,
        :password,
        :email,
        :role,
        :created
      )');
      $stmt->bindValue(':username', $this->username, \PDO::PARAM_STR);
      $stmt->bindValue(':first', $this->first, \PDO::PARAM_STR);
      $stmt->bindValue(':last', $this->last, \PDO::PARAM_STR);
      $stmt->bindValue(':password', $this->password, \PDO::PARAM_STR);
      $stmt->bindValue(':email', $this->email, \PDO::PARAM_STR);
      $stmt->bindValue(':role', 'user', \PDO::PARAM_STR);
      $stmt->bindValue(':created', date('Y-m-d H:i:s'), 
        \PDO::PARAM_STR);
      $stmt->execute();
      $inserted = $stmt->rowCount();
      return ($inserted > 0);
  }
}