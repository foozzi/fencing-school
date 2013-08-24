<?

/**
 * The localization class.
 * Stores loaded translations.
 * 
 * @author Yarick.
 * @version 1.0
 */
class Locale
{

	private static $loaded = array();
	private static $data = array();
	
	private static $locale = 'en_US';
	
	public static $TEST_MODE = true;
	
	/**
	 * The function clears the loaded transations.
	 * 
	 * @static
	 * @access public
	 */
	public static function clear()
	{
		self::$loaded = array();
		self::$data = array();
	}
	
	/**
	 * The function returns full locale file path.
	 * 
	 * @static
	 * @access protected
	 * @return string The locale file path.
	 */
	private static function getFilePath( $file )
	{
		return Runtime::get('LOCALE_DIR').'/'.self::get().'/'.$file;
	}
	
	/**
	 * The function loads data to language container from array or file.
	 * 
	 * @static
	 * @access public
	 * @param mixed $data The array of translation or file to translation file.
	 * You also use many parameters to load few files.
	 * @example
	 * Locale::add( 'Main', 'Tooltips', 'Frontend' );
	 */
	public static function load( $data )
	{
		if ( is_array( $data ) )
		{
			self::$data = array_merge( self::$data, $data );
		} 
		else
		{
			if ( func_num_args() > 1 )
			{
				for ( $i = 0; $i < func_num_args(); $i++ )
				{
					self::load( func_get_arg( $i ) );
				}
			}
			else
			{
				if ( in_array( $data, self::$loaded ) )
				{
					return false;
				}
				$filename = $data.'.php';
				if ( file_exists( self::getFilePath( $filename ) ) )
				{
					self::$loaded[] = $filename;
					self::$data = array_merge( self::$data, include( self::getFilePath( $filename ) ) );
				}
			}
		}
		return true;
	}
	
	/**
	 * The function sets current locale.
	 * 
	 * @static
	 * @access public
	 * @param string $locale The current locale.
	 */
	public static function set( $locale )
	{
		if ( !$locale )
		{
			$locale = 'en_US';
		}
		return self::$locale = $locale;
	}
	
	/**
	 * The function returns locale value by key from language container.
	 * 
	 * @static
	 * @access public
	 * @param string $key The language key.
	 * @param mixed $testMode If TRUE returns prefix for unknown labels, if NULL get value from TEST_MODE.
	 * @return string
	 */
	public static function get( $key = null, $testMode = null )
	{
		if ( $key === null )
		{
			return self::$locale;
		}
		if ( isset( self::$data[ $key ] ) )
		{
			return self::$data[ $key ];
		}
		if ( $testMode === null )
		{
			$testMode = self::$TEST_MODE;
		}
		return $testMode ? (self::$locale.'['.$key.']') : $key;
	}
	
	/**
	 * The function returns TRUE if translation for key exists and FALSE otherwise.
	 * 
	 * @static
	 * @access public
	 * @return bool TRUE if translation for key exists and FALSE otherwise.
	 */
	public static function has( $key )
	{
		return isset( self::$data[ $key ] );
	}
	
	/**
	 * The function translates input array.
	 *
	 * @static
	 * @access public
	 * @param array $array The input array.
	 * @return array The translated array.
	 */
	public static function translate( array $array )
	{
		$result = array();
		foreach ( $array as $key => $value )
		{
			$result[ $key ] = self::get( $value, false );
		}
		return $result;
	}

	/**
	 * The function returns enabled locales.
	 *
	 * @static
	 * @access public
	 * @return array The locales.
	 */
	public static function getEnabled()
	{
		$arr = array();
		$enabled = Config::get('l10n', '');
		if ( !$enabled )
		{
			return array();
		}
		$enabled = explode( ',', $enabled );
		foreach ( ISO_Language::getInstance()->getAssocData() as $code => $data )
		{
			if ( in_array( $code, $enabled ) )
			{
				$arr[ $code ] = $data;
			}
		}
		return $arr;
	}
	
	/**
	 * The function returns language name by its code.
	 * 
	 * @static
	 * @access public
	 * @param string $code The language code.
	 * @return string The name.
	 */
	public static function getName( $code )
	{
		return ISO_Language::getInstance()->get( $code );
	}

}

/**
 * The function returns translation for current key.
 * Can be used extra arguments for patters {arg1}, {arg2}, {argN}.
 * 
 * @param string $key The translation key.
 * @return string The translated string.
 */
function _t( $key )
{
	$str = Locale::get( $key );
	if ( func_num_args() > 1 )
	{
		$args = func_get_args();
		for ( $i = 1; $i < count( $args ); $i++ )
		{
			$repl['{arg'.$i.'}'] = $args[ $i ];
		}
		$str = strtr( $str, $repl );
	}
	return $str;
}
