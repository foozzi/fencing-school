<?

/*
{INSTALL:SQL{
create table admins_access(
	UserId int not null,
	Controller char(40) not null,

	primary key (UserId,Controller)
) engine = MyISAM;

}}
*/

/**
 * The Admin Access model.
 * 
 * @author Yarick.
 * @version 1.0
 */
class Admin_Access extends Object
{

	public $UserId;
	public $Controller;

	/**
	 * @see parent::getPrimary()
	 */
	public function getPrimary()
	{
		return array('UserId', 'Controller');
	}
	
	/**
	 * @see parent::getTableName()
	 */
	public function getTableName()
	{
		return 'admins_access';
	}
	
}
