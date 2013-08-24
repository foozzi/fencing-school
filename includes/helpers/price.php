<?

/**
 * The Price helper class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Price
{
	
	/**
	 * The function returns price in appropriate format.
	 * 
	 * @static
	 * @access public
	 * @param float $price The price.
	 * @param bool $plain If TRUE returns price without currency calculation.
	 * @return string The price in appropriate format.
	 */
	public static function show( $price, $plain = false )
	{
		$dec = 2;
		$point = '.';
		$sep = ',';

		$Default = Runtime::get( 'CURRENCY_DEFAULT', new Currency() );
		$Current = Runtime::get( 'CURRENCY_CURRENT', new Currency() );
		if ( $Current->Rate > 0 && !$plain )
		{
			if ( $Default->Rate > 0 )
			{
				$price /= $Default->Rate;
			}
			$price *= $Current->Rate;
			$dec = $Current->Decimals;
			$point = $Current->Point;
			$sep = $Current->getSeparator();
		}
		return number_format( $price, $dec, $point, $sep );
	}

	/**
	 * The function returns currency sign.
	 *
	 * @static
	 * @access public
	 * @return string The sign.
	 */
	public static function sign()
	{
		$Current = Runtime::get( 'CURRENCY_CURRENT', new Currency() );
		return $Current->Sign;
	}
	
	/**
	 * The function returns HTML code with price and currency sign in current currency.
	 *
	 * @static
	 * @access public
	 * @param float $price The price
	 * @return string The HTML code.
	 */
	public static function html( $price )
	{
		$arr = array();
		$arr[] = '<span class="js-value">'.self::show( $price ).'</span>';
		$arr[] = '<span class="js-curr">'.self::sign().'</span>';
		return '<span class="js-price" value="'.floatval( $price ).'">'.implode('', $arr).'</span>';
	}

}
