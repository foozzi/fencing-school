<?

/**
 * The Banners controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Banners extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Banner';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Баннера';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Banner = new Banner();
		$this->getView()->set( 'Banners', $Banner->findList( array(), 'Position asc' ) );
		return $this->getView()->render();
	}
	
}
