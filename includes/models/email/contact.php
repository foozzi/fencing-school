<?

/**
 * The Contact Email class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Email_Contact extends Email
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
		
		$arr = Contact::getOffices('email');
		$index = Request::get('Office');
		return empty( $arr[ $index ] ) ? Config::get('email@contacts/to') : $arr[ $index ];
	}
	
	/**
	 * @see parent::getTo()
	 */
	protected function getSubject()
	{
		return 'Обратная связь';
	}
	
	/**
	 * @see parent::getMessage()
	 */
	protected function getMessage()
	{
		return $this->includeLayout('contact.html');
	}
	
}
