<?

/**
 * The Runtime class.
 * 
 * @version 0.1
 */
class Runtime
{

	private static $container = array();
	
	/**
	 * The function sets value by its key.
	 * 
	 * @static
	 * @access public
	 * @param string $key The value key. Can be associated array with values.
	 * @param mixed $value The value.
	 */
	public static function set( $key, $value = null )
	{
		if ( is_array( $key ) )
		{
			foreach ( $key as $name => $value )
			{
				self::set( $name, $value );
			}
		}
		else
		{
			self::$container[ $key ] = $value;
		}
	}
	
	/**
	 * The function returns value by its key.
	 * 
	 * @static
	 * @access public
	 * @param string $key The value key.
	 * @return mixed The value.
	 */
	public static function get( $key )
	{
		return isset( self::$container[ $key ] ) ? self::$container[ $key ] : null;
	}

}
