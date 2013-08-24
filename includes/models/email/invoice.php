<?

/**
 * The Invoice Email class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Email_Invoice extends Email
{
	
	/**
	 * @see parent::getFrom()
	 */
	protected function getFrom()
	{
		if ( Error::check( $this->getObject()->getAddress()->Email, 'email' ) )
		{
			return $this->getObject()->getAddress()->Email;
		}
		return Config::get('email@from');
	}
	
	/**
	 * @see parent::getTo()
	 */
	protected function getTo()
	{
		return Config::get('email@order/to');
	}
	
	/**
	 * @see parent::getTo()
	 */
	protected function getSubject()
	{
		return 'СФ #'.$this->getObject()->id();
	}
	
	/**
	 * @see parent::getMessage()
	 */
	public function getMessage()
	{
		URL::absolute(true);
		$html = $this->includeLayout('order.invoice.html');
		URL::absolute(false);
		return $html;
	}
	
	/**
	 * @see parent::send()
	 */
	public function send()
	{
		$Order = $this->getObject();
		if ( $Order->IsFile )
		{
			$this->attach( File::path( $Order ), $Order->Filename );
		}
		return parent::send();
	}
	
}
