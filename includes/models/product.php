<?

/*
{INSTALL:SQL{
create table products(
	Id int not null auto_increment,
	CategoryId int not null,
	RootId int not null,
	ParentId int not null,

	Children int not null,

	Code varchar(20) not null,
	Name varchar(100) not null,
	Slug varchar(100) not null,
	Description text not null,
	Promo text not null,
	Short text not null,
	Story text not null,
	Video varchar(100) not null,
	Documents text not null,
	Products text not null,
	SetCount tinyint not null,

	Price float(12,2) not null,
	PriceRaw float(12,2) not null,
	Discount float(6,3) not null,
	MinQuantity int not null,
	RecQuantity int not null,
	Stock int not null,
	BonusId int not null,
	IsSet tinyint not null,
	IsFavorite tinyint not null,
	IsBanner tinyint not null,
	IsActive tinyint not null,
	IsVisible tinyint not null,
	Filename varchar(100) not null,
	IsFile tinyint not null,
	PostedAt int not null,
	Position int not null,

	primary key (Id),
	index (RootId),
	index (Slug),
	index (CategoryId),
	index (ParentId),
	index (SetCount),
	index (Price),
	index (IsSet),
	index (IsFavorite),
	index (IsActive),
	index (IsVisible),
	index (PostedAt),
	index (Position)
) engine = MyISAM;

}}
 */

/**
 * The Product model.
 *
 * @author Yarick.
 * @version 1.0
 */
class Product extends Object
{

	public $Id;
	public $CategoryId;
	public $RootId;
	public $ParentId;   // using for sets
	public $Children;   // count of children items, cached column
	public $Code;
	public $Name;
	public $Slug;
	public $Description;
	public $Promo;
	public $Short;
	public $Story;
	public $Video;
	protected $Documents;
	protected $Products;
	public $SetCount;
	public $Price;
	public $PriceRaw;
	public $Discount;		// discount for product in %
	public $MinQuantity;	// minimum quantity to order
	public $RecQuantity;	// recommended quantity to order
	public $Stock;			// the stock quantity
	public $BonusId;
	public $IsSet;			// Set type of product, 0 - regular product, 1 - product set, 2 - product box
	public $IsFavorite;
	public $IsBanner;		// The banner view in product list
	public $IsActive;		// product's active state in frontend
	public $IsVisible;		// product's visible state in frontend, cached column
	public $Filename;
	public $IsFile;
	public $PostedAt;
	public $Position;

	private $old;
	private $attrs;
	private $box;

	protected function getPrimary()
	{
		return array( 'Id' );
	}

	protected function getTableName()
	{
		return 'products';
	}

	public function getTestRules()
	{
		return array(
			'Name' => '/\S{2,}/',
			'Slug' => '/\S{2,}/',
		);
	}

	public function __construct()
	{
		parent::__construct();
		$this->IsSet = $this->getSetValue();
	}

	protected function getExtraParam()
	{
		return 'IsSet = '.$this->getSetValue();
	}

	protected function getSetValue()
	{
		return 0;
	}

	public function findShortList( $params = array( ), $order = null, $offset = null, $limit = null )
	{
		return $this->findResult( 'Id, CategoryId, ParentId, Children, Code, Name, Short, Promo, Slug, Price, PriceRaw, Discount, '
				. ' MinQuantity, RecQuantity, Stock, IsSet, IsFavorite, IsBanner, IsActive, IsVisible, Products, SetCount, Filename, IsFile', 
				$params, $order, $offset, $limit );
	}

	public function findResult( $columns = '*', $params = array( ), $order = null, $offset = null, $limit = null )
	{
		$params[] = $this->getExtraParam();
		return parent::findResult( $columns, $params, $order, $offset, $limit );
	}

	public function findList( $params = array( ), $order = null, $offset = null, $limit = null )
	{
		$params[] = $this->getExtraParam();
		return parent::findList( $params, $order, $offset, $limit );
	}

	public function findSize( $params = array() )
	{
		$params[] = $this->getExtraParam();
		return parent::findSize( $params );
	}

