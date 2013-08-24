<?

/**
 * The Calc view class.
 * 
 * @author Igor.
 * @version 0.1
 */
class View_Frontend_Calc extends View_Frontend
{
		
	public function index()
	{
		return $this->includeLayout('view/calc/index.html');
	}

}
