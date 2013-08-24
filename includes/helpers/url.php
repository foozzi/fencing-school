<?

/**
 * The static URL class for build links.
 * 
 * @author Yarick.
 * @version 0.3
 */
class URL
{
	
	private static $absolute = false;
	
	/**
	 * The function sets format of URL - absolute or relative.
	 *
	 * @static
	 * @access public
	 * @param bool $bool If TRUE all URL will be returned with absolute path (including host),
	 * if NULL just returns current value.
	 */
	public static function absolute( $bool = null )
	{
		if ( $bool !== null )
		{
			self::$absolute = (bool)$bool;
		}
		return self::$absolute;
	}
	
	/**
	 * The function returns correct absolute or relative URL.
	 *
	 * @static
	 * @access public
	 * @param string $link The link.
	 * @return string The URL.
	 */
	public static function abs( $link )
	{
		if ( preg_match( '/^(http|https|ssl|ftp):\/\//', $link ) )
		{
			return $link;
		}
		if ( !self::$absolute )
		{
			return '/'.ltrim( $link, '/' );
		}
		return Runtime::get('HTTP_PROTOCOL').Runtime::get('HTTP_HOST').'/'.ltrim( $link, '/' );
	}
	
	/**
	 * The function returns GET query from current variables.
	 * 
	 * @static
	 * @access public
	 * @param string $questionMark The first character of query.
	 * @return string The query.
	 */
	public static function restoreGet( $questionMark = '?' )
	{
		$link = '';
		if ( count( $_GET ) )
		{
			$link .= $questionMark.http_build_query( $_GET );
		}
		return $link;
	}
	
	/**
	 * The function returns link for current object.
	 * 
	 * @static
	 * @access public
	 * @param mixed $Object The object.
	 * @param string $tag The tag.
	 * @param bool $restoreGet If TRUE returns link with GET parameters.
	 * @return string The URL.
	 */
	public static function get( $Object, $tag = '', $restoreGet = false )
	{
		$res = Custom_URL::get( $Object, $tag, $restoreGet );
		if ( $res !== false )
		{
			return $res;
		}
		$link = '';
		if ( $restoreGet && count( $_GET ) )
		{
			$link .= strpos( $link, '?' ) === false ? '?' : '&';
			$link .= http_build_query( $_GET );
		}
		if ( $Object instanceof Content_Page )
		{
			if ( !$Object->Link && $Object->Children )
			{
				foreach ( Content_Page::getChildren( $Object->Id ) as $Child )
				{
					if ( $Child->Link )
					{
						return self::abs( $Child->Link );
					}
				}
			}
			return self::abs( $Object->Link );
		}
		if ( $Object instanceof Paginator )
		{
			$data = $_GET;
			if ( isset( $data['page'] ) )
			{
				unset( $data['page'] );
			}
			$data['page'] = $tag;
			$arr = explode( '?', Request::get('REQUEST_URI', '/', 'SERVER') );
			return self::abs( $arr[0].'?'.http_build_query( $data ) );
		}
		if ( $Object instanceof Controller )
		{
			return self::abs( _L( $Object ) );
		}
		if ( is_string( $Object ) && $Object != '' )
		{
			if ( class_exists( $Object ) )
			{
				return self::get( new $Object() );
			}
			return self::abs( $Object );
		}
		return self::abs( _L('Controller_Frontend') );
	}
	
	/**
	 * The function returns TRUE if Target object is current URL.
	 * 
	 * @static
	 * @access public
	 * @param object $Target The Target.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function on( $Target )
	{
		$uri = Runtime::get('REQUEST_URI');
		if ( $Target instanceof Content_Page )
		{
			return $Target->Link == $uri;
		}
		if ( $Target instanceof Article )
		{
			return $Target->Link == $uri;
		}
		return $Target == $uri;
	}

	/**
	 * The function returns GET query.
	 *
	 * @static
	 * @access public
	 * @param array The data.
	 * @return string The query.
	 */
	public static function buildGet( array $data = array() )
	{
		$data = array_merge( $_GET, $data );
		return http_build_query( $data );
	}
	
}
