<?

/**
 * The Articles controller class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Controller_Frontend_Interview extends Controller_Frontend_Articles
{
	/**
	 * @see parent::getSitemapNode();
	 */
	public function getSitemapNode()
	{
		$result = $params = array();
		$Article = new Article();
		$params = array();
		$params[] = 'Type = '.$this->getArticleType();
		foreach ( $Article->findShortList( $params, 'Id desc' ) as $Article )
		{
			$result[] = URL::get( $Article );
		}
		return $result;
	}
        
	/**
	 * @see parent::getName()
	 */
	public function getName()
	{
		return 'Интервью';
	}
	
	/**
	 * The function returns article type.
	 * 
	 * @access public
	 * @return int The article type.
	 */
	public function getArticleType()
	{
		return Article::INTERVIEW;
	}
	
}