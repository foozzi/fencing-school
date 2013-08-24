<?

/**
 * The Cart view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Frontend_Cart extends View_Frontend
{
	
	public function index()
	{
		return $this->includeLayout('view/cart/index.html');
	}

}
