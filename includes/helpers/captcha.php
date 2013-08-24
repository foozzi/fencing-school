<?

/**
 * The Captacha helper class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Captcha
{
	
	private static $instance = null;
	
	private function __construct()
	{
	}
	
	/**
	 * The function returns Captcha object.
	 * 
	 * @static
	 * @access public
	 * @return object The Captcha.
	 */
	public static function getCaptcha()
	{
		if ( self::$instance === null )
		{
			include_once( Runtime::get( 'LIBS_DIR' ).'/secureimage/securimage.php' );
			self::$instance = new Securimage();
			self::$instance->code_length	= 4;
			self::$instance->image_width	= 80;
			self::$instance->shadow_text	= true;
			self::$instance->line_color		= '#3399ee';
			self::$instance->arc_line_colors = '#3399ee';
			self::$instance->text_color		= '#1177cc';
		}
		return self::$instance;
	}
	
	/**
	 * The function prints out the image.
	 * 
	 * @static
	 * @access public
	 */
	public static function show()
	{
		return self::getCaptcha()->show();
	}
	
	/**
	 * The function returns TRUE if test is passed, otherwise FALSE.
	 * 
	 * @static
	 * @access public
	 * @param string $value The tested value.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function check( $value )
	{
		return self::getCaptcha()->check( $value );
	}	
	
}
