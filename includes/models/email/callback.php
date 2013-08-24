<?

/**
 * The Callback Email class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Email_Callback extends Email
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
			
		return Config::get('email@contacts/to');
	}
	
	/**
	 * @see parent::getTo()
	 */
	protected function getSubject()
	{
		return 'Перезвони мне с сайта '.Runtime::get('HTTP_HOST');
	}
	
	/**
	 * @see parent::getMessage()
	 */
	protected function getMessage()
	{
		return $this->includeLayout('callback.html');
	}
	
}
