<?

/**
 * The Delivery view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Frontend_Delivery extends View_Frontend
{
	
	public function index()
	{
		return $this->includeLayout('view/delivery/index.html');
	}

}
