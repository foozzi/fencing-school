<?

/**
 * The static Menu helper class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Menu
{
	
	private static $pages = null;
	
	/**
	 * The function initializes pages array.
	 * 
	 * @static
	 * @access private
	 */
	private static function init()
	{
		if ( self::$pages === null )
		{
			self::$pages = array();
			$Page = new Content_Page();
			foreach ( $Page->findResult( 'Id, Name, Title, Link, Description, Articles, Documents, Posts' ) as $Page )
			{
				self::$pages[ $Page->Id ] = $Page;
			}
		}
	}
	
	/**
	 * The function returns Content Page connected to current settings name.
	 * 
	 * @static
	 * @access public
	 * @param string $name The settings name.
	 * @return object The Content Page.
	 */
	public static function getPage( $name )
	{
		self::init();
		$Page = new Content_Page();
		if ( isset( self::$pages[ Config::get( 'links/'.$name ) ] ) )
		{
			$Page = self::$pages[ Config::get( 'links/'.$name ) ];
		}
		return $Page;
	}
	
	/**
	 * The function returns Content Page id connected to current settings name.
	 * 
	 * @static
	 * @access public
	 * @param string $name The settings name.
	 * @return string The id.
	 */
	public static function id( $name )
	{
		return intval( self::getPage( $name )->Id );
	}
	
	/**
	 * The function returns Content Page name connected to current settings name.
	 * 
	 * @static
	 * @access public
	 * @param string $name The settings name.
	 * @param string $default The default value if page not found.
	 * @return string The name.
	 */
	public static function name( $name, $default = null )
	{
		$Page = self::getPage( $name );
		if ( !$Page->Id )
		{
			return $default;
		}
		return self::getPage( $name )->Name;
	}
	
	/**
	 * The function returns Content Page label connected to current settings name.
	 * 
	 * @static
	 * @access public
	 * @param string $name The settings name.
	 * @param string $default The default value if page not found.
	 * @return string The label.
	 */
	public static function label( $name, $default = null )
	{
		$Page = self::getPage( $name );
		if ( !$Page->Id )
		{
			return $default;
		}
		return $Page->Title ? $Page->Title : $Page->Name;
	}
	
	/**
	 * The function returns Content Page link connected to current settings name.
	 * 
	 * @static
	 * @access public
	 * @param string $name The settings name.
	 * @return string The link.
	 */
	public static function link( $name )
	{
		return self::getPage( $name )->Link;
	}
	
}
