<?

/**
 * The class File works with filesystem. It helps to detect files mimetype, 
 * extension, reads files in directory, etc.
 * 
 * @author Yarick
 * @version 0.2
 */
class File
{
	
	private static $uploadError = null;
	
	/**
	 * The function returns the extension of the filename.
	 * 
	 * @static
	 * @access public
	 * @param string $file The filename or path of the file.
	 * @param bool $lowerCase The flag of extension case. If TRUE 
	 * extensions will be represented in lower case.
	 * @return string The extension of the filename.
	 */
	public static function extension( $filename, $lowerCase = false )
	{
		$ext = substr( $filename, strrpos( $filename, '.' ) + 1 );
		return $lowerCase ? strtolower( $ext ) : $ext;
	}
	
	/**
	 * The function returns mimetype detected by file extension.
	 * 
	 * @static
	 * @access public
	 * @param string $filename The filename or path of the file.
	 * @return string The mimetype of the file.
	 */
	public static function mimetype( $filename )
	{
		$types = array(
			'afl'	=> 'video/animaflex',
			'aif'	=> 'audio/aiff',
			'aifc'	=> 'audio/aiff',
			'aiff'	=> 'audio/aiff',
			'art'	=> 'image/x-jg',
			'asf'	=> 'video/x-ms-asf',
			'asx'	=> 'video/x-ms-asf',
			'au'	=> 'audio/basic',
			'avi'	=> 'video/avi',
			'avs'	=> 'video/avs-video',
			'bm'	=> 'image/bmp',
			'bmp'	=> 'image/bmp',
			'dif'	=> 'video/x-dv',
			'dl'	=> 'video/dl',
			'doc'	=> 'application/msword',
			'docx'	=> 'application/msword',
			'dv'	=> 'video/x-dv',
			'dwg'	=> 'image/vnd.dwg',
			'dxf'	=> 'image/vnd.dwg',
			'fif'	=> 'image/fif',
			'fli'	=> 'video/fli',
			'flo'	=> 'image/florian',
			'flv'	=> 'video/flash',
			'fmf'	=> 'video/x-atomic3d-feature',
			'fpx'	=> 'image/vnd.fpx',
			'funk'	=> 'audio/make',
			'g3'	=> 'image/g3fax',
			'gif'	=> 'image/gif',
			'gl'	=> 'video/gl',
			'gsd'	=> 'audio/x-gsm',
			'gsm'	=> 'audio/x-gsm',
			'ico'	=> 'image/x-icon',
			'ief'	=> 'image/ief',
			'iefs'	=> 'image/ief',
			'isu'	=> 'video/x-isvideo',
			'it'	=> 'audio/it',
			'jam'	=> 'audio/x-jam',
			'jfif'	=> 'image/jpeg',
			'jfif-tbnl'	=> 'image/jpeg',
			'jpe'	=> 'image/jpeg',
			'jpeg'	=> 'image/jpeg',
			'jpg'	=> 'image/jpeg',
			'jps'	=> 'image/x-jps',
			'jut'	=> 'image/jutvision',
			'kar'	=> 'audio/midi',
			'la'	=> 'audio/nspaudio',
			'lam'	=> 'audio/x-liveaudio',
			'lma'	=> 'audio/nspaudio',
			'm1v'	=> 'video/mpeg',
			'm2a'	=> 'audio/mpeg',
			'm2v'	=> 'video/mpeg',
			'm3u'	=> 'audio/x-mpequrl',
			'mcf'	=> 'image/vasa',
			'mid'	=> 'audio/midi',
			'midi'	=> 'audio/midi',
			'mjf'	=> 'audio/x-vnd.audioexplosion.mjuicemediafile',
			'mjpg'	=> 'video/x-motion-jpeg',
			'mod'	=> 'audio/mod',
			'moov'	=> 'video/quicktime',
			'mov'	=> 'video/quicktime',
			'movie'	=> 'video/x-sgi-movie',
			'mp2'	=> 'audio/mpeg',
			'mp3'	=> 'audio/mpeg3',
			'mpa'	=> 'audio/mpeg',
			'mpe'	=> 'video/mpeg',
			'mpeg'	=> 'video/mpeg',
			'mpg'	=> 'audio/mpeg',
			'mpga'	=> 'audio/mpeg',
			'mv'	=> 'video/x-sgi-movie',
			'my'	=> 'audio/make',
			'nap'	=> 'image/naplps',
			'naplps'	=> 'image/naplps',
			'nif'	=> 'image/x-niff',
			'niff'	=> 'image/x-niff',
			'odt'	=> 'application/vnd.oasis.opendocument.text',
			'pbm'	=> 'image/x-portable-bitmap',
			'pct'	=> 'image/x-pict',
			'pcx'	=> 'image/x-pcx',
			'pdf'	=> 'application/pdf',
			'pfunk'	=> 'audio/make',
			'pgm'	=> 'image/x-portable-graymap',
			'pic'	=> 'image/pict',
			'pict'	=> 'image/pict',
			'pm'	=> 'image/x-xpixmap',
			'png'	=> 'image/png',
			'pnm'	=> 'image/x-portable-anymap',
			'ppm'	=> 'image/x-portable-pixmap',
			'qcp'	=> 'audio/vnd.qcelp',
			'qif'	=> 'image/x-quicktime',
			'qt'	=> 'video/quicktime',
			'qtc'	=> 'video/x-qtc',
			'qti'	=> 'image/x-quicktime',
			'qtif'	=> 'image/x-quicktime',
			'ra'	=> 'audio/x-pn-realaudio',
			'ram'	=> 'audio/x-pn-realaudio',
			'rar'	=> 'application/rar',
			'ras'	=> 'image/cmu-raster',
			'rast'	=> 'image/cmu-raster',
			'rf'	=> 'image/vnd.rn-realflash',
			'rgb'	=> 'image/x-rgb',
			'rm'	=> 'audio/x-pn-realaudio',
			'rmi'	=> 'audio/mid',
			'rmm'	=> 'audio/x-pn-realaudio',
			'rmp'	=> 'audio/x-pn-realaudio',
			'rp'	=> 'image/vnd.rn-realpix',
			'rpm'	=> 'audio/x-pn-realaudio-plugin',
			'rv'	=> 'video/vnd.rn-realvideo',
			's3m'	=> 'audio/s3m',
			'scm'	=> 'video/x-scm',
			'sid'	=> 'audio/x-psid',
			'snd'	=> 'audio/basic',
			'svf'	=> 'image/vnd.dwg',
			'tif'	=> 'image/tiff',
			'tiff'	=> 'image/tiff',
			'tsi'	=> 'audio/tsp-audio',
			'tsp'	=> 'audio/tsplayer',
			'turbot'	=> 'image/florian',
			'vdo'	=> 'video/vdo',
			'viv'	=> 'video/vivo',
			'vivo'	=> 'video/vivo',
			'voc'	=> 'audio/voc',
			'vos'	=> 'video/vosaic',
			'vox'	=> 'audio/voxware',
			'vqe'	=> 'audio/x-twinvq-plugin',
			'vqf'	=> 'audio/x-twinvq',
			'vql'	=> 'audio/x-twinvq-plugin',
			'wav'	=> 'audio/wav',
			'wbmp'	=> 'image/vnd.wap.wbmp',
			'xbm'	=> 'image/x-xbitmap',
			'xdr'	=> 'video/x-amt-demorun',
			'xif'	=> 'image/vnd.xiff',
			'xls'	=> 'application/msexcel',
			'xlsx'	=> 'application/msexcel',
			'xm'	=> 'audio/xm',
			'xpm'	=> 'image/x-xpixmap',
			'x-png'	=> 'image/png',
			'xsr'	=> 'video/x-amt-showrun',
			'xwd'	=> 'image/x-xwd',
			'zip'	=> 'application/zip',
		);
		$ext = self::extension( $filename, true );
		return isset( $types[ $ext ] ) ? $types[ $ext ] : 'application/unknown';
	}
	
