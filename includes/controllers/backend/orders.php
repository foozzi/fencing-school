<?

/**
 * The Orders controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Orders extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Order';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Заказы';
	}

	public function getSort()
	{
		return 'PostedAt desc, Id desc';
	}

	public function getLimit()
	{
		return 20;
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$this->addBreadcrumb( new Breadcrumb( $this ) );
		$Order = new Order();
		$params = array();
		if ( Request::get('s') )
		{
			$params[] = 'Status = '.Request::get('s');
		}
		if ( Request::get('str') )
		{
			$params[] = $Order->getParam( 'search', Request::get('str') );
		}
		if ( Request::get('m') )
		{
			$params[] = $Order->getParam( 'month', Request::get('m') );
			if ( !Request::get('s') )
			{
				$params[] = 'Status <> '.Order::CANCELED;
			}
		}
		$Paginator = new Paginator( $Order->findSize( $params ), $this->getLimit(), $this->getPage() );
		$this->getView()->set( 'Paginator', $Paginator );
		$this->getView()->set( 'Orders', $Order->findList( $params, $this->getSort(), $this->getOffset(), $this->getLimit() ) );
		$params[] = 'Status <> '.Order::CANCELED;
		$this->getView()->set( 'Total', Order::getTotalSum( $params ) );
		return $this->getView()->render();
	}

	public function edit( $id = null )
	{		
		$response = array('result' => 1);
		$Order = new Order();
		$Order = $Order->findItem( array( 'Id = '.$id ) );		
        if ( $Order->Id )
		{
			if ( Request::get('ajax') )
			{
				$Order->PaidAt = 0;				
				$_POST['Address']['Email'] = $Order->getAddress()->Email;
				
				if ( $Order->Status != Request::get('Status') )
				{				
					$Send_Status = new Email_Order( $Order, null, Request::get('Status') );
					$Send_Status->send();
				}	
				
				$Order->setPost( $_POST );  				
				if ( !$Order->save() )
				{
					$response['error'] = $Order->getError();
				}													
				$response['html'] = $this->getView()->htmlOrder( $Order );
			}
		}
		return $this->outputJSON( $response );
	}
	
	/**
	 * The printed invoice of current order.
	 * 
	 * @access public
	 * @param int $id The Order id.
	 * @return string The HTML code.
	 */
	public function invoice( $id = null )
	{
		$Order = new Order();
		$Order = $Order->findItem( array( 'Id = '.intval( $id ) ) );
		if ( !$Order->Id )
		{
			return '';
		}
		$Email = new Email_Invoice( $Order );
		$this->getView()->set( 'Order', $Order );
		$this->getView()->set( 'Email', $Email );
		$this->getView()->setLayout('blank.html');
		return $this->getView()->render();
	}
	
	/**
	 * The detailed order view.
	 * 
	 * @access public
	 * @param int $id The Order id.
	 * @return string The HTML code.
	 */
	public function view( $id = null )
	{
		$Order = new Order();
		$Order = $Order->findItem( array( 'Id = '.intval( $id ) ) );
		if ( !$Order->Id )
		{
			return '';
		}
		if ( Request::get('ajax') )
		{
			return $this->getView()->htmlOrder( $Order );
		}
		$this->getView()->set( 'Order', $Order );
		return $this->getView()->render();
	}
	
	/**
	 * The order cancelation handler.
	 * 
	 * @access public
	 * @param int $id The Order id.
	 * @return string The JSON response.
	 */
	public function cancel( $id = null )
	{
		$response = array('result' => 0);
		$Order = new Order();
		$Order = $Order->findItem( array( 'Id = '.$id ) );
		if ( $Order->Id )
		{
			if ( $Order->Status < Order::SHIPPED )
			{
				$Order->Status = Order::CANCELED;
				$Order->save();
				$response['result'] = 1;
				$response['status'] = $Order->getStatus();
				$response['statusText'] = $Order->getStatus(true);
				$response['hidePayment'] = true;
			}
			else
			{
				$response['msg'] = _t('Order cant be canceled');
			}
		}
		else
		{
			$response['msg'] = _t('Order not found');
		}
		return $this->outputJSON( $response );
	}

	public function status( $id = null )
	{
		$response = array('result' => 0);
		$Order = new Order();
		$Order = $Order->findItem( array( 'Id = '.$id ) );
		if ( $Order->Id )
		{
			$Order->Status = Request::get('status');
			if ( $Order->save() )
			{
				$response['result'] = 1;
				$response['status'] = $Order->getStatus();
				$response['statusText'] = $Order->getStatus(true);
			}
			else
			{
				$response['msg'] = _t('Database error');
			}
		}
		else
		{
			$response['msg'] = _t('Order not found');
		}
		return $this->outputJSON( $response );
	}

}
