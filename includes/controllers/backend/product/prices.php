<?

/**
 * The Product Prices controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Product_Prices extends Controller_Backend_Standard
{
	
	public function getModelName( $method = 'edit' )
	{
		return 'Pricelist';
	}
	
	public function getAliasName( $method = 'edit' )
	{
		return 'Price';
	}

	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Прайс-лист';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Price = new Pricelist();
		$this->getView()->set( 'Prices', $Price->findList( array(), 'PostedAt desc, Id desc' ) );
		return $this->getView()->render();
	}
	
	public function load( $id = null )
	{
		$Price = new Pricelist();
		$Price = $Price->findItem( array( 'Id = '.$id ) );
		if ( !$Price->Id )
		{
			return $this->halt();
		}
		set_time_limit(0);
		ignore_user_abort(true);
		
		$Price->loadPrices();
		$this->getView()->set( 'Errors', $Price->getErrors() );
		return $this->getView()->render();
	}

	public function log( $id = null )
	{
		$Price = new Pricelist();
		$Price = $Price->findItem( array( 'Id = '.$id ) );
		if ( !$Price->Id )
		{
			if ( Request::get('ajax') )
			{
				return '';
			}
			return $this->halt();
		}
		return $this->getView()->htmlLog( $Price );
	}

}
