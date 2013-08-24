<?

/**
 * The Error helper class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Error
{
	
	const EMAIL		= '/^(?:(?:(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff]))(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff]|\x5c(?=[@,"\[\]\x5c\x00-\x20\x7f-\xff])|\.(?=[^\.])){1,62}(?:[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]|(?<=\x5c)[@,"\[\]\x5c\x00-\x20\x7f-\xff])|[^@,"\[\]\x5c\x00-\x20\x7f-\xff\.]{1,2})|"(?:[^"]|(?<=\x5c)"){1,62}")@(?:(?!.{64})(?:[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.?|[a-zA-Z0-9])+\.(?:xn--[a-zA-Z0-9]+|[a-zA-Z]{2,6})|\[(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])(?:\.(?:[0-1]?\d?\d|2[0-4]\d|25[0-5])){3}\])$/';
	const PHONE		= '/^(\+|0){1}[\d]{8,}$/';
	
	/**
	 * The function test value by rule.
	 * 
	 * @static
	 * @access private
	 * @param string $value The value.
	 * @param string $rule The test rule.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	private static function testField( $value, $rule )
	{
		$empty = false;
		if ( substr( $rule, 0, 1 ) == '?' )
		{
			$empty = true;
			$rule = substr( $rule, 1 );
		}
		if ( $empty && trim( $value == '' ) )
		{
			return true;
		}
		if ( substr( $rule, 0, 1 ) == '(' && substr( $rule, -1 ) == ')' )
		{
			return eval( 'return '.substr( $rule, 1, strlen( $rule ) - 2 ).';' );
		}
		else
		{
			return preg_match( $rule, $value );
		}
	}
	
	/**
	 * The function tests object values by object rules.
	 * 
	 * @static
	 * @access public
	 * @param object $Object The object to test.
	 * @return array The array of wrong fields.
	 */
	public static function test( Object $Object )
	{
		$result = array();
		foreach ( $Object->getTestRules() as $field => $rule )
		{
			if ( property_exists( $Object, $field ) )
			{
				if ( !self::testField( $Object->$field, $rule ) )
				{
					$result[] = $field;
				}
			}
			else
			{
				$result[] = $field;
			}
		}
		return $result;
	}
	
	/**
	 * The function checks validation of data by type.
	 * 
	 * @static
	 * @access public
	 * @param mixed $data The data to check.
	 * @param string $type The type.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function check( $data, $type )
	{
		switch ( strtolower( $type ) )
		{
			case 'email':
				return preg_match( self::EMAIL, $data );
		}
		return false;
	}
	
}
