<?

/*

{INSTALL:SQL{
create table contacts(
	Id int not null auto_increment,
	City varchar(100) not null,
	Label varchar(100) not null,
	Description varchar(200) not null,
	Email varchar(100) not null,
	

	Position int not null,

	primary key (Id),
	index (Position)
) engine = MyISAM;

}}
*/

/**
 * The Contact model class.
 * 
 * @author Slava.
 * @version 0.1
 */
class Contact extends Object
{

	public $Id;
	public $City;
	public $Label;
	public $Description ;
	public $Email;
	public $Position;
	

	/**
	 * @see parent::getPrimary()
	 */
	protected function getPrimary()
	{
		return array('Id');
	}
	
	/**
	 * @see parent::getTableName()
	 */
	protected function getTableName()
	{
		return 'contacts';
	}
	
	/**
	 * @see parent::getTestRules()
	 */
	public function getTestRules()
	{
		return array(
			'City'			=> '/\S{2,}/',
			'Label'			=> '/\S{2,}/',
			'Description'	=> '/\S{2,}/',
			'Email'			=> '/\S{2,}/',
		);
	}
	
	/**
	 * @see parent::save()
	 */
	public function save()
	{
		if ( !$this->Position )
		{
			$this->Position = intval( self::getLast( $this, 'Position' ) ) + 1;
		}
		return parent::save();
	}

	/**
	 * The function returns array of offices (departments).
	 *
	 * @static
	 * @access public
	 * @param string $field The field to show: all | name | email
	 * @return array The list of offices.
	 */
	public static function getOffices( $field = 'name' )
	{
		$result = array();
		$i = 0;
		foreach ( explode( "\n", Config::get('contacts/offices') ) as $line )
		{
			$line = trim( $line );
			if ( $field == 'all' )
			{
				$result[ $i ] = $line;
			}
			else if ( preg_match( '/^(.+)<(.+)>$/i', $line, $res ) )
			{
				$result[ $i ] = $field == 'name' ? trim( $res[1] ) : trim( $res[2] );
			}
			else
			{
				$result[ $i ] = $field == 'name' ? $line : '';
			}
			$i++;
		}
		return $result;
	}
	
}
