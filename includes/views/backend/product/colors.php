<?

/**
 * The Product Colors view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Product_Colors extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/product/colors/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/product/colors/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/product/colors/form.html');
	}

}
