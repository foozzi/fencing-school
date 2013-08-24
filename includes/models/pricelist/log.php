<?

/*

{INSTALL:SQL{
create table pricelists_log(
	Id int not null auto_increment,
	ListId int not null,
	Type tinyint not null,
	Line int not null,
	Error varchar(100) not null,

	PostedAt int not null,

	primary key (Id),
	index (ListId)
) engine = MyISAM;

}}
*/

/**
 * The Pricelist Log model class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Pricelist_Log extends Object
{
	
	public $Id;
	public $ListId;
	public $Type;
	public $Line;
	public $Error;
	public $PostedAt;
	
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
		return 'pricelists_log';
	}
	
	public function __construct()
	{
		parent::__construct();
		$this->PostedAt = time();
	}
	
	public function getDate()
	{
		return date( 'd.m.y', $this->PostedAt );
	}

	public function getErrorType()
	{
		return Pricelist::getErrorType( $this->Type );
	}
	
}
