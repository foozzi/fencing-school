<?

/** 
 * Class container of ISO data like Country table, Currency table
 * @author Yarick
 */

class ISO_Table
{

	private $data = array();
	private static $locale = null;

	/**
	 * Created new Instance of class ISO_Table or inherited classes and load data from CSV file.
	 */
	public function  __construct()
	{
		$this->load( $this->getCSVFile() );
	}

	/**
	 * Returns Filepath to CSV data
	 * 
	 * @return string Filepath to CSV data
	 */
	protected function getCSVFile()
	{
		$file = dirname( __FILE__ ).'/'.implode( '.', explode( '_', strtolower( get_class( $this ) ) ) );
		$ext = '';
		if ( self::$locale )
		{
			$ext .= '.'.self::$locale;
		}
		$ext .= '.csv';
		if ( !file_exists( $file.$ext ) )
		{
			$ext = '.csv';
		}
		return $file.$ext;
	}

	/**
	 * Returns true if element exists with current code otherway returns false
	 *
	 * @param string $code
	 * @return bool
	 */
	public function has( $code )
	{
		return array_key_exists( $code, $this->data );
	}

	/**
	 * Sets data by code to container of object
	 * 
	 * @param string $code
	 * @param array $data
	 */
	protected function set( $code, $data )
	{
		$this->data[ $code ] = $data;
	}

	/**
	 * Returns value of data by code and key
	 * 
	 * @param string $code
	 * @param string $key
	 * @return string Value of data by code and key
	 */
	public function get( $code, $key = 'name' )
	{
		return isset( $this->data[ $code ][ $key ] ) ? $this->data[ $code ][ $key ] : null;
	}

	/**
	 * Returns object data in array.
	 * 
	 * @access public
	 * @param array $columns The columns which must be returned.
	 * @return array The array of object data.
	 */
	public function getData( $columns = null )
	{
		if ( $columns === null || !is_array( $columns ) )
		{
			return $this->data;
		}
		$result = array();
		foreach ( $this->data as $code => $item )
		{
			$arr = array();
			foreach ( $item as $key => $value )
			{
				if ( in_array( $key, $columns ) )
				{
					$arr[ $key ] = $value;
				}
			}
			$result[ $code ] = $arr;
		}
		return $result;
	}

	/**
	 * Load file data to object container
	 * 
	 * @param string $file
	 * @return bool
	 */
	protected function load( $file )
	{
		if ( !file_exists( $file ) )
		{
			return false;
		}
		$fp = fopen( $file, 'r' );
		if ( $fp )
		{
			$this->data = array();
			$keys = array();
			$ptr = array();
			$i = 0;
			while ( ( $line = fgetcsv( $fp, 4096, ';' ) ) !== false )
			{
				if ( $i++ == 0 )
				{
					$j = 0;
					foreach ( $line as $item )
					{
						$keys[ strtolower( $item ) ] = $j;
						$ptr[ $j++ ] = strtolower( $item );
					}
					if ( !isset( $keys['code'] ) || !isset( $keys['name'] ) )
					{
						break;
					}
					continue;
				}
				if ( !isset( $line[ $keys['code'] ] ) || trim( $line[ $keys['code'] ] ) == '' )
				{
					continue;
				}
				$data = array();
				for ( $j = 0, $len = count( $line ); $j < $len; $j++ )
				{
					if ( isset( $ptr[ $j ] ) )
					{
						$data[ $ptr[ $j ] ] = $line[ $j ];
					}
				}
				$this->set( $line[ $keys['code'] ], $data );
			}
			fclose( $fp );
			if ( $i < 2 )
			{
				return false;
			}
		}
		return true;
	}
	
