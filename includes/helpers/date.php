<?

/**
 * The Date helper class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Date
{
	
	private static $months = array(
		'January'		=> array( 'Январь', 'Января' ),
		'February'		=> array( 'Февраль', 'Февраля' ),
		'March'			=> array( 'Март', 'Марта' ),
		'April'			=> array( 'Апрель', 'Апреля' ),
		'May'			=> array( 'Май', 'Мая' ),
		'June'			=> array( 'Июнь', 'Июня' ),
		'July'			=> array( 'Июль', 'Июля' ),
		'August'		=> array( 'Август', 'Августа' ),
		'September'		=> array( 'Сентябрь', 'Сентября' ),
		'October'		=> array( 'Октябрь', 'Октября' ),
		'November'		=> array( 'Ноябрь', 'Ноября' ),
		'December'		=> array( 'Декабрь', 'Декабря' ),
	);
	
	/**
	 * The function returns date with formatted month.
	 * 
	 * @static
	 * @access public
	 * @param string $date The date.
	 * @param bool $declension If TRUE returns month in declension.
	 * @return string The formatted date.
	 */
	public static function formatMonth( $date, $declension = false )
	{
		$replace = array();
		foreach ( self::$months as $source => $target )
		{
			$replace[ $source ] = $target[ $declension ? 1 : 0 ];
		}
		return strtr( $date, $replace );
	}
	
	public static function months( $declension = false )
	{
		$result = array();
		$i = 0;
		foreach ( self::$months as $target )
		{
			$result[ ++$i ] = $target[ $declension ? 1 : 0 ];
		}
		return $result;
	}
	
	public static function strtotime( $string, $now = null )
	{
		if ( preg_match( '/^(\d{1,2})\.(\d{1,2})\.(\d{1,2})$/', $string, $res ) ) // dd.mm.yy
		{
			return strtotime( sprintf( '%02d.%02d.%04d', $res[1], $res[2], $res[3] + ( $res[3] > 70 ? 1990 : 2000 ) ) );
		}
		if ( preg_match( '/^(\d{1,2})\.(\d{1,2})\.(\d{1,2}) (\d{2}):(\d{2})$/', $string, $res ) ) // dd.mm.yy hh:ii
		{
			return strtotime( sprintf( '%02d.%02d.%04d %02d:%02d', $res[1], $res[2], $res[3] + ( $res[3] > 70 ? 1990 : 2000 ), $res[4], $res[5] ) );
		}
		else if ( preg_match( '/^(\d{4})(\d{2})(\d{2})$/', $string, $res ) ) // yyyymmdd
		{
			return strtotime( sprintf( '%02d.%02d.%04d', $res[3], $res[2], $res[1] ) );
		}
		else if ( preg_match( '/^(\d{4})-(\d{2})$/', $string, $res ) ) // yyyy-mm
		{
			return strtotime( sprintf( '%02d.%02d.%04d', 1, $res[2], $res[1] ) );
		}
		return strtotime( $string, $now );
	}
	
}
