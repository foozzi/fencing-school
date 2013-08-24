<?

error_reporting(E_ALL);

define( 'ROOT_DIR',					dirname( dirname( __FILE__ ) ) );
define( 'INCLUDE_DIR',				dirname( __FILE__ ) );
define( 'INCLUDE_CORE_DIR',			INCLUDE_DIR.'/core' );
define( 'INCLUDE_INTERFACES_DIR',	INCLUDE_DIR.'/interfaces' );
define( 'INCLUDE_MODELS_DIR',		INCLUDE_DIR.'/models' );
define( 'INCLUDE_HELPERS_DIR',		INCLUDE_DIR.'/helpers' );
define( 'INCLUDE_CONTROLLERS_DIR',	INCLUDE_DIR.'/controllers' );
define( 'INCLUDE_VIEWS_DIR',		INCLUDE_DIR.'/views' );
define( 'LOCALE_DIR',				ROOT_DIR.'/i18n' );
define( 'FILES_DIR',				ROOT_DIR.'/files' );
define( 'LIBS_DIR',					ROOT_DIR.'/libs' );

include_once( INCLUDE_CORE_DIR.'/autoload.php' );

/**
 * The main Application class.
 * Application includes all needed classes and initializes all basic features.
 * 
 * @version 0.1
 */
class Application
{
	
	private static $started = array();
	private static $content = null;
	
	/**
	 * The function runs application.
	 * 
	 * @statis
	 * @access public
	 * @param string $modules The modules to load, separated by comma.
	 */
	public static function run( $modules = null )
	{
		self::$started = array( microtime( true ), memory_get_usage( true ) );
		Runtime::set( 'ROOT_DIR', ROOT_DIR );
		Runtime::set( 'INCLUDE_DIR', INCLUDE_DIR );
		Runtime::set( 'LOCALE_DIR', LOCALE_DIR );
		if ( defined('CONFIG_DIR') )
		{
			Runtime::set( 'CONFIG_DIR', CONFIG_DIR );
		}
		else
		{
			Runtime::set( 'CONFIG_DIR', Runtime::get('ROOT_DIR').'/config' );
		}
		if ( defined('TEMPLATE_DIR') )
		{
			Runtime::set( 'TEMPLATE_DIR', TEMPLATE_DIR );
		}
		else
		{
			Runtime::set( 'TEMPLATE_DIR', Runtime::get('ROOT_DIR').'/templates' );
			define('TEMPLATE_DIR', Runtime::get('TEMPLATE_DIR'));
		}
		if ( defined('FILES_DIR') )
		{
			Runtime::set( 'FILES_DIR', FILES_DIR );
		}
		else
		{
			Runtime::set( 'FILES_DIR', Runtime::get('ROOT_DIR').'/files' );
		}
		if ( defined('LIBS_DIR') )
		{
			Runtime::set( 'LIBS_DIR', LIBS_DIR );
		}
		else
		{
			Runtime::set( 'LIBS_DIR', Runtime::get('ROOT_DIR').'/libs' );
		}
		
		$arr = array( 'config', 'settings', 'locale', 'route', 'security', 'controller' );
		if ( $modules !== null )
		{
			$arr = explode( ',', $modules );
		}
		
		if ( in_array( 'config', $arr ) )
		{
			self::initConfig();
			Custom_Application::initConfig();
		}
		if ( in_array( 'settings', $arr ) )
		{
			self::initSettings();
			Custom_Application::initSettings();
		}
		if ( Config::get('timezone') )
		{
			date_default_timezone_set(Config::get('timezone'));
		}

		Database::getInstance()->execute( 'set time_zone="'.date('P').'"');

		Runtime::set('HTTP_HOST', Request::get('HTTP_HOST', Config::get('host'), 'SERVER'));
		Runtime::set('HTTP_PROTOCOL', Request::get('HTTPS', false, 'SERVER') ? 'https://' : 'http://');
		
		if ( in_array( 'locale', $arr ) )
		{
			if ( !Custom_Application::initLocale() )
			{
				self::initLocale();
			}
		}
		if ( in_array( 'route', $arr ) )
		{
			if ( !Custom_Application::initRoute() )
			{
				self::initRoute();
			}
		}
		if ( in_array( 'security', $arr ) )
		{
			if ( !Custom_Application::initSecurity() )
			{
				self::initSecurity();
			}
		}
		if ( in_array( 'controller', $arr ) )
		{
			if ( !Custom_Application::initController() )
			{
				self::initController();
			}
		}
	}
	
	/**
	 * The function loads configuration files and database configs.
	 * 
	 * @statis
	 * @access private
	 * @return bool TRUE on success, FALSE on failure.
	 */
	private static function initConfig()
	{
		$dir = Runtime::get( 'CONFIG_DIR' );
		$dh = opendir( $dir );
		if ( $dh )
		{
			while ( ( $file = readdir( $dh ) ) !== false )
			{
				if ( $file == '.' || $file == '..' || is_dir( $dir.'/'.$file ) )
				{
					continue;
				}
				Config::load( $dir.'/'.$file );
			}
			closedir( $dh );
			return true;
		}
		return false;
	}
	
	/**
	 * The function loads configuration data from database.
	 * 
	 * @static
	 * @access private
	 */
	private static function initSettings()
	{
		Config::loadObject( new Configuration() );
	}
	
