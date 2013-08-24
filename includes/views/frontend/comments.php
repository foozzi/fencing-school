<?

/**
 * The Comments iew class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Frontend_Comments extends View_Frontend
{

	public function approve()
	{
		return $this->includeLayout('view/comments/approve.html');
	}	

}
