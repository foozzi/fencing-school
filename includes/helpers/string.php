<?

/**
 * The String helper class.
 * 
 * @version 0.1
 */
class String
{

	/**
	 * The function returns random string with passed length.
	 * 
	 * @static
	 * @access public
	 * @param int $length The string length.
	 */
	public static function random( $length )
	{
		if ( $length <= 0 )
		{
			die('1');
			return null;
		}		
		$string = '';
		for ( $i = 0; $i < ceil( $length / 40 ); $i++ )
		{
			$string .= sha1( microtime().rand( 1, 10000 ).date( 'Ymd' ) );
		}		
		return substr( $string, 0, $length );
	}

	/**
	 * The function returns string converted with first upper case.
	 * 
	 * @static
	 * @access public
	 * @param string $string The input string.
	 * @param string $delimiter The glue string.
	 * @param string $separator The words delimiter.
	 * @return string
	 */
	public static function toFirstCase( $string, $delimiter = ' ', $separator = ' ' )
	{
		$arr = explode( $separator, $string );
		for ( $i = 0, $len = count( $arr ); $i < $len; $i++ )
		{
			$arr[ $i ] = mb_strtoupper( mb_substr( $arr[ $i ], 0, 1, Config::get( 'encoding', 'utf-8' ) ), Config::get( 'encoding', 'utf-8' ) )
				.mb_substr( $arr[ $i ], 1, mb_strlen( $arr[ $i ] ) - 1, Config::get( 'encoding', 'utf-8' ) );
		}
		return implode( $delimiter, $arr );
	}
	
	/**
	 * The function returns string converted to link case 
	 * (all symbols are lower case).
	 * 
	 * @static
	 * @access public
	 * @param string $string The input string.
	 * @param string $delimiter The glue string.
	 * @param string $separator The words delimiter.
	 * @return string
	 */
	public static function toLinkCase( $string, $delimiter = '-', $separator = ' ' )
	{
		$arr = explode( $separator, $string );
		for ( $i = 0, $len = count( $arr ); $i < $len; $i++ )
		{
			$arr[ $i ] = urlencode( mb_strtolower( $arr[ $i ], Config::get( 'encoding', 'utf-8' ) ) );
		}
		return implode( $delimiter, $arr );
	}
	
	/**
	 * The function returns string converted to camel case 
	 * (all symbols are lower case but first of each word is upper case).
	 * 
	 * @static
	 * @access public
	 * @param string $string The input string.
	 * @param string $delimiter The glue string.
	 * @param string $separator The words delimiter.
	 * @return string
	 */
	public static function toCamelCase( $string, $delimiter = '', $separator = ' ' )
	{
		$arr = explode( $separator, $string, 2 );
		$arr[0] = mb_strtolower( $arr[0], Config::get( 'encoding', 'utf-8' ) );
		if ( isset( $arr[1] ) )
		{
			$arr[1] = self::toFirstCase( $arr[1], $delimiter, $separator );
		}
		return implode( $delimiter, $arr );
	}
	
	/**
	 * The function cuts left part of string if it equals left part.
	 * 
	 * @static
	 * @access public
	 * @param string $string The string for cut.
	 * @param string $left The left part to cut.
	 * @return string The cutted string.
	 */
	public static function cutLeft( $string, $left = '' )
	{
		$len = mb_strlen( $left, Config::get( 'encoding', 'utf-8' ) );
		if ( mb_substr( $string, 0, $len, Config::get( 'encoding', 'utf-8' ) ) == $left )
		{
			$string = mb_substr( $string, $len, mb_strlen( $string, Config::get( 'encoding', 'utf-8' ) ), 
				Config::get( 'encoding', 'utf-8' ) );
		}
		return $string;
	}
	
	/**
	 * The function converts json encoded data with replacing tags.
	 * For PHP version less 5.3.0
	 * 
	 * @static
	 * @access public
	 * @param mixed $data The object data.
	 * @return string The JSON response.
	 */
	public static function json_encode( $data )
	{
		return strtr( json_encode( $data ), array(
			'<'	=> '\u003C',
			'>' => '\u003E',
		) );
	}
	
	/**
	 * The function converts non latin characters to latin.
	 *
	 * @static
	 * @access public
	 * @param string The input string.
	 * @return string The converted string.
	 */
	public static function translit( $string )
	{
		$a = 'а,б,в,г,ґ,д,е,ё,ж ,з,и,й,к,л,м,н,о,п,р,с,т,у,ф,х,ц,ч ,ш ,щ  ,ы,э,ю ,я ,ь ,ъ ,і,ї,є,'
			.'А,Б,В,Г,ґ,Д,Е,Ё,Ж ,З,И,Й,К,Л,М,Н,О,П,Р,С,Т,У,Ф,Х,Ц,Ч ,Ш ,Щ  ,Ы,Э,Ю ,Я ,Ь ,Ъ ,І,Ї,Є'; 
		$b = 'a,b,v,g,g,d,e,e,zh,z,i,j,k,l,m,n,o,p,r,s,t,u,f,h,c,ch,sh,sch,y,e,yu,ya,\',\',i,i,e,'
			.'A,B,V,G,G,D,E,E,ZH,Z,I,J,K,L,M,N,O,P,R,S,T,U,F,H,C,CH,SH,SCH,Y,E,YU,YA,\',\',I,I,E'; 

		$b = explode( ',', $b );
		$repl = array();
		foreach ( explode( ',', $a ) as $i => $value )
		{
			$repl[ trim( $value ) ] = $b[ $i ];
		}
		return strtr( $string, $repl );
	}

	/**
	 * The function converts string to link string.
	 *
	 * @static
	 * @access public
	 * @param string The input string.
	 * @return string The converted string.
	 */
	public static function slug( $string )
	{
		return preg_replace( '/[^\w\d\._]+/', '_', trim( self::translit( $string ), ' "\'()' ) );
	}
	
		
	public static function cropText( $string = null, $maxlen = null )
	{		
		$len = ( mb_strlen( $string ) > $maxlen ) ? mb_strripos( mb_substr( $string, 0, $maxlen ), ' ' ) : $maxlen;
    	$cutStr = mb_substr( $string, 0, $len );
    	return ( mb_strlen( $string ) > $maxlen ) ? '"' . $cutStr . '..."' : '"' . $cutStr . '"';
	}   	
}
