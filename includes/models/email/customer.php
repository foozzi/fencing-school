<?

class Email_Customer extends Email
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
		return $this->getObject()->Login;
	}
	
	/**
	 * @see parent::getTo()
	 */
	protected function getSubject()
	{
		$Customer = $this->getObject();
		$result = $Customer->IsApproved && $Customer->RestoreCode ? 'Восстановление пароля на ' : 'Регистрации на ';
		$result .= Runtime::get('HTTP_HOST');
		return $result;
	}
	
	/**
	 * @see parent::getMessage()
	 */
	protected function getMessage()
	{
		$Customer = $this->getObject();
		$layout = $Customer->IsApproved && $Customer->RestoreCode ? 'customer.restore.html' : 'customer.signup.html';
		URL::absolute(true);
		$html = $this->includeLayout( $layout );
		URL::absolute(false);
		return $html;
	}
	
}