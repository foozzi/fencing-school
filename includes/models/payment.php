<?

/*

{INSTALL:SQL{
create table payments(
	Id int not null auto_increment,
	Name varchar(200) not null,
	Position int not null,
	IsActive tinyint not null,

	primary key (Id),
	index (Position),
	index (IsActive)
) engine = MyISAM;
}}
*/

/**
 * The Payment model.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Payment extends Object
{
	
	public $Id;
	public $Name;
	public $Position;
	public $IsActive;
	
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
		return 'payments';
	}
	
	/**
	 * @see parent::getTestRules()
	 */
	public function getTestRules()
	{
		return array(
			'Name'		=> '/\S{2,}/',
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
	 * The function returns all payment methods.
	 * 
	 * @static
	 * @access public
	 * @param bool $assoc If TRUE returns associated array.
	 * @return array The payment methods.
	 */
	public static function getPayments( $assoc = false )
	{
		$Payment = new self();
		$result = array();
		$arr = $Payment->findList( array(), 'Position asc' );
		if ( !$assoc )
		{
			return $arr;
		}
		return self::convertArray( $arr, 'Id', 'Name' );
	}
	
}
