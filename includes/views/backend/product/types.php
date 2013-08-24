<?

/**
 * The Product Types view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Product_Types extends View_Backend
{

	public function index()
	{
		return $this->includeLayout('view/product/types/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/product/types/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/product/types/form.html');
	}

}
