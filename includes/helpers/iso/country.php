<?

/** 
 * Country ISO class which contain all country codes with their names in English.
 *
 * @author Yarick
 * @version 0.1
 */
class ISO_Country extends ISO_Table
{

	private static $objectInstance = null;
	private $assocData = array();

	public static function getInstance()
	{
		if ( self::$objectInstance === null )
		{
			self::$objectInstance = new ISO_Country();
		}
		return self::$objectInstance;
	}
	
	/**
	 * @see ISO_Country::getAssocData()
	 * 
	 * @static
	 */
	public static function data( $is_zip = null )
	{
		return ISO_Country::getInstance()->getAssocData( $is_zip );
	} 
	
	/**
	 * The function returns all countries in associate array by codes.
	 * 
	 * @access public
	 * @param bool|null $zip The filter by zip availability in country.
	 * @return array The array of countries. 
	 */
	public function getAssocData( $is_zip = null )
	{
		if ( !count( $this->assocData ) )
		{
			$ptr = array();
			foreach ( $this->getData() as $key => $item )
			{
				$ptr[ $item['name'] ] = $key;
			}
			ksort( $ptr );
			foreach ( $ptr as $name => $key )
			{
				if ( $is_zip !== null && $is_zip != $this->get( $key, 'is_zip' ) )
				{
					continue;
				}
				$this->assocData[ $key ] = $name;
			}
		}
		return $this->assocData;
	} 
	
	/**
	 * @see ISO_Table::get()
	 */
	public function get( $code, $key = 'name' )
	{
		return parent::get( strtoupper( $code ), $key );
	}
	
}
