<?

class API_Wheels
{
	private static function read( $method, array $data = array() )
	{
		$url = Config::get('api:url');
		$data['key'] = Config::get('api:key');
		if ( preg_match( '/^(\.|\/)/', $url ) )
		{
			// local file
			$data['Format'] = 'json';
			$url .= ' '.$method.' "'.http_build_query( $data ).'"';
			$res = array();
			exec('php '.$url, $res);
			return implode("\n", $res);
		}
		else
		{
			// remote address
			$url .= $method.'/?'.http_build_query( $data );
			return file_get_contents( $url );
		}
	}

	public static function exec( $method, array $data = array() )
	{
		$res = self::read( $method, $data );
		return json_decode( $res );
	}
	
}