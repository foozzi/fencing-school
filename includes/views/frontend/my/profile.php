<?

class View_Frontend_My_Profile extends View_Frontend_My
{
	
	public function htmlShort()
	{
		return $this->includeLayout('view/my/profile/short.html');
	}
	
	public function index()
	{
		return $this->includeLayout('view/my/profile/index.html');
	}

}
