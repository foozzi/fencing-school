<?

/*

{INSTALL:SQL{
create table galleries(
	Id int not null auto_increment,
	Name varchar(250) not null,
	Type tinyint not null,
	Description text not null,
	Button varchar(250) not null,
	ProductId int not null,

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
 * The Gallery model class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Gallery extends Object
{
	
	const GALLERY		= 0;
	const DEVELOPMENT	= 1;
	const CAPABILITY	= 2;
	
	public $Id;
	public $Name;
	public $Type;
	public $Description;
	public $Button;
	public $ProductId;
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
		return 'galleries';
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
	 * @see parent::getUploadFileInfo()
	 */
	public function getUploadFileInfo()
	{
		return array(
			'allow'			=> array('gif', 'jpg', 'jpeg', 'png'),
			'extension'		=> 'jpg',
			'folderFormat'	=> '%05d',
			'folderLimit'	=> 1000,

			'sizes'			=> array('142x98'),
			'crop'			=> array('Center'),
			'quality'		=> array(100),
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
			foreach ( $this->getItems() as $Item )
			{
				$Item->drop();
			}
			return true;
		}
		return false;
	}
	
	/**
	 * The function returns current gallery Items.
	 * 
	 * @access public
	 * @param int $limit The items limit.
	 * @return array The Gallery Items.
	 */
	public function getItems( $limit = null )
	{
		$Item = new Gallery_Item();
		return $Item->findList( array( 'GalleryId = '.$this->Id ), 'Position asc', null, $limit );
	}
	
	/**
	 * The function returns first gallery Item.
	 * 
	 * @access public
	 * @return object The Gallery Item.
	 */
	public function getImage()
	{
		$Item = new Gallery_Item();
		foreach ( $Item->findList( array( 'GalleryId = '.$this->Id ), 'Position asc', 0, 1 ) as $Item );
		return $Item;
	}
	
	/**
	 * The function returns Product attached to current Gallery.
	 * 
	 * @access public
	 * @return object The Product.
	 */
	public function getProduct()
	{
		$Product = new Product();
		return $Product->findItem( array( 'Id = '.$this->ProductId ) );
	}
	
	/**
	 * The function returns array of gallery types.
	 * 
	 * @static
	 * @access public
	 * @return array The types
	 */
	public static function getTypes()
	{
		return array(
			self::GALLERY		=> 'Портфолио',
			self::DEVELOPMENT	=> 'Производство',
			self::CAPABILITY	=> 'Возможности',
		);
	}
	
}
