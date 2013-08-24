<?

class Controller_Frontend_My_Profile extends Controller_Frontend_My
{
	
	public function __construct()
	{
		parent::__construct();
		$this->setView( new View_Frontend_My_Profile() );
	}
	
	public function getName()
	{
		return 'Мой кабинет - профиль';
	}
	
	public function index()
	{
		if ( Request::get('ajax') )
		{
			$User = $this->getUser();
			$response = array('result' => 0);
			if ( isset( $_POST['Address'] ) )
			{
				$pwd = Request::get('Password');
				if ( !$pwd )
				{
					unset( $_POST['Password'] );
				}
				$User->setPost( $_POST );
				
				if ( $pwd )
				{
					$User->Password = Customer::pwd( $pwd );
				}
				if ( isset( $_POST['subs-check'] ) )
				{
					$Subscription = new Subscription();
					if ( $Subscription->findSize( array( 'Email = '.Request::get('Login') ) ) )
					{					
						$response['msg'] = 'Данный E-mail уже есть в базе подписчиков';
					}
					else
					{
						$Subscription->Email = Request::get('Email');
						$Subscription->Name = Request::get('Name');
						$Subscription->save();					
					}
				}
					$User->save();
					$response['result'] = 1;
					$response['msg'] = 'Ваш профиль сохранен';
					$response['url'] = _L('Controller_Frontend_My');				
			}
			return $this->outputJSON( $response );
		}

		$Subs = new Subscription();
		$Subs = $Subs->FindItem( array( 'Email = ' . $this->getUser()->Login ) );		
		
		$this->getView()->set( array( 'Subs' => $Subs ) );

		return $this->getView()->render();
	}
	
	public function checkSubs( $email = null )
	{
		$Subs = new Subscription();
		
		$params = array();
		$params[] = 'Email = ' . $email;
		
		$Subs = $Subs->findItem( $params ); 
		
		if ( count( $Subs ) )
		{
			return true;
		}
		
		return false;
	}
	
	public static function htmlShort()
	{					
		$Self = new self();
		return $Self->getView()->htmlShort();
	}	

}