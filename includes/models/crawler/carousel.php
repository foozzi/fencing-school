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
		foreach ( $dom->find('div.slide div.slide-inner a.fpss_img span span span img') as $link )
		{					
			$file = $savedir . '/' . urldecode( pathinfo( $link->src, PATHINFO_BASENAME ) );
			if( file_exists( $file ) )
			{
				$name = pathinfo( $link->src, PATHINFO_FILENAME );
				$ext = pathinfo( $link->src, PATHINFO_EXTENSION );
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

					preg_replace( '/http:\/\//', '', $link->src );
					$parts = explode( '/', $link->src );
					$images = $parts[count($parts)-1];			
					$file = $savedir . DIRECTORY_SEPARATOR . $images;					
					$path = '/files/carousel' . DIRECTORY_SEPARATOR . $images;
			}
			file_put_contents( $file, file_get_contents( $link->src ) );							
			
			$Carousel = new Carousel();
			$Carousel->Path = $file;
			$Carousel->Url = $path;

			$Carousel->PostedAt = time();
			$Carousel->save();

			printf('Обновлено');			
						
		}					
	}

}