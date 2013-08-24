<?

/**
 * The News controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_News extends Controller_Backend_Articles
{
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Новости';
	}
	
	/**
	 * The function returns Article type for current controller.
	 * 
	 * @access public
	 * @return int The Article type.
	 */
	public function getArticleType()
	{
		return Article::NEWS;
	}
	
}
