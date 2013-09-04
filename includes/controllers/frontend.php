<?

/**
 * The Frontend controller.
 * 
 * @author Yarick.
 * @version 0.1 
 */
class Controller_Frontend extends Controller_Base
{
	
	protected $defaultAccess = Access::FULL;

	private $contentPage;
	
	/**
	 * @see parent::isHidden()
	 */
	public function isHidden()
	{
		return true;
	}
	
	/**
	 * The function returns TRUE if current content page is shown in menu, otherwise FALSE.
	 * 
	 * @acess public
	 * @param object $Page The current content page.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function isPageInMenu( Content_Page $Page )
	{
		return true;
	}

	/**
	 * @see parent::getUser()
	 */
	public function getUser()
	{
		return Runtime::get('SECURITY_CUSTOMER');
	}
	
	/**
	 * The function returns array of sitemap items for sitemap.xml
	 * 
	 * @access public
	 * @return array
	 */
	public function getSitemapNode()
	{
		return array( URL::abs( $this->getContentPage()->Link ) );
	}
	
	private function showPage( Content_Page $Page )
	{
		$this->getView()->setMethod('showPage');
		return $this->getView()->render();
	}
	
	/**
	 * The function returns name of controller.
	 * 
	 * @access public
	 * @return string The name.
	 */
	public function getName()
	{
		return get_class( $this );
	}
	
	public function setContentPage( $Object )
	{
		$Page = new Content_Page();
		if ( $Object instanceof Controller_Frontend )
		{
			$Page = $Page->findItem(array('Module = ' . get_class($Object)));
		}
		else if ( $Object instanceof Product )
		{
			$Page = $this->getCategoryPage($Object->getCategory());
			$Page->SeoTitle = $Object->Name;
		}
		else if ( $Object instanceof Product_Category )
		{
			$Page = $this->getCategoryPage($Object);
			$Page->SeoTitle = $Object->Name;
		}
		else if ( $Object instanceof Content_Page )
		{
			$Page = $Object;
		}
		Runtime::set('ROUTING_PAGE', $Page);
	}
	
	/**
	 * The function returns Content Page for current URL.
	 * 
	 * @access public
	 * @param bool $except404 If TRUE do not fetch the ErrorDocument page, otherwise fetch.
	 * @return object The Content Page.
	 */
	public function getContentPage( $except404 = false )
	{
		if ( $this->contentPage )
		{
			return $this->contentPage;
		}
		if ( !Runtime::get('ROUTING_PAGE') )
		{
			$Page = new Content_Page();
			$arr = explode( '/', Runtime::get('REQUEST_URI') );
			for ( $i = count( $arr ); $i > 0; $i-- )
			{
				$url = implode( '/', array_slice( $arr, 0, $i ) );
				$Page = $Page->findItem( array( 'Link = '.$url ) );
				if ( $Page->Id )
				{
					break;
				}
			}
			if ( !$except404 && !$Page->Id )
			{
				$Page = $Page->findItem( array( 'module = Controller_Frontend_404' ) );
			}
			Runtime::set('ROUTING_PAGE', $Page);
		}
		return Runtime::get('ROUTING_PAGE');
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{		
		$Article = new Article();		
		$Banner = new Banner();
		$Carousel = new Carousel();
		foreach ( $Banner->findList( array('Position = '.Banner::MAIN), 'rand()', 0, 1 ) as $Banner );		
		$this->getView()->set( 'Articles', $Article->findShortList( array('Type = '.Article::NEWS), 'PostedAt desc', 0, 4 ) );	
		$this->getView()->set( 'Banner', $Banner );		
		$params = array();
		$this->getView()->set( 'Carousels', $Carousel->findList( $params ) );
		$this->getView()->bodyId = 'main';
		$Articles = $Article->findShortList( 'PostedAt desc, Id desc', $this->getOffset(), $this->getLimit() );
		foreach ( $Articles as $Article )
		{
			$this->getView()->set( 'Article', $Article );
		}
		return $this->getView()->render();
	}	
	
	public function promo()
	{
		return $this->getView()->render();
	}
	
	public function search()
	{
		return $this->getView()->render();
	}
	
	/**
	 * The noMethod hanlder.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function noMethod( $method = null, $tag = null )
	{		
		if ( preg_match( '/^([^-]+)-([^-]+)-(\d+)$/', $method, $res ) )
		{
			$Tyre = new Car_Tyre();
			$Tyre = $Tyre->findItem( array( 'Id = '.$res[3] ) );
			$Controller = $Tyre->getController();
			$Controller->setView();
			$Page = new Content_Page();
			$Page = $Page->findItem( array( 'Module = '.get_class( $Controller ) ) );
			if ( $Page->Id )
			{
				Runtime::set('ROUTING_PAGE', $Page);
			}
			$Engine = self::getEngine( $tag );
			return $Controller->view( $Tyre, $Engine );
		}
		$Page = $this->getContentPage(true);
		if ( $Page->Id )
		{
			return $this->showPage( $Page );
		}
		return $this->notFound();
	}
	
	/**
	 * The error document page handler.
	 * 
	 * @access protected
	 * @return string The HTML code.
	 */
	protected function notFound()
	{
		header("HTTP/1.0 404 Not Found");
		$Page = new Content_Page();
		$Page = $Page->findItem( array( 'module = Controller_Frontend_404' ) );
		Runtime::set( 'ROUTING_PAGE', $Page );		
		$this->getView()->setMethod('notFound');
		return $this->getView()->render();
	}
	
	/**
	 * The noAccess handler
	 * 
	 * @access public
	 */
	public function noAccess()
	{
	}
	
	/**
	 * The function returns array of modules.
	 * 
	 * @static
	 * @access public
	 * @return array The array of modules.
	 */
	public static function getModules()
	{
		$result = array();
		$dir = dirname( __FILE__ ).'/frontend';
		foreach ( File::readDir( $dir, true ) as $file )
		{
			$file = str_replace( $dir.'/', '', $file );
			$name = 'Controller_Frontend_'.basename( str_replace( '/', '_', $file ),'.php' );
			$class = new $name();
			$result[ get_class( $class ) ] = $class->getName();
		}
		asort( $result );
		return $result;
	}
	
	/**
	 * TLhe function returns array of layouts.
	 * 
	 * @static
	 * @access public
	 * @return array The array of layouts.
	 */
	public static function getLayouts()
	{
		$result = array();
		foreach ( File::readDir( Runtime::get('TEMPLATE_DIR').'/frontend/layout/' ) as $file )
		{
			$result[ basename( $file ) ] = basename( $file, '.html' );
		}
		asort( $result );
		return $result;
	}
	
	protected static function getEngine( $string )
	{
		//die(var_dump($string));
		if ( preg_match( '/^([^-]+)-([^-]+)-([^-]+)-(\d{4})$/', $string, $res ) )
		{
			$Brand = new Car_Brand();
			$Brand = $Brand->findItem( array( 'Type = '.Car_Brand::CAR, 'Slug = '.$res[1] ) );
			if ( $Brand->Id )
			{
				$Model = new Car_Model();
				$Model = $Model->findItem( array( 'BrandId = '.$Brand->Id, 'Slug = '.$res[2] ) );
				if ( $Model->Id )
				{
					$Engine = new Car_Engine();
					return $Engine->findItem( array( 'ModelId = '.$Model->Id, 'Year = '.$res[4], 'Slug = '.$res[3] ) );
				}
			}
		}
		return false;
	}
	
}
