<?

/*

{INSTALL:SQL{
create table configuration(
	Id int not null auto_increment,
	Name varchar(100) not null,
	Value text not null,

	primary key (Id),
	unique key(Name)
) engine = MyISAM;
}}
*/

class Configuration extends Object
{
	
	public $Id;
	public $Name;
	public $Value;
	
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
		return 'configuration';
	}
	
	/**
	 * @see parent::getTestRules()
	 */
	public function getTestRules()
	{
		return array(
			'Name'			=> '/\S{2,}/',
		);
	}
	
	/**
	 * The function sets configuration value by its name and saves it in database.
	 * 
	 * @access public
	 * @param string $name The config value name.
	 * @param mixed $value The config value.
	 * @return bool TRUE on success, FALSE on failure.
	 */
	public static function setValue( $name, $value )
	{
		$Config = new self();
		$Config = $Config->findItem( array( 'Name = '.$name ) );
		$Config->Name = $name;
		$Config->Value = serialize( $value );
		return $Config->save();
	}
	
}
