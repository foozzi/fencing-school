<?

/**
 * The Product Margins view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Product_Margins extends View_Backend_Product_Car
{
	
	public function index()
	{
		return $this->includeLayout('view/product/margins/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/product/margins/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/product/margins/form.html');
	}

}
