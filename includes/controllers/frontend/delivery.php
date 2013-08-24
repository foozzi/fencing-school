<?

/**
 * The Contact controller class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Controller_Frontend_Delivery extends Controller_Frontend
{
	
	/**
	 * @see parent::getName()
	 */
	public function getName()
	{
		return 'Доставка';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		return $this->getView()->render();
	}
	
}
