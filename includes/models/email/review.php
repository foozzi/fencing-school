<?

/**
 * The Review Email class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Email_Review extends Email
{
	
	/**
	 * @see parent::getFrom()
	 */
	protected function getFrom()
	{
		return Request::get('Email', Config::get('email@from'));
	}
	
	/**
	 * @see parent::getTo()
	 */
	protected function getTo()
	{
		return Config::get('email@contacts/to');
	}
	
	/**
	 * @see parent::getTo()
	 */
	protected function getSubject()
	{
		return 'Отзыв в книге на сайте '.Runtime::get('HTTP_HOST');
	}
	
	/**
	 * @see parent::getMessage()
	 */
	protected function getMessage()
	{
		return $this->includeLayout('review.html');
	}
	
}
