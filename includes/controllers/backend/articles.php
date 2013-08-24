<?

/**
 * The Articles controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Articles extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Article';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Статьи';
	}
	
	/**
	 * The function returns Article type for current controller.
	 * 
	 * @access public
	 * @return int The Article type.
	 */
	public function getArticleType()
	{
		return Article::ARTICLE;
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Article = new Article();
		
		$this->getView()->set( 'Articles', $Article->findShortList( array( 'Type = '.$this->getArticleType() ), 'PostedAt desc, Id desc' ) );
		return $this->getView()->render();
	}
	
	/**
	 * @see parent::add()
	 */
	public function add()
	{
		$Article = $this->getModel();
		$Article->Type = $this->getArticleType();
		return $this->initForm( $Article );
	}

	/**
	 * @see parent::pos()
	 */
	public function pos()
	{
		return null;
	}
	
	/**
	 * The function returns JSON responses for different methods.
	 * 
	 * @access public
	 * @param string $method THe method.
	 * @return string The JSON response.
	 */
	public function json( $method = null )
	{
		$response = array();
		switch ( $method )
		{
			case 'tags':
				$Tag = new Tag();
				$params = array();
				$params[] = $Tag->getParam( 'search', Request::get('term') );
				foreach ( $Tag->findList( $params, 'Name asc', 0, 20 ) as $Tag )
				{
					$response[] = $Tag->Name;
				}
				break;
				
			case 'ref':
				$Object = null;
				$params = array();
				if ( Request::get('field') == 'Reference[Page]' )
				{
					$Object = new Content_Page();
				}
				else if ( Request::get('field') == 'Reference[Product]' )
				{
					$Object = new Product();
				}
				if ( $Object )
				{
					$params[] = $Object->getParam('search', Request::get('term'));
					foreach ( $Object->findShortList( $params, 'Name asc', 0, 10 ) as $Object )
					{
						printf( "%d|%s\n", $Object->Id, $Object->Name );
					}
				}
				exit;
				break;
		}
		return $this->outputJSON( $response );
	}
	
	/**
	 * The function returns references for current Article.
	 * 
	 * @access public
	 * @param int $id The Article id.
	 * @return string The HTML code.
	 */
	public function ref( $id = null )
	{
		$Article = new Article();
		if ( Request::get('Reference') )
		{
			$Article = $Article->findItem( array( 'Id = '.$id ) );
			if ( $Article->Id )
			{
				$Reference = new Article_Reference();
				$Reference->ArticleId = $Article->Id;
				$Reference->setPost( Request::get('Reference', array()) );
				$Reference->save();
			}
		}
		if ( Request::get('delete') )
		{
			$Reference = new Article_Reference();
			$Reference = $Reference->findItem( array( 'Id = '.Request::get('id') ) );
			$Article = $Reference->getArticle();
			$Reference->drop();
		}
		return $this->getView()->htmlReferences( $Article );
	}
	
}
