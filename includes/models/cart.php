<?

/**
 * The Shopping Cart class.
 * 
 * @author Yarick.
 * @version 0.1 
 */
class Cart
{
	
	private static $instance = null;
	
	private $items = array();
	private $shipping = null;
	
	
	private function __construct()
	{
		$this->load();
	}
	
	/**
	 * The function returns Cart Items array.
	 * 
	 * @access public
	 * @return array The Cart Items.
	 */
	public function getItems()
	{
		return $this->items;
	}
	
	/**
	 * The function returns TRUE if items are in cart already.
	 * 
	 * @access public
	 * @return bool TRUE if items are in cart, otherwise FALSE.
	 */
	public function hasItems()
	{
		return count( $this->items ) > 0;
	}
	
	/**
	 * The function clears Cart data.
	 * 
	 * @access public
	 */
	public function clear()
	{
		$this->items = array();
		$this->shipping = null;
	}
	
	/**
	 * The function adds Cart Item to shopping cart.
	 * 
	 * @access public
	 * @param object $Item The shopping cart Item.
	 * @return object The updated or added Cart Item.
	 */
	public function addItem( Cart_Item $Item )
	{
		if ( !$Item->getProduct()->Id )
		{
			return false;
		}
		foreach ( $this->getItems() as $index => $In )
		{
			if ( $In->Product == $Item->Product )
			{
				$In->Quantity += $Item->Quantity;
				return $In;
			}
		}
		$this->items[] = $Item;
		return $Item;
	}
	
	/**
	 * The function returns Cart Item by its id or Product id.
	 * 
	 * @access public
	 * @param mixed $id The Item id or Product object.
	 * @return object The Cart Item object.
	 */
	public function getItem( $id )
	{
		if ( $id instanceof Car_Tyre )
		{
			foreach ( $this->getItems() as $Item )
			{
				if ( $Item->Product == $id->Id )
				{
					return $Item;
				}
			}
			return null;
		}
		return isset( $this->items[ $id ] ) ? $this->items[ $id ] : null;
	}
	
	/**
	 * The function returns Cart Item by its id or Product id.
	 * 
	 * @access public
	 * @param mixed $id The Item id or Item object.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function dropItem( $Item )
	{
		$index = null;
		if ( is_a( $Item, 'Cart_Item' ) )
		{
			foreach ( $this->getItems() as $i => $In )
			{
				if ( $In->equals( $Item ) )
				{
					$index = $i;
					break;
				}
			}
		}
		else
		{
			$index = $Item;
		}
		if ( isset( $this->items[ $index ] ) )
		{
			unset( $this->items[ $index ] );
			return true;
		}
		return false;
	}
	
	/**
	 * The function returns count of Cart Items.
	 * 
	 * @access public
	 * @return int The count.
	 */
	public function getItemsCount()
	{
		return count( $this->items );
	}
	
	/**
	 * The function returns amount of Cart Items.
	 * 
	 * @access public
	 * @return int The amount.
	 */
	public function getItemsAmount()
	{
		$result = 0;
		$Tyre = new Car_Tyre();

		foreach ( $this->getItems() as $Item )
		{
			$params = array();
			$params[] = 'Id = ' . $Item->getProduct()->Id;
			$Tyre = $Tyre->findItem( $params );
			if ( $Tyre->Stock > 0 )
			{
				$result += $Item->Quantity;
			}			
		}
		return $result;
	}
	
	/**
	 * The function returns total price of all items in cart.
	 * 
	 * @access public
	 * @return float The total price.
	 */
	public function getTotal()
	{
		$total = 0;
		$Tyre = new Car_Tyre();
		
		foreach ( $this->getItems() as $Item )
		{
			$params = array();
			$params[] = 'Id = ' . $Item->getProduct()->Id;
			$Tyre = $Tyre->findItem( $params );
			if ( $Tyre->Stock > 0 )
			{
				$total += $Item->getAmount();
			}		
		}
		return $total;
	}
	
	/**
	 * The function returns total price of all items in cart plus shipping price.
	 * 
	 * @access public
	 * @return float The total price.
	 */
	public function getGrandTotal()
	{
		return $this->getTotal() + $this->getShipping()->Price;
	}
	
	/**
	 * The function sets current shipping.
	 * 
	 * @access public
	 * @param object $Shipping The Shipping object.
	 */
	public function setShipping( Shipping $Shipping )
	{
		$this->shipping = $Shipping->Id;
	}
	
	/**
	 * The function returns current shopping cart Shipping object.
	 * If it is not set yet returns first from the list.
	 * 
	 * @access public
	 * @return object The Shipping object.
	 */
	public function getShipping()
	{
		$Shipping = new Shipping();
		$Shipping = $Shipping->findItem( array( 'Id = '.$this->shipping ) );
		if ( !$Shipping->Id )
		{
			foreach ( $Shipping->findList( array( 'IsActive = 1' ), 'Position asc', 0, 1 ) as $Shipping )
			{
				return $Shipping;
			}
		}
		return $Shipping;
	}
	
	/**
	 * The function loads items to shopping cart.
	 * 
	 * @access private
	 */
	private function load()
	{
		$data = Request::get( 'ShoppingCartData', array(), 'SESSION' );
		$this->items	= isset( $data['items'] ) ? $data['items'] : array();
		$this->shipping	= isset( $data['shipping'] ) ? $data['shipping'] : null;
	}
	
	/**
	 * The function saves shopping cart items.
	 * 
	 * @access public
	 */
	public function save()
	{
		$_SESSION['ShoppingCartData'] = array(
			'items'		=> $this->items,
			'shipping'	=> $this->shipping,
		);
	}
	
	/**
	 * The function returns Order created from shopping cart.
	 * 
	 * @access public
	 * @return object The Order.
	 */
	public function getOrder()
	{
		$Order = new Order();
		$Order->setShipping( $this->getShipping() );
		foreach ( $this->getItems() as $Item )
		{
			$Order->addRow( $Item );
		}
		return $Order;
	}
	
	/**
	 * The singleton function returns Cart object.
	 * 
	 * @static
	 * @access public
	 * @return object The Cart object.
	 */
	public static function getCart()
	{
		if ( self::$instance === null )
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
}
