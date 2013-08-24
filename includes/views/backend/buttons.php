<?

/**
 * The Buttons view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Buttons extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/buttons/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/buttons/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/buttons/form.html');
	}

}
