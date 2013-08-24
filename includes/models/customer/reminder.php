<?

/*

{INSTALL:SQL{
create table customers_remind(
	Id int not null auto_increment,
	UserId int not null,
	Object varchar(50) not null,
	ObjectId int not null,
	PostedAt int not null,

	primary key (Id),
	index (UserId),
	index (Object),
	index (PostedAt)
) engine = MyISAM;

}}
*/

/**
 * The Client model class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Customer_Reminder extends Object
{
	
	public $Id;
	public $UserId;
	public $Object;
	public $ObjectId;
	public $PostedAt;
	
	
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
		return 'customers_remind';
	}

	public function __construct( Customer $Customer = null )
	{
		parent::__construct();
		if ( $Customer )
		{
			$this->UserId = $Customer->Id;
		}
	}

	public function attach( Object $Object )
	{
		$this->Object = get_class( $Object );
		if ( property_exists( $Object, 'Id' ) )
		{
			$this->ObjectId = intval( $Object->Id );
		}
	}
	
	public function detach( Object $Object )
	{
		$params = array();
		$params[] = 'UserId = '.$this->UserId;
		$params[] = 'Object = '.get_class( $Object );
		if ( property_exists( $Object, 'Id' ) )
		{
			$params[] = 'ObjectId = '.intval( $Object->Id );
		}
		$this->dropList( $params );
	}

	public function saveNew()
	{
		$this->PostedAt = time();
		return parent::saveNew();
	}
	
	public function getProducts( Customer $Customer )
	{
		if ( !$Customer->Id )
		{
			return array();
		}
		$Product = new Product();
		$params = array();
		$params[] = '* Id in (select ObjectId from customers_remind where UserId = '.$Customer->Id.' and Object = "Product")';
		return $Product->findList( $params, 'Name asc' );
	}
	
}