	public function setPost( array $data = array( ) )
	{
		$this->old = $this->getFields();
		parent::setPost( $data );
		if ( isset( $data['Attribute'] ) && is_array( $data['Attribute'] ) )
		{
			$this->attrs = array();
			foreach ( $data['Attribute']['AttrId'] as $i => $id )
			{
				if ( $id )
				{
					$this->attrs[ $id ] = $data['Attribute']['Value'][ $i ];
				}
			}
		}
		if ( isset( $data['PostedAt'] ) )
		{
			$this->PostedAt = Date::strtotime( $data['PostedAt'] );
		}
		if ( isset( $data['clear_documents'] ) )
		{
			$this->Documents = array();
		}
		if ( isset( $data['Document'] ) && is_array( $data['Document'] ) )
		{
			$this->Documents = $data['Document'];
		}
		if ( isset( $data['clear_products'] ) )
		{
			$this->Products = array();
		}
		if ( isset( $data['Product'] ) && is_array( $data['Product'] ) )
		{
			$arr = array();
			foreach ( $data['Product'] as $id )
			{
				$Product = new Product();
				if ( $Product->findSize( array( 'Id = '.$id ) ) > 0 )
				{
					$arr[] = $id;
				}
			}
			$this->Products = $arr;
			$this->SetCount = count( $arr );
		}
		if ( isset( $data['clear_box'] ) )
		{
			$this->box = array();
		}
		if ( isset( $data['Box'] ) && is_array( $data['Box'] ) )
		{
			$this->box = $data['Box'];
		}
		$this->Name = trim( $this->Name );
		$this->Slug = self::trimSlug( $this->Slug );
	}

	public function copy()
	{
		$Product = new self();
		$Product->set( $this );
		$Product->Id = null;
		$Product->PostedAt = time();
		if ( $Product->save() )
		{
			$Conn = new Product_Attribute_Connector();
			foreach ( $Conn->findList( array( 'ProductId = '.$this->Id ) ) as $Conn )
			{
				$Attr = new Product_Attribute_Connector();
				$Attr->set( $Conn );
				$Attr->ProductId = $Product->Id;
				$Attr->saveNew();
			}
			foreach ( $this->getImages() as $Item )
			{
				$Image = new Product_Image();
				$Image->set( $Item );
				$Image->ProductId = $Product->Id;
				$Image->Id = null;
				if ( $Image->save() )
				{
					$info = $Image->getUploadFileInfo();
					foreach ( $info['sizes'] as $i => $value )
					{
						$source = File::path( $Item, $i );
						$target = File::path( $Image, $i );
						File::restore( $target );
						copy( $source, $target );
					}
					if ( empty( $info['dropOrig'] ) )
					{
						$source = File::path( $Item );
						$target = File::path( $Image );
						File::restore( $target );
						copy( $source, $target );
					}
				}
			}
		}
		return $Product;
	}

	/**
	 * @see parent::save()
	 */
	public function save()
	{
		if ( !$this->PostedAt )
		{
			$this->PostedAt = time();
		}
		if ( !$this->Position )
		{
			$this->Position = self::getLast( $this, 'Position', array( 'CategoryId = '.$this->CategoryId ) ) + 1;
		}
		$this->checkVisibility();
		if ( parent::save() )
		{
			if ( is_array( $this->attrs ) )
			{
				Product_Attribute_Connector::detach( $this );
				foreach ( $this->attrs as $id => $value )
				{
					Product_Attribute_Connector::attach( $this, $id, $value );
				}
			}

			if ( is_array( $this->box ) )
			{
				Product_Connector::detach( $this );
				foreach ( $this->box as $id )
				{
					Product_Connector::attach( $this, $id );
				}
			}

			$this->cacheParent();
			if ( $this->hasChanged('CategoryId') )
			{
				$this->getCategory( $this->old['CategoryId'] )->checkVisibility();
			}
			if ( $this->hasChanged('CategoryId') || $this->hasChanged('IsActive') || $this->hasChanged('IsVisible') )
			{
				$this->getCategory()->checkVisibility();
			}
			return true;
		}
		return false;
	}

	public function rawSave()
	{
		return parent::save();
	}

	/**
	 * @see parent::drop()
	 */
	public function drop()
	{
		if ( parent::drop() )
		{
			Product_Attribute_Connector::detach( $this );
			Product_Connector::detach( $this );

			$Image = new Product_Image();
			foreach ( $Image->findList( array( 'ProductId = ' . $this->Id ) ) as $Image )
			{
				$Image->drop();
			}
			if ( $this->ParentId == 0 )
			{
				$Product = new self();
				foreach ( $Product->findList( array( 'ParentId = ' . $this->Id ) ) as $Product )
				{
					$Product->drop();
				}
			}
			$this->cacheParent();
			$this->getCategory()->checkVisibility();
			return true;
		}
		return false;
	}

	public function checkVisibility()
	{
		$this->IsVisible = intval( $this->IsActive > 0 && $this->getCategory()->IsActive > 0 );
	}

	public function inStock()
	{
		return $this->Stock - $this->MinQuantity > 0;
	}

