<?

/**
 * The Base View class for children views.
 * 
 * @version 0.1
 */
class View_Base extends View
{
	
	private $layout = 'main.html';
	
	/**
	 * The function returns current controller link.
	 * If passed link starts from / returns related to root controller.
	 * 
	 * @access protected
	 * @param string $link The part of link.
	 * @param bool $restoreGet If TRUE returns link with GET parameters.
	 * @return string The link.
	 */
	protected function getLink( $link = '', $restoreGet = false )
	{
		$Controller = $this->getController();
		if ( substr( $link, 0, 1 ) == '/' )
		{
			$Controller = Route::getController('/');
		}
		if ( $link )
		{
			$link = '/'.ltrim( $link, '/' );
		}
		if ( $restoreGet && count( $_GET ) )
		{
			$link .= strpos( $link, '?' ) === false ? '?' : '&';
			$link .= http_build_query( $_GET );
		}
		return rtrim( _L( $Controller ), '/' ).$link;
	}
	
	/**
	 * The function returns META Title value.
	 * 
	 * @access protected
	 * @return string The Title.
	 */
	protected function getTitle()
	{
		return 'TITLE';
	}
	
	/**
	 * The function returns Page Seo Title.
	 * 
	 * @access protected
	 * @return string The Seo title.
	 */
	protected function getSeoTitle()
	{
		return '';
	}
	
	public function setLayout( $layout = 'main.html' )
	{
		$this->layout = $layout;
	}
	
	protected function getLayout()
	{
		return $this->layout;
	}

	/**
	 * The function returns Page Seo Keywords.
	 * 
	 * @access protected
	 * @return string The Seo keywords.
	 */
	protected function getSeoKeywords()
	{
		return '';
	}

	/**
	 * The function returns Page Seo Description.
	 * 
	 * @access protected
	 * @return string The Seo description.
	 */
	protected function getSeoDescription()
	{
		return '';
	}
	
	/**
	 * The function returns phone HTML code.
	 * 
	 * @access protected
	 * @param string $phone The phone.
	 * @return string The HTML code.
	 */
	protected function htmlPhone( $phone )
	{
		return substr( $phone, 0, strlen( $phone ) - 9 ).'<span class="big">'.substr( $phone, -9 ).'</span>';
	}

	/**
	 * The function executes current controller method in view.
	 * 
	 * @access protected
	 * @return string The method result.
	 */
	protected function htmlBody()
	{
		if ( method_exists( $this, $this->getMethod() ) )
		{			
			return eval( 'return $this->'.$this->getMethod().'();' );
		}
		return 'No View method found: '.$this->getMethod()."\n";
	}
	
	/**
	 * The function returns tracker's HTML code.
	 * 
	 * @access protected
	 * @param string $target The tracker target place on page.
	 * @return string The HTML code.
	 */
	protected function htmlTracker( $target = null )
	{
		if ( Request::onLocalhost() )
		{
			return '';
		}
		return $this->includeLayout( 'block/tracker-'.$target.'.html' );
	}

	protected function htmlDeveloper()
	{
		return $this->includeLayout( '../frontend/block/developer.html' );
	}
	
	protected function htmlContacts()
	{
		return $this->includeLayout( '../frontend/block/contacts.html' );
	}
	
}
