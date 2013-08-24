<?

/**
 * The Frontend View class.
 * 
 * @version 0.1
 */
class View_Frontend extends View_Base
{
	
	private static $abilities = null;
	private static $trends = null;
	private static $clients = null;
	private static $articles = null;
	private static $about = null;
	public $bodyId = '';
	public $headBanner = false;
	/**
	 * @see parent::includeLayout()
	 */
	protected function includeLayout( $layout, $data = null )
	{
		$this->set('Page', $this->getController()->getContentPage());
		$this->set( 'User', $this->getController()->getUser() );
		return parent::includeLayout( $layout, $data );
	}
	
	/**
	 * @see parent::getTitle()
	 */
	protected function getTitle()
	{
		return $this->getController()->getContentPage()->Title;
	}
	
	/**
	 * @see parent::getSeoTitle()
	 */
	protected function getSeoTitle()
	{
		return $this->getController()->getContentPage()->SeoTitle;
	}

	/**
	 * @see parent::getSeoKeywords()
	 */
	protected function getSeoKeywords()
	{
		return $this->getController()->getContentPage()->SeoKeywords;
	}

	/**
	 * @see parent::getSeoDescription()
	 */
	protected function getSeoDescription()
	{
		return $this->getController()->getContentPage()->SeoDescription;
	}

	/**
	 * @see parent::getLayoutDir()
	 */
	protected function getLayoutDir()
	{
		return parent::getLayoutDir().'/frontend';
	}
	
	protected function getBrands()
	{
		$result = array();
		foreach ( Car_Brand::getBrands( Car_Brand::CAR ) as $Brand )
		{
			$result[] = array(
				'value'		=> $Brand->Id,
				'text'		=> $Brand->Name,
				'tag'		=> $Brand->Slug,
			);
		}
		return $result;
	}
	
	protected function getModels( Car_Engine $Engine )
	{
		$result = array();
		foreach ( $Engine->getModel()->getBrand()->getModels() as $Model )
		{
			$result[] = array(
				'value'		=> $Model->Id,
				'text'		=> $Model->Name,
				'tag'		=> $Model->Slug,
			);
		}
		return $result;
	}
	
	protected function getEngines( Car_Engine $Engine )
	{
		$result = array();
		foreach ( $Engine->getModel()->getEngines( $Engine->Year ) as $Engines )
		{
			$result[] = array(
				'value'		=> $Engines->Id,
				'text'		=> $Engines->Name,
				'tag'		=> $Engines->Slug,
			);
		}
		return $result;
	}

	/**
	 * The function shows current Controller Page.
	 * 
	 * @access protected
	 * @return string The HTML code.
	 */
	protected function showPage()
	{
		return $this->includeLayout( 'view/page.html', array( 'Page' => $this->getController()->getContentPage() ) );
	}
	
	/**
	 * The function returns menu HTML code.
	 * 
	 * @access protected
	 * @return string The HTML code.
	 */
	protected function htmlMenu()
	{
		return $this->includeLayout( 'block/menu.html' );
	}

	/**
	 * The function returns header HTML code.
	 * 
	 * @access protected
	 * @return string The HTML code.
	 */
	protected function htmlHeader()
	{
		return $this->includeLayout( 'block/header.html' );
	}

	/**
	 * The function returns footer HTML code.
	 * 
	 * @access protected
	 * @return string The HTML code.
	 */
	protected function htmlFooter()
	{
		return $this->includeLayout( 'block/footer.html' );
	}
	
	/**
	 * The function returns social share HTML code.
	 * 
	 * @access protected
	 * @return string The HTML code.
	 */
	protected function htmlSocialShare()
	{
		return $this->includeLayout( 'block/social-share.html' );
	}        
        
        /**
	 * The function returns google analitics and yandex metrica HTML code.
	 * 
	 * @access protected
	 * @return string The HTML code.
	 */
    protected function htmlAnalitics()
	{
		return $this->includeLayout( 'block/analitics.html' );
	}
	
	/**
	 * The function returns Page blocks.
	 * 
	 * @access protected
	 * @param object $Page The Content Page.
	 * @return string The HTML code.
	 */
	protected function htmlPageBlocks( Content_Page $Page )
	{
		return $this->includeLayout( 'block/blocks.html', array( 'Page' => $Page ) );
	}
	
	/**
	 * The function returns paging HTML code.
	 * 
	 * @access protected
	 * @return string The HTML code.
	 */
	protected function htmlPaginator()
	{
		return $this->includeLayout( 'block/paginator.html' );
	}
	
