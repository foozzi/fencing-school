<?

/**
 * The Route class for loading router table and parsing URL.
 * 
 * @version 0.1
 */
class Route 
{
	
	private static $_data = array();
	private static $_links = array();
	private static $_classes = array();
	private static $Refs = array();

	/**
	 * The function parses url and add to Route container.
	 * 
	 * @static
	 * @access public
	 * @param string $url The URL.
	 */
	public static function run( $url )
	{
		if ( count( self::$_links ) )
		{
			return false;
		}
		$Router = new Router();
		foreach ( $Router->findList( array(), 'Link asc' ) as $Router )
		{
			self::set( $Router->Controller, $Router->Link, $Router->Type == Router::PAGE, $Router );
		}
		if ( !self::getController('/') )
		{
			self::set( 'Controller_Frontend', '/' );
		}
		$url = parse_url( $url );
		if ( !empty( $url['path'] ) )
		{
			self::$_data = explode( '/', ltrim( urldecode( $url['path'] ), '/' ) );
		}
		return true;
	}
	
	/**
	 * The function returns Route data array.
	 * 
	 * @static
	 * @access public
	 * @return array The Route data array.
	 */
	public static function get()
	{
		return self::$_data;
	}
	
	/**
	 * The function sets Router data value.
	 * 
	 * @static
	 * @access public
	 * @param mixed $Controller The Controller object or its name.
	 * @param string $link The link.
	 * @param bool $asClass If TRUE registers current link as class.
	 */
	public static function set( $Controller, $link, $asClass = false, Router $Router = null )
	{
		if ( is_a( $Controller, 'Controller' ) )
		{
			$Controller = get_class( $Controller );
		}
		if ( !isset( self::$_classes[ $link ] ) )
		{
			self::$_links[ $link ] = $Controller;
		}
		if ( $asClass && !isset( self::$_classes[ $Controller ] ) )
		{
			self::$_classes[ $Controller ] = $link;
		}
		if ( $Router !== null )
		{
			self::$Refs[ $link ] = $Router;
		}
	}
	
	/**
	 * The function returns controller name by link.
	 * 
	 * @static
	 * @access public
	 * @param string $link The link.
	 * @return string The controller name if it exists, otherwise NULL.
	 */
	public static function getController( $link )
	{
		return isset( self::$_links[ $link ] ) ? self::$_links[ $link ] : null;
	}
	
	/**
	 * The function returns link by controller or its name.
	 * 
	 * @static
	 * @access public
	 * @param mixed $Controller The controller object or its name.
	 * @return string The link.
	 */
	public static function getLink( $Controller )
	{
		if ( is_a( $Controller, 'Controller' ) )
		{
			$Controller = get_class( $Controller );
		}
		return isset( self::$_classes[ $Controller ] ) ? self::$_classes[ $Controller ] : null;
	}
	
	/**
	 * The function returns Router object for current link.
	 * 
	 * @static
	 * @access public
	 * @param string $link The link.
	 * @return object The Router.
	 */
	public static function getRouter( $link )
	{
		return isset( self::$Refs[ $link ] ) ? self::$Refs[ $link ] : new Router();
	}

}

	
