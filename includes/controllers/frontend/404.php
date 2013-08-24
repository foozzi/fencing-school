<?

/**
 * The Error 404 document controller class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Controller_Frontend_404 extends Controller_Frontend
{
	
	/**
	 * @see parent::getName()
	 */
	public function getName()
	{
		return 'Ошибка 404';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		return $this->notFound();
	}
	
}
