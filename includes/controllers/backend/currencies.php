<?

/**
 * The Currencies controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Currencies extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Currency';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return _t('Currencies');
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$this->getView()->set( 'Currencies', Currency::findCurrencies() );
		return $this->getView()->render();
	}

	public function status()
	{
		$response = array('result' => 0);

		$Currency = new Currency();
		$Currency = $Currency->findItem( array( 'Id = '.Request::get('id') ) );
		if ( $Currency->Id )
		{
			switch ( Request::get('action') )
			{
				case 'status':
					$Currency->IsEnabled = 1 - $Currency->IsEnabled;
					$Currency->save();
					$response['result'] = 1;
					break;

				case 'default':
					$Currency->setDefault();
					$response['result'] = 1;
					break;
			}
		}
		return $this->outputJSON( $response );
	}
	
}
