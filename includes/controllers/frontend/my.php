<?

class Controller_Frontend_My extends Controller_Frontend
{

	protected $defaultAccess = Access::READ;

	public function noAccess()
	{
		return $this->halt( 'login' );
	}

	public function isAccess( $method = null )
	{
		if ( in_array( $method, array('login', 'forgot', 'signup', 'approve', 'repeat', 'hauth') ) )
		{
			return true;
		}
		return $this->getUser()->Id > 0;
	}

	public function getName()
	{
		return 'Мой кабинет';
	}

	public function index()
	{
		$Order = new Order();
		$params = array( );
		$params[] = 'UserId = ' . $this->getUser()->Id;
		$this->getView()->set( 'Orders', $Order->findList( $params, 'PostedAt desc', 0, 5 ) );
		return $this->getView()->render();
	}
	
	public function hauth()
	{		
		require_once INCLUDE_HELPERS_DIR.'/hybridauth/index.php';
	}
	 
	public function login()
	{				
		$response = array('result' => 0);
		$this->getView()->set('Error', '');
		if ( Request::get('provider') )
		{					
			$provider = Request::get( 'provider' );
		
			$Auth = new Auth( $provider );
			$Profile = $Auth->login();						
			$Customer = new Customer();			

			if ( !$Profile->email )
			{
				$Login = $Profile->identifier . '@email.com';
			}				
			else
			{
				$Login = $Profile->email;
			}			
			
			$OAuth = $Customer->findItem( array( 'Login = ' . $Login ) );						
			if ( !$OAuth->Login )
			{										
				$Customer->Login = $Login;
				$Customer->Name = $Profile->firstName . ' ' . $Profile->lastName;
				$Customer->IsApproved = 1;
				
				$Customer->save();					
				$Customer->forceLogin();
				return $this->halt();
			}
			else
			{									
				$Customer->loginHybrid( $Login );				
				return $this->halt();
			}
					
			return $this->getView()->render();
		}

		if ( Request::get( 'Login' ) )
		{			
			$response = array( 'result' => 0 );
			$Customer = new Customer();
			if ( ( $Customer = $Customer->login( Request::get('Login'), Request::get('Password'), Request::get('Forever') ) ) !== false )
			{
				$url = Request::get('backUrl');
				if ( $url )
				{
					$url = base64_decode( $url );
				}				
				$response['result'] = 1;
				$response['close'] = 1;
				$response['url'] = $this->getLoginLink( $Customer );
				$response['msg'] = 'Вы успешно вошли в систему';
			}
			else
			{
				$Customer = new Customer();
				$Customer = $Customer->findItem( array( 'Login = '.Request::get('Login') ) );
				if ( $Customer->Id && !$Customer->IsApproved )
				{
					$response['msg'] = 'Аккаунт не активирован. На Ваш e-mail было направлено письмо с инструкциями.';
				}
				else
				{
					$response['msg'] = 'Неверная комбинация логина и пароля';
				}
			}
			if ( !$response['result'] )
			{
				$this->getView()->set('Error', $response['msg']);
			}
		}
		if ( Request::get('ajax') )
		{
			return $this->outputJSON( $response );
		}
		return $this->getView()->render();
	}

	public function logout()
	{
		$this->getUser()->logout();		
		return $this->halt(':'._L('Controller_Frontend'));
	}

