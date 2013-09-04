<?

/**
 * The Galleries view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Galleries extends View_Backend
{
	
	public function index()
	{
		return $this->includeLayout('view/galleries/index.html');
	}

	public function add()
	{
		return $this->includeLayout('view/galleries/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/galleries/form.html');
	}

	public function items()
	{
		return $this->includeLayout('view/galleries/items.html');
	}

	public function addi()
	{
		return $this->includeLayout('view/galleries/item.html');
	}

	public function editi()
	{
		return $this->includeLayout('view/galleries/item.html');
	}

	public function upload()
	{
		return $this->includeLayout('view/galleries/upload.html');
	}

}
