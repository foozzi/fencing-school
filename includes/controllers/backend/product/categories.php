<?

/**
 * The Product Categories controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Product_Categories extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = 'edit' )
	{
		return 'Car_Category';
	}
	
	/**
	 * @see parent::getAliasName()
	 */
	protected function getAliasName( $method = 'edit' )
	{
		return 'Category';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Категории';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Category = new Car_Category();
		$this->getView()->set( 'Categories', $Category->findList( array(), 'Name asc' ) );
		return $this->getView()->render();
	}
	
}
