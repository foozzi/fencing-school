<?

/**
 * The Articles view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Frontend_Articles extends View_Frontend
{
	
	protected function getArticleType()
	{
		return $this->getController()->getArticleType();
	}
	
	public function index()
	{
		return $this->includeLayout('view/articles/index.html');
	}

	public function view()
	{
		return $this->includeLayout('view/articles/view.html');
	}

	public function interview()
	{
		return $this->includeLayout( 'view/articles/interview.html' );
	}

	public function competention()
	{
		return $this->includeLayout( 'view/articles/competention.html' );
	}

}
