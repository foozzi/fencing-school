<?

/**
 * The Product Brands controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Product_Brands extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Car_Brand';
	}
	
	/**
	 * @see parent::getAliasName()
	 */
	protected function getAliasName( $method = null )
	{
		return 'Brand';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Бренды';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Brand = new Car_Brand();
		$params = array();
		$params[] = 'Type = '.Request::get('t', Car_Brand::CAR);
		$this->getView()->set( 'Brands', $Brand->findList( $params, 'Name asc' ) );
		return $this->getView()->render();
	}

	public function add()
	{
		$Brand = new Car_Brand();
		$Brand->Type = Request::get('t');
		return $this->initForm( $Brand, 'add' ); 
	}

	public function state()
	{
		$response = array('result' => 0);
		$Brand = new Car_Brand();
		$Brand = $Brand->findItem( array( 'Id = '.Request::get('id') ) );
		if ( $Brand->Id )
		{
			$Brand->IsFavorite = Request::get('value');
			if ( $Brand->save() )
			{
				$response['result'] = 1;
			}
			else
			{
				$response['msg'] = 'Ошибка записи';
			}
		}
		else
		{
			$response['msg'] = 'Бренд не найден';
		}
		return $this->outputJSON( $response );
	}
	
}
