<?

/**
 * The Tags controller class.
 * 
 * @author foozzi
 * @version 0.1
 */
class Controller_Backend_Tags extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Tag';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return _t('Tags');
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Tag = new Tag;
		$params = array();
		$params[] = 'Id desc';

		$this->getView()->set( 'Tags', $Tag->findList( $params ) );		
		return $this->getView()->render();
	}


	
}
