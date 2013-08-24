<?

include_once( LIBS_DIR . '/simple_html_dom.php' );

/**
 * The crawler (parser) base class.
 * 
 * @author Yarick.
 * @version 0.1
 */
abstract class Crawler
{
	
	const CACHE_TIMEOUT = 86400000; // 1000 days
	
	private $lastUrl = null;
	
	abstract protected function isCacheable();
	
	abstract protected function URL();

	protected function getCacheDir()
	{
		return Runtime::get('FILES_DIR').'/crawlers/'.get_class( $this );
	}
	
	private function getCacheFile( $url, $post = false, array $data = array() )
	{
		$post = $post ? 1 : 0;
		$data = serialize( $data );
		$arr = explode('/', str_replace( $this->URL(), '', $url ));
		$folder = preg_replace('/(\.|\?).*$/i', '', $arr[0]);
		if (!$folder)
		{
			$folder = 'index_page';
		}
		return $this->getCacheDir().'/'.$folder.'/'.$post.md5( $url.$data ).'.html';
	}

	protected function convertHTML( $html )
	{
		return $html;
	}
	
	public function getURL( $url, $post = false, array $data = array() )
	{
		$this->lastUrl = $url;
		
		$file = $this->getCacheFile( $url, $post, $data );
		if ( $this->isCacheable() )
		{
			if ( file_exists( $file ) )
			{
				if ( filemtime( $file ) > time() - self::CACHE_TIMEOUT )
				{
					return $this->convertHTML( file_get_contents( $file ) );
				}
				else
				{
					unlink( $file );
				}
			}
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, $post);
		if ( count( $data ) )
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		$result = curl_exec($ch);
		curl_close($ch);
		if ( $this->isCacheable() )
		{
			File::restore( $file );
			file_put_contents( $file, $result );
		}
		return $this->convertHTML( $result );
	}
	
	public function getLastUrl()
	{
		return $this->lastUrl;
	}
	
	public function getDom( $html, $post = false, array $data = array() )
	{
		if ( preg_match( '/^(http|https|ftp)/i', $html ) )
		{
			return str_get_html( $this->getURL( $html, $post, $data ) );
		}
		return str_get_html( $html );
	}
	
}