	/**
	 * The function returns parent Product for current one.
	 *
	 * @access public
	 * @return object The parent Product.
	 */
	public function getParent()
	{
		$Parent = new self();
		return $Parent->findItem( array( 'Id = ' . $this->ParentId ) );
	}

	/**
	 * The function calculates actual count of children items.
	 *
	 * @access protected
	 */
	protected function calcChildren()
	{
		$this->Children = $this->findSize( array( 'ParentId = ' . $this->Id ) );
	}

	/**
	 * The function cache parent Product with recalculated values.
	 *
	 * @access protected
	 * @return bool TRUE on success, FALSE on failure.
	 */
	protected function cacheParent()
	{
		if ( $this->hasChanged('ParentId') )
		{
			$this->RootId = $this->getCategory()->getRoot()->Id;
			$Parent = $this->getParent();
			if ( $Parent->Id )
			{
				$Parent->calcChildren();
				$Parent->save();
				return true;
			}
		}
		return false;
	}

	/**
	 * The function returns price for current Product.
	 *
	 * @access public
	 * @param bool $noDiscount If TRUE returns Price without discount, otherwise calculated discount price.
	 * @return float The price.
	 */
	public function getPrice( $noDiscount = false )
	{
		if ( $noDiscount )
		{
			return $this->Price;
		}
		return $this->Price * (100 - $this->Discount) / 100;
	}

	public function getName()
	{
		if ( $this->ParentId )
		{
			return $this->getParent()->getName().' - '.$this->Name;
		}
		return $this->Name;
	}

	/**
	 * The function returns default Product Image.
	 *
	 * @access public
	 * @return object The Product Image.
	 */
	public function getImage()
	{
		$Image = new Product_Image();
		$params = array( );
		$params[] = 'ProductId = ' . $this->Id;
		foreach ( $Image->findList( $params, 'Position asc', 0, 1 ) as $Image );
		return $Image;
	}

	/**
	 * The function returns array of Product Images attached to current Product.
	 *
	 * @access public
	 * @return array The Product Images.
	 */
	public function getImages()
	{
		$Image = new Product_Image();
		$params = array( );
		$params[] = 'ProductId = ' . $this->Id;
		return $Image->findList( $params, 'Position asc' );
	}

	/**
	 * The function returns related Products.
	 *
	 * @access public
	 * @return array The Products.
	 */
	public function getRelated()
	{
		$Related = new self();
		return $Related->findList( array( 'CategoryId = ' . $this->CategoryId, 'IsVisible = 1' ), 'random', 0, 10 );
	}

	/**
	 * The function returns Tags attached to current Product.
	 *
	 * @access public
	 * @return array The Tags.
	 */
	public function getTags()
	{
		$Tag = new Tag();
	}

	/**
	 * The function returns Category for current Product.
	 * 
	 * @access public
	 * @return object The Product Category.
	 */
	public function getCategory( $id = null )
	{
		if ( !$id )
		{
			$id = $this->CategoryId;
		}
		$Category = new Product_Category();
		return $Category->findItem( array( 'Id = ' . $id ) );
	}

	public function getQuantity()
	{
		$Quantity = 1;
		if ( !$this->RecQuantity && !$this->MinQuantity )
		{
			$Quantity = 1;
		}
		if ( $this->RecQuantity )
		{
			$Quantity = ($this->RecQuantity > $this->MinQuantity) ? $this->RecQuantity : $this->MinQuantity;
		}
		else if ( $this->MinQuantity )
		{
			$Quantity = $this->MinQuantity;
		}

		return $Quantity;
	}

	public function getParam( $name, $value = null )
	{
		switch ( $name )
		{
			case 'search':
				$str = $this->db()->quote( $value . '%' );
				if ( strpos( $value, '*' ) !== false )
				{
					$str = $this->db()->quote( str_replace( '*', '%', $value ) );
				}
				return '* Name like ' . $str;

			case 'incategory':
				$value = intval( $value );
				return '* CategoryId in (select Id from products_category where find_in_set('
					. $this->db()->quote( $value ) . ', CachedPath)) or CategoryId = ' . $value;

			case 'online':
				return '* IsVisible = 1';

			case 'available':
				return 'Stock > 0';

			case 'stock':
				if ( $value == 'in' )
				{
					return 'Stock > 0';
				}
				else if ( $value == 'out' )
				{
					return 'Stock = 0';
				}
				else if ( $value == 'sold' )
				{
					return 'Stock = -1';
				}
				return null;
		}
	}

	public function id()
	{
		return $this->Code;
	}

