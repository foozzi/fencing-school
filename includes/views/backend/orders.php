<?

/**
 * The Orders view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Orders extends View_Backend
{
	
	public function htmlOrder( Order $Order )
	{
		return $this->includeLayout('view/orders/view.html', array( 'Order' => $Order ));
	}
	
	protected function htmlFilter( $pos = 'header' )
	{
		return $this->includeLayout('view/orders/filter.html', array('pos' => $pos));
	}

	public function index()
	{
		return $this->includeLayout('view/orders/index.html');
	}

	public function view()
	{
		return $this->includeLayout('view/orders/view.html');
	}

	public function invoice()
	{
		return $this->includeLayout('view/orders/invoice.html');
	}

	public function stats()
	{
		return $this->includeLayout('view/orders/stats.html');
	}

}
