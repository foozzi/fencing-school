<?

/**
 * The Currencies view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Currencies extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/currencies/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/currencies/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/currencies/form.html');
	}

}
