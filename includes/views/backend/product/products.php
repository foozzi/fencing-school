<?

/**
 * The Products view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Product_Products extends View_Backend
{
	
	public function htmlUpload( Product $Product )
	{
		return $this->includeLayout('view/product/products/upload.html', array('Product' => $Product));
	}
	
	public function index()
	{
		return $this->includeLayout('view/product/products/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/product/products/add.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/product/products/form.html');
	}

}
