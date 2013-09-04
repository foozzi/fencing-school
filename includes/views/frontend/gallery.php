<?

/**
 * The Gallery view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Frontend_Gallery extends View_Frontend
{
	
	public function htmlItems( array $Items, Paginator $Paginator = null )
	{
		if ( $Paginator === null )
		{
			$Paginator = $this->get('Paginator');
		}
		return $this->includeLayout('view/gallery/items.html', array('Items' => $Items, 'Paginator' => $Paginator));
	}
	
	public function index()
	{
		return $this->includeLayout('view/gallery/index.html');
	}
	
	public function view()
	{
		return $this->includeLayout('view/gallery/view.html');
	}

}
