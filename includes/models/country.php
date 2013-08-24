<?

/**
 * The Country class.
 * Stores enabled countries on the website.
 *
 * @author Yarick.
 * @version 0.1
 */
class Country
{
	
	private static $enabled = null;
	
	/**
	 * The function returns available countries.
	 *
	 * @static
	 * @access public
	 * @return array The countries.
	 */
	public static function getAvailable()
	{
		return ISO_Country::getInstance()->getAssocData();
	}
	
	/**
	 * The function returns enabled countries.
	 *
	 * @static
	 * @access public
	 * @return array The countries.
	 */
	public static function getEnabled()
	{
		if ( self::$enabled === null )
		{
			self::$enabled = array();
			$enabled = strtoupper( Config::get('countries/enabled', '') );
			if ( !$enabled )
			{
				return array();
			}
			$enabled = explode( ',', $enabled );
			foreach ( self::getAvailable() as $code => $data )
			{
				if ( in_array( $code, $enabled ) )
				{
					self::$enabled[ $code ] = $data;
				}
			}
			
		}
		return self::$enabled;
	}
	
	/**
	 * The function returns TRUE if country is enabled, otherwise FALSE.
	 *
	 * @static
	 * @access public
	 * @param string $code The country code.
	 * @return bool The enabled status.
	 */
	public static function on( $code )
	{
		$arr = array_keys( self::getEnabled() );
		return in_array( strtoupper( $code ), $arr );
	}
	
	/**
	 * The function returns Country name.
	 *
	 * @static
	 * @access public $code The country code.
	 * @return string The name.
	 */
	public static function get( $code )
	{
		$arr = self::getAvailable();
		return isset( $arr[ $code ] ) ? $arr[ $code ] : null;
	}
	
}