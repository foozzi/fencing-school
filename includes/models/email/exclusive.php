<?

/**
 * The Order Exclusive class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Email_Exclusive extends Email
{
	private $spam = false;
	
	public function __construct( $spam = false )
	{
		parent::__construct( );
		$this->spam = $spam;
	}	
	
	/**
	 * @see parent::getFrom()
	 */
	protected function getFrom()
	{
		return Config::get('email@from');
	}
	
	/**
	 * @see parent::getTo()
	 */
	protected function getTo()
	{
		if($this->spam)
			return Config::get('email@spam');
		
		return Config::get('email@order/to');
	}
	
	/**
	 * @see parent::getTo()
	 */
	protected function getSubject()
	{
		return 'Заказ эксклюзива';
	}
	
	/**
	 * @see parent::getMessage()
	 */
	protected function getMessage()
	{
		return $this->includeLayout('exclusive.html');
	}
	
	/**
	 * @see parent::send()
	 */
	public function send()
	{
		if ( !empty( $_FILES['file']['tmp_name'] ) )
		{
			$this->attach( $_FILES['file']['tmp_name'], $_FILES['file']['name'] );
		}
		return parent::send();
	}
	
}
