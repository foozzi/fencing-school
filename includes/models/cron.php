<?

class Cron
{
	
	// Sitemap generator
	public static function sitemap()
	{
		$xml = new SimpleXMLElement('<urlset />');
		$xml->addAttribute( 'xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );

		URL::absolute(true);

		$arr = array();
		$Page = new Content_Page();
		foreach ( $Page->findShortList( array( 'IsEnabled = 1' ), 'Position asc' ) as $Page )
		{
			foreach ( $Page->getController()->getSitemapNode() as $link )
			{
				$node = $xml->addChild( 'url' );
				$node->addChild( 'loc', $link );
			}
		}
		$xml = $xml->asXML();
		$xml = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="UTF-8"?>', $xml );
		$dom = new DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($xml);
		$xml = $dom->saveXML();
		file_put_contents( FILES_DIR.'/sitemap.xml', $xml );
	}

	public static function run()
	{
		/*$Price = new Pricelist();
		foreach ( $Price->findList( array( 'Status = '.Pricelist::POSTED ), 'Id asc' ) as $Price )
		{
			$Price->loadPrices();
		}*/
		//Cron::newsletter();
		//Cron::sitemap();
		Cron::ParseCarouser();	
	}
	
	public static function newsletter()
	{				
		$time = Config::get('cron/newsletterAt', 0);
		if ( $time > 0 && time() - 86400 * 7 > $time )
		{			
			return false;
		}
		Configuration::setValue('cron/newsletterAt', time());		
		$Subscription = new Subscription();
		
		$news = Article::getLastArticles(10, Article::NEWS);
		
		if ( count( $news ) > 0 )
		{			
			foreach ( $Subscription->findList() as $Subscription )
			{
				$Email = new Email_Newsletter( $Subscription, $news );
				$Email->send();
			}
		}
	}

	public static function ParseCarouser()
	{
		$Carousel = new Crawler_Carousel();
		$Carousel->parser();
	}

}
