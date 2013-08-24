<?

/**
 * The Support controller class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Controller_Frontend_Support extends Controller_Frontend
{
	
	/**
	 * @see parent::getName()
	 */
	public function getName()
	{
		return 'Вопросы и ответы';
	}
	
	/**
	 * The support index handler.
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
