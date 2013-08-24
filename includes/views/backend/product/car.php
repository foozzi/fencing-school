<?

class View_Backend_Product_Car extends View_Backend
{
	
	protected function htmlBrandOptions( $current = null )
	{
		return HTML::options( Car_Brand::getBrands( Car_Brand::TYRE, true, 'any' ), $current, true );
	}
	
}