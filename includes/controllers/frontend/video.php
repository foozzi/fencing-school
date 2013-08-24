<?

/**
 * The Video controller class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Controller_Frontend_Video extends Controller_Frontend
{
	
	/**
	 * @see parent::getName()
	 */
	public function getName()
	{
		return 'Видео';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		return $this->halt(':'.Config::get('youtube/channel'));
		return $this->getView()->render();
	}
	
}
