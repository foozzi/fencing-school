<?

/**
 * The Catalog controller class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Controller_Frontend_Catalog extends Controller_Frontend
{
	/**
	 * @see parent::getSitemapNode();
	 */
	public function getSitemapNode()
	{
		$result = $params = array();
		$Tyre = new Car_Tyre();
		$params[] = 'Type = ' . $this->getType();
		$params[] = $Tyre->getParam('online');

		foreach ( $Tyre->findList($params, 'Id desc') as $Tyre )
		{
			$result[] = URL::get($Tyre);
		}
		return $result;
	}

	/**
	 * @see parent::getName()
	 */
	public function getName()
	{
		return 'Каталог';
	}

	/**
	 * @see parent::getLimit()
	 */
	public function getLimit()
	{
		return Request::get('limit', 12);
	}

	public function getType()
	{
		return 1;
	}

	public function getSort( $key = false )
	{
		$arr = self::getSorts();
		$keys = array_keys($arr);
		$s = Request::get('sort', $keys[1]);		
		if ( $key )
		{
			return $s;
		}
		return isset($arr[$s]) ? $arr[$s] : null;
		//$order = isset($arr[$s]) ? $arr[$s] : null;
		//if ( isset($_GET['sale']) && $_GET['sale'] && isset($arr[$_GET['sale']]) )
		//	$order = $arr[$_GET['sale']].$order;
		
		//return $order;
	}

	/**
	 * The catalog index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$this->getView()->headBanner = true;		
		return $this->showTyres();
	}

	protected function showTyres( Car_Engine $Engine = null, $Promo = false )
	{	
		$cats = Request::get('c', array());
		if ( !is_array($cats) )
		{
			$cats = array();
		}
		$cats = filter_var_array($cats, FILTER_SANITIZE_NUMBER_INT);
		$_GET['c'] = $cats;

		$Tyre = new Car_Tyre();
		$params = array();
		//$params[] = 'ParentId = 0';
		$params[] = 'Type = ' . $this->getType();
		if ( Request::get('engine') )
		{
			$Engine = new Car_Engine();
			$Engine = $Engine->findItem(array('Id = ' . Request::get('engine')));
		}

		$Engine = $Engine ? $Engine : new Car_Engine();
		$_GET['engine'] = $Engine->Id;
		$Codes = array();
		if ( $Engine->Id )
		{
			foreach ( $Engine->getTyres($this->getType(), false) as $Item )
			{
				$Tyre = new Car_Tyre();
				$clause = array();
				$clause[] = 'Type = ' . $Item->Type;
				$clause[] = 'Code = ' . $Item->Code;
				$clause[] = 'Stock > 0';	
				$clause[] = 'isActive = 1';
				if ( $Tyre->findSize($clause) == 0 )
				{
					//continue;
				}
				$f = $Item->getFactory(true);
				if ( !isset($Codes[$f]) )
				{
					$Codes[$f] = array();
				}
				$a = $Item->getAxle();
				$Codes[$f][$a][] = $Item;
				
				/*
				  if ( !isset( $Codes[ $f ][ $a ] ) )
				  {
				  $Codes[ $f ][ $a ][] = $Item;
				  }
				 */
			}
			/*if ( !Request::get('code') )
			{
				$break = false;
				foreach ( $Codes as $i => $set )
				{
					foreach ( $set as $arr )
					{
						foreach ( $arr as $Item )
						{
							$_GET['code'] = $Item->Code;
							$break = true;
							break;
						}
						if ( $break )
						{
							break;
						}
					}
					if ( $break )
					{
						break;
					}
				}
			}*/

			if ( Request::get('code') && $this->getType() == Car_Brand::TYRE )
			{
				$params[] = 'Code = ' . Request::get('code');
			}
			else
			{
				$params[] = $Tyre->getParam('codes', $Engine);
			}
		}

		$params[] = $Tyre->getParam('online');
		$range = $Tyre->getPriceLimits($params);
		$rangePromo = $Tyre->getPriceLimits($params, true);
		
		$Brands = $Tyre->getBrands($params);		
		if ( Request::get('s') )
		{
			$params[] = 'Season = ' . Request::get('s');
		}
		if ( Request::get('a') )
		{
			$params[] = 'Auto = ' . Request::get('a');
		}
		if ( Request::get('sp') )
		{
			$params[] = 'Spike = ' . Request::get('sp');
		}
		if ( Request::get('b') )
		{
			$params[] = 'BrandId = ' . Request::get('b');
		}
		if ( count($cats) )
		{
			$params[] = '* CategoryId in (' . implode(',', $cats) . ')';
		}
		$vars = array('w' => 'Width', 'd' => 'Diameter', 'pcd' => 'PCD', 'et' => 'ET', 'hub' => 'HUB', 'r' => 'Profile',
			'min' => 'PriceMin', 'max' => 'PriceMax', 'minpromo' => 'PriceMinPromo', 'maxpromo' => 'PriceMaxPromo');
		foreach ( $vars as $id => $value )
		{
			if ( Request::get($id) )
			{
				$params[] = $Tyre->getParam($value, Request::get($id));
			}
		}		
		if ( $Promo )
		{
			$params[] = 'PriceShare > 1';
			$params[] = 'Price > 1';			
		}				
		$_GET['sort'] = $this->getSort(true);
		$Tyres = $Tyre->findComplex($params, $this->getSort(), $this->getOffset(), $this->getLimit());		
		//die(Database::getInstance()->getLastQuery());
		$Paginator = new Paginator($Tyre->findComplexSize($params), $this->getLimit(), $this->getPage(), 5);
		$this->getView()->set(array(
			'Engine' => $Engine,
			'Codes' => $Codes,
			'Brands' => $Brands,
			'Tyres' => $Tyres,			
			'Paginator' => $Paginator,
			'PriceMin' => floor(floatval($range[0]) / 10) * 10,
			'PriceMax' => ceil(floatval($range[1]) / 10) * 10,
			'PriceMinPromo' => floor(floatval($rangePromo[0]) / 10) * 10,
			'PriceMaxPromo' => ceil(floatval($rangePromo[1]) / 10) * 10
		));
		if ( Request::get('ajax') )
		{
			$response = array();
			if ( Request::get('action') == 'count' )
			{
				$response['count'] = $Paginator->Size;
			}
			if ( Request::get('filters') )
			{				
				$response['w'] = Car_Tyre::getParameters('Width', $this->getType(), true, $params);
				$response['r'] = Car_Tyre::getParameters('Profile', $this->getType(), true, $params);
				$response['d'] = Car_Tyre::getParameters('Diameter', $this->getType(), true, $params);
				$response['b'] = Car_Brand::getBrands(Car_Brand::TYRE, true, true, $params);
				$response['params'] = $params;
			}
			return $this->outputJSON($response);
		}
		$this->getView()->setMethod('index');
		$this->setContentPage($this);
		return $this->getView()->render();
	}

	/**
	 * Сheck whether there is a promo price 
	 * 
	 * @access public
	 * @return bool
	 */
	public function checkPromo()
	{
		$Tyre = new Car_Tyre();
		
		$params = array();
		$params[] = 'PriceShare > 0';
		$params[] = 'Stock > 0';
		$Tyres = $Tyre->findList( $params );
		if ( $Tyres )
		{
			return true;
		}

		return false;
	}


	/**
	 * The catalog view handler.
	 * 
	 * @param int $id The Product id.
	 * @return string The HTML code.
	 */
	public function view( $id = null, $engine = null )
	{
		$Tyre = new Car_Tyre();
		if ( $id instanceof Car_Tyre )
		{
			$Tyre = $id;
		}
		else
		{
			$Tyre = $Tyre->findItem(array('Id = ' . $id));
		}
		if ( !$Tyre->Id )
		{
			return $this->halt('', true);
		}
		$Engine = $engine instanceof Car_Engine ? $engine : new Car_Engine();
		$Page = $this->getContentPage();
		$Page->SeoTitle = $Tyre->Name . ' | ' . $Page->SeoTitle;
		$this->getView()->setMethod('view');
		$this->getView()->set('Tyre', $Tyre);
		$this->getView()->set('Engine', $Engine);
		$this->getView()->set('Diameter', Request::get('d'));
		$this->getView()->set('Comments', $Tyre->getComments());
		
		if ( !$Tyre->isFilled(true) && $Tyre->Stock > 0 )
		{			
			return $this->halt('', true);
		}				
		return $this->getView()->render();
	}

	/**
	 * The function returns part of HTML code depends on method.
	 * 
	 * @access public
	 * @return string The JSON response.
	 */
	public function json( $method = null )
	{
		$method = Request::get('method', $method);
		$response = array('result' => 0);
		Request::flashNull();
		switch ( $method )
		{
			case 'choose':
				if ( Request::get('Engine') )
				{
					$Engine = new Car_Engine();
					$Engine = $Engine->findItem(array('Id = ' . Request::get('Engine')));
					$Tyre = new Car_Tyre();
					$response['items'] = array();
					$response['wheelsCount'] = $Tyre->getCount(Car_Brand::WHEEL, $Engine, true);
					$response['tyresCount'] = $Tyre->getCount(Car_Brand::TYRE, $Engine, true);
					$response['result'] = 1;
				}
				else if ( Request::get('Year') )
				{
					$Engine = new Car_Engine();
					$response['items'] = $Engine->findShortList(array('ModelId = ' . Request::get('Model'), 'Year = ' . Request::get('Year')));
					$response['result'] = 1;
				}
				else if ( Request::get('Model') )
				{
					$response['items'] = array();
					$Model = new Car_Model();
					$Model = $Model->findItem(array('Id = ' . Request::get('Model')));
					foreach ( $Model->getYears() as $year )
					{
						$response['items'][] = array(
							'Id' => $year,
							'Name' => $year,
						);
					}
					$response['result'] = 1;
				}
				else if ( Request::get('Brand') )
				{
					$Model = new Car_Model();
					$response['items'] = $Model->findShortList(array('BrandId = ' . intval(Request::get('Brand'))), 'Name asc');
					$response['result'] = 1;
				}
				else
				{
					$response['msg'] = 'Неправильный параметр';
				}
				$response['post'] = $_GET;
				$response['query'] = Database::getInstance()->getLastQuery();
				break;
		}
		return $this->outputJSON($response);
	}

	public function comment( $id = null )
	{
		$response = array('result' => 0);
		$Tyre = new Car_Tyre();
		$Tyre = $Tyre->findItem(array('Id = ' . $id));
		if ( $Tyre->Id )
		{
			if ( Comment::post($Tyre, $_POST) )
			{
				$response['result'] = 1;
				$response['msg'] = 'Вам был отправлен e-mail с сылкой для подтверждения отзыва.';
				$response['timeout'] = 3000;
				$response['callback'] = 'close';
			}
			else
			{
				$response['msg'] = 'Ошибка базы данных';
			}
		}
		return $this->outputJSON($response);
	}

	public function noMethod( $method = null )
	{
		$Engine = self::getEngine($method);
		if ( $Engine && $Engine->Id )
		{
			return $this->showTyres($Engine);
		}
		return parent::noMethod($method);
	}

	private static function getSorts( $promo = false )
	{		
		return array(
			'pa' => 'Price asc',
			'pd' => 'Price desc',
			'nd' => 'IsNew desc, Price asc',
			'od' => 'Points desc, Price asc',						
			'sd' => 'PriceShare desc, ',						
		);	
	}

}
