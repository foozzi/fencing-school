<?

class Controller_Frontend_My_Orders extends Controller_Frontend_My
{
	
	public function __construct()
	{
		parent::__construct();
		$this->setView( new View_Frontend_My_Orders() );
	}
	
	public function getName()
	{
		return 'Мой кабинет - мои заказы';
	}
	
	public function index()
	{
		$Order = new Order();
		$params = array();
		$params[] = 'UserId = '.$this->getUser()->Id;
		$this->getView()->set('Orders', $Order->findList( $params, 'PostedAt desc' ));
		return $this->getView()->render();
	}
	
	/**
	 * The detailed order view.
	 * 
	 * @access public
	 * @param int $id The Order id.
	 * @return string The HTML code.
	 */
	public function view( $id = null )
	{		
		$Order = new Order();
		$Order = $Order->findItem( array( 'Id = '.intval( $id ), 'UserId = '.$this->getUser()->Id ) );
		if ( !$Order->Id )
		{
			return '';
		}
		//if ( Request::get('ajax') )
		//{
			return $this->getView()->htmlOrder( $Order );
		//}
		return $this->halt();
	}
	
	public static function htmlShort()
	{
		$Self = new self();
		$Order = new Order();
		$params = array();
		$params[] = $Order->getParam('customer', $Self->getUser());
		return $Self->getView()->htmlShort( $Order->findList( $params, 'PostedAt desc', 0, 5 ) );
	}

}
