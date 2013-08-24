<?

/**
 * The Shippings view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Shippings extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/shippings/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/shippings/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/shippings/form.html');
	}

}
