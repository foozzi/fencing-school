<?

/**
 * The Backend controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend extends Controller_Base
{

	private $breadcrumbs = array();
	
	
	public function __construct()
	{
		parent::__construct();
		Locale::load('Backend');
	}
	
	/**
	 * @see parent::getUser()
	 */
	public function getUser()
	{
		return Runtime::get('SECURITY_USER');
	}
	
	/**
	 * @see parent::isAccess()
	 */
	public function isAccess( $method = null )
	{
		if ( $method == 'login' )
		{
			return true;
		}
		return parent::isAccess( $method );
	}
	
	/**
	 * The function returns controller title.
	 * 
	 * @access public
	 * @return string The title.
	 */
	public function getTitle()
	{
		return 'Панель управления сайтом';
	}
	
	/**
	 * @see parent::isAccess()
	 */
	public function noAccess()
	{
		$this->halt('login');
	}
	
	/**
	 * @see parent::isAccess()
	 */
	public function noMethod()
	{
		echo "Backend::nomethod\n";
	}

	protected function addBreadcrumb( $Breadcrumb )
	{
		if ( $Breadcrumb instanceof Breadcrumb )
		{
			$this->breadcrumbs[] = $Breadcrumb;
		}
		else if ( $Breadcrumb instanceof Controller_Backend )
		{
			$B = new Breadcrumb( $Breadcrumb->getTitle(), $Breadcrumb->getLink() );
			$this->breadcrumbs[] = $B;
		}
	}

	public function getBreadcrumbs()
	{
		$root = new Breadcrumb( _t('Homepage'), _L('Controller_Backend') );
		return array_merge( array( $root ), $this->breadcrumbs );
	}
	
	/**
	 * The backend index hanlder.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		return $this->getView()->render();
	}

	/**
	 * The login page handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function login()
	{
		$this->getView()->set( 'Error', null );
		if ( Request::get('login') && Request::get('password') )
		{
			$Admin = new Admin();
			if ( $Admin->login( Request::get('login'), Request::get('password') ) !== false )
			{
				return $this->halt();
			}
			else
			{
				$this->getView()->set( 'Error', 'Wrong password' );
			}
		}
		return $this->getView()->render();
	}
	
	/**
	 * The authorization ping handler.
	 * 
	 * @access public
	 * @return string The JSON response.
	 */
	public function auth()
	{
		$response = array( 'result' => 1 );
		return $this->outputJSON( $response );
	}
	
	public function logout()
	{
		$Admin = $this->getUser();
		if ( $Admin && $Admin->Id )
		{
			$Admin->logout();
		}
		return $this->halt('login');
	}

	/**
	 * The function returns array of backend modules.
	 *
	 * @static
	 * @access public
	 * @return array The modules.
	 */
	public static function getModules()
	{
		$result = array();
		$dir = dirname( __FILE__ ).'/backend';
		foreach ( File::readDir( $dir ) as $file )
		{
			$file = str_replace( '/', '_', str_replace( $dir.'/', '', $file ) );
			$file = substr( $file, 0, strlen( $file ) - 4 );
			if ( $file == 'standard' )
			{
				continue;
			}
			$class = 'Controller_Backend_'.$file;
			$controller = new $class();
			$result[ get_class( $controller ) ] = $controller->getTitle();
		}
		return $result;
	}	
}
