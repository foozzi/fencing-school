<?

/**
 * The Tyres Catalog controller class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Controller_Frontend_Catalog_Tyres extends Controller_Frontend_Catalog
{

	public function getName()
	{
		return 'Каталог - Шины';
	}

	public function getType()
	{
		return Car_Brand::TYRE;
	}
	
	public function beforeExecute()
	{
		if ( Request::get('sale') === 'sd' )
		{
			return $this->halt(':'.URL::get( new Controller_Frontend_Catalog_Promo() ));
		}
	}
	
}
