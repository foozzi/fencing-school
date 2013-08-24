<?

/**
 * The Settings view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Settings extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/settings/index.html');
	}
	
	public function footer()
	{
		return $this->includeLayout('view/settings/footer.html');
	}

	public function search()
	{
		return $this->includeLayout('view/settings/search.html');
	}
	
	public function social()
	{
		return $this->includeLayout('view/settings/social.html');
	}

	public function contacts()
	{
		return $this->includeLayout('view/settings/contacts.html');
	}

	public function email()
	{
		return $this->includeLayout('view/settings/email.html');
	}

	public function links()
	{
		return $this->includeLayout('view/settings/links.html');
	}

}
