<?

/**
 * The Articles view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Articles extends View_Backend
{
	
	public function htmlReferences( Article $Article )
	{
		return $this->includeLayout('view/articles/references.html', array('Article' => $Article));
	}
	
	public function index()
	{
		return $this->includeLayout('view/articles/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/articles/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/articles/form.html');
	}

}
