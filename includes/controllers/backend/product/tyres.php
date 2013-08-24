<?

/**
 * The Product Tyres controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Product_Tyres extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		if ( in_array( $method, array('posi', 'deli') ) )
		{
			return 'Car_Image';
		}
		return 'Car_Tyre';
	}
	
	/**
	 * @see parent::getAliasName()
	 */
	protected function getAliasName( $method = null )
	{
		if ( in_array( $method, array('posi', 'deli') ) )
		{
			return 'Image';
		}
		return 'Tyre';
	}
	
	protected function haltForm( Object $Object, $method = 'edit' )
	{		
		if ( in_array( $method, array('addt', 'addw', 'adde') ) )
		{
			$_GET['t'] = $Object->Type;
			return $this->halt('edit/'.$Object->Id, true);
		}
		return parent::haltForm( $Object, $method );
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Товары';
	}

	protected function getDefaultSort()
	{
		return 'Name asc';
	}

	public function getLimit()
	{
		return 50;
	}
	
	private function getRequestParams()
	{
		$params = array();
		$Tyre = new Car_Tyre();
		$params[] = 'ParentId = 0';
		if ( Request::get('t') )
		{
			$params[] = 'Type = '.Request::get('t');
		}
		if ( Request::get('b') )
		{
			$params[] = 'BrandId = '.Request::get('b');
		}
		if ( Request::get('d') )
		{
			$params[] = 'Diameter = '.Request::get('d');
		}
		if ( Request::get('pr') )
		{
			$arr = explode( '-', Request::get('pr') );
			if ( count( $arr ) == 2 )
			{
				$params[] = 'Price >= '.$arr[0];
				$params[] = 'Price <= '.$arr[1];
			}
		}
		if ( Request::get('s') )
		{
			$params[] = $Tyre->getParam('search', Request::get('s'));
		}
		if ( Request::get('f') )
		{
			$params[] = $Tyre->getParam( Request::get('f') );
		}
		return $params;
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Tyre = new Car_Tyre();
		$params = $this->getRequestParams();

		$Paginator = new Paginator( $Tyre->findSize( $params ), $this->getLimit(), $this->getPage() );
		$this->getView()->set( 'Paginator', $Paginator );
		$this->getView()->set( 'Tyres', $Tyre->findList( $params, $this->getSort(), $this->getOffset(), $this->getLimit() ) );
		return $this->getView()->render();
	}


	public function unique()
	{
		return $this->index();
	}

	public function initForm( Object $Object, $method = 'edit' )
	{
		if ( isset( $_POST['video'] ) )
		{
			$response = array('result' => 1);
			$response['embed'] = YouTube::getEmbed( $_POST['video'] );
			return $this->outputJSON( $response );
		}
		return parent::initForm( $Object, $method );
	}

	public function upload( $id = null )
	{
		$Tyre = new Car_Tyre();
		$Tyre = $Tyre->findItem( array( 'Id = '.$id ) );
		if ( !$Tyre->Id )
		{
			return '';
		}
		if ( isset( $_FILES['img'] ) && count( $_FILES['img'] ) )
		{
			foreach ( $_FILES['img']['name'] as $id => $value )
			{
				if ( $_FILES['img']['tmp_name'][ $id ] )
				{
					$Image = new Car_Image();
					$Image->TyreId = $Tyre->Id;
					if ( $Image->save() )
					{
						if ( File::upload( $Image, File::convertMultiple( $_FILES['img'], $id ) ) )
						{
							$Image->save();
						}
					}
				}
			}
			$Tyre->cacheImage();
		}
		return $this->getView()->htmlImages( $Tyre );
	}

	public function posi( $id = null )
	{
		$result = $this->rawPos('posi', false);
		$Tyre = new Car_Tyre();
		$Tyre = $Tyre->findItem( array( 'Id = '.$id ) );
		$Tyre->cacheImage();
		return $result;
	}

	public function deli( $id = null )
	{
		return $this->rawDelete( 'deli', $id );
	}
	
	public function addt()
	{
		$Tyre = new Car_Tyre();
		$Tyre->Type = Car_Brand::TYRE;
		return $this->initForm( $Tyre, 'addt' );
	}
	
	public function addw()
	{
		$Tyre = new Car_Tyre();
		$Tyre->Type = Car_Brand::WHEEL;
		return $this->initForm( $Tyre, 'addt' );
	}
	
	public function adde()
	{
		$Tyre = new Car_Tyre();
		$Tyre->Type = Car_Brand::EXTRA;
		return $this->initForm( $Tyre, 'addt' );
	}
	
	public function fav( $id = null )
	{		
		$response = array('result' => 0);
		$Tyre = new Car_Tyre();
		$Tyre = $Tyre->findItem( array( 'Id = '.Request::get('id', $id) ) );
		if ( $Tyre->Id )
		{
			$Tyre->IsFavorite = 1 - $Tyre->IsFavorite;
			if ( $Tyre->save() )
			{
				$response['result'] = 1;
			}
		}
		return $this->outputJSON( $response );
	}
	
	public function sale( $id = null )
	{		
		$response = array('result' => 0);
		$Tyre = new Car_Tyre();
		$Tyre = $Tyre->findItem( array( 'Id = '.Request::get('id', $id) ) );		
		if ( $Tyre->Id )
		{
			$Tyre->IsSale = 1 - $Tyre->IsSale;
			if ( $Tyre->save() )
			{
				$response['result'] = 1;
			}
		}
		return $this->outputJSON( $response );
	}

	public function state( $id = null )
	{
		$response = array('result' => 0);
		$Tyre = new Car_Tyre();
		$Tyre = $Tyre->findItem( array( 'Id = '.Request::get('id', $id) ) );
		if ( $Tyre->Id )
		{
			$Tyre->IsNew = 1 - $Tyre->IsNew;
			if ( $Tyre->save() )
			{
				$response['result'] = 1;
			}
		}
		return $this->outputJSON( $response );
	}
	
	public function pic( $id = null )
	{
		$Tyre = new Car_Tyre();
		$Tyre = $Tyre->findItem( array( 'Id = '.Request::get('id', $id) ) );
		if ( $Tyre->Id )
		{
			if ( Request::get('Image') )
			{
				$response = array('result' => 0);
				$Image = new Car_Image();
				$Image->TyreId = $Tyre->Id;
				if ( $Image->save() )
				{
					if ( File::upload( $Image, Request::get('Image') ) )
					{
						$Image->save();
						$Tyre->ImageId = $Image->Id;
						$Tyre->save();
						$response['result'] = 1;
						$response['Id'] = $Tyre->Id;
					}
				}
				return $this->outputJSON( $response );
			}
			
			$params = array();
			$params['Brand'] = $Tyre->getBrand()->Name;
			$arr = preg_split( '/[\s0-9\W]/', $Tyre->Name, 2 );
			$params['Name'] = $arr[0].'%';
			$exact = API_Wheels::exec('get', $params);
			$rough = array();
			if ( $exact->Count == 0 )
			{
				unset( $params['Name'] );
				$rough = API_Wheels::exec('get', $params);
				$rough = $rough->Items;
			}
			$exact = $exact->Items;
			
			return $this->getView()->htmlPictures( $Tyre, $exact, $rough );
		}
		return '';
	}
	
}
