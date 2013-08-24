<?

/**
 * The Order Email class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Email_Order extends Email
{
	
	private $user = null;
	public $status = null;

	public function __construct( Object $Object = null, $User = null, $Status = null )
	{
		parent::__construct( $Object );
		$this->user = $User;	
		$this->status = $Status;
	}
	
	/**
	 * @see parent::getFrom()
	 */
	protected function getFrom()
	{		
		return Config::get( 'email@from' );
	}
	
	/**
	 * @return url site
	 */
	protected function getUrl()
	{
		return Config::get('host');
	}


	/**
	 * @see parent::getTo()
	 */
	protected function getTo()
	{
		if ( $this->status )
		{
			return $this->getObject()->getAddress()->Email;
		}
		return $this->user ? Config::get( 'email@order/to' ) : $this->getObject()->getAddress()->Email;
	}
	
	/**
	 * @see parent::getTo()
	 */
	protected function getSubject()
	{
		if ( $this->status )
		{
			return 'Изменение статуса заказа на сайта ' . Config::get('host');
		}
		return 'Заказ с сайта ' . Config::get('host') . ' #' . $this->getObject()->id();
	}
	
	/**
	 * @see parent::getMessage()
	 */
	protected function getMessage()
	{
		URL::absolute( true );
		$layout = 'order.html';
		if ( !$this->user )
		{
			$layout = 'order.customer.html';
		}
		if ( $this->status )
		{
			$layout = 'order.status.html';
		}
		$html = $this->includeLayout( $layout );
		URL::absolute( false );
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
