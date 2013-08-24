<?

class View_Frontend_My extends View_Frontend
{

	/**
	 * The default page handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		return $this->includeLayout( 'view/my/index.html' );
	}

	public function login()
	{
		return $this->includeLayout( 'view/my/login.html' );
	}

	public function approve()
	{
		return $this->includeLayout( 'view/my/approve.html' );
	}
	
	public function forgot()
	{
		return $this->includeLayout( 'view/my/forgot.html' );
	}			
	
}
