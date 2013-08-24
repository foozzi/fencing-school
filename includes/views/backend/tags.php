<?

/**
 * The Tags view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Tags extends View_Backend
{

	public function add()
	{
		return $this->includeLayout('view/tags/form.html');
	}

	public function index()
	{
		return $this->includeLayout('view/tags/index.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/tags/form.html');
	}

}