	/**
	 * The function returns Attribute value for current Attr.
	 *
	 * @access public
	 * @param mixed $Attr The Attribute object or its Id.
	 * @return string The Attribute value.
	 */
	public function getAttr( $Attr )
	{
		$id = $Attr instanceof Product_Attribute ? $Attr->Id : $Attr;
		if ( is_array( $this->attrs ) )
		{
			return isset( $this->attrs[$id] ) ? $this->attrs[$id] : null;
		}
		$Conn = Product_Attribute_Connector::get( $this, $id );
		return $Conn->Value;
	}

	public function getAttributes( $assoc = false )
	{
		$result = array();
		$arr = Product_Attribute_Connector::getAttributes( $this );
		if ( !$assoc )
		{
			return $arr;
		}
		foreach ( $arr as $Conn )
		{
			$result[ $Conn->Name ] = $Conn->Value;
		}
		return $result;
	}

	public function getAttrs()
	{
		return $this->getAttributes(true);
	}
	
	public function getShort( $length = 120 )
	{		
		return String::toLength( $this->Short, $length );
	}
	
	/*
	 * @todo
	 */
	public static function getActionSets( $count = 5 )
	{
		$Sets = array();
		return $Sets;
	}

	/*
	 * @todo
	 */
	public static function getPopular( $count = 5 )
	{
		$Product = new self();
		$params = array( );
		$params[] = $Product->getParam('online');
		$params[] = 'Price > 0';
		return $Product->findList( $params, 'rand()', 0, $count );
	}

	/*
	 * @todo
	 */
	public function onSale()
	{
		return true;
	}

	/*
	 * @todo
	 */
	public function isNew()
	{
		return true;
	}

	public function getBonus()
	{
		$Bonus = new Product_Bonus();
		return $Bonus->findItem( array( 'Id = '.$this->BonusId ) );
	}

	public function getDate()
	{
		return date( 'd.m.y H:i', $this->PostedAt > 0 ? $this->PostedAt : time() );
	}

	private function checkDocuments()
	{
		if ( !is_array( $this->Documents ) )
		{
			$this->Documents = @unserialize( $this->Documents );
		}
		if ( !is_array( $this->Documents ) )
		{
			$this->Documents = array();
		}
	}

	public function getDocuments()
	{
		$this->checkDocuments();
		if ( count( $this->Documents ) == 0 )
		{
			return array();
		}
		$Document = new Document();
		$params = array();
		$params[] = '* Id in ('.implode( ',', $this->Documents ).')';
		return $Document->findList( $params, 'Position asc' );
	}

	protected function hasDocument( Document $Document )
	{
		$this->checkDocuments();
		return in_array( $Document->Id, $this->Documents );
	}

	public function has( $Object )
	{
		if ( $Object instanceof Document )
		{
			return $this->hasDocument( $Object );
		}
		if ( $this instanceof Product_Box )
		{
			return Product_Connector::has( $this, $Object );
		}
		return false;
	}
	
	private function checkProducts()
	{
		if ( !is_array( $this->Products ) )
		{
			$this->Products = @unserialize( $this->Products );
		}
		if ( !is_array( $this->Products ) )
		{
			$this->Products = array();
		}
	}

	public function getProduct( $index = 0 )
	{
		$this->checkProducts();
		$id = isset( $this->Products[ $index ] ) ? $this->Products[ $index ] : 0;
		$Product = new Product();
		return $Product->findItem( array( 'Id = '.$id ) );
	}

	public function getProducts()
	{
		if ( $this instanceof Product_Box )
		{
			$Product = new Product();
			$params = array();
			$params[] = '* Id in (select ProductId from products_conn where ParentId = '.$this->Id.')';
			return $Product->findShortList( $params, 'Position asc' );
		}
		else
		{
			$this->checkProducts();
			if ( !count( $this->Products ) )
			{
				return array();
			}
			$Product = new Product();
			$params = array();
			$params[] = '* Id in ('.implode( ',', $this->Products ).')';
			return $Product->findShortList( $params, 'Position asc' );
		}
		return array();
	}

	private static function trimSlug( $string )
	{
		$string = trim( $string );
		$string = preg_replace( '/\s+/i', '-', $string );
		return $string;
	}

	public static function findProductsFor( Object $Object )
	{
		$Product = new self();
		$params = array();
		if ( $Object instanceof Product_Box )
		{
			$params[] = 'ParentId = 0';
			$params[] = 'CategoryId = '.$Object->CategoryId;
		}
		else if ( $Object instanceof Product_Set )
		{
			$params[] = 'Id = 0';
		}
		else if ( $Object instanceof Product )
		{
			$params[] = 'ParentId = '.$Object->Id;
		}
		return $Product->findShortList( $params, 'Position asc' );
	}

}
