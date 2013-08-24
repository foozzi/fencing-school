<?

/**
 * The Comments view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Comments extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/comments/index.html');
	}

	public function approve()
	{
		return $this->includeLayout('view/comments/form.html');
	}
	
	public function edit()
	{
		return $this->includeLayout('view/comments/form.html');
	}

	public function answer()
	{
		return $this->includeLayout('view/comments/answer.html');
	}

}
