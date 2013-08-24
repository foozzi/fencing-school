<? 

class View 
{
	
	private $data = array();
	private $method = 'index';
	
	private $controller = null;
	
	public function set( $key, $value = null )
	{
		if ( is_array( $key ) )
		{
			foreach ( $key as $name => $value )
			{
				$this->set( $name, $value );
			}
		}
		else
		{
			$this->data[ $key ] = $value;
		}
		
	}
	
	public function get( $key = null )
	{
		if ( $key === null )
		{
			return $this->data;
		}
		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
	}
	
	/**
	 * The function sets current method for view.
	 * 
	 * @access public
	 * @param string $method The method value.
	 */
	public function setMethod( $method )
	{
		$this->method = $method;
	}
	
	/**
	 * The function returns current method for view.
	 * 
	 * @access public
	 * @return string The method value.
	 */
	protected function getMethod()
	{
		return $this->method;
	}
	
	/**
	 * The function sets controller object for current view.
	 * 
	 * @access public
	 * @param object $Controller The controller object.
	 */
	public function setController( Controller $Controller )
	{
		$this->controller = $Controller;
	}
	
	/**
	 * The function returns controller object from current view.
	 * 
	 * @access protected
	 * @return object The controller object.
	 */
	protected function getController()
	{
		return $this->controller;
	}
	
	/**
	 * The function returns current layout file name.
	 * 
	 * @access protected
	 * @return string The file name.
	 */
	protected function getLayout()
	{
		return 'main.html';
	}
	
	/**
	 * The function returns current templates (layout) directory.
	 * 
	 * @access protected
	 * @return string The directory path.
	 */
	protected function getLayoutDir()
	{
		return Runtime::get('TEMPLATE_DIR');
	}
	
	/**
	 * The function returns layout file path.
	 * 
	 * @access protected
	 * @return string The file path.
	 */
	protected function getLayoutPath( $layout = null )
	{
		if ( $layout === null )
		{
			$layout = $this->getLayout();
		}
		return $this->getLayoutDir().'/'.$layout;
	}
	
	/**
	 * The function includes layout.
	 * 
	 * @access protected
	 * @param string $layout The layout file name.
	 * @param mixed $data Layout data.
	 * @return string The rendered layout.
	 */
	protected function includeLayout( $layout, $data = null )
	{
		$data = array_merge( $this->get(), is_array( $data ) ? $data : array() );
		extract( $data );
		ob_start();
		include( $this->getLayoutPath( $layout ) );
		$result = ob_get_contents();
		ob_end_clean();
		return $result;
	}
	
	/**
	 * The function renders View layout.
	 * 
	 * @access public
	 * @param string $layout The layout.
	 * @return string The rendered layout.
	 */
	public function render( $layout = null )
	{
		return $this->includeLayout( $layout );
	}
	
}
