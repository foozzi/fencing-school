<?

/**
 * The Product Auto view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Product_Auto extends View_Backend
{

	public function htmlDataList( array $Models )
	{
		return $this->includeLayout('view/product/auto/list.html', array('Models' => $Models));
	}
	
	public function index()
	{
		return $this->includeLayout('view/product/auto/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/product/auto/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/product/auto/form.html');
	}

	public function engine()
	{
		return $this->includeLayout('view/product/auto/engine.html');
	}

	public function model()
	{
		return $this->includeLayout('view/product/auto/model.html');
	}

	public function wheels()
	{
		return $this->includeLayout('view/product/auto/wheels.html');
	}

	public function editw()
	{
		return $this->includeLayout('view/product/auto/wheel.html');
	}

}
