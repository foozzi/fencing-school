<?

/**
 * The Comments controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Comments extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Comment';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Комментарии';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$this->addBreadcrumb( new Breadcrumb( $this ) );
		$Comment = new Comment();
		$params = array();
		$params[] = 'IsApproved = '.( Request::get('t') ? 2 : 1 );
		$this->getView()->set( 'Comments', $Comment->findList( $params, 'PostedAt desc, Id desc' ) );
		return $this->getView()->render();
	}

	public function approve( $id = null )
	{
		$error = array();
		$Comment = new Comment();
		$Comment = $Comment->findItem( array( 'Id = '.$id ) );
		if ( !$Comment->Id )
		{
			return $this->halt();
		}
		if ( isset( $_POST['submit'] ) )
		{
			$Comment->setPost($_POST);
			$Comment->approve();
			return $this->halt('', true);
		}
		$this->addBreadcrumb( new Breadcrumb( $this ) );
		$this->addBreadcrumb( new Breadcrumb( $Comment ) );
		$this->getView()->set('Comment', $Comment);
		$this->getView()->set('Error', $error);
		return $this->getView()->render();
	}
	
	public function answer( $id = null )
	{
		$error = array();
		$Comment = new Comment();
		$Answer = new Comment();
		$Comment = $Comment->findItem( array( 'Id = '.$id ) );
		if ( !$Comment->Id )
		{
			return $this->halt();
		}
		if ( isset( $_POST['submit'] ) )
		{
			$Comment->approve();
			$Answer = new Comment();
			$Answer->ParentId = $Comment->Id;
			$Answer->setPost( $_POST );
			$Answer->Author = $this->getUser()->Login;
			$Answer->save();
			return $this->halt('', true);
		}
		$this->addBreadcrumb( new Breadcrumb( $this ) );
		$this->addBreadcrumb( new Breadcrumb( $Comment ) );
		$this->getView()->set('Comment', $Comment);
		$this->getView()->set('Answer', $Answer);
		$this->getView()->set('Error', $error);
		return $this->getView()->render();
	}
	
}
