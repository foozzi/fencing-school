<?

/*

{INSTALL:SQL{
create table buttons(
	Id int not null auto_increment,
	Name varchar(200) not null,
	Link varchar(150) not null,
	Content text not null,
	Filename varchar(200) not null,
	IsFile tinyint not null,
	Position int not null,

	primary key (Id),
	index (Position)
) engine = MyISAM;

}}
*/

/**
 * The Button model.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Button extends Banner
{
	
	public $Id;
	public $Name;
	public $Link;
	public $Content;
	public $Filename;
	public $IsFile;
	public $Position;
	
	/**
	 * @see parent::getPrimary()
	 */
	protected function getPrimary()
	{
		return array('Id');
	}
	
	public function getUploadFileInfo()
	{
		return array(  
                        'urlFormat'             => false,
		);
	}
        
        
        /**
	 * @see parent::getTableName()
	 */
	protected function getTableName()
	{
		return 'buttons';
	}

	public function drop()
	{
		if ( parent::drop() )
		{
			File::detach( $this );
			return true;
		}
		return false;
	} 
	
}
