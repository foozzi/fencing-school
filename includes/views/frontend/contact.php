<?

/**
 * The Contact view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Frontend_Contact extends View_Frontend
{
	
	public function index()
	{
		//return $this->includeLayout('view/contact/layout.html');
		return $this->includeLayout('view/contact/index.html');
	}

}
