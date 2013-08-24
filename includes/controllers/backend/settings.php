<?

/**
 * The Settings controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Settings extends Controller_Backend
{
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Настройки сайта';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		return $this->getView()->render();
	}
	
	/**
	 * The function saves configuration values.
	 * 
	 * @access private
	 */
	private function save()
	{
		if ( isset( $_POST['submit'] ) )
		{
			if ( isset( $_POST['Config'] ) && is_array( $_POST['Config'] ) )
			{
				foreach ( $_POST['Config'] as $key => $value )
				{
					Configuration::setValue( $key, $value );
				}
			}
			$this->halt();
		}
	}
	
	/**
	 * The contacts settings handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function contacts()
	{
		$this->save();
		return $this->getView()->render();
	}
	
	/**
	 * The email settings handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function email()
	{
		$this->save();
		return $this->getView()->render();
	}
	
	/**
	 * The footer settings handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function footer()
	{
		$this->save();
		return $this->getView()->render();
	}
	
	/**
	 * The search settings handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function search()
	{
		$this->save();
		return $this->getView()->render();
	}
	
	/**
	 * The search settings handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function social()
	{
		$this->save();
		return $this->getView()->render();
	}
	
	/**
	 * The links settings handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function links()
	{
		$this->save();
		return $this->getView()->render();
	}

	public function qrcode()
	{
		include_once( LIBS_DIR.'/phpqrcode/qrlib.php' );
		$str = Config::get('contacts/address');

		QRcode::png( $str, FILES_DIR.'/qrcode.png', 'L', 4, 2 );

		return $this->halt('', true);
	}
	
}
