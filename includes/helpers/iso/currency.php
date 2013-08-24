<?
/**
 * Currency ISO class which contain all currency codes with their names in 
 * English.
 *
 * @author Yarick
 */

class ISO_Currency extends ISO_Table
{

	private static $objectInstance = null;
	private $assocData = array();

	public static function getInstance()
	{
		if ( self::$objectInstance === null )
		{
			self::$objectInstance = new ISO_Currency();
		}
		return self::$objectInstance;
	}

	public function getAssocData()
	{
		if ( !count( $this->assocData ) )
		{
			$ptr = array();
			foreach ( $this->getData() as $key => $item )
			{
				$this->assocData[ $key ] = $key.' - '.$item['name'];
			}
		}
		return $this->assocData;
	} 
	
}

