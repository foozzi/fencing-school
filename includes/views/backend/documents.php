<?

/**
 * The Documents view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Documents extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/documents/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/documents/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/documents/form.html');
	}

}
