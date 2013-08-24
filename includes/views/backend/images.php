<?

/**
 * The Images view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Images extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/images/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/images/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/images/form.html');
	}

}