	/**
	 * The function reads directory and returns files in array.
	 * 
	 * @static
	 * @access public
	 * @param $path The path of directory.
	 * @param bool $recursive The flag of recursive search or not.
	 * @param array $ignore The array of filenames which must not be in result.
	 * @param string $mask The pattern mask for search.
	 * @return array The array of fiels.
	 */
	public static function readDir( $path, $recursive = false, $ignore = null, $mask = null )
	{
		if ( !is_array( $ignore ) && $ignore !== null )
		{
			$ignore = array( $ignore );
		}
		if ( $ignore === null )
		{
			$ignore = array();
		}
		if ( !in_array( '.', $ignore ) )
		{
			$ignore[] = '.';
		}
		if ( !in_array( '..', $ignore ) )
		{
			$ignore[] = '..';
		}
		if ( !in_array( '.svn', $ignore ) )
		{
			$ignore[] = '.svn';
		}
		
		if ( $mask !== null )
		{
			$mask = strtr( preg_quote( $mask ), array(
				'\\*'	=> '.*',
				'\\?' 	=> '.{1}',
			) );
			$mask = '/^'.$mask.'$/i';
		}
		
		$result = array();
		
		$dh = opendir( $path );
		if ( $dh !== false )
		{
			while ( ( $file = readdir( $dh ) ) !== false )
			{
				if ( in_array( $file, $ignore ) )
				{
					continue;
				}
				if ( $mask === null || preg_match( $mask, $file ) )
				{
					$result[] = $path.'/'.$file;
				}
				if ( is_dir( $path.'/'.$file ) && $recursive )
				{
					$result = array_merge( $result, self::readDir( $path.'/'.$file, $recursive, $ignore ) );
				}
			}
			sort( $result );
			closedir( $dh );
		}
		
		return $result;
	}
	
