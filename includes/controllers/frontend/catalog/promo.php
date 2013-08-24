<?

/**
 * The Tyres Catalog controller class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Controller_Frontend_Catalog_Promo extends Controller_Frontend_Catalog_Tyres
{

	public function getName()
	{
		return 'Каталог - Акция';
	}

	public function beforeExecute()
	{
		$_GET['sale'] = Request::get('sale', 'sd');		
	}

	public function index()
	{
		$this->getView()->headBanner = true;		
		return $this->showTyres( null, true );
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
	
	private static function getSorts( $promo = false )
	{		
		return array(
			'pa' => 'PriceShare asc',
			'pd' => 'PriceShare desc',
			'nd' => 'IsNew desc, PriceShare asc',
			'od' => 'Points desc, PriceShare asc',						
			'sd' => 'PriceShare desc, ',						
		);
	}
	
}
