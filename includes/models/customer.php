<?

/*
{INSTALL:SQL{
create table customers(
	Id int not null auto_increment,
	Login varchar(50) not null,
	Name varchar(50) not null,
	Password char(50) not null,
	Email varchar(150) not null,
	Phone varchar(15) not null,
	Address text not null,
	VerifyCode varchar(5) not null,
	RestoreCode varchar(5) not null,
	BackUrl varchar(100) not null,
	IsApproved tinyint not null,
	PostedAt int not null,
	RestoreAt int not null,

	primary key (Id),
	unique key (Login),
	index (VerifyCode),
	index (RestoreCode),
	index (IsApproved),
	index (PostedAt)
) engine = MyISAM;

}}
*/

class Customer extends User
{
	
	const ACTIVATION_TIME = 86400;
	const RESTORE_TIME = 86400;

	public $Id;
	public $Login;
	public $Name;
	public $Password;
	public $Email;
	public $Phone;
	protected $Address;
	public $VerifyCode;
	public $RestoreCode;
	public $BackUrl;
	public $IsApproved;
	public $PostedAt;
	public $RestoreAt;

	/**
	 * @see parent::getPrimary()
	 */
	protected function getPrimary()
	{
		return array( 'Id' );
	}

	/**
	 * @see parent::getTableName()
	 */
	protected function getTableName()
	{
		return 'customers';
	}

	public function getTestRules()
	{
		return array(
			'Login' => Error::EMAIL,
		);
	}

	protected function getSessionRow( User $User = null )
	{
		return new Customer_Session( $User ? $User : $this  );
	}

	public function setPost( array $data = array( ) )
	{
		parent::setPost( $data );
		/*if ( isset( $data['Email'] ) && !isset( $data['Login'] ) )
		{
			$this->Login = $data['Email'];
		}*/
		if ( isset( $data['Address'] ) && is_array( $data['Address'] ) )
		{
			$this->Address = new Address( $data['Address'] );
		}
	}

	public function save()
	{
		if ( !$this->PostedAt )
		{
			$this->PostedAt = time();
		}
		return parent::save();
	}

	public function signup()
	{
		// copy address data from customer fields while signing up
		$Address = $this->getAddress();		
		foreach ( $Address as $key => $value )
		{			
			if ( !$Address->$key && property_exists( $this, $key ) && $this->$key )
			{				
				$Address->$key = $this->$key;
			}
			else if ( $Address->$key && property_exists( $this, $key ) && !$this->$key )
			{				
				$this->$key = $value;
			}
		}		
		$this->Address = $Address;
		$this->makeVerifyCode();
		return $this->save();
	}

	public function makeVerifyCode()
	{
		do
		{
			$code = String::random( 5 );		
		}		
		while ( $this->findSize( array( 'VerifyCode = ' . $code ) ) > 0 );
		$this->VerifyCode = $code;		
	}

	public function restore()
	{
		do
		{
			$code = String::random( 5 );
		}
		while ( $this->findSize( array( 'RestoreCode = ' . $code ) ) > 0 );
		$this->RestoreCode = $code;
		$this->RestoreAt = time();
	}

	public function approve()
	{
		if ( !$this->IsApproved && (time() - $this->PostedAt) > self::ACTIVATION_TIME )
		{
			return false;
		}
		else
		{
			$this->IsApproved = 1;
			$this->VerifyCode = '';
			return $this->save();
		}
	}

	public function forceLogin()
	{
		return $this->rawLogin();
	}

	public function canLogin()
	{
		return $this->Id > 0 && $this->IsApproved > 0;
	}

	/**
	 * The function returns TRUE if current Admin has access for current controller.
	 *
	 * @access public
	 * @param mixed $controller The Controller or its name.
	 * @param bool $modules If TRUE checkes for modules access.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function hasAccess( $controller = null, $modules = false )
	{
		return $this->Id > 0;
	}

	/*
	 * The function returns TRUE if user with $email exist in DataBase
	 * else return False
	 */

	public function exist( $login = null )
	{
		if ( !$login )
		{
			$login = $this->Login;
		}
		$params = array( );
		$params[] = 'Login = ' . $login;
		return $this->findSize( $params ) > 0;
	}

	public function generate( Address $Address )
	{
		$password = String::random( 10 );

		$Customer = new self;
		$Customer->Name = $Address->Name;
		$Customer->Email = $Address->Email;
		$Customer->Login = $Address->Email;
		$Customer->Phone = $Address->Phone;
		$Customer->Address = $Address;
		$Customer->Password = self::pwd( $password );

		if ( $Customer->signup() )
		{
			$Customer->Password = $password;
			return $Customer;
		}
		return false;
	}

	/**
	 * The function returns last Order.
	 * 
	 * @access public
	 * @return object The Order.
	 */
	public function getOrder()
	{
		$Order = new Order();
		if ( $this->Id )
		{
			foreach ( $Order->findList( array( 'UserId = ' . $this->Id ), 'PostedAt desc', 0, 1 ) as $Order );
		}
		return $Order;
	}
	
	public function getAddress()
	{
		if ( !( $this->Address instanceof Address ) && $this->Address )
		{
			@$this->Address = unserialize( $this->Address );
		}
		if ( !( $this->Address instanceof Address ) )
		{
			$this->Address = is_array( $this->Address ) ? new Address( $this->Address ) : new Address();			
		}
		return $this->Address;
	}
	
	/**
	 * The function returns last used address in Order or registration.
	 * 
	 * @access public
	 * @return object The Address.
	 */
	public function getLastAddress()
	{
		$Address = new Address();
		if ( $this->Id )
		{
			$Order = $this->getOrder();
			if ( $Order->Id )
			{
				return $Order->getAddress();
			}
			else
			{
				return $this->getAddress();
			}
		}
		return $Address;
	}

	public function hasRemind( Object $Object )
	{
		if ( !$this->Id )
		{
			return false;
		}
		$Reminder = new Customer_Reminder();
		$params = array();
		$params[] = 'UserId = '.$this->Id;
		$params[] = 'Object = '.get_class( $Object );
		if ( property_exists( $Object, 'Id' ) )
		{
			$params[] = 'ObjectId = '.$Object->Id;
		}
		return $Reminder->findSize( $params ) > 0;
	}

}