<?

/**
 * The Comments controller class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Controller_Frontend_Comments extends Controller_Frontend
{
	
	/**
	 * @see parent::getName()
	 */
	public function getName()
	{
		return 'Комментарии';
	}

	public function approve( $hash )
	{
		$Comment = Comment::findHash( $hash );
		if ( !$Comment->IsApproved )
		{
			$Comment->approve();
		}
		$this->getView()->set( 'Comment', $Comment );
		return $this->getView()->render();
	}
	
}
