<?

/** 
 * Tyres Weight ISO class.
 *
 * @author Yarick
 * @version 0.1
 */
class ISO_TyreWeight extends ISO_Table
{

	private static $objectInstance = null;
	private $assocData = array();

	public static function getInstance()
	{
		if ( self::$objectInstance === null )
		{
			self::$objectInstance = new self();
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
				$this->assocData[ $key ] = $item['name'];
			}
		}
		return $this->assocData;
	} 	
}
