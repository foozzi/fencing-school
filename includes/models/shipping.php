<?

/*

{INSTALL:SQL{
create table shippings(
	Id int not null auto_increment,
	Name varchar(200) not null,
	`Text` text not null,
	IsAddress tinyint not null,
	Price float(15,2) not null,
	Position int not null,
	IsActive tinyint not null,

	primary key (Id),
	index (Position),
	index (IsActive)
) engine = MyISAM;
}}
*/

/**
 * The Shipping model.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Shipping extends Object
{
	
	public $Id;
	public $Name;
	public $Text;
	public $IsAddress;
	public $Price;
	public $Position;
	public $IsActive;
	
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
		return 'shippings';
	}
	
	/**
	 * @see parent::getTestRules()
	 */
	public function getTestRules()
	{
		return array(
			'Name'		=> '/\S{2,}/',
		);
	}
	
	/**
	 * The function returns array of serialized properties.
	 * 
	 * @access public
	 * @return array The properties.
	 */
	public function __sleep()
	{
		return array( 'Id', 'Name', 'Price' );
	}
	
	/**
	 * @see parent::save()
	 */
	public function save()
	{
		if ( !$this->Position )
		{
			$this->Position = intval( self::getLast( $this, 'Position' ) ) + 1;
		}
		return parent::save();
	}
	
	/**
	 * The function returns all shipping methods.
	 * 
	 * @static
	 * @access public
	 * @param bool $assoc If TRUE returns associated array.
	 * @return array The shipping methods.
	 */
	public static function getShippings( $assoc = false )
	{
		$Shipping = new self();
		$result = array();
		$arr = $Shipping->findList( array(), 'Position asc' );
		if ( !$assoc )
		{
			return $arr;
		}
		return self::convertArray( $arr, 'Id', 'Name' );
	}
	
}
