<?

/**
 * The Pages controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Pages extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return _t('Pages');
	}
	
	/**
	 * @see parent::getModelName()
	 */
	public function getModelName( $method = null )
	{
		if ( in_array( $method, array('addb', 'editb', 'delb', 'posb') ) )
		{
			return 'Content_Page_Block';
		}
		return 'Content_Page';
	}
	
	/**
	 * @see parent::getAliasName()
	 */
	public function getAliasName( $method = null )
	{
		if ( in_array( $method, array('addb', 'editb', 'delb', 'posb') ) )
		{
			return 'Block';
		}
		return 'Page';
	}

	protected function haltForm( Object $Object, $method = 'edit' )
	{
		if ( $Object instanceof Content_Page )
		{
			if ( $Object->Module && $Object->Link )
			{
				Router::attachPage( $Object );
			}
			else
			{
				Router::detachPage( $Object );
			}
		}
		if ( $Object instanceof Content_Page_Block )
		{
			return $this->halt( 'edit/'.$Object->PageId.'#blocks' );
		}
		return parent::haltForm( $Object, $method );
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Page = new Content_Page();
		if ( !Request::get('t') )
		{
			$_GET['t'] = 'root';
		}
		$this->getView()->set( 'Pages', $Page->findShortList( array( 'ParentId = 0' ), 'Position asc' ) );
		return $this->getView()->render();
	}

	public function addb( $id = null )
	{
		$Page = new Content_Page();
		$Page = $Page->findItem( array( 'Id = '.$id ) );
		if ( !$Page->Id )
		{
			return $this->halt();
		}
		$this->addBreadcrumb( new Breadcrumb( _t('Adding block') ) );
		$Block = new Content_Page_Block();
		$Block->PageId = $Page->Id;
		return $this->initForm( $Block, 'addb' );
	}

	public function editb( $id = null )
	{
		$Block = new Content_Page_Block();
		$Block = $Block->findItem( array( 'Id = '.$id ) );
		if ( !$Block->Id )
		{
			return $this->halt();
		}
		return $this->initForm( $Block, 'editb' );
	}
	
	/**
	 * The position handler.
	 * 
	 * @access public
	 * @return string The JSON response.
	 */
	public function posb()
	{
		return $this->rawPos('posb');
	}

	public function delb( $id = null )
	{
		return $this->rawDelete( 'delb', $id );
	}
	
	/**
	 * The function enables/disabled Page in menu.
	 *
	 * @access public
	 * @return string The JSON response.
	 */
	public function status()
	{
		$response = array( 'result' => 0 );
		$Page = new Content_Page();
		$Page = $Page->findItem( array( 'Id = '.Request::get('id') ) );
		if ( $Page->Id )
		{
			if ( Request::get('name') == 'InMenu[]' )
			{
				$Page->InMenu = intval( Request::get('state') );
			}
			else if ( Request::get('name') == 'IsEnabled[]' )
			{
				$Page->IsEnabled = 1 - $Page->IsEnabled;
			}
			if ( $Page->save() )
			{
				$response['result'] = 1;
				$response['name'] = Request::get('name');
			}
			else
			{
				$response['msg'] = 'Ошибка записи';
			}
		}
		else
		{
			$response['msg'] = 'Элемент не найден';
		}
		return $this->outputJSON( $response );
	}
	
	
}