	/**
	 * The function reads directory with classes.
	 * 
	 * @static
	 * @access public
	 * @param string $path The classes directory.
	 * @param string $prefix The class name prefix.
	 * @return array The classes.
	 */
	public static function readClasses( $path, $prefix = '' )
	{
		$classes = array();
		foreach ( File::readDir( $path, true, null, '*.php' ) as $file )
		{
			if ( is_dir( $file ) )
			{
				continue;
			}
			$size = filesize( $file );
			$text = file_get_contents( $file );
			$arr = explode( "\n", $text );
			$lines = count( $arr );
			$file = str_replace( $path.'/', '', $file );
			$name = $prefix.str_replace( '.php', '', String::toFirstCase( $file, '_', '/' ) );
			//Console::writeln( $name );
			if ( preg_match('/public function __construct\((.+)\)/i', str_replace( "\n", '', $text ), $res ) )
			{
				$break = false;
				foreach ( explode( ',', $res[1] ) as $arg )
				{
					if ( strlen( $arg ) > 1 && !strpos( $arg, '=' ) )
					{
						$classes[] = array( $name, $size, $lines, 'constructor arguments' );
						$break = true;
						break;
					}
				}
				if ( $break )
				{
					continue;
				}
			}
			if ( strpos( $text, 'private function __construct' ) )
			{
				//Console::writeln( '::singleton '.$name );
				$classes[] = array( $name, $size, $lines, 'signleton' );
			}
			else if ( strpos( $text, 'abstract class' ) )
			{
				//Console::writeln( '::abstract '.$name );
				$classes[] = array( $name, $size, $lines, 'abstract' );
			}
			else
			{
				$class = new $name();
				$classes[] = array( $class, $size, $lines );
				//Console::writeln( get_class( $class ) );
			}
		}
		return $classes;
	}
	
	/**
	 * The function returns filesize in human format, like 1 392,44M
	 * 
	 * @static
	 * @access public
	 * @param int $value The filesize.
	 * @return string The filesize in human format.
	 */
	public static function size( $value )
	{
		$params = array( 1024, 'K' );
		if ( $value > 1024 * 1024 * 1024 )
		{
			$params = array( 1024 * 1024 * 1024, 'G' );
		}
		else if ( $value > 1024 * 1024 )
		{
			$params = array( 1024 * 1024, 'M' );
		}
		return number_format( $value / $params[0], 2, ',', ' ' ).$params[1];
	}
	
	/**
	 * The function returns array of option from the string which are separated by commas.
	 * 
	 * @static
	 * @access protected
	 * @param string $string The option string with key=value pairs separated by commas.
	 * @return array The array of options.
	 */
	protected static function convertIniOptions( $string )
	{
		$result = array();
		$arr = explode( ',', $string );
		for ( $i = 0, $len = count( $arr ); $i < $len; $i++ )
		{
			$tmp = explode( '=', $arr[ $i ], 2 );
			if ( count( $tmp ) == 2 )
			{
				$result[ trim( $tmp[0] ) ] = trim( $tmp[1] );
			}
		}
		return $result;
	}
	
