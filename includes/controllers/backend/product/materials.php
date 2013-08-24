<?

/**
 * The Product Materials controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Product_Materials extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName()
	{
		return 'Product_Material';
	}
	
	/**
	 * @see parent::getAliasName()
	 */
	protected function getAliasName()
	{
		return 'Material';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Материалы';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Material = new Product_Material();
		$this->getView()->set( 'Materials', $Material->findList( array(), 'Name asc' ) );
		return $this->getView()->render();
	}
	
}
