<?

/**
 * The Product Categories view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Product_Categories extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/product/categories/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/product/categories/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/product/categories/form.html');
	}

}
