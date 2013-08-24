<?

/**
 * The Payments controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Payments extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Payment';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Оплата';
	}

	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$this->addBreadcrumb( new Breadcrumb( $this ) );
		$Payment = new Payment();
		$params = array();
		$this->getView()->set( 'Payments', $Payment->findList( $params, 'Position asc' ) );
		return $this->getView()->render();
	}
	
}