<?

/*

{INSTALL:SQL{
create table content_images(
	Id int not null auto_increment,
	Name varchar(100) not null,
	Filename varchar(200) not null,
	IsFile tinyint not null,

	primary key (Id),
	index (Name)
) engine = MyISAM;

}}
*/

/**
 * The Content Image model class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Content_Image extends Object
{

	public $Id;
	public $Name;
	public $Filename;
	public $IsFile;

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
		return 'content_images';
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
			'folderFormat'	=> '%05d',
			'folderLimit'	=> 1000,
		);
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
	 * The function returns array of Images.
	 * 
	 * @static
	 * @access public
	 * @param bool $assoc If TRUE returns associative array of images.
	 * @return array The Images.
	 */
	public static function getImages( $assoc = false )
	{
		$Image = new self();
		$array = $Image->findList( array(), 'Name asc' );
		if ( !$assoc )
		{
			return $array;
		}
		$result = array();
		foreach ( $array as $Image )
		{
			$result[ $Image->Id ] = $Image->Name;
		}
		return $result;
	}
	
}