	/**
	 * The function loads ini file to array.
	 * 
	 * @static
	 * @access public
	 * @param string $path The path to ini file.
	 * @return array The array of converted data from ini file.
	 */
	public static function loadIniFile( $path )
	{
		$result = array();
		if ( !file_exists( $path ) )
		{
			return $result;
		}
		$file = file( $path );
		$block = '';
		for ( $i = 0, $len = count( $file ); $i < $len; $i++ )
		{
			$line = trim( $file[ $i ] );
			if ( !$line || substr( $line, 0, 1 ) == ';' || substr( $line, 0, 1 ) == '#' )
			{
				continue;
			}
			if ( substr( $line, 0, 1 ) == '[' && substr( $line, -1 ) == ']' )
			{
				$block = strtolower( substr( $line, 1, strlen( $line ) - 2 ) );
				$arr = explode( ':', $block, 2 );
				$config = array();
				if ( count( $arr ) == 2 )
				{
					$block = $arr[0];
					$config = self::convertIniOptions( $arr[1] );
				}
				$result[ $block ] = array(
					'config'	=> $config,
					'data'		=> array(),
				);
			}
			else if ( !$block )
			{
				continue;
			}
			else
			{
				$arr = explode( ':', $line, 2 );
				if ( !isset( $result[ $block ] ) )
				{
					continue;
				}
				$result[ $block ]['data'][ trim( $arr[0] ) ] = isset( $arr[1] ) ? trim( $arr[1] ) : trim( $arr[0] );
			}
		}
		return $result;
	}
	
	/**
	 * The function restores path of file (create folder if it does not exist)
	 * with current permissions.
	 * 
	 * @static
	 * @access public
	 * @param string $path The path to the file.
	 * @param int $perm The permissions code in octal format.
	 */
	public static function restore( $path, $perm = 0777 )
	{
		if ( !is_dir( dirname( $path ) ) )
		{
			return mkdir( dirname( $path ), $perm, true );
		}
		return true;
	}
	
	/**
	 * The function execute command depends on server OS.
	 * 
	 * @static
	 * @access public
	 * @param string $cmd The command to execute.
	 * @return array The array with output lines;
	 */
	public static function exec( $cmd )
	{
		$result = array();
		if ( substr( PHP_OS, 0, 3 ) == 'WIN' )
		{
			$result = explode( "\n", shell_exec( $cmd ) );
		}
		else
		{
			exec( $cmd, $result );
		}
		return $result;
	}
	
	/**
	 * The function checks if current file extension is allowed.
	 * 
	 * @static
	 * @access private
	 * @param array $info The info array.
	 * @param string $ext The file extension.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	private static function checkExtension( $info, $ext )
	{
		if ( isset( $info['deny'] ) && is_array( $info['deny'] ) )
		{
			if ( in_array( $ext, $info['deny'] ) )
			{
				self::$uploadError = 'Current extension in deny list: '.$ext.' in ('.implode( ', ', $info['deny'] ).')';
				return false;
			}
		}
		if ( isset( $info['allow'] ) && is_array( $info['allow'] ) )
		{
			if ( !in_array( $ext, $info['allow'] ) )
			{
				self::$uploadError = 'Current extension is not allowed: '.$ext.' not in ('.implode( ', ', $info['allow'] ).')';
				return false;
			}
		}
		return true;
	}
	
	/**
	 * The function sends file to output.
	 * 
	 * @static
	 * @access public
	 * @param string $file The path to file.
	 * @param string $name The name of file which will be sent to headers, if 
	 * empty then basename of $file will be taken.
	 */
	public static function output( $file, $name = null )
	{
		if ( !file_exists( $file ) )
		{
			return false;
		}
		if ( !$name )
		{
			$name = basename( $file );
		}
	    header( 'Content-Description: File Transfer' );
	    header( 'Expires: 0' );
	    header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	    header( 'Pragma: public' );
		header( 'Content-Type: '.self::mimetype( $file ) );
		header( 'Content-Disposition: attachment; filename="'.$name.'"' );
	    header( 'Content-Length: ' . filesize( $file ) );
		return readfile( $file );
	}
	
