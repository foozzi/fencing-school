<?

/*
{INSTALL:SQL{
create table admins_session(
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

/**
 * The Member Session model.
 * 
 * @author Yarick.
 * @version 1.0
 */
class Admin_Session extends User_Session
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
		return 'admins_session';
	}
	
	/**
	 * @see parent::getUserRow()
	 */
	public function getUserRow()
	{
		return new Admin();
	}
	
}
