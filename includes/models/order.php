<?

/*
{INSTALL:SQL{
	create table orders(
	Id int not null auto_increment,
	Ip int not null,
	UserId int not null,
	Type tinyint not null,
	Address text not null,
	Shipping text not null,
	ShippingId int not null,
	ShippingNumber varchar(50) not null,
	PaymentId int not null,
	Payment text not null,
	VAT tinyint not null,
	Currency char(3) not null,
	CurrencyPrint text not null,
	BranchId int not null,
	Pickup tinyint not null,

	CustomData text not null,

	Status tinyint not null,
	Total float(15,2) not null,
	TotalRaw float(15,2) not null,
	Discount float(15,2) not null,
	PostedAt int not null,
	PaidAt int not null,

	Filename varchar(100) not null,
	IsFile tinyint not null,

	ADId int not null,
	ADHost varchar(50) not null,
	ADTail varchar(50) not null,
	ADRef text not null,

	primary key (Id),
	index (UserId),
	index (Status),
	index (Type),
	index (Total),
	index (PostedAt)
) engine = MyISAM;
}}
 */

/**
 * The Order model class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Order extends Object
{
	
	const POSTED = 1;
	const PENDING = 2;
	const PROCESS = 3;
	const DELAY = 4;
	const SHIPPED = 10;
	const DELIVERED = 11;
	const RETURNED = -1;
	const CANCELED = -10;

	private $rows = array( );
	private $rowsChanged = false;
	private $rowsToDel;
	private $rowsToChange;

	public $Id;
	public $UserId;
	protected $Ip;
	protected $Address;
	public $Shipping;
	public $ShippingId;
	public $ShippingNumber;
	public $PaymentId;
	public $Payment;
	public $VAT;
	public $Currency;
	protected $CurrencyPrint;
	public $BranchId;
	public $Pickup;
	public $Status;
	public $Total;
	public $TotalRaw;
	public $Discount;
	public $Type;
	protected $CustomData;
	public $Filename;
	public $IsFile;
	public $PostedAt;
	public $PaidAt;
	public $ADId;
	public $ADHost;
	public $ADTail;
	public $ADRef;
	
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
		return 'orders';
	}
	
	/**
	 * @see parent::__construct()
	 */
	public function __construct()
	{
		parent::__construct();
		$this->Ip = ip2long( Request::get( 'REMOTE_ADDR', null, 'SERVER' ) );
		$this->Status = self::POSTED;
	}
	
	/**
	 * @see parent::getUploadFileInfo()
	 */
	public function getUploadFileInfo()
	{
		return array(
			'allow'		=> array('pdf', 'doc', 'docx', 'rtf', 'txt'),
		);
	}
	
	/**
	 * @see parent::save()
	 */
	public function save()
	{
		if ( !$this->PostedAt )
		{
			$this->PostedAt = time();
		}			
		if ( parent::save() )
		{			
			if ( $this->rowsChanged )
			{
				foreach ( $this->getRows() as $Row )
				{
					$Row->OrderId = $this->Id;
					$Row->save();
				}
			}
			if ( is_array( $this->rowsToDel ) )
			{
				$rows = $this->getRows();
				if ( count( $rows ) > count( $this->rowsToDel ) )
				{
					foreach ( $rows as $Row )
					{
						if ( in_array( $Row->Id, $this->rowsToDel ) )
						{
							$Row->drop();
						}
					}
				}
				$this->refreshTotal(true);
				parent::save();
			}
			if ( is_array( $this->rowsToChange ) )
			{
				foreach ( $this->rowsToChange as $id => $item )
				{
					$Row = new Order_Row();
					$Row = $Row->findItem( array( 'Id = '.$id, 'OrderId = '.$this->Id ) );
					if ( $Row->Id )
					{
						$Row->setPost( $item );
						$Row->save();
					}
				}
				$this->refreshTotal(true);
				parent::save();
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
		if ( parent::drop() )
		{
			$Row = new Order_Row( $this );
			$Row->dropList( array( 'OrderId = '.$this->Id ) );
			return true;
		}
		return false;
	}
	
	public function getParam( $name, $value = null )
	{
		switch ( $name )
		{
			case 'customer':
				$value = $value instanceof User ? $value->Id : intval( $value );
				return 'UserId = '.$value;
		}
		return null;
	}
	
	/**
	 * @see parent::setPost()
	 */
	public function setPost( array $data = array() )
	{
		parent::setPost( $data );
		if ( isset( $data['Address'] ) && is_array( $data['Address'] ) )
		{
			$this->setAddress( $data['Address'] );
		}
		if ( isset( $data['PaidDate'] ) && isset( $data['PaidTime'] ) )
		{
			$this->PaidAt = Date::strtotime( $data['PaidDate'].' '.$data['PaidTime'] );
		}
		if ( isset( $data['Shipping'] ) && is_array( $data['Shipping'] ) )
		{
			$this->setShipping( $data['Shipping'] );
		}
		if ( isset( $data['Row'] ) && is_array( $data['Row'] ) )
		{
			$this->rowsToChange = $data['Row'];
		}
		if ( isset( $data['DeleteRow'] ) && is_array( $data['DeleteRow'] ) )
		{
			$this->rowsToDel = $data['DeleteRow'];
		}
	}
	
	/**
	 * The function sets Order Address.
	 * 
	 * @access public
	 * @param mixed $data The Address object or address data array.
	 */
	public function setAddress( $data )
	{
		if ( !is_a( $data, 'Address' ) )
		{
			$data = new Address( $data );
		}
		$this->Address = serialize( $data );
	}

	public function setShipping( $Shipping )
	{
		if ( $Shipping instanceof Shipping )
		{
			$this->Shipping = $Shipping;
		}
		else
		{
			$obj = new Shipping();
			$obj->set( $Shipping );
			$this->Shipping = $obj;
		}
	}
	
	/**
	 * The function returns Order Address object.
	 * 
	 * @access public
	 * @return object The Address.
	 */
	public function getAddress()
	{
		$Address = @unserialize( $this->Address );
		if ( !is_a( $Address, 'Address' ) )
		{
			$Address = new Address();
		}
		return $Address;
	}
	
	public function getCurrency()
	{
		if ( $this->CurrencyPrint )
		{
			if ( is_object( $this->CurrencyPrint ) )
			{
				return $this->CurrencyPrint;
			}
			else
			{
				return @unserialize( $this->CurrencyPrint );
			}
		}
		$Currency = new Currency();
		return $Currency->findItem( array( 'Code = '.$this->Currency ) );
	}
	
	/**
	 * The function updates Order total value.
	 * 
	 * @access protected
	 * @param bool $force If TRUE gets rows from database, otherwise checks in cache.
	 * @return float The new total value.
	 */
	protected function refreshTotal( $force = false )
	{
		$this->Total = $this->TotalRaw = 0;
		foreach ( $this->getRows( $force ) as $Row )
		{
			$this->Total += $Row->getAmount();
			$this->TotalRaw += $Row->getRawAmount();
		}
		return $this->Total;
	}
	
	/**
	 * The function returns total amount includes shipping costs.
	 * 
	 * @access public
	 * @return float The total amount.
	 */
	public function getGrandTotal()
	{
		return $this->Total;
	}
	
	/**
	 * The function clears rows from Order.
	 * 
	 * @access public
	 */
	public function clearRows()
	{
		$this->rows = array();
		$this->rowsChanged = true;
	}
	
	/**
	 * The function adds Row to current Order.
	 * 
	 * @access public
	 * @param mixed $Item The Order Row object or Cart Item object.
	 * @param bool $silent If TRUE flag rowsChanged stays the same, otherwise flag changes to TRUE.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function addRow( $Item, $silent = false )
	{
		$Row = new Order_Row( $this );
		if ( is_a( $Item, 'Cart_Item' ) )
		{
			$Row->ProductId	= $Item->getProduct()->Id;
			$Row->Price		= $Item->getProduct()->Price;
			$Row->PriceRaw	= $Item->getProduct()->PriceRaw;
			$Row->Quantity	= $Item->Quantity;
		}
		else if ( is_a( $Item, 'Order_Row' ) )
		{
			$Row = $Item;
		}
		else
		{
			return false;
		}
		$this->rows[] = $Row;
		if ( !$silent )
		{
			$this->rowsChanged = true;
			$this->refreshTotal();
		}
		return true;
	}
	
	/**
	 * The function returns Order Rows.
	 * 
	 * @access public
	 * @param bool $force If TRUE gets rows from database, otherwise checks in cache.
	 * @return array The array of Rows.
	 */
	public function getRows( $force = false )
	{
		if ( $force || !$this->rowsChanged && !count( $this->rows ) )
		{
			$this->rows = array();
			$Row = new Order_Row( $this );
			foreach ( $Row->findList( array( 'OrderId = '.$this->Id ), 'Id asc' ) as $Row )
			{
				$this->addRow( $Row, true );
			}
		}
		return $this->rows;
	}
	
	/**
	 * The function returns Order id string representation.
	 * 
	 * @access public
	 * @return string The Id.
	 */
	public function id()
	{
		return sprintf( '%05d', $this->Id );
	}
	
	/**
	 * The function returns Order posted date.
	 * 
	 * @access public
	 * @param bool $short The short format.
	 * @return string The date.
	 */
	public function getDate( $short = false, $time = null )
	{
		if ( !$this->PostedAt )
		{
			$this->PostedAt = $time;
		}
		if ( $short )
		{
			return date( 'd.m.Y', $this->PostedAt );
		}
		return Date::formatMonth( date( 'j F Y', $this->PostedAt ), true );
	}
	
	/**
	 * The function returns time of current Order.
	 * 
	 * @access public
	 * @return string The time.
	 */
	public function getTime()
	{
		return date( 'H:i', $this->PostedAt );
	}
	
	/**
	 * The function returns date string converted from unixtime.
	 * 
	 * @access private
	 * @param int $time The unixtime.
	 * @param bool $short If TRUE returns in short format.
	 * @return string The date.
	 */
	private function getRawDate( $time, $short = false )
	{
		if ( $short )
		{
			return date( 'd.m.y', $time );
		}
		return Date::formatMonth( date( 'j F Y', $time ), true );
	}
	
	/**
	 * The function returns Order paid date.
	 * 
	 * @access public
	 * @param bool $short The short format.
	 * @return string The date.
	 */
	public function getPaidDate( $short = false, $time = null )
	{
		return $this->getRawDate( $this->PaidAt ? $this->PaidAt : $time, $short );
	}
	
	/**
	 * The function returns paid time of current Order.
	 * 
	 * @access public
	 * @return string The time.
	 */
	public function getPaidTime( $time = null )
	{
		return date( 'H:i', $this->PaidAt ? $this->PaidAt : $time );
	}
	
	/**
	 * The function returns IP address of customer.
	 * 
	 * @access public
	 * @return string The IP address.
	 */
	public function getIP()
	{
		return long2ip( $this->Ip );
	}
	
	/**
	 * The function returns months of all orders timelime.
	 * 
	 * @access public
	 * @param bool $activeOnly If TRUE returns only active orders months.
	 * @return array The months.
	 */
	public function getMonthsRaw( $activeOnly = false )
	{
		$result = array();
		$params = array();
		if ( $activeOnly )
		{
			$params[] = 'Status <> '.self::CANCELED;
		}
		$query = 'select distinct from_unixtime(PostedAt, "%Y%m") as d from orders where 1 ';
		$query .= $this->db()->sqlParams( $params );
		$query .= ' order by d desc';
		$arr = $this->db()->query( $query );
		$month = explode( ',', _t('months.long') );
		foreach ( $arr as $item )
		{
			if ( count( $month ) == 12 )
			{
				$result[ $item['d'] ] = $month[ intval( substr( $item['d'], 4 ) ) - 1 ].' '.substr( $item['d'], 0, 4 );
			}
			else
			{
				$result[ $item['d'] ] = $item['d'];
			}
		}
		return $result;
	}

	public function getShipping()
	{
		$result = $this->Shipping instanceof Shipping ? $this->Shipping : @unserialize( $this->Shipping );
		if ( is_array( $result ) )
		{
			$Shipping = new Shipping();
			$Shipping->set( $result );
			return $Shipping;
		}
		if ( !( $result instanceof Shipping ) )
		{
			$Shipping = new Shipping();
			return $Shipping->findItem( array( 'Id = '.$this->ShippingId ) );
		}
		return $result;
	}
	
	public function getPayment()
	{
		$Payment = new Payment();
		return $Payment->findItem( array( 'Id = '.$this->PaymentId ) );
	}
	
	/**
	 * The function returns current Order status.
	 * 
	 * @access public
	 * @param bool $translated If TRUE returns translated status, otherwise English key.
	 * @return string The status.
	 */
	public function getStatus( $translated = false )
	{
		$arr = self::getStatuses( $translated );
		return isset( $arr[ $this->Status ] ) ? $arr[ $this->Status ] : null;
	}
	
	/**
	 * The function returns TRUE if order is paid already, othwerwise FALSE.
	 * 
	 * @access public
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function isPaid()
	{
		return $this->PaidAt > 0;
	}
	
	/**
	 * The function returns TRUE if Order must be paid, otherwise FALSE.
	 * 
	 * @access public
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function isPayable()
	{
		return $this->Status != self::CANCELED && !$this->isPaid();
	}

	private function getTotalSumRaw( array $params )
	{
		$arr = $this->db()->query('select sum(Total - Discount) as Total from orders where 1 '.$this->db()->sqlParams( $params ));
		return isset( $arr[0]['Total'] ) ? floatval( $arr[0]['Total'] ) : 0;
	}

	public function getIncome()
	{
		return $this->Total - $this->Discount - $this->TotalRaw;
	}

	/**
	 * The function returns Order statuses..
	 * 
	 * @static
	 * @access public
	 * @param bool $translated If TRUE returns translated status, otherwise English key.
	 * @return array The statuses.
	 */
	public static function getStatuses( $translated = false )
	{
		return array(
			self::POSTED	=> $translated ? 'Новый' : 'posted',
			self::PROCESS	=> $translated ? 'Обрабатывается' : 'process',
			self::DELAY		=> $translated ? 'Задерживается' : 'delay',
			self::SHIPPED	=> $translated ? 'Отправлен' : 'shipped',
			self::DELIVERED	=> $translated ? 'Доставлен' : 'delivered',
			self::RETURNED	=> $translated ? 'Возвращен' : 'returned',
			self::CANCELED	=> $translated ? 'Отменен' : 'canceled',
		);
	}

	/**
	 * The function returns months of all orders timelime.
	 * 
	 * @static
	 * @access public
	 * @param bool $activeOnly If TRUE returns only active orders months.
	 * @return array The months.
	 */
	public static function getMonths( $activeOnly = false )
	{
		$Order = new self();
		return $Order->getMonthsRaw( $activeOnly );
	}

	/**
	 * The function returns sum of orders total amount.
	 *
	 * @static
	 * @access public
	 * @param array $params The search params.
	 * @return float The total sum.
	 */
	public static function getTotalSum( array $params = array() )
	{
		$Order = new self();
		return $Order->getTotalSumRaw( $params );
	}

}
