<?

/**
 * The Comment Email class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Email_Comment extends Email
{
	
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
		return Request::get('Email');
	}
	
	/**
	 * @see parent::getTo()
	 */
	protected function getSubject()
	{
		return $this->getObject()->getType().' на сайте '.Runtime::get('HTTP_HOST');
	}
	
	/**
	 * @see parent::getMessage()
	 */
	protected function getMessage()
	{
		return $this->includeLayout('comment.html');
	}
	
}
