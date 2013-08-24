<?

/*

{INSTALL:SQL{
create table articles_references(
	Id int not null auto_increment,
	Layout tinyint not null,
	ArticleId int not null,
	RefId int not null,
	CategoryId int not null,
	BrandId int not null,
	TypeId int not null,
	MaterialId int not null,

	primary key (Id),
	index (Layout,RefId),
	index (ArticleId),
	index (CategoryId,BrandId,TypeId,MaterialId)
) engine = MyISAM;

}}
*/

/**
 * The Article Reference connection model.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Article_Reference extends Object
{
	
	const PAGE		= 1;
	const PRODUCT	= 2;
	const CATALOG	= 3;
	
	public $Id;
	public $Layout;
	public $ArticleId;
	public $RefId;
	public $CategoryId;
	public $BrandId;
	public $TypeId;
	public $MaterialId;
	
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
		return 'articles_references';
	}
	
	/**
	 * @see parent::save()
	 */
	public function save()
	{
		if ( in_array( $this->Layout, array( self::PAGE, self::PRODUCT ) ) && !$this->RefId )
		{
			return false;
		}
		if ( $this->Layout == self::CATALOG && !$this->CategoryId && !$this->BrandId && !$this->TypeId && !$this->MaterialId )
		{
			return false;
		}
		return parent::save();
	}
	
	/**
	 * The function returns current Article layout.
	 * 
	 * @access public
	 * @return string The layout.
	 */
	public function getLayout()
	{
		$arr = self::getLayouts();
		return isset( $arr[ $this->Layout ] ) ? $arr[ $this->Layout ] : null;
	}
	
	/**
	 * The function returns connected Object to Article.
	 * 
	 * @access public
	 * @return object The connected Object.
	 */
	public function getObject()
	{
		if ( $this->Layout == self::PAGE )
		{
			$Page = new Content_Page();
			return $Page->findItem( array( 'Id = '.$this->RefId ) );
		}
		if ( $this->Layout == self::PRODUCT )
		{
			$Product = new Product();
			return $Product->findItem( array( 'Id = '.$this->RefId ) );
		}
		$Object = new stdClass();
		$Object->Name = '';
		return $Object;
	}
	
	/**
	 * The function returns Article for current Reference.
	 * 
	 * @access public
	 * @return object The Article.
	 */
	public function getArticle()
	{
		$Article = new Article();
		return $Article->findItem( array( 'Id = '.$this->ArticleId ) );
	}
	
	/**
	 * The function returns current Reference product Category.
	 * @access public
	 * @return object The Category.
	 */
	public function getCategory()
	{
		$Category = new Product_Category();
		return $Category->findItem( array( 'Id = '.$this->CategoryId ) );
	}
	
	/**
	 * The function returns current Reference product Brand.
	 * @access public
	 * @return object The Brand.
	 */
	public function getBrand()
	{
		$Brand = new Product_Brand();
		return $Brand->findItem( array( 'Id = '.$this->BrandId ) );
	}
	
	/**
	 * The function returns current Reference product Type.
	 * @access public
	 * @return object The Type.
	 */
	public function getType()
	{
		$Type = new Product_Type();
		return $Type->findItem( array( 'Id = '.$this->TypeId ) );
	}
	
	/**
	 * The function returns current Reference product Material.
	 * @access public
	 * @return object The Material.
	 */
	public function getMaterial()
	{
		$Material = new Product_Material();
		return $Material->findItem( array( 'Id = '.$this->MaterialId ) );
	}
	
	/**
	 * The function returns reference layouts.
	 * 
	 * @static
	 * @acccess public
	 * @return array The layouts.
	 */
	public static function getLayouts()
	{
		return array(
			self::PAGE		=> 'Страница',
			self::PRODUCT	=> 'Продукт',
			self::CATALOG	=> 'Каталог',
		);
	}
	
}