	/**
	 * The function exports ISO_Table data to external file in JSON format.
	 * 
	 * @static
	 * @access public
	 * @param string $filename The path to filename.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function exportJSON( $filename, $data )
	{
		return file_put_contents( $filename, 
			strtr( json_encode( $data ), array( '},' => "},\n" ) ) ) > 0;
	}
	
	/**
	 * The function exports ISO_Table data to external file in PHP format.
	 * 
	 * @static
	 * @access public
	 * @param string $filename The path to filename.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function exportPHP( $filename, $data )
	{
		$output = "<?php\nreturn array(\n";
		foreach ( $data as $id => $item )
		{
			$output .= "\t'".addslashes( $id )."' => ";
			if ( is_array( $item ) )
			{
				$output .= "array(\n";
				foreach ( $item as $key => $value )
				{
					$output .= "\t\t'".addslashes( $key )."' => '".addslashes( $value )."',\n";
				}
				$output .= ')';
			}
			else
			{
				$output .= "'".addslashes( $item )."'";
			}
			$output .= ",\n";
		}
		$output .= "\n);\n?>";
		return file_put_contents( $filename, $output ) > 0;
	}
	
	/**
	 * The function exports ISO_Table data to external file in XML format.
	 * 
	 * @static
	 * @access public
	 * @param string $filename The path to filename.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function exportXML( $filename, $data )
	{
		$output = "<?xml version=\"1.0\" ?>\n<list>\n";
		foreach ( $data as $id => $item )
		{
			$output .= "\t<item code=\"".htmlspecialchars( $id )."\">";
			if ( is_array( $item ) )
			{
				$output .= "\n";
				foreach ( $item as $key => $value )
				{
					$output .= "\t\t<".htmlspecialchars( $key ).'>'
						.htmlspecialchars( $value )
						.'</'.htmlspecialchars( $key ).'>'."\n";
				}
				$output .= "\t";
			}
			else
			{
				$output .= htmlspecialchars( $item );
			}
			$output .= "</item>\n";
		}
		$output .= "</list>";
		return file_put_contents( $filename, $output ) > 0;
	}
	
	/**
	 * The function exports ISO_Table data to external file in TXT format.
	 * 
	 * @static
	 * @access public
	 * @param string $filename The path to filename.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function exportTXT( $filename, $data )
	{
		$plain = false;
		$max = array();
		foreach ( $data as $id => $item )
		{
			if ( is_array( $item ) )
			{
				foreach ( $item as $key => $value )
				{
					if ( !isset( $max[ $key ] ) )
					{
						$max[ $key ] = 0;
					}
					$max[ $key ] = max( $max[ $key ], mb_strlen( $value ) );
				}
			}
			else
			{
				$plain = true;
			}
		}
		$output = '';
		foreach ( $data as $id => $item )
		{
			if ( $plain )
			{
				$output .= $item."\n";
			}
			else
			{
				foreach ( $item as $key => $value )
				{
					$output .= sprintf( "%-' ".( $max[ $key ] + 2 )."s", $value );
				}
				$output .= "\n";
			}
		}
		return file_put_contents( $filename, $output ) > 0;
	}
	
	/**
	 * The function exports ISO_Table data to external file in CSV format.
	 * 
	 * @static
	 * @access public
	 * @param string $filename The path to filename.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function exportCSV( $filename, $data )
	{
		$fp = fopen( $filename, 'w' );
		if ( $fp )
		{
			foreach ( $data as $code => $item )
			{
				fputcsv( $fp, $item, ';' );
			}
			fclose( $fp );
			return true;
		}
		return false;
	}

	/**
	 * The function exports ISO_Table data to external file.
	 * Supported formats: json, php, xml, csv.
	 * 
	 * @access public
	 * @param string $filename The path to filename.
	 * @param array $columns The columns for export.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public function export( $filename, $columns = null )
	{
		$data = $this->getData( $columns );
		$ext = substr( $filename, strrpos( $filename, '.' ) + 1 );
		switch ( strtolower( $ext ) )
		{
			case 'js':
			case 'json':
				return self::exportJSON( $filename, $data );
				
			case 'php':
				return self::exportPHP( $filename, $data );
			
			case 'xml':
				return self::exportXML( $filename, $data );
				
			case 'txt':
				return self::exportTXT( $filename, $data );
				
			case 'csv':
			default:
				return self::exportCSV( $filename, $data );
		}
	}
	
	/**
	 * The function sets current locale for all ISO tables.
	 *
	 * @static
	 * @access public
	 * @param string $locale The locale.
	 */
	public static function setLocale( $locale = null )
	{
		self::$locale = $locale;
	}

	/**
	 * Prints an object
	 * 
	 * @return string 
	 */
	public function  __toString()
	{
		return var_export( $this->data, true );
	}

}