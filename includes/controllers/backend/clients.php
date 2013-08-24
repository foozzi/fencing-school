<?

/**
 * The Clients controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Clients extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Client';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Клиенты';
	}

	public function haltForm( Object $Client, $method = 'edit' )
	{
		if ( !empty( $_FILES['cert']['tmp_name'] ) )
		{
			$Award = new Award();
			$Award->Id = $Client->Id;
			$Award->saveNew();
			if ( File::upload( $Award, $_FILES['cert'] ) )
			{
				$Award->save();
			}
		}
		if ( !empty( $_POST['detach_cert'] ) )
		{
			$Client->getAward()->drop();
		}
		return parent::haltForm( $Client, $method );
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Client = new Client();
		$this->getView()->set( 'Clients', $Client->findList( array(), 'Position asc' ) );
		return $this->getView()->render();
	}
	
}
