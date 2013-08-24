<?

/**
 * The Payments view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Payments extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/payments/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/payments/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/payments/form.html');
	}

}
