<?

/*
{INSTALL:SQL{
create table customers_session(
	Id char(20) not null,
	UserId int not null,
	Timeout int not null,
	CreatedAt int not null,
	UpdatedAt int not null,

	primary key (Id),
	index (UserId)
) engine = MyISAM;

}}
*/
class Customer_Session extends User_Session
{

	public $Id;
	public $UserId;
	public $Timeout;
	public $CreatedAt;
	public $UpdatedAt;

	/**
	 * @see parent::getPrimary()
	 */
	public function getPrimary()
	{
		return array('Id');
	}
	
	/**
	 * @see parent::getTableName()
	 */
	public function getTableName()
	{
		return 'customers_session';
	}
	
	public function getUserRow()
	{
		return new Customer();
	}

}