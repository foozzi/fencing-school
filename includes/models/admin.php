<?

/*
{INSTALL:SQL{
create table admins(
	Id int not null auto_increment,
	Login char(50) not null,
	Password char(50) not null,
	Email char(150) not null,
	IsSuper tinyint not null,

	primary key (Id),
	unique key (Login)
) engine = MyISAM;

}} 
*/

/**
 * The Admin model.
 * 
 * @author Yarick.
 * @version 1.0
 */
class Admin extends User
{
	
	public $Id;
	public $Login;
	public $Password;
	public $Email;
	public $IsSuper;
	
	private $Access;
	private $Perm;
	
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
		return 'admins';
	}
	
	/**
	 * @see parent::getSessionRow()
	 */
	protected function getSessionRow( User $User = null )
	{
		return new Admin_Session( $User ? $User : $this );
	}
	
	/**
	 * @see parent::setPost()
	 */
	public function setPost( array $data = array() )
	{
		if ( isset( $data['Password'] ) && empty( $data['Password'] ) )
		{
			unset( $data['Password'] );
		}
		parent::setPost( $data );
		if ( isset( $data['Password'] ) && !empty( $data['Password'] ) )
		{
			$this->Password = self::pwd( $data['Password'] );
		}
		if ( !empty( $data['clear_access'] ) )
		{
			$this->Access = array();
		}
		if ( !empty( $data['access'] ) )
		{
			$this->Access = $data['access'];
		}
	}
	
	/**
	 * @see parent::save()
	 */
	public function save()
	{
		if ( parent::save() )
		{
			if ( is_array( $this->Access ) )
			{
				$Row = new Admin_Access();
				$Row->dropList( array( 'UserId = '.$this->Id ) );
				foreach ( $this->Access as $class )
				{
					$Row = new Admin_Access();
					$Row->UserId = $this->Id;
					$Row->Controller = $class;
					$Row->saveNew();
				}
			}
			return true;
		}
		return false;
	}
	
	/**
	 * @see parent::drop()
	 */
	public function drop()
	{
		if ( $this->IsSuper )
		{
			return false;
		}
		if ( parent::drop() )
		{
			$Row = new Admin_Access();
			$Row->dropList( array( 'UserId = '.$this->Id ) );
			return true;
		}
		return false;
	}
	
	/**
	 * The function returns search clause for current key and value.
	 *
	 * @access public
	 * @param string $key The search key.
	 * @param mixed $value The search value.
	 * @return string The search clause.
	 */
	public function getParam( $key, $value = null )
	{
		switch ( $key )
		{
			case 'access':
				if ( is_object( $value ) )
				{
					$value = get_class( $value );
				}
				return '* IsSuper = 1 or Id in (select UserId from admins_access where Controller = "'.$value.'")';
		}
		return null;
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
		if ( $this->IsSuper )
		{
			return true;
		}
		if ( is_object( $controller ) && ! $controller instanceof Controller_Backend )
		{
			return false;
		}
		if ( $controller instanceof Controller_Backend )
		{
			$controller = get_class( $controller );
		}
		$controller = strtoupper( $controller );
		if ( !$this->Perm )
		{
			$this->Perm = array();
			$Row = new Admin_Access();
			foreach ( $Row->findList( array( 'UserId = '.$this->Id ) ) as $Row )
			{
				$this->Perm[] = strtoupper( $Row->Controller );
			}
		}
		return in_array( $controller, $this->Perm );
	}
	
	/**
	 * The function returns array of Admins.
	 * 
	 * @static
	 * @access public
	 * @param bool $weekOnly If TRUE returns only regular admins, otherwise returns regular and super.
	 * @return array The Admins.
	 */
	public static function getAdmins( $weakOnly = false )
	{
		$Admin = new self();
		$params = array();
		if ( $weakOnly )
		{
			$params[] = 'IsSuper = 0';
		}
		return $Admin->findList( $params, 'Login asc' );
	}
	
}
