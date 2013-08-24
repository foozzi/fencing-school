<?

/**
 * The Base Controller class for storing base functions for children 
 * controllers.
 * 
 * @version 0.1
 */
abstract class Controller_Base extends Controller
{
	
	/**
	 * The function returns authorized User object.
	 * 
	 * @access protected
	 * @return object The User object.
	 */
	protected function getUser()
	{
		return null;
	}

	/**
	 * @see parent::isAccess()
	 */
	public function isAccess( $method = null )
	{
		if ( $this->defaultAccess == Access::FULL )
		{
			return true;
		}
		$User = $this->getUser();
		if ( $User && $User->Id )
		{
			return true;
		}
		return false;
	}
	
	/**
	 * The function returns parent class name for routing if it is different to this class.
	 * 
	 * @access protected
	 * @return string The parent class name.
	 */
	protected function getRouteParent()
	{
		return null;
	}
	
	/**
	 * The function returns array with all parent controllers.
	 * 
	 * @access protected
	 * @param string $root The controller root name.
	 * @param bool $route If TRUE finds parents for route.
	 * @return array The array of controllers.
	 */
	protected function getParents( $root = 'Controller_Base', $route = false )
	{
		$result = array();
		$class = $this;
		do
		{
			$name = '';
			if ( $route && $class->getRouteParent() )
			{
				$name = $class->getRouteParent();
			}
			else
			{
				$name = get_parent_class( $class );
			}
			if ( $name == $root )
			{
				break;
			}
			$class = new $name();
			$result[] = $class;
		} while ( $name && $name != $root );
		return $result;
	}
	
	/**
	 * The function returns link for current controller.
	 * 
	 * @access public
	 * @return string The link.
	 */
	public function getLink()
	{
		if ( ( $link = Route::getLink( $this ) ) !== null )
		{
			return $link;
		}
		$link = null;
		$args = array();
		$parents = $this->getParents('Controller_Base', true);
		array_unshift( $parents, $this );
		foreach ( $parents as $class )
		{
			$link = Route::getLink( $class );
			if ( $link )
			{
				break;
			}
		}
		$suffix = str_replace( get_class( $class ), '', get_class( $this ) );
		if ( $suffix )
		{
			$link .= '/'.ltrim( strtolower( str_replace( '_', '/', $suffix ) ), '/' );
		}
		Route::set( $this, $link );
		return $link;
	}

	protected function getDefaultSort()
	{
		return 'Id asc';
	}
	
	/**
	 * THe function returns sorting parameter.
	 * 
	 * @access public
	 * @return string The sorting parameter.
	 */
	public function getSort()
	{
		return str_replace( '-', ' ', Request::get('sort', $this->getDefaultSort()) );
	}
	
	/**
	 * The function returns limit of items per page.
	 * 
	 * @access public
	 * @return int The limit.
	 */
	public function getLimit()
	{
		return 20;
	}
	
	/**
	 * The function returns offset of current page.
	 * 
	 * @access public
	 * @return int The offset.
	 */
	public function getOffset()
	{
		$page = intval( Request::get('page') );
		if ( $page < 1 )
		{
			$page = 1;
		}
		return ( $page - 1 ) * $this->getLimit();
	}
	
	/**
	 * The function returns current page value.
	 * 
	 * @access public
	 * @return int The page.
	 */
	public function getPage()
	{
		return intval( Request::get('page') );
	}
	
	/**
	 * The function redirects user to link.
	 * Link is relative to current controller if not started from /.
	 *
	 * @access protected
	 * @param string $link The link.
	 * @param bool $restoreGet If TRUE restores GET query.
	 */
	public function halt( $link = '', $restoreGet = false )
	{
		if ( $link )
		{
			if ( substr( $link, 0, 1 ) == ':' )
			{
				$link = substr( $link, 1 );
			}
			else if ( substr( $link, 0, 1 ) != '/' )
			{
				$link = rtrim( $this->getLink(), '/' ).'/'.$link;
			}
			else
			{
				$link = $this->getLink();
			}
		}
		else
		{
			$link = $this->getLink();
		}
		if ( $restoreGet && count( $_GET ) )
		{
			$link .= strpos( $link, '?' ) === false ? '?' : '&';
			$link .= http_build_query( $_GET );
		}
		header( 'location: '.$link );
		exit;
	}
	
	/**
	 * The function prints JSON data to output.
	 * 
	 * @access public
	 * @param array $array The array to print.
	 * @return string The JSON response.
	 */
	public function printJSON( array $array, $exit = true )
	{
		echo json_encode( $array, JSON_HEX_TAG );
		if ( $exit )
		{
			exit;
		}
	}
	
}

/**
 * @see Controller_Base::getLink()
 * @param mixed $Controller The Controller object or its name.
 */
function _L( $Controller )
{
	if ( !is_a( $Controller, 'Controller' ) )
	{
		$Controller = new $Controller();
	}
	$Controller = new $Controller();
	return $Controller->getLink();
}
