<?

/**
 * The Pages view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Pages extends View_Backend
{
	
	protected function htmlListItem( Content_Page $Page )
	{
		return $this->includeLayout('view/pages/list.item.html', array('Page' => $Page));
	}
	
	public function index()
	{
		return $this->includeLayout('view/pages/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/pages/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/pages/form.html');
	}

	public function addb()
	{
		return $this->includeLayout('view/pages/block.html');
	}

	public function editb()
	{
		return $this->includeLayout('view/pages/block.html');
	}

}
