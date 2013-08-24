<?

class Custom_URL extends URL
{

	public static function get( $Object, $tag = '', $tag2 = '' )
	{
		if ( $Object instanceof Car_Tyre )
		{
			if ( $Object->ParentId )
			{
				$Object = $Object->getParentTyre();
			}
			$url = rtrim( _L('Controller_Frontend'), '/' ).'/'.$Object->getBrand()->Slug.'-'.$Object->Slug.'-'.$Object->Id;
			if ( $tag instanceof Car_Engine && $tag->Id )
			{
				$url .= '/'.$tag->getBrand()->Slug.'-'.$tag->getModel()->Slug.'-'.$tag->Slug.'-'.$tag->Year;
			}
			return self::abs( $url.self::restoreGet() );
		}
		if ( $Object instanceof Customer )
		{
			if ( $Object->IsApproved )
			{
				
			}
			else
			{
				// @todo / /verify/<code>
			}
		}
		if ( $Object instanceof Article )
		{
			if ( $tag )
			{
				if ( $tag instanceof Product )
				{
					return self::abs( $Object->getParentLink().'/view/'.$Object->Id.'?backto='.Article_Reference::PRODUCT.'-'.$tag->Id.self::restoreGet('&') );
				}
				else
				{
					if ( isset( $_GET['tag'] ) )
					{
						unset( $_GET['tag'] );
					}
					return self::abs( $Object->getParentLink().'?tag='.urlencode( $tag instanceof Tag ? $tag->Name : $tag ).self::restoreGet('&') );
				}
			}
			else
			{
				return self::abs( $Object->getParentLink().'/view/'.$Object->Id.self::restoreGet() );
			}
			return self::abs( $Object->getParentLink().'/view/'.$Object->Id.self::restoreGet() );
		}
		if ( $Object instanceof Comment )
		{
			if ( !$Object->IsApproved )
			{
				return self::abs( _L('Controller_Frontend_Comments').'/approve/'.$Object->getHash() );
			}
			return self::abs( _L('Controller_Frontend_Comments') );
		}
		if ( $Object instanceof Document )
		{
			if ( $Object->IsFile )
			{
				return self::abs( File::url( $Object ) );
			}
			else
			{
				$link = $Object->getLink();
				return substr( $link, 0, 1 ) == '/' ? self::abs( $link ) : $link;
			}
		}
		if ( $Object instanceof Subscription )
		{
			return self::abs( _L('Controller_Frontend_Articles').'/unsubscribe/'.$Object->getCode() );
		}

		return false;
	}

	public static function detectKeyword( $host, array $array = array( ) )
	{
		return false;
	}

}
