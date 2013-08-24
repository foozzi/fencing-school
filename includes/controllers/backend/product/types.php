<?

/**
 * The Product Types controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Product_Types extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName()
	{
		return 'Product_Type';
	}
	
	/**
	 * @see parent::getAliasName()
	 */
	protected function getAliasName()
	{
		return 'Type';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Типы';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Type = new Product_Type();
		$this->getView()->set( 'Types', $Type->findList( array(), 'Position asc' ) );
		return $this->getView()->render();
	}
	
}
