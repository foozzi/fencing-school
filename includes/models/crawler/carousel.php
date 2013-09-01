<?

/**
 * The Carousel image parser model class.
 * 
 * @author foozzi.
 * @version 0.1
 */

class Crawler_Carousel extends Crawler
{

	protected function isCacheable()
	{
		return true;
	}

	protected function URL()
	{
		return 'http://nffu.org.ua';
	}

	public function parser()
	{
		header('Access-Control-Allow-Origin: *');				
		ignore_user_abort(true);
		
		$dom = $this->getDom($this->URL());		
		$savedir = FILES_DIR . '/carousel';		

		if( !file_exists( $savedir ) )
		{
		    mkdir( $savedir, 0777, true ) or die( 'Невозможно создать директорию: ' . $savedir );
		}
		$num = 1;
		foreach ( $dom->find('div.slide div.slide-inner') as $link )
		{												
			$img = ($link->find('img',0)->src);
			$text = ($link->find('p',0)->plaintext);
			
			$file = $savedir . '/' . urldecode( pathinfo( $img, PATHINFO_BASENAME ) );
			$path = '/files/carousel' . '/' . urldecode( pathinfo( $img, PATHINFO_BASENAME ) );
			if( file_exists( $file ) )
			{
				$name = pathinfo( $img, PATHINFO_FILENAME );
				$ext = pathinfo( $img, PATHINFO_EXTENSION );
				$pattern = '#^' . preg_quote( $name, '#' ) . '(\d+)\.' . $ext . '$#i';
				$handle = opendir( $savedir );
				
					while( ( $file_name = readdir( $handle ) ) !== false )
					{
		    			if( preg_match( $pattern, $file_name, $match ) )
		    			{
		        			$n = max( $n, $match[1] );
		    			}
					}

					closedir( $handle );				

					preg_replace( '/http:\/\//', '', $img );
					$parts = explode( '/', $img );
					$images = $parts[count($parts)-1];			
					$file = $savedir . DIRECTORY_SEPARATOR . $images;					
					$path = '/files/carousel' . DIRECTORY_SEPARATOR . $images;
			}
			$Carousel = new Carousel();
			$params = array();
			$params[] = 'Url = ' . $images;
			$Carousel = $Carousel->findItem( $params );		

			if ( count( $Carousel ) )
			{
				file_put_contents( $file, file_get_contents( $img ) );		

				$Carousel->Url = $images;
				$Carousel->Path = $path;
				$Carousel->PostedAt = time();
				$Carousel->Description = $text;				

				echo 'Картинка ' . $num . 'обновлена';							

				$num++;

				$Carousel->save();
			}							
		}												
	}										
}
