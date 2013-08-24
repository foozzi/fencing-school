<?php

/**
 * The Newsletter class.
 * 
 * @author Igor.
 * @version 0.1
 */
Class Email_Newsletter extends Email
{
	public $news = null;		
	
	public function __construct( Object $Subscription = null, array $news = array() )
	{
		parent::__construct( $Subscription );
		$this->news = $news;
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
		return $this->getObject()->Email;		
	}
	
	/**
	 * @see parent::getTo()
	 */
	protected function getSubject()
	{
		return 'Новости за последнюю неделю от '.Runtime::get('HTTP_HOST');
	}
	
	/**
	 * @see parent::getMessage()
	 */
	protected function getMessage()
	{		
		return $this->includeLayout( 'newsletter.html' );
	}
	
}