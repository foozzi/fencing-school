<?

class View_Frontend_My_Orders extends View_Frontend_My
{

	public function htmlShort( array $Orders )
	{
		return $this->includeLayout('view/my/orders/short.html', array('Orders' => $Orders));
	}
	
	public function htmlOrder( Order $Order )
	{
		return $this->includeLayout('view/my/orders/view.html', array('Order' => $Order));
	}
	
	public function index()
	{
		return $this->includeLayout('view/my/orders/index.html');
	}

}
