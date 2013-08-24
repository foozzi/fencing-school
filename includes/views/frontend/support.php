<?

/**
 * The Support view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Frontend_Support extends View_Frontend
{
	
	public function index()
	{
		return $this->includeLayout('view/support/index.html');
	}

}
