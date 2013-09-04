<?

/*

{INSTALL:SQL{
create table galleries_item(
	Id int not null auto_increment,
	GalleryId int not null,
	Name varchar(250) not null,

	Filename varchar(200) not null,
	IsFile tinyint not null,

	PostedAt int not null,
	Position int not null,

	primary key (Id),
	index (PostedAt),
	index (Position)
) engine = MyISAM;

}}
*/

/**
 * The Gallery Item model class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Gallery_Item extends Object
{
	
	public $Id;
	public $GalleryId;
	public $Name;
	public $Filename;
	public $IsFile;
	public $PostedAt;
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
		return 'galleries_item';
	}
	
	/**
	 * @see parent::getUploadFileInfo()
	 */
	public function getUploadFileInfo()
	{
		return array(
			'allow'			=> array('gif', 'jpg', 'jpeg', 'png'),
			'extension'		=> 'jpg',
			'folderFormat'	=> '%05d',
			'folderLimit'	=> 1000,

			'sizes'			=> array('142x98', '1000x600'),
			'crop'			=> array('Center', null),
			'quality'		=> array(100, 90),
			'timeAffix'		=> 'UpdatedAt',
			'dropOrig'		=> true,
		);
	}
	
	/**
	 * @see parent::saveNew()
	 */
	public function saveNew()
	{
		$this->Position = self::getLast( $this, 'Position' ) + 1;
		$this->PostedAt = time();
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
	
	/**
	 * The function returns search param for Product.
	 * 
	 * @access public
	 * @param string $name The search key.
	 * @param mixed $value The search value.
	 * @return string The param string.
	 */
	public function getParam( $name, $value = null )	
	{
		switch ( $name )
		{
			case 'type':
				return '* GalleryId in (select Id from galleries where Type = '.intval( $value ).')';
		}
		return null;
	}

	/**
	 * The function returns items Name and if it is not set Filename.
	 * 
	 * @access public
	 * @return string The name.
	 */
	public function getName()
	{
		return $this->Name ? $this->Name : $this->Filename;
	}
	
}
