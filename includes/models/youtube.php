<?

class YouTube
{

	public static function getEmbed( $string )
	{
		if ( preg_match( '/youtube\.com.+v=([\w\d-_]+)/i', $string, $res ) 
			|| preg_match( '/youtu\.be\/([\w\d-_]+)/', $string, $res ) )
		{
			return 'http://youtube.com/embed/'.$res[1];
		}
		return null;
	}

}