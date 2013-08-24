<?

/*

{INSTALL:SQL{
create table content_page_blocks  (
	Id int not null auto_increment, 
	PageId int not null,
	Title varchar(255) not null,
	Description text not null,
	Content mediumtext not null,
	Filename varchar(255) not null,
	IsFile tinyint not null,
	Position int not null,
	
	primary key (Id),
	index (PageId),
	index (Position)
) engine=MyISAM;
}}
*/

/**
 * The Content Page Block model.
 * 
 * @author Yarick.
 * @version 0.2
 */
class Content_Page_Block extends Object
{

	public $Id;
	public $PageId;
	public $Title;
	public $Description;
	public $Content;
	public $Filename;
	public $IsFile;
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
		return 'content_page_blocks';
	}
	
	/**
	 * @see parent::getUploadFileInfo()
	 */
	public function getUploadFileInfo()
	{
		return array(
			'allow'			=> array('gif', 'jpg', 'png'),
			'folderFormat'	=> '%05d',
			'folderLimit'	=> 1000,
			'sizes'			=> array('450x300'),
			'quality'		=> array(90),
		);
	}

	public function saveNew()
	{
		if ( !$this->Position )
		{
			$this->Position = self::getLast( $this, 'Position', array( 'PageId = '.$this->PageId ) ) + 1;
		}
		return parent::saveNew();
	}
	
	/**
	 * @see parent::drop()
	 */
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
