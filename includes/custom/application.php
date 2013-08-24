<?

final class Custom_Application
{

	public static function initConfig()
	{

	}

	public static function initSettings()
	{

	}

	public static function initLocale()
	{

	}

	public static function initRoute()
	{

	}

	public static function initSecurity()
	{

	}

	public static function initController()
	{
		$id = Request::get('Currency', null, 'COOKIE');
		$Current = new Currency();
		$Default = Currency::findDefault();
		$code = null;
		if ( function_exists('geoip_record_by_name') )
		{
			$arr = @geoip_record_by_name( Request::get('REMOTE_ADDR', '', 'SERVER') );
			$code = isset( $arr['country_code'] ) ? $arr['country_code'] : null;
		}
		$arr = Currency::findCurrencies(true);
		foreach ( $arr as $i => $Currency )
		{
			if ( $Currency->Id == $id )
			{
				$Current = $Currency;
				break;
			}
		}
		if ( !$Current->Id && $code )
		{
			foreach ( $arr as $Currency )
			{
				if ( $Currency->hasCountry( $code ) )
				{
					$Current = $Currency;
					break;
				}
			}
		}
		if ( !$Current->Id )
		{
			foreach ( $arr as $Currency )
			{
				$Current = $Currency;
				break;
			}
		}

		Runtime::set( 'CURRENCY_DEFAULT', $Default );
		Runtime::set( 'CURRENCY_CURRENT', $Current );

		return false;
	}

}