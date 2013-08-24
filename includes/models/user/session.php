<?

/**
 * The User Session class.
 * Allows to use many sessions for one user, for using account from PC, laptop, phone, etc.
 * 
 * @author Yarick.
 * @version 1.0
 */
class User_Session extends Object
{
	
	public $Id;
	public $UserId;
	public $Timeout;
	public $CreatedAt;
	public $UpdatedAt;
	
	private $User = null;
	
	const SESSION_LENGTH = 20;
	const TIMEOUT_SHORT	= 3600; // one hour
	const TIMEOUT_LONG	= 31536000; // one year (365 days)
	
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
		return 'user_sessions';
	}
	
	/**
	 * The class constructor.
	 * 
	 * @access public
	 * @param object $User The User.
	 */
	public function __construct( User $User = null )
	{
		parent::__construct();
		if ( $User )
		{
			$this->User = $User;
		}
	}
	
	/**
	 * The function returns User object.
	 * 
	 * @access protected
	 * @return object The User.
	 */
	protected function getUserRow()
	{
		return new User();
	}
	
	/**
	 * The function returns current session User id.
	 * 
	 * @access protected
	 * @return int The User id.
	 */
	protected function getUserId()
	{
		if ( $this->UserId )
		{
			return $this->UserId;
		}
		return $this->User ? $this->User->Id : 0;
	}
	
	/**
	 * The function creates session for current User.
	 * 
	 * @access public
	 * @param bool $forever The remember me checkox value, if TRUE sets session timeout very long.
	 * @return string The session id or FALSE on failure.
	 */
	public function create( $forever = false )
	{
		if ( !$this->getUserId() )
		{
			return false;
		}
		// drop expired sessions
		$this->dropList( array( 'UserId = '.$this->getUserId(), '* Timeout + UpdatedAt < '.time() ) );
		
		$this->Id			= $this->getRandId();
		$this->UserId		= $this->getUserId();
		$this->Timeout		= $this->getTimeout( $forever );
		$this->CreatedAt	= time();
		$this->UpdatedAt 	= time();
		if ( $this->saveNew() )
		{
			return $this->Id;
		}
		return false;
	}
	
	/**
	 * The function finds session by id.
	 * 
	 * @access public
	 * @param string $id The session id.
	 * @return object The Session.
	 */
	public function findSession( $id )
	{
		$Session = $this->findItem( array( 'Id = '.$id ) );
		if ( $Session->Id && $Session->UpdatedAt + $Session->Timeout < time() )
		{
			$Session->drop();
			$Session->Id = null;
		}
		return $Session;
	}
	
	/**
	 * The function prolongs the session.
	 * 
	 * @access public
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function prolong()
	{
		if ( !$this->Id )
		{
			return false;
		}
		$this->UpdatedAt = time();
		return $this->save();
	}
	
	/**
	 * The function returns session timeout in seconds.
	 * 
	 * @access public
	 * @param bool $long If TRUE returns long session timeout, otherwise returns short timeout.
	 * @return int The session timeout.
	 */
	public function getTimeout( $long = false )
	{
		return $long ? self::TIMEOUT_LONG : self::TIMEOUT_SHORT;
	}
	
	/**
	 * The function returns User attached to current Session.
	 * 
	 * @access public
	 * @return object The User.
	 */
	public function getUser()
	{
		if ( $this->User )
		{
			return $this->User;
		}
		$User = $this->getUserRow();
		return $User->findItem( array( 'Id = '.$this->getUserId() ) );
	}

	/**
	 * The function returns unique session id.
	 * 
	 * @access private
	 * @return string The session id.
	 */
	private function getRandId()
	{
		do
		{
			$id = '';
			do
			{
				$id .= base_convert( sha1( microtime(true).time().rand(0, 1000).var_export( $_SERVER, true ) ), 16, 36 );
			} while ( strlen( $id ) < self::SESSION_LENGTH );
			$id = substr( $id, 0, self::SESSION_LENGTH );
		} while ( $this->findSize( array( 'Id = '.$id ) ) > 0 );
		return $id;
	}
	
}
