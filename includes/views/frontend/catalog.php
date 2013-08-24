<?

/**
 * The Catalog view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Frontend_Catalog extends View_Frontend
{
	
	protected function fitTyre( Car_Tyre $Tyre )
	{
		$arr = array( 'r' => 'Profile', 'w' => 'Width', 'code' => 'Code', 'd' => 'Diameter', 
			'pcd' => 'PCD', 'et' => 'ET', 'hub' => 'HUB' );
		$any = false;
		foreach ( $arr as $key => $column )
		{
			if ( Request::get( $key ) )
			{
				$any = true;
			}
		}
		if ( !$any )
		{
			return false;
		}
		foreach ( $arr as $key => $column )
		{
			if ( Request::get( $key ) )
			{
				if ( Request::get( $key ) != $Tyre->$column )
				{
					return false;
				}
			}
		}
		return true;
	}
	
	protected function getLink( $link = '', $restoreGet = false, $tag = '' )
	{
		$Controller = $this->getController();
		if ( substr( $link, 0, 1 ) == '/' )
		{
			$Controller = Route::getController('/');
		}
		$url = _L( $Controller );
		if ( $tag instanceof Car_Engine && $tag->Id )
		{
			 $url = _L( $this->getController() ).'/'
				.$tag->getBrand()->Slug.'-'.$tag->getModel()->Slug.'-'.$tag->Slug.'-'.$tag->Year.'/';
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
		return rtrim( $url, '/' ).$link;
	}

	protected function getGroups( array $children )
	{
		$result = array();
		foreach ( $children as $Child )
		{
			if ( !in_array( $Child->getGroupValue(), $result ) )
			{
				$result[] = $Child->getGroupValue();
			}
		}
		return $result;
	}

	protected function getSorts()
	{
		return array(			
			'pa' => 'цене по возрастанию',
			'pd' => 'цене по убыванию',
			'nd' => 'новинкам',
			'od' => 'популярности',			
		);
	}
	
	protected function htmlCartDialog()
	{
		return $this->includeLayout('view/cart/dialog.html');
	}

	protected function htmlSortForm()
	{
		return $this->includeLayout('view/catalog/sort.html');
	}

	protected function htmlFilterForm( Car_Engine $Engine )
	{
		return $this->includeLayout( 'view/catalog/filter.html', array(
			'seasons'	=> array(
				Car_Tyre::ANY 		=> 'Всесезонные',
				Car_Tyre::WINTER 	=> 'Зимние',
				Car_Tyre::SUMMER	=> 'Летние',
			),
			'Engine'	=> $Engine,
		) );
	}
	
	protected function htmlFilterCodes( array $Codes, $nolinks = false )
	{
		return $this->includeLayout('view/catalog/codes.html', array('Codes' => $Codes, 'nolinks' =>$nolinks));
	}
	
	public function htmlList( array $Tyres, Paginator $Paginator = null )
	{
		if ( $Paginator === null )
		{
			$Paginator = $this->get('Paginator');
		}
		return $this->includeLayout('view/catalog/list.html', array('Tyres' => $Tyres, 'Paginator' => $Paginator));
	}
	
	protected function htmlFilter( $type, array $Brands, array $Codes )
	{
		$file = 'filter.extra.html';
		if ( $type == Car_Brand::WHEEL )
		{
			$file = 'filter.wheel.html';
		}
		else if ( $type == Car_Brand::TYRE )
		{
			$file = 'filter.tyre.html';
		}
		return $this->includeLayout('view/catalog/'.$file, array('Brands' => $Brands, 'Codes' => $Codes));
	}
	
	public function index()
	{
		return $this->includeLayout('view/catalog/index.html');
	}
	
	public function view()
	{
		return $this->includeLayout('view/catalog/view.html');
	}
	
}