	/**
	 * The function uploads file to object.
	 * 
	 * @static
	 * @acces public
	 * @param object $Object The object.
	 * @param mixed $file The array of File data or path to file on server.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function upload( Object $Object, $file )
	{
		self::$uploadError = null;
		$info = $Object->getUploadFileInfo();
		if ( !is_array( $info ) || !$Object->Id )
		{
			self::$uploadError = $Object->Id ? 'Upload file info is not defined in model' : 'Object Id is not defined';
			return false;
		}
		$name = is_array( $file ) ? $file['name'] : basename( $file );
		$ext = self::extension( $name, true );
		if ( !self::checkExtension( $info, $ext ) )
		{
			return false;
		}
		if ( property_exists( $Object, 'Filename' ) )
		{
			$Object->Filename = $name;
		}
		if ( property_exists( $Object, 'Extension' ) )
		{
			$Object->Extension = $ext;
		}

		$ok = false;
		self::restore( self::path( $Object ) );
		if ( is_array( $file ) )
		{
			$ok = move_uploaded_file( $file['tmp_name'], self::path( $Object ) );
		}
		else if ( preg_match( '/^(http|https|ftp)\:\/\//i', $file ) )
		{
			$data = file_get_contents( $file );
			$ok = file_put_contents( self::path( $Object ) , $data ) > 0;
		}
		else
		{
			$ok = rename( $file, self::path( $Object ) );
		}
		if ( !$ok )
		{
			self::$uploadError = 'Could not move/copy uploaded file';
			return false;
		}
		
		if ( isset( $info['sizes'] ) && is_array( $info['sizes'] ) )
		{
			foreach ( $info['sizes'] as $index => $size )
			{
				$crop = $gravity = null;
				if ( isset( $info['crop'][ $index ] ) && $info['crop'][ $index ] )
				{
					$arr = getimagesize( self::path( $Object ) );
					$origW = $arr[0];
					$origH = $arr[1];
					$arr = explode( 'x', $size );
					$w = $arr[0];
					$h = $arr[1];
					$scale = $origW / $w < $origH / $h ? $origW / $w : $origH / $h;
					$resW = intval( $origW / $scale );
					$resH = intval( $origH / $scale );
					$crop = $size;//.'+'.intval( ( $resW - $w ) / 2 ).'+'.intval( ( $resH - $h ) / 2 );
					$gravity = $info['crop'][ $index ];
					$size = $resW.'x'.$resH;
				}
				self::restore( self::path( $Object, $index ) );
				self::resizeImage( self::path( $Object ), $size, self::path( $Object, $index ), 
					isset( $info['quality'][ $index ] ) ? $info['quality'][ $index ] : null );
				if ( $crop !== null )
				{
					self::cropImage( self::path( $Object, $index ), $crop, $gravity );
				}
			}
		}
		if ( property_exists( $Object, 'Filesize' ) )
		{
			$Object->Filesize = filesize( self::path( $Object ) );
		}
		if ( property_exists( $Object, 'IsFile' ) )
		{
			$Object->IsFile = 1;
		}
		if ( isset( $info['dropOrig'] ) && (bool)$info['dropOrig'] )
		{
			@unlink( self::path( $Object ) );
		}
		return true;
	}
	
	/**
	 * The function returns last upload error message.
	 * 
	 * @static
	 * @access public
	 * @return string The error message.
	 */
	public static function getUploadError()
	{
		return self::$uploadError;
	}
	
	/**
	 * The function returns allowed and denied extensions for current Object uploads.
	 * 
	 * @static
	 * @access public
	 * @param object $Object The Object.
	 * @return string The allowed extensions.
	 */
	public static function getAllowedExt( Object $Object )
	{
		$info = $Object->getUploadFileInfo();
		$allow = $deny = '';
		if ( isset( $info['deny'] ) && is_array( $info['deny'] ) )
		{
			$deny = implode( ', ', $info['deny'] );
		}
		if ( isset( $info['allow'] ) && is_array( $info['allow'] ) )
		{
			$allow = implode( ', ', $info['allow'] );
		}
		if ( $allow && $deny )
		{
			return $allow.' except '.$deny;
		}
		else if ( $deny )
		{
			return '* except '.$deny;
		}
		return $allow;
	}
	
