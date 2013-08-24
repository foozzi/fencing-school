<?

/*
{INSTALL:SQL{
create table order_rows(
	Id int not null auto_increment,
	OrderId int not null,
	ProductId int not null,
	Quantity int not null,
	Price float(15,2) not null,
	PriceRaw float(15,2) not null,

	primary key (Id),
	index (OrderId)
) engine = MyISAM;
}}
*/

/**
 * The Order model class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Order_Row extends Object
{
	
	private $cachedOrder = null;
	private $cachedProduct = null;
	
	public $Id;
	public $OrderId;
	public $ProductId;
	public $Quantity;
	public $Price;
	public $PriceRaw;
	
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
		return 'order_rows';
	}
	
	/**
	 * @see parent::__construct()
	 */
	public function __construct( Order $Order = null )
	{
		parent::__construct();
		if ( $Order )
		{
			$this->OrderId = $Order->Id;
		}
	}
	
	/**
	 * @see parent::save()
	 */
	public function save()
	{
		if ( !$this->OrderId )
		{
			return false;
		}
		return parent::save();
	}
	
	/**
	 * The function returns Order object for current row.
	 * 
	 * @access public
	 * @return object The Order object.
	 */
	public function getOrder()
	{
		if ( $this->cachedOrder === null )
		{
			$Order = new Order();
			$this->cachedOrder = $Order->findItem( array( 'Id = '.$this->OrderId ) );
		}
		return $this->cachedOrder;
	}
	
	/**
	 * The function returns Product object for current row.
	 * 
	 * @access public
	 * @return object The Product object.
	 */
	public function getProduct()
	{
		if ( $this->cachedProduct === null )
		{
			$Product = new Car_Tyre();
			$this->cachedProduct = $Product->findItem( array( 'Id = '.$this->ProductId ) );
		}
		return $this->cachedProduct;
	}
	
	/**
	 * The function returns price amount for current row.
	 * 
	 * @access public
	 * @return float The price.
	 */
	public function getAmount()
	{
		return $this->Price * $this->Quantity;
	}	
	
	/**
	 * The function returns price raw amount for current row.
	 * 
	 * @access public
	 * @return float The price.
	 */
	public function getRawAmount()
	{
		return $this->PriceRaw * $this->Quantity;
	}	
	
}