	protected function htmlCurrencyBox()
	{
		return $this->includeLayout('block/currency-box.html');
	}
	
	protected function htmlSliderMain( $Tyres )
	{
		return $this->includeLayout( 'block/slider-main.html' , array('Tyres' => $Tyres) );
	}
	
	protected function htmlSearchForm()
	{
		if ( $this->bodyId != 'main' )
		{
			return '';
		}
		return $this->includeLayout( 'block/searchform.html' );
	}
	
	protected function htmlBanner( Banner $Banner = null )
	{
		if ( !$Banner )
		{
			$Banner = new Banner();
			foreach ( $Banner->findList( array('Position = '.Banner::TOP), 'rand()', 0, 1 ) as $Banner );
		}
		return $this->includeLayout( 'block/banner.html' , array('Banner'=>$Banner));
	}
	
	protected function htmlHeadBanner()
	{
		if ( $this instanceof View_Frontend_Catalog || $this instanceof View_Frontend_Contact )
		{
			$Banner = new Banner();
			foreach ( $Banner->findList( array('Position = '.Banner::TOP), 'rand()', 0, 1 ) as $Banner );
			return $this->includeLayout( 'block/head-banner.html' , array('Banner'=>$Banner));
		}
		return '';
	}

	protected function htmlAutoDialog( $type = null )
	{
		return $this->includeLayout('view/catalog/auto.html', array('Type' => $type));
	}

	/**
	 * The function returns Shopping cart status info.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function htmlCartStatus()
	{
		return $this->includeLayout('view/cart/status.html', array('Cart' => Cart::getCart()));
	}
	
	protected function htmlCartDialog( )
	{
		return $this->includeLayout('view/cart/dialog.html');
	}
	
	/**
	 * The function returns address (contact) block for order.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function htmlCartAddress()
	{
		return $this->includeLayout('view/cart/contact.html', array('Cart' => Cart::getCart()));
	}
	
	/**
	 * The function returns Tyre item html block.
	 * 
	 * @access public
	 * @param object $Tyre The Tyre.
	 * @return string The HTML code.
	 */
	public function htmlTyreItem( Car_Tyre $Tyre, Car_Engine $Engine = null )
	{
		if ( !$Engine )
		{
			$Engine = new Car_Engine();
		}
		return $this->includeLayout('view/catalog/item.html', array('Tyre' => $Tyre, 'Engine' => $Engine));
	}

	/**
	 * The function returns page content.
	 *
	 * @access protected
	 * @return string The HTML code.
	 */
	protected function htmlLayout()
	{
		return $this->includeLayout( 'layout/'.$this->getController()->getContentPage()->getLayout() );
	}
	
	/**
	 * The function returns Page header HTML block.
	 *
	 * @access protected
	 * @return string The HTML code.
	 */
	protected function htmlPageHeader()
	{
		return '';
	}
		
	protected function htmlAuthBox( $className = null )
	{
		return $this->includeLayout( 'block/auth-box.html', array('className' => $className) );
	}
	
	protected function htmlAddthis()
	{
		return $this->includeLayout( 'block/addthis.html' );
	}

	protected function htmlNavbar()
	{
		return $this->includeLayout( 'block/nav.html' );
	}

	protected function htmlSponsor()
	{
		return $this->includeLayout( 'block/sponsors.html' );
	}


	/**
	 * The function returns TRUE if current page (controller) is same to passed controller.
	 * 
	 * @access protected
	 * @param string $controller The controller name.
	 * @param bool $exact If TRUE checks only for current page, otherwise for parent as well.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	protected function on( $controller, $exact = false )
	{
		if ( $controller instanceof Content_Page )
		{
			$Page = $this->getController()->getContentPage();			
			return $Page->Id == $controller->Id || ( ( $Page->Id == $controller->ParentId || $Page->ParentId == $controller->Id ) && !$exact );
		}
		return $controller == get_class( $this->getController() );
	}
	
	/**
	 * The error 404 document.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function notFound()
	{
		return $this->includeLayout('view/page.html');
	}
	
	/**
	 * The function returns poll box HTML code.
	 * 
	 * @access public
	 * @param object $Poll The Poll
	 * @return string The HTML code.
	 */
	public function htmlPoll( Poll $Poll )
	{
		return $this->includeLayout( 'block/poll.html', array( 'Poll' => $Poll ) );
	}
	
	/**
	 * The homepage handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		return $this->includeLayout('view/index/index.html');
	}
	
	public function getDate()
	{
		return date('Y-m-d');
	}

}
