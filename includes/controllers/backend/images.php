<?

/**
 * The Images controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Images extends Controller_Backend_Standard
{
	
	/**
	 * @see parent::getModelName()
	 */
	protected function getModelName( $method = null )
	{
		return 'Content_Image';
	}
	
	/**
	 * @see parent::getAliasName()
	 */
	protected function getAliasName( $method = null )
	{
		return 'Image';
	}
	
	/**
	 * @see parent::getTitle()
	 */
	public function getTitle()
	{
		return 'Изображения страниц';
	}
	
	/**
	 * The index handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function index()
	{
		$Image = new Content_Image();
		$this->getView()->set( 'Images', $Image->findList( array(), 'Name asc' ) );
		return $this->getView()->render();
	}
	
	/**
	 * The function uploads Benefit examples.
	 * 
	 * @access public
	 * @param int $id The Benefit id.
	 * @return string The JSON response.
	 */
	public function upload( $id = null )
	{
		$response = array( 'result' => 0 );
		if ( isset( $_POST['upload'] ) )
		{
			$Benefit = new Benefit();
			$Benefit = $Benefit->findItem( array( 'Id = '.$id ) );
			if ( $Benefit->Id )
			{
				if ( empty( $_FILES['example']['tmp_name'] ) )
				{
					$response['msg'] = 'Выберите файл';
				}
				else
				{
					$Example = new Benefit_Example();
					$Example->BenefitId = $Benefit->Id;
					if ( $Example->save() )
					{
						if ( File::upload( $Example, $_FILES['example'] ) )
						{
							$Example->save();
							$response['result'] = 1;
							$response['html'] = $this->getView()->htmlExamples( $Benefit );
						}
						else
						{
							$response['msg'] = 'Не удалось загрузить файл';
						}
					}
					else
					{
						$response['msg'] = 'Ошибка записи данных';
					}
				}
			}
			else
			{
				$response['msg'] = 'Продукция не найдена';
			}
		}
		return $this->outputJSON( $response );
	}
	
	/**
	 * The function deletes Benefit Example.
	 * 
	 * @access public
	 * @param int $id The Example id.
	 * @return string The JSON response.
	 */
	public function delex( $id = null )
	{
		$response = array( 'result' => 0 );
		$Example = new Benefit_Example();
		$Example = $Example->findItem( array( 'Id = '.$id ) );
		if ( $Example->Id )
		{
			$Example->drop();
			$response['result'] = 1;
		}
		else
		{
			$response['msg'] = 'Пример не найден';
		}
		return $this->outputJSON( $response );
	}

}
