<?

/**
 * The Faq view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Faq extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/faq/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/faq/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/faq/form.html');
	}

}
