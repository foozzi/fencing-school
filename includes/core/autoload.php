<?

/**
 * The Autoload class for loading classes by names.
 * 
 * @version 0.1
 */
class Autoload
{
	/**
	 * The function loads class file by its name if file exists.
	 * 
	 * @static
	 * @access public
	 * @param string $class The class name.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function load( $class )
	{
		if ( ( $file = self::exist( $class ) ) )
		{
			include_once( $file );
			return true;
		}
		return false;
	}
	
	/**
	 * The function returns TRUE if class exists, otherwise FALSE.
	 * 
	 * @static
	 * @access public
	 * @param string $class The class name.
	 * @return bool TRUE if class exists, otherwise FALSE.
	 */
	public static function exist( $class )
	{
		$dirs = array();
		if ( defined('INCLUDE_CORE_DIR') )
		{
			$dirs[] = INCLUDE_CORE_DIR;
		}
		if ( defined('INCLUDE_DIR') )
		{
			$dirs[] = INCLUDE_DIR.'/core';
		}
		if ( defined('INCLUDE_MODELS_DIR') )
		{
			$dirs[] = INCLUDE_MODELS_DIR;
		}
		if ( defined('INCLUDE_DIR') )
		{
			$dirs[] = INCLUDE_DIR.'/models';
		}
		if ( defined('INCLUDE_HELPERS_DIR') )
		{
			$dirs[] = INCLUDE_HELPERS_DIR;
		}
		if ( defined('INCLUDE_DIR') )
		{
			$dirs[] = INCLUDE_DIR.'/helpers';
		}
		$class = strtolower( $class );
		$arr = explode( '_', $class, 2 );
		if ( $arr[0] == 'controller' && !empty( $arr[1] ) )
		{
			$dirs = array();
			if ( defined('INCLUDE_CONTROLLERS_DIR') )
			{
				$dirs[] = INCLUDE_CONTROLLERS_DIR;
			}
			if ( defined('INCLUDE_DIR') )
			{
				$dirs[] = INCLUDE_DIR.'/controllers';
			}
			$class = $arr[1];
		}
		else if ( $arr[0] == 'view' && !empty( $arr[1] ) )
		{
			$dirs = array();
			if ( defined('INCLUDE_VIEWS_DIR') )
			{
				$dirs[] = INCLUDE_VIEWS_DIR;
			}
			if ( defined('INCLUDE_DIR') )
			{
				$dirs[] = INCLUDE_DIR.'/views';
			}
			$class = $arr[1];
		}
		else if ( $arr[0] == 'custom' && !empty( $arr[1] ) )
		{
			$dirs = array();
			if ( defined('INCLUDE_CUSTOM_DIR') )
			{
				$dirs[] = INCLUDE_VIEWS_DIR;
			}
			if ( defined('INCLUDE_DIR') )
			{
				$dirs[] = INCLUDE_DIR.'/custom';
			}
			$class = $arr[1];
		}
		else
		{
			# nothing to do
		}
		foreach( $dirs as $dir )
		{
			$file = $dir.'/'.str_replace( '_', '/', $class ).'.php';
			if ( file_exists( $file ) )
			{
				return $file;
			}
		}
		if ( defined('INCLUDE_INTERFACES_DIR') )
		{
			$dir = INCLUDE_INTERFACES_DIR;
			if ( substr( $class, 0, 1 ) == 'i' )
			{
				$file = $dir.'/'.str_replace( '_', '/', substr( $class, 1 ) ).'.php';
				if ( file_exists( $file ) )
				{
					return $file;
				}
			} 
		}
		return false;
	}
	
}

spl_autoload_register('Autoload::load');
