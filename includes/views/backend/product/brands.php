<?

/**
 * The Product Brands view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Product_Brands extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/product/brands/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/product/brands/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/product/brands/form.html');
	}

}
