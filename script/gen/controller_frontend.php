<?

/**
 * The %Name controller class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class %Name {extends %Extends}
{
	
	/**
	 * @see parent::getName()
	 */
	public function getName()
	{
		return '%Name';
	}
	
	/**
	 * The articles index handler.
	 * 
	 * @access public
	 * @param int $year The year or meta id.
	 * @return string The HTML code.
	 */
	public function index()
	{
		$%Alias = new %Model();
		$params = array();
		
		$Paginator = new Paginator( $%Alias->findSize( $params ), $this->getLimit(), $this->getPage() );
		$this->getView()->set( '%Aliass', $%Alias->findList( $params, 'Id desc', $this->getOffset(), $this->getLimit() ) );
		$this->getView()->set( 'Paginator', $Paginator );
		return $this->getView()->render();
	}
	
	/**
	 * The %Alias index and view handler.
	 * 
	 * @access public
	 * @param int $id The %Alias id.
	 * @return string The HTML code.
	 */
	public function view( $id = null )
	{
		$%Alias = new %Model();
		$%Alias = $%Alias->findItem( array( 'Id = '.$id ) );
		if ( !$%Alias->Id )
		{
			$this->halt();
		}
		
		$this->getView()->set( '%Alias', $%Alias );
		return $this->getView()->render();
	}
	
}
