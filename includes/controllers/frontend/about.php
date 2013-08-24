<?

/**
 * The About company controller class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Controller_Frontend_About extends Controller_Frontend
{
	
	/**
	 * @see parent::getName()
	 */
	public function getName()
	{
		return 'Компания';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Client = new Client();
		$this->getView()->set( 'Clients', $Client->findList( array(), 'Position asc' ) );
		$this->getView()->bodyId='about';
		return $this->getView()->render();
	}

	public function noMethod()
	{
		return $this->halt();
	}
	
	public function collaboration()
	{
		$response = array( 'result' => 0 );
		if ( Request::get('Name') && 
			 Request::get('Phone') && 
			 Request::get('Address') && 
			 Request::get('Area') && 
			 Request::get('Face') && 
			 Request::get('Mobile') && 
			 Request::get('Position') )
		{
			$spam = Request::get('Confirm')? true: false;
			$Email = new Email_Collaborative($spam);
			if ( $Email->send() )
			{
				$response['result'] = 1;
				$response['msg'] = 'Спасибо за сотрудничество. <br /> В ближайшее время наш менеджер свяжется с Вами.';
				$response['timeout'] = 4000;
				$response['callback'] = 'close';
			}
			else
			{
				$response['msg'] = 'Ошибка при отправлении запроса';
			}
		}
		else
		{
			$response['msg'] = 'Заполните все обязательные поля';
		}
		return $this->outputJSON( $response );
	}
	
}
