<?

/**
 * The Request class.
 * 
 * @version 0.1
 */
class Request
{
	
	/**
	 * The function returns value by its key from target global array.
	 * 
	 * @static
	 * @access public
	 * @param string $key The value key.
	 * @param mixed $default The default value.
	 * @param string $target The array names separated by comma from 
	 * which variable will be given.
	 * @return mixed The value.
	 */
	public static function get( $key, $default = null, $target = 'GET,POST' )
	{
		$array = array();
		foreach ( explode( ',', $target ) as $word )
		{
			switch ( strtoupper( $word ) )
			{
				case 'SERVER':
					$array = array_merge( $array, $_SERVER );
					break;
					
				case 'COOKIE':
					$array = array_merge( $array, $_COOKIE );
					break;
					
				case 'GET':
					$array = array_merge( $array, $_GET );
					break;
					
				case 'POST':
					$array = array_merge( $array, $_POST );
					break;
					
				case 'SESSION':
					$array = array_merge( $array, $_SESSION );
					break;
					
			}
		}
		if ( $key === null )
		{
			return $array;
		}
		return isset( $array[ $key ] ) ? $array[ $key ] : $default;
	}
	
	/**
	 * The function sets global array value by its ket and target name.
	 * 
	 * @static
	 * @access public
	 * @param string $target The target array name such as COOKIE, 
	 * SERVER, GET, POST.
	 * @param string $key The value $key.
	 * @param mixed $value The value.
	 */
	public static function set( $target, $key, $value )
	{
		switch ( strtoupper( $target ) )
		{
			case 'GET':
				$_GET[ $key ] = $value;
				break;
				
			case 'POST':
				$_POST[ $key ] = $value;
				break;
				
			case 'SERVER':
				$_SERVER[ $key ] = $value;
				break;
				
			case 'COOKIE':
				$_COOKIE[ $key ] = $value;
				$args = func_get_args();
				if ( isset( $args[3] ) )
				{
					$path = isset( $arg[4] ) ? $arg[4] : '/';
					$domain = isset( $arg[5] ) ? $arg[5] : null;
					setcookie( $key, $value, $args[3], $path, $domain );
				}
				break;
		}
	}
	
	/**
	 * The function checks where from user came and returns TRUE if host equals to posted host.
	 * 
	 * @static
	 * @access public
	 * @param string $host The hostname, if NULL will be used current website hostname
	 * @return bool TRUE if came from posted host, otherwise FALSE.
	 */
	public static function cameFrom( $host = null )
	{
		if ( $host === null )
		{
			$host = self::get( 'HTTP_HOST', 'localhost', 'SERVER' );
		}
		$referer	= parse_url( self::get( 'HTTP_REFERER', '', 'SERVER' ) );
		$referer	= isset( $referer['host'] ) ? $referer['host'] : null;
		$host		= preg_replace( '/^www\./', '', strtolower( $host ) );
		$referer	= preg_replace( '/^www\./', '', strtolower( $referer ) );
		return $referer == $host;
	}

	/**
	 * The function changes all _GET "null" values to empty string.
	 *
	 * @static
	 * @access public
	 */
	public static function flashNull()
	{
		foreach ( $_GET as $id => $value )
		{
			if ( strtolower( $value ) == 'null' )
			{
				$_GET[ $id ] = '';
			}
		}
	}

	public static function onLocalhost()
	{
		return strpos( self::get('HTTP_HOST', '', 'SERVER'), '.' ) === false;
	}
	
}
