<?

/**
 * The Articles controller class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Controller_Frontend_Articles extends Controller_Frontend
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
		return 'Статьи';
	}
	
	/**
	 * @see parent::getArticleType()
	 */
	public function getArticleType()
	{
		return Article::ARTICLE;
	}
	
	/**
	 * @see parent::getLimit()
	 */
	public function getLimit()
	{
		return 10;
	}
	
	/**
	 * The articles index handler.
	 * 
	 * @access public
	 * @param int $year The year or meta id.
	 * @return string The HTML code.
	 */
	public function index( $year = null )
	{
		$Article = new Article();
		$offset = $limit = null;
		$params = array();
		$params[] = 'Type = '.$this->getArticleType();
		if ( $year )
		{
			$params[] = $Article->getParam( 'year', $year );
		}
		else
		{
			$offset = 0;
			$limit = 10;
		}
		$Tag = null;
		if ( Request::get('tag') )
		{
			$Tag = new Tag();
			$Tag = $Tag->findItem( array( 'Name = '.Request::get('tag') ) );
			$params[] = $Article->getParam( 'tag', Request::get('tag') );
		}
		$Paginator = new Paginator( $Article->findSize( $params ), $this->getLimit(), $this->getPage() );		
		$this->getView()->set( 'Articles', $Article->findShortList( $params, 'PostedAt desc, Id desc', $this->getOffset(), $this->getLimit() ) );
		$this->getView()->set( 'Paginator', $Paginator );
		$this->getView()->set( 'Current', $year );
		$this->getView()->set( 'Tag', $Tag );
		$this->getView()->set( 'Years', $Article->getYears( $this->getArticleType() ));
		$this->getView()->set( 'Year', $year );
		return $this->getView()->render();
	}

	/**
	 * The articles index and view handler.
	 * 
	 * @access public
	 * @param int $id The Article id.
	 * @return string The HTML code.
	 */
	public function view( $id = null )
	{
		$Article = new Article();
		$Article = $Article->findItem( array( 'Id = '.$id ) );
		if ( !$Article->Id )
		{
			$this->halt();
		}
		
		$Page = $this->getContentPage();
		$Page->SeoTitle = $Article->Title;
		
		$params = array();
		$params[] = 'Id <> '.$Article->Id;
		$params[] = 'Type = '.$Article->Type;
		$this->getView()->set( 'Last', $Article->findShortList( $params, 'PostedAt desc', 0, 10 ) );		
		$this->getView()->set( 'Article', $Article );		
		$this->getView()->set( 'Comments', $Article->getComments() );
		return $this->getView()->render();
	}

	public function json( $method = null )
	{		
		$response = array('result' => 0);
		switch ( $method )
		{
			case 'subscribe':
				if ( Request::get('Email') && Request::get('Name') )
				{
					$Subscription = new Subscription();
					if ( $Subscription->findSize( array( 'Email = '.Request::get('Email') ) ) )
					{
						$response['result'] = 1;
						$response['msg'] = 'Данный E-mail уже есть в базе подписчиков';
					}
					else
					{
						$Subscription->Email = Request::get('Email');
						$Subscription->Name = Request::get('Name');
						if ( $Subscription->save() )
						{
							$response['result'] = 1;
							$response['msg'] = Request::get('Name') . ', вы успешно подписались на новости';
						}
						else
						{
							$response['msg'] = 'Ошибка записи данных';
						}
					}
				}
				break;
		}
		return $this->outputJSON( $response );
	}
	
	public function unsubscribe( $hash = null )
	{
		$Subscription = new Subscription();
		
		$Subs = $Subscription->findCode( $hash );		
		
		if ( $Subs->Email )
		{
			$params = array();
			
			$params[] = 'Email = ' . $Subs->Email;
			
			$Subs = $Subscription->dropList( $params );
			
			$this->getView()->set('Msg', 'Вы отписались от рассылки');
			return $this->getView()->render();
		}
		else
		{		
			return $this->halt( '' );
		}		
	}

	public function comment( $id = null )
	{
		$response = array('result' => 0);
		$Article = new Article();
		$Article = $Article->findItem(array('Id = ' . $id));
		if ( $Article->Id )
		{
			if ( Comment::post($Article, $_POST) )
			{
				$response['result'] = 1;
				$response['msg'] = 'Комментарий добавлен.';
				//$response['timeout'] = 3000;
				//$response['callback'] = 'close';
			}
			else
			{
				$response['msg'] = 'Ошибка базы данных';
			}
		}
		return $this->outputJSON($response);
	}
	
	/**
	 * @see parent::noMethod()
	 */
	public function noMethod()
	{
		$this->getView()->setMethod('index');
		return $this->index( func_get_arg(0) );
	}
	
	
}
