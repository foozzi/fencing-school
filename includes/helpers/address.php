<?

/**
 * The Address helper class.
 * 
 * @author Yarick.
 * @version 0.2
 */
class Address
{
	
	public $Street;
	public $Zip;
	public $City;
	public $Province;
	public $Country;
	
	public $Name;
	public $Email;
	public $Phone;
	public $Fax;
	public $Comment;
	
	public $Company;
	
	/**
	 * The class constructor.
	 * 
	 * @access public
	 * @param array $data The address data.
	 */
	public function __construct( array $data = array() )
	{
		foreach ( $data as $key => $value )
		{
			if ( property_exists( $this, $key ) )
			{
				$this->$key = $value;
			}
		}
		if ( isset( $data['FirstName'] ) && isset( $data['LastName'] ) )
		{
			$this->Name = $data['LastName']."\n".$data['FirstName'];
		}
	}
	
	public function getFirstName()
	{
		$arr = explode( "\n", $this->Name );
		return isset( $arr[1] ) ? $arr[1] : '';
	}
	
	public function getLastName()
	{
		$arr = explode( "\n", $this->Name );
		return isset( $arr[0] ) ? $arr[0] : '';
	}

	public function __toString()
	{
		$arr = array();
		foreach ( array('Street', 'City', 'Zip', 'Province', 'Country') as $key )
		{
			if ( $this->$key && $key == 'Country' )
			{
				if ( $this->$key != 'XX' )
				{
					$arr[] = Country::get( $this->$key );
				}
			}
			else if ( $this->$key )
			{
				$arr[] = $this->$key;
			}
		}
		return implode( ', ', $arr );
	}
	
}
