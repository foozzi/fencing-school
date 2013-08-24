<?
/**
 * Language ISO 3166 class for detecting language by Accept Language value
 *
 * @author Yarick
 */

class ISO_Language extends ISO_Table
{

	private static $objectInstance = null;

	private $shortData = array();

	public static function getInstance()
	{
		if ( self::$objectInstance === null )
		{
			self::$objectInstance = new ISO_Language();
		}
		return self::$objectInstance;
	}

	/**
	 * Override constructor. Prepare short codes after data is loaded.
	 */
	public function  __construct()
	{
		parent::__construct();
		$this->prepareShort();
	}
	
	/**
	 * Prepare short codes for Languages
	 * 
	 * @access protected
	 * @return bool
	 */
	protected function prepareShort()
	{
		if ( count( $this->shortData ) )
		{
			return false;
		}
		$ptr = array();
		foreach ( $this->getData() as $code => $item )
		{
			$short = substr( $code, 0, 2 );
			$arr = explode( '(', $item['name'], 2 );
			$item['short'] = $short;
			$item['name'] = trim( $arr[0] );
			
			$ptr[ $item['name'] ] = $item;
		}
		ksort( $ptr );
		foreach ( $ptr as $item )
		{
			if ( !isset( $this->shortData[ $item['short'] ] ) )
			{
				$this->shortData[ $item['short'] ] = $item['name'];
			}
		}
		return true;
	}

	/**
	 * Returns array of items with short codes.
	 * 
	 * @access public
	 * @return array
	 */
	public function getShortData()
	{
		return $this->shortData;
	}
	
	public function getAssocData( $short = false )
	{
		if ( $short )
		{
			return $this->getShortData();
		}
		$result = array();
		foreach ( $this->getData() as $code => $item )
		{
			$result[ $code ] = $item['name'];
		}
		return $result;
	}

}
