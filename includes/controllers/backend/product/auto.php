<?

/**
 * The Product Auto controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Product_Auto extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Car_Engine';
	}
	
	/**
	 * @see parent::getAliasName()
	 */
	protected function getAliasName( $method = null )
	{
		return 'Engine';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Модификации авто';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Model = new Car_Model();
		$params = array();
		/*
		if ( !Request::get('b') )
		{
			$arr = Car_Brand::getBrands( Car_Brand::CAR );
			if ( isset( $arr[0] ) )
			{
				$_GET['b'] = $arr[0]->Id;
			}
		}
		*/
		if ( Request::get('b') )
		{
			$params[] = 'BrandId = '.Request::get('b');
		}
		$Models = Request::get('b') ? $Model->findList( $params, 'Name asc' ) : array();
		if ( Request::get('ajax') )
		{
			return $this->getView()->htmlDataList( $Models );
		}
		$this->getView()->set( 'Models', $Models );
		return $this->getView()->render();
	}

	private function initEngineForm( Car_Engine $Engine )
	{
		if ( Request::get('submit') )
		{
			$response = array('result' => 0);
			$Engine->setPost( $_POST );
			if ( $Engine->save() )
			{
				$response['result'] = 1;
			}
			else
			{
				$response['msg'] = 'Ошибка записи';
			}
			if ( Request::get('ajax') )
			{
				return $this->outputJSON( $response );
			}
		}

		$this->getView()->set( 'Engine', $Engine );
		return $this->getView()->engine();
	}

	private function initModelForm( Car_Model $Model )
	{
		if ( Request::get('submit') )
		{
			$response = array('result' => 0);
			$Model->setPost( $_POST );
			if ( $Model->save() )
			{
				$response['result'] = 1;
			}
			else
			{
				$response['msg'] = 'Ошибка записи';
			}
			if ( Request::get('ajax') )
			{
				return $this->outputJSON( $response );
			}
		}

		$this->getView()->set( 'Model', $Model );
		return $this->getView()->model();
	}

	private function initEngineTyreForm( Car_Engine_Tyre $Tyre )
	{
		if ( Request::get('submit') )
		{
			$response = array('result' => 0);
			$Tyre->setPost( $_POST );
			if ( $Tyre->save() )
			{
				$response['result'] = 1;
			}
			else
			{
				$response['msg'] = 'Ошибка записи';
			}
			if ( Request::get('ajax') )
			{
				return $this->outputJSON( $response );
			}
		}

		$this->getView()->set( 'Tyre', $Tyre );
		return $this->getView()->editw();
	}

	public function adde( $id = null, $year = null )
	{
		$Model = new Car_Model();
		$Model = $Model->findItem( array( 'Id = '.$id ) );
		if ( !$Model->Id )
		{
			return '';
		}
		$Engine = new Car_Engine();
		$Engine->ModelId = $Model->Id;
		$Engine->Year = $year;

		return $this->initEngineForm( $Engine );
	}

	public function edite( $id = null )
	{
		$Engine = new Car_Engine();
		$Engine = $Engine->findItem( array( 'Id = '.$id ) );
		if ( !$Engine->Id )
		{
			return '';
		}
		return $this->initEngineForm( $Engine );
	}

	public function dele( $id = null )
	{
		$response = array('result' => 0);
		$Engine = new Car_Engine();
		$Engine = $Engine->findItem( array( 'Id = '.$id ) );
		if ( $Engine->Id )
		{
			if ( $Engine->drop() )
			{
				$response['result'] = 1;
				$response['close'] = 1;
				$response['refresh'] = 1;
			}
			else
			{
				$response['msg'] = 'Ошибка базы данных';
			}
		}
		return $this->outputJSON( $response );
	}

	public function addm( $id = null )
	{
		$Brand = new Car_Brand();
		$Brand = $Brand->findItem( array( 'Id = '.$id, 'Type = '.Car_Brand::CAR ) );
		if ( !$Brand->Id )
		{
			return '';
		}
		$Model = new Car_Model();
		$Model->BrandId = $Brand->Id;

		return $this->initModelForm( $Model );
	}

	public function editm( $id = null )
	{
		$Model = new Car_Model();
		$Model = $Model->findItem( array( 'Id = '.$id ) );
		if ( !$Model->Id )
		{
			return '';
		}
		return $this->initModelForm( $Model );
	}

	public function delm( $id = null )
	{
		$response = array('result' => 0);
		$Model = new Car_Model();
		$Model = $Model->findItem( array( 'Id = '.$id ) );
		if ( $Model->Id )
		{
			if ( $Model->drop() )
			{
				$response['result'] = 1;
				$response['close'] = 1;
				$response['refresh'] = 1;
			}
			else
			{
				$response['msg'] = 'Ошибка базы данных';
			}
		}
		return $this->outputJSON( $response );
	}

	public function wheels( $id = null )
	{
		$response = array('result' => 0);
		$Engine = new Car_Engine();
		$Engine = $Engine->findItem( array( 'Id = '.$id ) );
		$this->getView()->set( 'Engine', $Engine );
		return $this->getView()->wheels();
	}

	private function addWheel( $id, $type )
	{
		$Engine = new Car_Engine();
		$Engine = $Engine->findItem( array( 'Id = '.$id ) );
		if ( !$Engine->Id )
		{
			return '';
		}
		$Tyre = new Car_Engine_Tyre();
		$Tyre->EngineId = $Engine->Id;
		$Tyre->Type = $type;
		return $this->initEngineTyreForm( $Tyre );
	}

	public function addw( $id = null )
	{
		return $this->addWheel( $id, Car_Brand::WHEEL );
	}

	public function addt( $id = null )
	{
		return $this->addWheel( $id, Car_Brand::TYRE );
	}

	public function editw( $id = null )
	{
		$Tyre = new Car_Engine_Tyre();
		$Tyre = $Tyre->findItem( array( 'Id = '.$id ) );

		return $this->initEngineTyreForm( $Tyre );
	}

	public function delw( $id = null )
	{
		$Tyre = new Car_Engine_Tyre();
		$Tyre = $Tyre->findItem( array( 'Id = '.$id ) );
		if ( $Tyre->Id )
		{
			if ( $Tyre->drop() )
			{
				$response['result'] = 1;
				$response['refreshBox'] = 1;
			}
			else
			{
				$response['msg'] = 'Ошибка базы данных';
			}
		}
		return $this->outputJSON( $response );
	}

}
