<?

/**
 * The Email helper class.
 * 
 * @abstract
 * @author Yarick.
 * @version 0.2
 */
abstract class Email
{
	
	/**
	 * The function returns sender name and email.
	 * 
	 * @abstract
	 * @access protected
	 * @return string The sender info.
	 */
	abstract protected function getFrom();

	/**
	 * The function returns receiver name and email.
	 * 
	 * @abstract
	 * @access protected
	 * @return string The receiver info.
	 */
	abstract protected function getTo();

	/**
	 * The function returns email subject.
	 * 
	 * @abstract
	 * @access protected
	 * @return string The subject.
	 */
	abstract protected function getSubject();

	/**
	 * The function returns email body message.
	 * 
	 * @abstract
	 * @access protected
	 * @return string The body message.
	 */
	abstract protected function getMessage();
	

	private $object = null;
	private $layoutDir = null;
	private $files = array();
	private $boundary = null;
	
	/**
	 * The class constructor.
	 * 
	 * @access public
	 * @param object $Object The Object.
	 */
	public function __construct( Object $Object = null )
	{
		Locale::load('Email');
		$this->setObject( $Object );
		$dir = Runtime::get('TEMPLATE_DIR').'/email/'.Locale::get();
		if ( is_dir( $dir ) )
		{
			$this->layoutDir = $dir;
		}
		else
		{
			$this->layoutDir = Runtime::get('TEMPLATE_DIR').'/email';
		}
	}
	
	/**
	 * The function sets current email Object.
	 * 
	 * @access protected
	 * @param object $Object The Object to set.
	 */
	protected function setObject( Object $Object = null )
	{
		$this->object = $Object;
	}
	
	/**
	 * The function returns current email Object.
	 * 
	 * @access protected
	 * @return object The Object.
	 */
	protected function getObject()
	{
		return $this->object;
	}
	
	/**
	 * The function returns email transport value.
	 * 
	 * @access private
	 * @return string The transport value.
	 */
	private function getTransport()
	{
		return Config::get('email/transport', 'mail');
	}
	
	/**
	 * The function returns multipart boundary.
	 * 
	 * @access private
	 * @return string The boundary.
	 */
	private function getBoundary()
	{
		if ( $this->boundary === null )
		{
			$this->boundary = md5( time().microtime().rand(1, 100000) );
		}
		return $this->boundary;
	}
	
	/**
	 * The function returns email headers.
	 * 
	 * @access protected
	 * @return string The headers.
	 */
	protected function getHeaders( $extraHeaders = false )
	{
		$headers = "MIME-Version: 1.0\n"
			."Return-Path: ".self::extractEmail( $this->getFrom() )."\n"
			."From: ".self::getUTF8Email( $this->getFrom() )."\n"
			."Reply-To: ".self::getUTF8Email( $this->getFrom() )."\n";
		if ( $extraHeaders )
		{
			$headers .= "To: ".self::getUTF8Email( $this->getTo() )."\n"
				."Subject: ".self::getUTF8Subject( $this->getSubject() )."\n";
		}
		if ( count( $this->files ) )
		{
			$headers .= "Content-Type: multipart/mixed;\n boundary=\"".$this->getBoundary()."\"\n\n"
				."This is a multi-part message in MIME format.\n";
		}
		else
		{
			$headers .= "Content-Type: text/html; charset=UTF-8\n";
		}
		return $headers;
	}
	
