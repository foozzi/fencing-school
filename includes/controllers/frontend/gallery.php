<?

/**
 * The Gallery controller class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Controller_Frontend_Gallery extends Controller_Frontend
{
	
	/**
	 * @see parent::getName()
	 */
	public function getName()
	{
		return 'Галерея';
	}
	
	/**
	 * @see parent::getLimit()
	 */
	public function getLimit()
	{
		return Request::get('limit', 20);
	}
	
	/**
	 * The gallery index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Gallery = new Gallery();
		$params = array();
		$params[] = 'Type = '.Gallery::GALLERY;
		$this->getView()->set( 'Galleries', $Gallery->findList( $params, 'Position asc' ) );
		return $this->getView()->render();
	}
	
	/**
	 * The gallery items hanlder.
	 * 
	 * @access public
	 * @param int $id The Gallery id.
	 * @return string The HTML code.
	 */
	public function view( $id = null )
	{
		$Gallery = new Gallery();
		$Gallery = $Gallery->findItem( array( 'Id = '.$id ) );
		if ( !$Gallery->Id )
		{
			return $this->halt();
		}
		$Item = new Gallery_Item();
		$params = array();
		$params[] = 'GalleryId = '.$Gallery->Id;
		
		$Paginator = new Paginator( $Item->findSize( $params ), $this->getLimit(), $this->getPage() );
		$Items = $Item->findList( $params, 'Position asc', $this->getOffset(), $this->getLimit() );
		if ( Request::get('ajax') )
		{
			return $this->getView()->htmlItems( $Items, $Paginator );
		}
		
		$this->getView()->set( 'Gallery', $Gallery );
		$this->getView()->set( 'Paginator', $Paginator );
		$this->getView()->set( 'Items', $Items );
		return $this->getView()->render();
	}
	
	public function noMethod()
	{
		return $this->halt();
	}
	
}
