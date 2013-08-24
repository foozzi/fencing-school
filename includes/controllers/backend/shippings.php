<?

/**
 * The Shippings controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Shippings extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Shipping';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Доставка';
	}

	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Shipping = new Shipping();
		$params = array();
		$this->getView()->set( 'Shippings', $Shipping->findList( $params, 'Position asc' ) );
		return $this->getView()->render();
	}

	public function state( $id = null )
	{
		$response = array('result' => 0);
		$Shipping = new Shipping();
		$Shipping = $Shipping->findItem( array( 'Id = '.Request::get('id', $id) ) );
		if ( $Shipping->Id )
		{
			switch ( Request::get('name') )
			{
				case 'IsAddress':
					$Shipping->IsAddress = 1 - $Shipping->IsAddress;
					$Shipping->save();
					$response['result'] = 1;
					break;
			}
		}
		return $this->outputJSON( $response );
	}
	
}