	/**
	 * The function returns email body.
	 * 
	 * @access protected
	 * @return string The body.
	 */
	protected function getBody()
	{
		$body = '';
		if ( count( $this->files ) )
		{
			$body = "--".$this->getBoundary()."\n";
			$body .= "Content-Type: text/html; charset=UTF-8\n"
				."Content-Transfer-Encoding: 8bit\n\n"
				.$this->getMessage()."\n";
			foreach ( $this->files as $item )
			{
				$mime = $item[2] ? $item[2] : File::mimetype( $item[0] );
				$name = $item[1] ? $item[1] : basename( $item[0] );
				$name = self::getUTF8Subject( $name );
				$body .= "--".$this->getBoundary()."\n"
					."Content-Type: $mime; name=\"$name\"\n";
				if ( !$item[3] )
				{
					$body .= "Content-Disposition: attachment;\n filename=\"".$name."\"; size=".filesize( $item[0] ).";\n";
				}
				$body .= "Content-Transfer-Encoding: base64\n\n"
					.chunk_split( base64_encode( file_get_contents( $item[0] ) ) )."\n\n";
			}
			$body .= "--".$this->getBoundary()."--\n\n";
		}
		else
		{
			$body = $this->getMessage();
		}
		$body .= "\n";
		return $body;
	}
	
	/**
	 * The function attaches file to email.
	 * 
	 * @access public
	 * @param string $file The file path.
	 * @param string $name The file name.
	 * @param string $mimetype The mime type.
	 * @param string $embedAs The file name for embed images.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function attach( $file, $name = null, $mimetype = null, $embedAs = null )
	{
		if ( file_exists( $file ) )
		{
			$this->files[] = array( $file, $name, $mimetype, $embedAs );
			return true;
		}
		return false;
	}
	
	/**
	 * The function returns file path for saving email.
	 * 
	 * @access private
	 * @return string The file path.
	 */
	private function getTempFile()
	{
		$tmp = sys_get_temp_dir().'/'.get_class( $this ).'-'.date('YmdHis');
		$file = $tmp.'.eml';
		$i = 0;
		while ( file_exists( $file ) )
		{
			$file = sprintf( '%s-%02d.eml', $tmp, ++$i );
		}
		return $file;
	}
	
	/**
	 * The function returns email source code.
	 * 
	 * @access public
	 * @return string The email code.
	 */
	public function getEmailCode()
	{
		return $this->getHeaders(true)."\n".$this->getBody();
	}
	
	/**
	 * The function sends Email.
	 * 
	 * @access public
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function send()
	{
		$result = false;
		$abs = URL::absolute();
		URL::absolute(true);
		switch ( $this->getTransport() )
		{
			case 'file':
				$file = $this->getTempFile();
				$result = file_put_contents( $file, $this->getEmailCode() ) > 0;
				break;
				
			case 'mail':
				$result = mail( $this->getTo(), $this->getSubject(), $this->getBody(), $this->getHeaders() );
				break;
		}
		URL::absolute( $abs );
		return $result;
	}
	
	/**
	 * The function returns layout directory.
	 * 
	 * @access protected
	 * @return string The layout directory.
	 */
	protected function getLayoutDir()
	{
		return $this->layoutDir;
	}
	
	/**
	 * The function returns result of rendered layout.
	 * 
	 * @access protected
	 * @param string $layout The layout file name.
	 * @return string The HTML code.
	 */
	protected function includeLayout( $layout )
	{
		ob_start();
		include( $this->getLayoutDir().'/'.$layout );
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}
	
	/**
	 * The function extract email from address string.
	 * 
	 * @static
	 * @access public
	 * @param string $string The string.
	 * @return string The email.
	 */
	public static function extractEmail( $string )
	{
		if ( preg_match( '/<(.+?)>/i', $string, $res ) )
		{
			return $res[1];
		}
		return $string;
	}

	/**
	 * The function returns UTF8 encoded subject.
	 * 
	 * @static
	 * @access protected
	 * @param string $subject The subject.
	 * @return string The encoded subject.
	 */
	protected static function getUTF8Subject( $subject )
	{
		return '=?UTF-8?B?'.trim(base64_encode($subject), '=').'?=';
	}
	
	/**
	 * The function returns UTF8 encoded email.
	 * 
	 * @static
	 * @access protected
	 * @param string $subject The subject.
	 * @return string The encoded subject.
	 */
	protected static function getUTF8Email( $email )
	{
		if ( preg_match( '/^(.+?)<(.+?)>$/i', $email, $res ) )
		{
			return self::getUTF8Subject( trim( $res[1] ) ).' <'.$res[2].'>';
		}
		return $email;
	}
	
}