	public function forgot( $code = null )
	{
		$response = array('result' => 0);
		$this->getView()->set('Error', '');

		$Customer = new Customer();
		if ( $code )
		{			
			$Customer = $Customer->findItem( array( 'RestoreCode = '.$code ) );
			if ( $Customer->Id && $Customer->RestoreAt > time() - Customer::RESTORE_TIME )
			{
				if ( Request::get('Password') )
				{
					$Customer->Password = Customer::pwd( Request::get('Password') );
					$Customer->RestoreAt = 0;
					$Customer->RestoreCode = '';
					$Customer->save();

					return $this->forceLogin( $Customer );
				}
			}
			else
			{
				$this->getView()->set('Error', 'Ссылка восстановления пароля просрочена');
			}
		}
		if ( Request::get( 'Login' ) )
		{			
			$response = array( 'result' => 0 );
			$Customer = $Customer->findItem( array( 'Login = '.Request::get('Login') ) );
			if ( $Customer->Id )
			{
				$response['result'] = 1;
				$response['msg'] = 'На Ваш эл. адрес отправлено письмо с инструкциями.';
				/*if ( Request::get('backUrl') )
				{
					$Customer->BackUrl = Request::get('backUrl');
				}*/
				if ( $Customer->IsApproved )
				{
					$Customer->restore();
					$Customer->save();
				}
				else
				{
					$Customer->makeVerifyCode();
					$Customer->PostedAt = time();
					$Customer->save();
				}
				$Email = new Email_Customer( $Customer );
				$Email->send();
			}
			else
			{
				$response['msg'] = 'Такой пользователь не зарегистрирован';
			}
			if ( !$response['result'] )
			{
				$this->getView->set('Error', $response['msg']);
			}
		}
		if ( Request::get('ajax') )
		{
			return $this->outputJSON( $response );
		}
		$this->getView()->set('Customer', $Customer);
		return $this->getView()->render();
	}
	
	public function signup()
	{		
		$response = array( 'result' => 0 );
		if ( Request::get('Login') )
		{
			$Customer = new Customer();
			$Customer->setPost( $_POST );	
			
			if ( Request::get('subscribe') )
			{
				$Subscription = new Subscription();
				if ( $Subscription->findSize( array( 'Email = '.Request::get('Login') ) ) )
				{					
					$response['msg'] = 'Данный E-mail уже есть в базе подписчиков';
				}
				else
				{
					$Subscription->Email = Request::get('Login');
					$Subscription->Name = Request::get('Name');
					$Subscription->save();					
				}
			}
			
			if ( Request::get('backUrl') )
			{
				$Customer->BackUrl = Request::get('backUrl');
			}
			$fields = Locale::translate( Error::test( $Customer ) );
			if ( Request::get( 'Password' ) != Request::get( 'PasswordC' ) || !Request::get( 'Password' ) )
			{
				$response['msg'] = 'Не совпадают пароль и его подтверждение';
			}
			else if ( $Customer->Login )
			{
				if ( $Customer->exist() )
				{
					$response['msg'] = 'Пользователем с указанным эл. адресом уже зарегистрирован.';
				}
				else
				{
					if ( count( $fields ) )
					{
						$response['msg'] = 'Заполнены не все поля';
						$response['fields'] = $fields;
					}
					else
					{
						$Customer->Password = Customer::pwd( $Customer->Password );		
						$Customer->Email = $Customer->Login;
						if ( $Customer->signup() )
						{
							$Email = new Email_Customer( $Customer );
							$Email->send();
							$response['result'] = 1;
							$response['msg'] = 'На Ваш эл. адрес отправлено сообщение со ссылкой подтверждения регистрации';
						}
						else
						{
							$response['msg'] = 'Ошибка базы данных данных';
						}
					}
				}
			}
		}
		else
		{
			if ( !Request::get('ajax') )
			{
				return $this->halt();
			}
		}
		return $this->outputJSON( $response );
	}

	public function approve( $code = null )
	{		
		$Customer = new Customer();
		$this->getView()->set('Error', '');
		if ( $code )
		{			
			$Customer = $Customer->findItem( array( 'VerifyCode = ' . $code ) );
			if ( $Customer->Id && ( $Customer->IsApproved || $Customer->approve() ) )
			{						
				return $this->forceLogin( $Customer );
			}
			else
			{				
				$this->getView()->set('Error', 'Код верификации истек.');
			}
		}
		else
		{			
			return $this->halt('login');
		}
		$this->getView()->set( 'Customer', $Customer );
		return $this->getView()->render();
	}

	private function getLoginLink( Customer $Customer )
	{		
		if ( $Customer->BackUrl )
		{			
			$url = base64_decode( $Customer->BackUrl );
			$Customer->BackUrl = '';
			$Customer->save();
			return $url;
		}
		return _L( $Customer && Cart::getCart()->hasItems() ? 'Controller_Frontend_Cart' : 'Controller_Frontend_My_Orders' );
	}

