<?

/**
 * The User model with salt hashing passwords.
 * Use as base class for inherited user classes.
 * 
 * @author Yarick.
 * @version 1.0
 */
class User extends Object
{
	
	const SALT_LENGTH		= 10;
	const ONLINE_TIMEOUT	= 180; # 3 minutes
	
	public $Id;
	public $Login;
	public $Password;
	
	private $LastTimeOnline = null;
	
	/**
	 * @see parent::getPrimary()
	 */
	protected function getPrimary()
	{
		return array('Id');
	}
	
	/**
	 * @see parent::getTableName()
	 */
	protected function getTableName()
	{
		return 'users';
	}
	
	/**
	 * The function returns User Session object.
	 * 
	 * @access protected
	 * @param object $User The User reference.
	 * @return object The User Session.
	 */
	protected function getSessionRow( User $User = null )
	{
		return new User_Session( $User ? $User : $this );
	}
	
	/**
	 * The function returns TRUE if current User can login (activated, verified, existent, etc.)
	 * 
	 * @access public
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function canLogin()
	{
		return $this->Id > 0;
	}
	
	/**
	 * @see parent::drop()
	 */
	public function drop()
	{
		if ( parent::drop() )
		{
			$Row = $this->getSessionRow();
			$Row->dropList( array( 'UserId = '.$this->Id ) );
			return true;
		}
		return false;
	}
	
	/**
	 * The function returns host for which cookie will be set.
	 * 
	 * @access protected
	 * @return string The host.
	 */
	protected function getHost()
	{
		return null;
	}
	
	/**
	 * The function lets user log in.
	 *
	 * @access public
	 * @param object $User The User, if NULL current $this object is used.
	 * @param bool $forever The remember me checkox value, if TRUE sets session timeout very long.
	 * @return mixed The User object on success, FALSE on failure.
	 */
	protected function rawLogin( User $User = null, $forever = false )
	{
		$User = $User === null ? $this : $User;
		$Session = $this->getSessionRow( $User );
		$session = $Session->create( $forever );
		if ( $session === false )
		{
			return false;
		}
		Request::set( 'COOKIE', $this->getSessionName(), $session, time() + $Session->getTimeout( $forever ), $this->getHost() );
		return $User;
	}
	
	/**
	 * The function checks for user in database and logins if it exists.
	 * 
	 * @access public
	 * @param string $login The User login.
	 * @param string $password The User password.
	 * @param bool $forever The remember me checkox value, if TRUE sets session timeout very long.
	 * @return mixed The User object on success, FALSE on failure.
	 */
	public function login( $login, $password, $forever = false )
	{
		$User = $this->findItem( array( 'Login = '.$login ) );
		if ( $User->canLogin() && self::checkPwd( $User->Password, $password ) )
		{
			return $this->rawLogin( $User, $forever );
		}
		return false;
	}
	
	public function loginHybrid( $login, $forever = false )
	{
		$User = $this->findItem( array( 'Login = ' . $login ) );
		if ( $User->Login )
		{
			return $this->rawLogin( $User, $forever );
		}
		return false;
	}

	/**
	 * The function logouts current user.
	 * 
	 * @access public
	 * @param string $session The session ID.
	 */
	public function logout( $session = null )
	{
		if ( $session === null )
		{
			$session = Request::get( $this->getSessionName(), null, 'COOKIE' );
		}
		if ( !$session )
		{
			return false;
		}
		$Session = $this->getSessionRow();
		$Session = $Session->findSession( $session );
		$Session->drop();
		Request::set( 'COOKIE', $this->getSessionName(), $session, time() - 3600 );
		return true;
	}
	
	/**
	 * The function authorizes user by its session ID if it is not expired.
	 * 
	 * @access public
	 * @param string $session The session ID.
	 * @return mixed The User object on success, FALSE on failure.
	 */
	public function auth( $session = null )
	{
		$Session = $this->getCurrentSession( $session );
		if ( $Session->prolong() )
		{
			$User = $Session->getUser();
			if ( $User->Id )
			{
				Request::set( 'COOKIE', $this->getSessionName(), $Session->Id, time() + $Session->Timeout );
				return $User;
			}
		}
		return false;
	}
	
	/**
	 * The function returns current user Session.
	 *
	 * @access protected
	 * @param string $session The session ID.
	 * @return obect The Session.
	 */
	protected function getCurrentSession( $session = null )
	{
		if ( $session === null )
		{
			$session = Request::get( $this->getSessionName(), null, 'COOKIE' );
		}
		$Session = $this->getSessionRow();
		return $Session->findSession( $session );
	}
	
	/**
	 * The function returns current class session name.
	 * 
	 * @access private
	 * @return string The session name.
	 */
	private function getSessionName()
	{
		return get_class( $this ).'_SID';
	}
	
	/**
	 * The function returns last user session update time.
	 *
	 * @access public
	 * @return int The time.
	 */
	public function getLastTimeOnline()
	{
		if ( $this->LastTimeOnline === null )
		{
			$this->LastTimeOnline = 0;
			$Row = $this->getSessionRow();
			foreach ( $Row->findList( array( 'UserId = '.$this->Id ), 'UpdatedAt desc', 0, 1 ) as $Row )
			{
				$this->LastTimeOnline = $Row->UpdatedAt;
			}
			if ( !$this->LastTimeOnline && property_exists( $this, 'PostedAt' ) )
			{
				$this->LastTimeOnline = $this->PostedAt;
			}
		}
		return $this->LastTimeOnline;
	}
	
	/**
	 * The function returns TRUE if User is online, otherwise FALSE.
	 *
	 * @access public
	 * @return bool TRUE if online.
	 */
	public function isOnline()
	{
		return $this->getLastTimeOnline() + self::ONLINE_TIMEOUT >= time();
	}

	/**
	 * The function returns hash for password.
	 * 
	 * @static
	 * @access public
	 * @param string $password The password.
	 * @param string $salt The salt.
	 * @return string The hash for password.
	 */
	public static function pwd( $password, $salt = null )
	{
		if ( !$salt )
		{
			$salt = sha1( microtime(true).time().rand(0, 1000).var_export( $_SERVER, true ) );
		}
		$salt = substr( $salt, 0, self::SALT_LENGTH );
		return $salt.sha1( $salt.$password );
	}
	
	/**
	 * The function returns TRUE if hash equals to hashed password.
	 * 
	 * @static
	 * @access public
	 * @param string $hash The hashed password.
	 * @param string $password The plain password.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function checkPwd( $hash, $password )
	{
		$salt = substr( $hash, 0, self::SALT_LENGTH );
		return self::pwd( $password, $salt ) == $hash;
	}
	
}
