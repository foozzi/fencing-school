<?

/**
 * The Products controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Product_Products extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		if ( $method == 'deli' || $method == 'posi' )
		{
			return 'Product_Image';
		}
		if ( $method == 'dele' || $method == 'pose' )
		{
			return 'Product_Example';
		}
		if ( $method == 'deld' )
		{
			return 'Product_Document';
		}
		return 'Product';
	}
	
	/**
	 * @see parent::getAliasName()
	 */
	protected function getAliasName( $method = null )
	{
		if ( $method == 'deli' || $method == 'posi' )
		{
			return 'Image';
		}
		if ( $method == 'dele' || $method == 'pose' )
		{
			return 'Example';
		}
		if ( $method == 'deld' )
		{
			return 'Document';
		}
		return 'Product';
	}
	
	/**
	 * @see parent::dropOnFailedUpload();
	 */
	protected function dropOnFailedUpload( Object $Object )
	{
		if ( $Object instanceof Product_Document || $Object instanceof Product_Image || $Object instanceof Product_Example )
		{
			return true;
		}
		return parent::dropOnFailedUpload( $Object );
	}
	
	/**
	 * @see parent::haltForm()
	 */
	protected function haltForm( Object $Object, $method = 'edit' )
	{
		if ( $method == 'add' )
		{
			return $this->halt('edit/'.$Object->Id, true);
		}
		return parent::haltForm($Object, $method);
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Товары';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Product = new Product();
		$Category = new Product_Category();
		foreach ( $Category->findList( array(), 'Position asc', 0, 1 ) as $Category );
		
		$params = array();
		$c = Request::get('c', $Category->Id);
		$params[] = 'CategoryId = '.$c;
		$_GET['c'] = $c;
		$this->getView()->set( 'Products', $Product->findList( $params, 'Position asc' ) );
		return $this->getView()->render();
	}
	
	/**
	 * @see parent::add()
	 */
	public function add()
	{
		$Product = new Product();
		$Product->CategoryId = intval( Request::get('c') );
		return $this->initForm( $Product, 'add' );
	}
	
	/**
	 * The upload handler.
	 * 
	 * @param int $id The Product id.
	 * @return string The HTML code.
	 */
	public function upload( $id = null )
	{
		$Product = new Product();
		$Product = $Product->findItem( array( 'Id = '.$id ) );
		if ( isset( $_POST['upload'] ) )
		{
			$files = array(
				'image' => new Product_Image(), 
				'example' => new Product_Example(), 
				'doc' => new Product_Document()
			);
			foreach ( $files as $id => $class )
			{
				if ( isset( $_FILES[ $id ] ) )
				{
					foreach ( $_FILES[ $id ]['name'] as $i => $value )
					{
						$file = File::convertMultiple( $_FILES[ $id ], $i );
						if ( empty( $file['tmp_name'] ) )
						{
							continue;
						}
						$class->Id = null;
						$class->ProductId = $Product->Id;
						if ( $class->save() )
						{
							if ( File::upload( $class, $file ) )
							{
								$class->save();
							}
							else if ( $this->dropOnFailedUpload( $class ) )
							{
								$class->drop();
							}
						}
					}
				}
			}
		}
		return $this->getView()->htmlUpload( $Product );
	}
	
	/**
	 * The delete image handler.
	 * 
	 * @access public
	 * @param int $id The Image id.
	 * @return string The JSON response.
	 */
	public function deli( $id = null )
	{
		return $this->rawDelete( 'deli', $id );
	}
	
	/**
	 * The delete example handler.
	 * 
	 * @access public
	 * @param int $id The Image id.
	 * @return string The JSON response.
	 */
	public function dele( $id = null )
	{
		return $this->rawDelete( 'dele', $id );
	}
	
	/**
	 * The delete document handler.
	 * 
	 * @access public
	 * @param int $id The Image id.
	 * @return string The JSON response.
	 */
	public function deld( $id = null )
	{
		return $this->rawDelete( 'deld', $id );
	}
	
	/**
	 * The order position handler for product images.
	 * 
	 * @access public
	 * @return string THe JSON response.
	 */
	public function posi()
	{
		return $this->rawPos('posi');
	}
	
	/**
	 * The order position handler for product examples.
	 * 
	 * @access public
	 * @return string THe JSON response.
	 */
	public function pose()
	{
		return $this->rawPos('pose');
	}
	
}
