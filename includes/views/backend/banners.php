<?

/**
 * The Banners view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Banners extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/banners/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/banners/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/banners/form.html');
	}

}
