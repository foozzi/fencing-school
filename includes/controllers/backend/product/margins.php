<?

/**
 * The Product Margins controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Product_Margins extends Controller_Backend_Standard
{
	
	public function getModelName( $method = 'edit' )
	{
		return 'Car_Margin';
	}
	
	public function getAliasName( $method = 'edit' )
	{
		return 'Margin';
	}

	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Наценки';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Margin = new Car_Margin();
		$this->getView()->set( 'Margins', $Margin->findList( array(), 'Position asc' ) );
		return $this->getView()->render();
	}
	
}
