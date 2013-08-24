<?

/**
 * The Product Colors controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Product_Colors extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName()
	{
		return 'Product_Color';
	}
	
	/**
	 * @see parent::getAliasName()
	 */
	protected function getAliasName()
	{
		return 'Color';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Цвета';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Color = new Product_Color();
		$this->getView()->set( 'Colors', $Color->findList( array(), 'Position asc' ) );
		return $this->getView()->render();
	}
	
}
