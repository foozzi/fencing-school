<?

/**
 * The Console class to show information in command line.
 * 
 * @author Yarick.
 * @version 0.1
 */
final class Console
{
	
	const RED 		= 31;
	const GREEN		= 32;
	const ORANGE	= 33;
	const BLUE		= 34;
	const PURPLE	= 35;
	const CYAN		= 36;
	const GREY		= 37;
	/*
	90 = dark grey
91 = light red
92 = light green
93 = yellow
94 = light blue
95 = light purple
96 = turquoise
	 */
	const NORMAL	= 0;
	const BOLD		= 1;
	
	private static $cols = null;
	private static $rows = null;
		
	/**
	 * The function prints out arguments to command line.
	 * 
	 * @static
	 * @access private
	 * @param array $args The arguments to print.
	 */	
	private static function show( $args = array() )
	{
		
	}
	
	/**
	 * The function initializes command line window size.
	 * 
	 * @static
	 * @access private
	 * @return bool TRUE on success, FALSE on failure.
	 */
	private static function init()
	{
		if ( self::$cols || self::$rows )
		{
			return false;
		}
		if ( preg_match_all( "/rows.([0-9]+);.columns.([0-9]+);/", strtolower(exec('stty -a |grep columns')), $output ) )
		{
			self::$rows = $output[1][0];
			self::$cols = $output[2][0];
		}
		return (bool)( self::$cols || self::$rows );
	}
	
	/**
	 * The function prints out the string at the left side with spaces at the end.
	 * 
	 * @static
	 * @access public
	 * @param string $string The string to print.
	 * @param int $length The length of the left column. 
	 * @param int $style The string style.
	 */
	public static function left( $string, $length = 50, $style = self::NORMAL )
	{
		self::init();
		$cols = self::$cols > $length ? $length : self::$cols - 10;
		self::write( sprintf( "%-".$cols."s", $string ), $style );
	}
	
	/**
	 * The function prints out the string at the right side with spaces at the begining.
	 * 
	 * @static
	 * @access public
	 * @param string $string The string to print.
	 * @param int $length The length of the left column. 
	 */
	public static function right( $string, $length = 50 )
	{
		self::init();
		$cols = self::$cols > $length ? $length : self::$cols - 10;
		self::write( sprintf( "%".$cols."s", $string ) );
	}
	
	/**
	 * The function prints out the status and current style.
	 * 
	 * @static
	 * @access public
	 * @param $status $status The status to print.
	 * @param int $style The string style.
	 */
	public static function status( $status, $style = self::NORMAL )
	{
		self::init();
		self::write( "[ " );
		self::write( $status, $style );
		self::writeln( " ]" );
	}
	
	/**
	 * The function prints out the string with the current style.
	 * 
	 * @static
	 * @access public
	 * @param string $string The string to print.
	 * @param int $style The string style.
	 */
	public static function write( $string, $style = self::NORMAL )
	{
		self::init();
		if ( $style != self::NORMAL )
		{
			echo "\033[".$style."m";
		}
		if ( is_array( $string ) )
		{
			$string = implode( ', ', $string );
		}
		echo $string;
		if ( $style != self::NORMAL )
		{
			echo "\033[30m";
		}
	}
	
	/**
	 * The function prints out the string with the current style and end line 
	 * character.
	 * 
	 * @static
	 * @access public
	 * @param string $string The string to print.
	 * @param int $style The string style.
	 */
	public static function writeln( $string = '', $style = self::NORMAL )
	{
		if ( is_array( $string ) )
		{
			$string = implode( "\n", $string );
		}
		self::write( $string."\n", $style );
	}
	
	/**
	 * The function prints out the progress bar with percentage.
	 * 
	 * @static
	 * @access public
	 * @param int $value The percentage value between 0 and 100.
	 * @param string $char The progress bar char if empty - no progress 
	 * @param string $prefix The string before progress bar.
	 * bar, only percentage.
	 * @param int $length The progress bar length.
	 */
	public static function progress( $value, $char = '', $prefix = '', $length = 40, $startAt = 0 )
	{
		//self::write( "\r".$prefix );
		self::goColumn( $startAt );
		self::write( $prefix );
		if ( $char )
		{
			$done = ceil( $value * $length / 100 );
			self::write( str_repeat( $char, $done ) );
			self::write( str_repeat( ' ', $length - $done ) );
			self::write( ' ('.$value.'%)' );
		}
		else
		{
			self::write( $value.'%' );
		}
	}
	
	/**
	 * The function prints out the string of dashes in one line like separator.
	 * 
	 * @static
	 * @access public
	 * @param int $length The count of dashes.
	 */
	public static function divider( $length = 60 )
	{
		self::writeln( str_repeat( '-', $length ) );
	}
	
	/**
	 * The function reads data from php standard input stream.
	 * 
	 * @static
	 * @access public
	 * @param mixed $variants The possible variants which could be accepted 
	 * from console. The array of vairants or string separeted by comma in low 
	 * case.
	 * @param string $error The error string if read value is not in possible 
	 * variants.
	 * @return string The read string.
	 */
	public static function read( $variants = null, $error = null )
	{
		$fh = fopen( 'php://stdin', 'r' );
		if ( $variants !== null && !is_array( $variants ) )
		{
			$variants = explode( ',', $variants );
		}
		$line = null;
		while ( ( $string = fgets( $fh, 512 ) ) !== false )
		{
			$line = strtolower( trim( $string ) );
			if ( $variants === null )
			{
				break;
			}
			if ( in_array( $line, $variants ) )
			{
				break;
			}
			else if ( $error )
			{
				self::writeln( $error, self::RED );
			}
		}
		return $line;
	}
	
	/**
	 * The function returns console window size.
	 * 
	 * @static
	 * @access public
	 * @return string The size.
	 */
	public static function getWindowSize()
	{
		self::init();
		return self::$cols.'x'.self::$rows;
	}
	
	/**
	 * The function sets cursor to first column.
	 * 
	 * @static
	 * @access public
	 */
	public static function goColumn( $col = 0 )
	{
		echo chr(27) . "[".$col."G";
	}
	
	/**
	 * The function sets cursor to begin of row.
	 * 
	 * @static
	 * @access public
	 */
	public static function goFirst()
	{
		echo "\r";
	}
	
	/**
	 * The function sets cursor up to $level rows.
	 * 
	 * @static
	 * @access public
	 * @param int $level The count of rows.
	 */
	public static function goUp( $level = 1 )
	{
		echo chr(27) . "[" . $level ."A";
	}
	
}