	private function forceLogin( Customer $Customer )
	{
		$Customer = $Customer->forceLogin();		
		return $this->halt( ':'.$this->getLoginLink( $Customer ) );
	}
	
	/**
	 * The function updates Item in Cart.
	 * 
	 * @access public
	 * @return string The JSON response.
	 */
	public function update()
	{
		if ( Request::get('ShippingId') )
		{
			return $this->order();
		}
		$Cart = Cart::getCart();
		$response = array( 'result' => 0 );

		$Tyre = new Car_Tyre();
		$Tyre = $Tyre->findItem( array( 'Id = '.Request::get('item', 0) ) );
		$Item = $Cart->getItem( $Tyre );
		if ( $Item )
		{
			$quantity = intval( Request::get('quantity') );
			if ( $quantity )
			{
				$Item->Quantity = $quantity;
				$response['quantity'] = $Item->Quantity;
			}
			else
			{
				if ( $Cart->dropItem( $Item ) )
				{
					$response['quantity'] = 0;
				}
				else
				{
					$response['quantity'] = $Item->Quantity;
				}
			}
			$Cart->save();
			$response['status']		= $this->getView()->htmlCartStatus();
			$response['count']		= $Cart->getItemsAmount();
			$response['amount']		= Price::show( $Item->getAmount() );
			$response['total']		= Price::show( $Cart->getTotal() );
			$response['grand']		= Price::show( $Cart->getGrandTotal() );
			$response['result']		= 1;
		}
		else
		{
			$response['msg'] = 'Товар не найден';
		}
		if ( !Request::get('ajax') )
		{
			return $this->halt();
		}
		return $this->outputJSON( $response );
	}
	
	
	/**
	 * The function posts order.
	 * 
	 * @access public
	 * @return string The JSON response.
	 */
	public function order()
	{
		$Cart = Cart::getCart();
		$Order = $Cart->getOrder();
		$Order->VAT = 0;
		
		return $this->postOrder( $Order, $Cart );
	}
	
		
	/**
	 * The function posts Order.
	 * 
	 * @access private
	 * @param object $Order The Order to save.
	 * @param object $Cart The Cart object to clear items.
	 * @return string The JSON response.
	 */
	private function postOrder( Order $Order, Cart $Cart = null )
	{
		$error = array();

		$Order->setPost( $_POST );

		$Shipping = new Shipping();
		$Shipping = $Shipping->findItem( array( 'Id = '.Request::get('ShippingId') ) );
		$Order->setShipping( $Shipping );
		$Order->UserId = $this->getUser()->Id;

		$Address = $Order->getAddress();
		if ( !$Address->Name || !$Address->Phone || !$Shipping->Id )
		{
			$error[] = 'Заполните все обязательные поля';
		}
		else if ( $Order->Total < 10 )
		{			
			$error[] = 'Минимальный заказ - 10 грн.';
		}		
		else if ( $Order->save() )
		{					
			if ( !empty( $_FILES['file']['tmp_name'] ) )
			{
				if ( File::upload( $Order, $_FILES['file'] ) )
				{
					$Order->save();
				}
			}
			if ( $Cart )
			{
				$Cart->clear();
				$Cart->save();
			}

			// send email to admin
			$Email = new Email_Order( $Order, true );
			$Email->send();

			// send email to customer
			$Email = new Email_Order( $Order );
			$Email->send();
		}
		else
		{					
			$error[] = 'Ошибка записи данных';
		}
		
		$response = array('result' => count( $error ) ? 0 : 1 );
		$response['posted'] = 1;
		$response['msg'] = implode( "\n", $error );
		if ( $response['result'] )
		{
			$response['msg'] = 'Ваш заказ принят и в ближайшее время будет обработан';
			$response['close'] = 1;
			$response['url'] = _L('Controller_Frontend');
			$response['timeout'] = 4000;
		}
		return $this->outputJSON( $response );
	}

}
