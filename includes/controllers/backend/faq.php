<?

/**
 * The FAQ controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Faq extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Faq';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Вопросы и ответы';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Faq = new Faq();
		$this->getView()->set( 'Faqs', $Faq->findList( array(), 'Position asc' ) );
		return $this->getView()->render();
	}
	
}