	/**
	 * The function sets current localization.
	 * 
	 * @static
	 * @access private
	 */
	private static function initLocale()
	{
		$current = Config::get('l10n@default', 'en_US');
		$l10n = Config::get('l10n', $current);
		if ( is_array( $l10n ) )
		{
			$host = strtolower( Runtime::get('HTTP_HOST') );
			if ( isset( $l10n[ $host ] ) )
			{
				$current = $l10n[ $host ];
			}
		}
		else
		{
			$locales = explode( ',', $l10n );
			if ( isset( $locales[0] ) )
			{
				$current = $locales[0];
			}
			$arr = explode(',', Request::get('HTTP_ACCEPT_LANGUAGE', '', 'SERVER'));
			foreach ( $arr as $locale )
			{
				if ( strlen( $locale ) == 5 )
				{
					$locale = strtolower(substr( $locale, 0, 2 )).'_'.strtoupper(substr($locale, 3, 2 ));
					if ( in_array( $locale, $locales ) )
					{
						$current = $locale;
						break;
					}
				}
				if ( strlen( $locale ) == 2 )
				{
					foreach ( $locales as $item )
					{
						if ( strtolower( $locale ) == substr( $item, 0, 2 ) )
						{
							$current = $item;
							break;
						}
					}
				}
			}
		}
		Locale::set( $current );
		Locale::$TEST_MODE	= !Config::get('l10n@smooth', false);
		ISO_Table::setLocale( Locale::get() );
		Locale::load('Common');
	}
	
	/**
	 * The function initializes router feature.
	 * 
	 * @static
	 * @access private
	 */
	private static function initRoute()
	{
		Route::run( Request::get('REQUEST_URI', '/', 'SERVER') );
		
		$host = strtolower(Config::get('host'));
		if ( $host )
		{
			$sub = trim( str_replace( $host, '', preg_replace('/^www\./', '', strtolower(Runtime::get('HTTP_HOST'))) ), '.' );
			if ( !$sub )
			{
				$sub = 'www';
			}
			Runtime::set('HTTP_SUBDOMAIN', $sub);
		}

		$controller = 0;
		$args = array();

		$path = Route::get();
		Runtime::set( 'REQUEST_URI', '/'.implode( '/', $path ) );
		if ( !is_array( $path ) )
		{
			$path = ltrim( '/', explode( '/', $path ) );
		}
		$values = $path;
		$link = '/';
		for ( $i = 0; $i < count( $values ); $i++ )
		{
			$arr = array_slice( $values, 0, count( $values ) - $i );
			$link = '/'.implode( '/', $arr );
			$controller = Route::getController( $link );
			if ( $controller )
			{
				$args = array_slice( $values, count( $values ) - $i );
				break;
			}
		}
		if ( !$controller )
		{
			$controller = Route::getController('/');
			$args = $values;
		}
		if ( !$controller )
		{
			echo "No controller found: /".implode( '/', $path )."\n";
			exit;
		}
		Runtime::set( 'ROUTING_CONTROLLER', $controller );
		Runtime::set( 'ROUTING_ROUTER', Route::getRouter( $link ) );
		Runtime::set( 'ROUTING_ARGUMENTS', $args );
	}
	
	/**
	 * The function initializes and authorizes system users, such as 
	 * admins and customers.
	 * 
	 * @static
	 * @access private
	 */
	private static function initSecurity()
	{
		$Admin = new Admin();
		$Admin = $Admin->auth();
		if ( $Admin === false )
		{
			$Admin = new Admin();
		}
		Runtime::set( 'SECURITY_USER', $Admin );
		$Customer = new Customer();
		$Customer = $Customer->auth();
		if ( $Customer === false )
		{
			$Customer = new Customer();
		}
		Runtime::set( 'SECURITY_CUSTOMER', $Customer );
	}
	
	/**
	 * The function initializes and runs current controller.
	 * 
	 * @static
	 * @access private 
	 */
	private static function initController()
	{
		// cart init
		session_start();
		self::setContent( Controller::executeController( Runtime::get('ROUTING_CONTROLLER'), Runtime::get('ROUTING_ARGUMENTS') ) );
	}
	
	/**
	 * The function sets content value.
	 * 
	 * @static
	 * @access protected
	 * @param string $content The rendered content.
	 */
	protected static function setContent( $content )
	{
		self::$content = $content;
	}
	
	/**
	 * The function returns Application content.
	 * 
	 * @static
	 * @access public
	 * @return string The rendered content
	 */
	public static function getContent()
	{
		return Config::get('obfuscating', false) ? preg_replace('/\s{2,}/', '', self::$content) : self::$content;
	}
	
	/**
	 * The function returns debug info.
	 * 
	 * @static
	 * @access public
	 * @return string The debug info.
	 */
	public static function getDebugInfo()
	{
		$result = sprintf( "memory:\n\tinitial usage %d Kb\n\tpeack usage %d Kb\n\tmax usage %d Kb\n\tcurrent usage %dKb\n\tused by application %d Kb", 
			self::$started[1] / 1024, memory_get_peak_usage( true ) / 1024, memory_get_peak_usage() / 1024, memory_get_usage( true ) / 1024, 
			( memory_get_peak_usage() - self::$started[1] ) / 1024 );
		/*
		if ( function_exists('getrusage') )
		{
			$data = getrusage();
			$result .= sprintf( "\tmax resident size %d Kb\n\tshared memory size %d Kb\n\tunshared data size %d Kb\n", 
				$data['ru_maxrss'] / 1024, $data['ru_ixrss'] / 1024, $data['ru_idrss'] / 1024 );
		}
		*/
		$result .= sprintf( "\ntime:\n\tused by application %.03f\n", microtime( true ) - self::$started[0] );
		$queries = Database::getInstance()->getLog();
		$result .= sprintf( "\nqueries:\n\t%d", count( $queries ) );
		$result .= sprintf( "\ninstances of objects:\n\t%d", Object::getInstanceCount() );
		return $result;
	}
	
}

