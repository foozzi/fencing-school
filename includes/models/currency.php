<?

/*
{INSTALL:SQL{
create table currencies(
	Id int not null auto_increment,
    Code char(3) not null,
	Name varchar(50) not null,
	Decimals tinyint not null,
	Point char(1) not null,
	`Separator` char(1) not null,
	Sign varchar(10) not null,
	Rate float(5,3) not null,
	Countries text not null,
	IsEnabled tinyint not null,
	IsDefault tinyint not null,

	Position int not null,

	primary key (Id),
	index (IsEnabled),
	index (IsDefault),
	index (Position)
) engine = MyISAM;

}}
*/

/**
 * The Currency model class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Currency extends Object
{

	public $Id;
	public $Name;
	public $Code;
	public $Decimals;
	public $Point;
	public $Separator;
	public $Sign;
	public $Rate;
	public $Countries;
	public $IsEnabled;
	public $IsDefault;
	public $Position;

	/**
	 * @see parent::getPrimary()
	 */
	protected function getPrimary()
	{
		return array( 'Id' );
	}

	/**
	 * @see parent::getTableName()
	 */
	protected function getTableName()
	{
		return 'currencies';
	}

	public function getSeparator()
	{
		return strtr( $this->Separator, array( '_' => ' ' ) );
	}

	public function getExample()
	{
		return number_format( 9999999.99, $this->Decimals, $this->Point, $this->getSeparator() ) . ' ' . $this->Sign;
	}

	public function getFormat()
	{
		return sprintf( '%1s%1s%s', $this->Point, $this->getSeparator(), $this->Decimals );
	}

	public function setDefault()
	{
		if ( !$this->Id )
		{
			return false;
		}
		$query = 'update currencies set IsDefault = 0';
		$this->db()->execute( $query );
		$this->IsDefault = 1;
		return $this->save();
	}

	public function hasCountry( $code )
	{
		if ( $this->Countries == '*' )
		{
			return true;
		}
		$arr = explode( ',', strtoupper( $this->Countries ) );
		if ( count( $arr ) == 1 && !$arr[0] )
		{
			return false;
		}
		return in_array( strtoupper( $code ), $arr );
	}

	public static function findDefault( $onlyActive = false )
	{
		$Currency = new self();
		return $Currency->findItem( array( 'IsDefault = 1' ) );
	}

	public static function findCurrencies( $onlyActive = false )
	{
		$Currency = new self();
		$params = array( );
		if ( $onlyActive )
		{
			$params[] = 'IsEnabled = 1';
		}
		return $Currency->findList( $params, 'Position asc' );
	}

	public static function getCurrencies( $onlyActive = false )
	{
		return self::convertArray( self::findCurrencies( $onlyActive ), 'Id', 'Name' );
	}

	public static function getCurrency( $id = false )
	{
		$Currency = new self();
		$params = array( );
		if ( $id )
		{
			return $Currency->findItem( array('Id = ' . $id) );
		}
		if ( $Id = Request::get( 'Currency', false, 'COOKIE' ) )
		{
			$params[] = 'Id = ' . $Id;
		}
		else
		{
			$params[] = 'Position = 1';
		}
		return $Currency->findItem( $params );
	}

}
