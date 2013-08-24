<?

/*

{INSTALL:SQL{  
create table router(
	Id int not null auto_increment,
	Link char(100) not null,
	Controller char(50) not null,
	Type tinyint not null,
	PageId int not null,
	
	primary key (Id),
	index (Type,PageId)
) engine=MyISAM;

insert into router (Id, Link, Controller) values (1, '/', 'Controller_Frontend'), (2, '/admin', 'Controller_Backend');
}}
*/

/**
 * The Router model class for storing routed URLs.
 * 
 * @version 0.1
 */
class Router extends Object
{
	
	const PAGE			= 0;
	
    public $Id;
    public $Link;
    public $Controller;
    public $Type;
    public $PageId;
    
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
		return 'router';
	}
	
	/**
	 * The function returns connected object to current Router.
	 * 
	 * @access public
	 * @return object The object.
	 */
	public function getReference()
	{
		$Object = new Content_Page();
		return $Object->findItem( array( 'Id = '.$this->PageId ) );
	}
	
	/**
	 * The function returns type of Router by reference object.
	 * 
	 * @static
	 * @access public
	 * @param object $Object The reference Object.
	 * @return int The type.
	 */
	public static function getType( Object $Object )
	{
		if ( $Object instanceof Content_Page )
		{
			return self::PAGE;
		}
		return 0;
	}
	
	/**
	 * The function attaches Page to router.
	 * 
	 * @static
	 * @access public
	 * @param object $Object The Object.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function attachPage( Object $Object )
	{
		$type = self::getType( $Object );
		if ( $type == self::PAGE && $Object->Link == '/' )
		{
			return false;
		}
		$Router = new self();
		$Router = $Router->findItem( array( 'PageId = '.$Object->Id, 'Type = '.$type ) );
		$Router->Type	= $type;
		$Router->PageId	= $Object->Id;
		if ( $Router->Type == self::PAGE )
		{
			$Router->Link		= $Object->Link;
			$Router->Controller	= $Object->Module ? $Object->Module : 'Controller_Frontend';
		}
		return $Router->save();
	}
	
	/**
	 * The function detaches Page to router.
	 * 
	 * @static
	 * @access public
	 * @param object $Object The Object.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function detachPage( Object $Object )
	{
		if ( $Page->Link == '/' )
		{
			return false;
		}
		$Router = new self();
		$Router = $Router->findItem( array( 'PageId = '.$Object->Id, 'Type = '.self::getType( $Object ) ) );
		if ( !$Router->Id )
		{
			return false;
		}
		return $Router->drop();
	}
	
	/**
	 * The function returns TRUE if current link for current Object is already in Router, otherwise FALSE.
	 * 
	 * @static
	 * @access public
	 * @param string $link The link.
	 * @param object $Object The Object.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function has( $link, Object $Object = null )
	{
		$Router = new self();
		$params = array();
		$params[] = 'Link = '.$link;
		if ( $Object !== null )
		{
			$params[] = 'Type = '.self::getType( $Object );
			$params[] = 'PageId = '.$Object->Id;
		}
		return $Router->findSize( $params ) > 0;
	}
    
	/**
	 * The function returns reference object from Router.
	 * 
	 * @static
	 * @access public
	 * @param string $link The link.
	 * @param object $Object The Object.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function get( $link, Object $Object )
	{
		$Router = new self();
		$params = array();
		$params[] = 'Link = '.$link;
		$params[] = 'Type = '.self::getType( $Object );
		if ( $Object->Id )
		{
			$params[] = 'PageId = '.$Object->Id;
		}
		$Router = $Router->findItem( $params );
		return $Object = $Object->findItem( array( 'Id = '.intval( $Router->PageId ) ) );
	}
    
}
