<?

/**
 * The Buttons controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Buttons extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Button';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Кнопки на главной';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Button = new Button();
		$this->getView()->set( 'Buttons', $Button->findList( array(), 'Position asc' ) );
		return $this->getView()->render();
	}
	
}