	/**
	 * The function attaches file to current Object by index.
	 * 
	 * @static
	 * @access public
	 * @param object $Object The object.
	 * @param mixed $file The array of File data or path to file on server.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function attach( Object $Object, $file, $index = null )
	{
		$info = $Object->getUploadFileInfo();
		if ( !is_array( $info ) || !$Object->Id )
		{
			self::$uploadError = $Object->Id ? 'Upload file info is not defined in model' : 'Object Id is not defined';
			return false;
		}
		self::restore( self::path( $Object, $index ) );
		if ( $index === null )
		{
			$index = 'orig';
		}
		$name = is_array( $file ) ? $file['name'] : basename( $file );
		$source = is_array( $file ) ? $file['tmp_name'] : $file;
		$ext = self::extension( $name, true );
		
		if ( !self::checkExtension( $info, $ext ) )
		{
			return false;
		}

		if ( $index === 'orig' )
		{
			if ( property_exists( $Object, 'Filename' ) )
			{
				$Object->Filename = $name;
			}
			if ( property_exists( $Object, 'Extension' ) )
			{
				$Object->Extension = $ext;
			}
			if ( property_exists( $Object, 'Filesize' ) )
			{
				$Object->Filesize = filesize( $source );
			}
		}

		if ( is_array( $file ) )
		{
			$ok = move_uploaded_file( $file['tmp_name'], self::path( $Object, $index ) );
		}
		else
		{
			$ok = rename( $file, self::path( $Object, $index ) );
		}
		if ( !$ok )
		{
			self::$uploadError = 'Could not move/copy uploaded file';
			return false;
		}
		if ( $index === 'orig' )
		{
			if ( property_exists( $Object, 'IsFile' ) )
			{
				$Object->IsFile = 1;
			}
		}
		return true;
	}
	
	/**
	 * The function detaches Object files.
	 * 
	 * @static
	 * @access public
	 * @param object $Object The object.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function detach( Object $Object )
	{
		$info = $Object->getUploadFileInfo();
		if ( !is_array( $info ) || !$Object->Id )
		{
			return false;
		}
		
		@unlink( self::path( $Object ) );
		if ( isset( $info['sizes'] ) && is_array( $info['sizes'] ) )
		{
			foreach ( $info['sizes'] as $index => $size )
			{
				@unlink( self::path( $Object, $index ) );
			}
		}
		if ( property_exists( $Object, 'Filename' ) )
		{
			$Object->Filename = '';
		}
		if ( property_exists( $Object, 'Filesize' ) )
		{
			$Object->Filesize = 0;
		}
		if ( property_exists( $Object, 'IsFile' ) )
		{
			$Object->IsFile = 0;
		}
		return true;
	}
	
	/**
	 * The function resizes an image.
	 * 
	 * @static
	 * @access private
	 * @param string $source The source file name.
	 * @param string $size The size.
	 * @param string $target The target file name.
	 * @param int $quality The quality for JPG images.
	 */
	private static function resizeImage( $source, $size, $target = null, $quality = null )
	{
		if ( !$target )
		{
			$target = $source;
		}
		$cmd = 'convert "'.$source.'" -resize "'.$size.'>" '.( $quality ? ' -quality "'.$quality.'"' : '' ).' "'.$target.'"';
		exec( $cmd );
	}
	
	/**
	 * The function resizes an image.
	 * 
	 * @static
	 * @access private
	 * @param string $source The source file name.
	 * @param string $crop The size.
	 * @param string $gravity The position.
	 * @param string $target The target file name.
	 * @param int $quality The quality for JPG images.
	 */
	private static function cropImage( $source, $crop, $gravity, $target = null, $quality = null )
	{
		if ( !$target )
		{
			$target = $source;
		}
		$cmd = 'convert "'.$source.'" -gravity '.$gravity.' -crop "'.$crop.'+0+0" +repage '.( $quality ? ' -quality "'.$quality.'"' : '' ).' "'.$target.'"';
		exec( $cmd );
	}
	
