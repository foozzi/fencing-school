<?

/**
 * The Galleries controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Galleries extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		if ( in_array( $method, array('items', 'addi', 'editi', 'deli', 'posi') ) )
		{
			return 'Gallery_Item';
		}
		return 'Gallery';
	}
	
	/**
	 * @see parent::getAliasName()
	 */
	protected function getAliasName( $method = null )
	{
		if ( in_array( $method, array('items', 'addi', 'editi', 'deli', 'posi') ) )
		{
			return 'Item';
		}
		return 'Gallery';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Галереи';
	}
	
	/**
	 * @see parent::haltForm()
	 */
	protected function haltForm( $Object, $method = 'edit' )
	{
		if ( $method == 'editi' || $method == 'addi' )
		{
			return $this->halt( 'items/'.$Object->GalleryId, true );
		}
		return parent::haltForm( $Object, $method );
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Gallery = new Gallery();
		$params = array();
		$params[] = 'Type = '.intval( Request::get('t') );
		$this->getView()->set( 'Galleries', $Gallery->findList( $params, 'Position asc' ) );
		return $this->getView()->render();
	}
	
	/**
	 * @see parent::add()
	 */
	public function add()
	{
		$Gallery = new Gallery();
		$Gallery->Type = intval( Request::get('t') );
		return $this->initForm( $Gallery, 'add' );
	}
	
	/**
	 * The gallery items hanlder.
	 * 
	 * @access public
	 * @param int $id The Gallery id.
	 * @return string The HTML code.
	 */
	public function items( $id = null )
	{
		$Gallery = new Gallery();
		$Gallery = $Gallery->findItem( array( 'Id = '.$id ) );
		if ( !$Gallery->Id )
		{
			return $this->halt();
		}
		$Item = new Gallery_Item();
		$this->getView()->set( 'Gallery', $Gallery );
		$this->getView()->set( 'Items', $Item->findList( array( 'GalleryId = '.$Gallery->Id ), 'Position asc' ) );
		return $this->getView()->render();
	}
	
	/**
	 * The function adds gallery item.
	 * 
	 * @access public
	 * @param int $id The gallery id.
	 * @return string The HTML code.
	 */
	public function addi( $id = null )
	{
		$Gallery = new Gallery();
		$Gallery = $Gallery->findItem( array( 'Id = '.$id ) );
		if ( !$Gallery->Id )
		{
			return $this->halt();
		}
		$Item = new Gallery_Item();
		$Item->GalleryId = $Gallery->Id;
		return parent::initForm( $Item, 'addi' );
	}
	
	/**
	 * The editing gallery item handler.
	 * 
	 * @access public
	 * @param int $id The gallery item id.
	 * @return string The HTML code.
	 */
	public function editi( $id = null )
	{
		$Item = new Gallery_Item();
		$Item = $Item->findItem( array( 'Id = '.$id ) );
		if ( !$Item->Id )
		{
			return $this->halt();
		}
		return parent::initForm( $Item, 'editi' );
	}
	
	/**
	 * @see parent::delete()
	 */
	public function deli( $id = null )
	{
		return $this->rawDelete('deli', $id);
	}
	
	/**
	 * @see parent::pos()
	 */
	public function posi()
	{
		return $this->rawPos('posi');
	}
	
	/**
	 * The upload form handler.
	 * 
	 * @access public
	 * @param int $id The Gallery id.
	 * @return string The HTML code.
	 */
	public function upload( $id = null )
	{
		$error = array();
		$Gallery = new Gallery();
		$Gallery = $Gallery->findItem( array( 'Id = '.$id ) );
		if ( !$Gallery->Id )
		{
			return $this->halt();
		}
		if ( isset( $_POST['submit'] ) )
		{
			if ( isset( $_FILES['file'] ) )
			{
				foreach ( $_FILES['file']['name'] as $id => $value )
				{
					$file = array(
						'name'	=> $_FILES['file']['name'][ $id ],
						'type'	=> $_FILES['file']['type'][ $id ],
						'tmp_name'	=> $_FILES['file']['tmp_name'][ $id ],
						'error'	=> $_FILES['file']['error'][ $id ],
						'size'	=> $_FILES['file']['size'][ $id ],
					);
					$Item = new Gallery_Item();
					$Item->GalleryId = $Gallery->Id;
					if ( $Item->save() )
					{
						if ( File::upload( $Item, $file ) )
						{
							$Item->save();
						}
					}
				}
			}
			return $this->halt( 'items/'.$Gallery->Id );
		}
		$this->getView()->set( 'Gallery', $Gallery );
		$this->getView()->set( 'Error', $error );
		return $this->getView()->render();
	}
	
}

