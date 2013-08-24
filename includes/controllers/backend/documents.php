<?

/**
 * The Documents controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Documents extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Document';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Документы и бланки';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Document = new Document();
		$this->getView()->set( 'Documents', $Document->findList( array(), 'Position asc' ) );
		return $this->getView()->render();
	}
	
}
