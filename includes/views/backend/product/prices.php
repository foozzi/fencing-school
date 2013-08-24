<?

/**
 * The Product Prices view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Product_Prices extends View_Backend
{
	
	public function htmlLog( Pricelist $Price )
	{
		return $this->includeLayout('view/product/prices/log.html', array( 'Price' => $Price ));
	}

	public function index()
	{
		return $this->includeLayout('view/product/prices/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/product/prices/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/product/prices/form.html');
	}

	public function load()
	{
		return $this->includeLayout('view/product/prices/load.html');
	}

}
