<?

/**
 * The Product Materials view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Product_Materials extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/product/materials/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/product/materials/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/product/materials/form.html');
	}

}