	/**
	 * The function returns file path for object by index.
	 * 
	 * @static
	 * @access public
	 * @param object Object The object.
	 * @param int $index The index of file.
	 * @return string The file path.
	 */
	public static function path( Object $Object, $index = null )
	{
		if ( !$Object->Id )
		{
			return null;
		}
		$info = $Object->getUploadFileInfo();
		$limit = isset( $info['folderLimit'] ) ? $info['folderLimit'] : 1000;
		$folder = isset( $info['folder'] ) ? $info['folder'] : get_class( $Object );
		$folderID = isset( $info['folderFormat'] ) ? sprintf( $info['folderFormat'], floor( $Object->Id / $limit ) ) : null;
		if ( !isset( $info['extension'] ) )
		{
			$info['extension'] = 'jpg';
		}
		$ext = property_exists( $Object, 'Filename' ) ? self::extension( $Object->Filename ) : $info['extension'];
		if ( $index === null )
		{
			$index = 'orig';
		}
		if ( $info['extension'] && $index !== 'orig' )
		{
			$ext = $info['extension'];
		}
		$ext = strtolower( $ext );		
		return Runtime::get('FILES_DIR').'/'.$folder.'/'.$index.'/'.( $folderID === null ? '' : $folderID.'/' ).$Object->Id.'.'.$ext;
	}
	
	/**
	 * The function returns file url for object by index.
	 * 
	 * @static
	 * @access public
	 * @param object $Object The object.
	 * @param int $index The index of file.
	 * @return string The file url.
	 */
	public static function url( Object $Object, $index = null )
	{
		if ( !$Object->Id )
		{
			return null;
		}
		$info = $Object->getUploadFileInfo();		
		$limit = isset( $info['folderLimit'] ) ? $info['folderLimit'] : 1000;
		$folder = isset( $info['folder'] ) ? $info['folder'] : get_class( $Object );
                
                //$folder = str_replace('Banner', 'Planer', $folder);
                
		$folderID = isset( $info['folderFormat'] ) ? sprintf( $info['folderFormat'], floor( $Object->Id / $limit ) ) : null;
		if ( !isset( $info['extension'] ) )
		{
			$info['extension'] = 'jpg';
		}
		$ext = property_exists( $Object, 'Filename' ) ? self::extension( $Object->Filename ) : $info['extension'];
		if ( $index === null )
		{
			$index = 'orig';
		}
		if ( $info['extension'] && $index !== 'orig' )
		{
			$ext = $info['extension'];
		}
		$ext = strtolower( $ext );
		if ( !empty( $info['urlFormat'] ) )
		{
			return $Object->getFileUrl( $folder, $folderID, $index, $ext );
		}
		return '/files/'.$folder.'/'.$index.'/'.( $folderID === null ? '' : $folderID.'/' ).$Object->Id.'.'.$ext;
	}
	
	/**
	 * The function returns filesize.
	 * 
	 * @access public
	 * @param int $size The file size.
	 * @param string $factors The string with factors separated by comma.
	 * @return string The filesize.
	 */
	public static function getFilesize( $size, $factors = ' b, Kb, Mb, Gb' )
	{
		$factors = explode( ',', $factors );
		if ( $size / 1024 / 1024 / 1024 > 10 )
		{
			return sprintf( '%d'.$factors[3], $size / 1024 / 1024 / 1024 );
		}
		else if ( $size / 1024 / 1024 / 1024 > 1 )
		{
			return sprintf( '%.01f'.$factors[3], $size / 1024 / 1024 / 1024 );
		}
		else if ( $size / 1024 / 1024 > 10 )
		{
			return sprintf( '%d'.$factors[2], $size / 1024 / 1024 );
		}
		else if ( $size / 1024 / 1024 > 1 )
		{
			return sprintf( '%.01f'.$factors[2], $size / 1024 / 1024 );
		}
		else if ( $size / 1024 > 10 )
		{
			return sprintf( '%d'.$factors[1], $size / 1024 );
		}
		else if ( $size / 1024 > 1 )
		{
			return sprintf( '%.01f'.$factors[1], $size / 1024 );
		}
		return sprintf( '%d'.$factors[0], $size );
	}
	
	/**
	 * The function converts multiple file array to single for uploading.
	 * 
	 * @static
	 * @access public
	 * @param array $file The posted file array.
	 * @param int $index The index key.
	 */
	public static function convertMultiple( array $file, $index )
	{
		return array(
			'name'		=> $file['name'][ $index ],
			'type'		=> $file['type'][ $index ],
			'tmp_name'	=> $file['tmp_name'][ $index ],
			'error'		=> $file['error'][ $index ],
			'size'		=> $file['size'][ $index ],
		);		
	}
	
}
