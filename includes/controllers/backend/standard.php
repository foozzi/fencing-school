<?

/**
 * The Standard abstract controller class.
 * 
 * @author Yarick
 * @version 0.1
 */
class Controller_Backend_Standard extends Controller_Backend
{
	
	/**
	 * @see parent::__construct()
	 */
	public function __construct()
	{
		parent::__construct();
		$this->loadLocale();
		$this->addBreadcrumb( $this );
	}

	protected function loadLocale( $force = false )
	{
		Locale::load( 'modules/'.str_replace( 'Controller_Backend_', '', get_class( $this ) ), $force );
	}

	/**
	 * The function returns Model name for current controller.
	 * 
	 * @access protected
	 * @param string $method The method from which function is called.
	 * @return string The Model name.
	 */
	protected function getModelName( $method = null )
	{
		return null;
	}
	
	/**
	 * The function returns variable name for current controller which must be used in template.
	 * 
	 * @access protected
	 * @param string $method The method from which function is called.
	 * @return string The Model name in template.
	 */
	protected function getAliasName( $method = null )
	{
		return $this->getModelName( $method );
	}
	
	/**
	 * The function returns Model object.
	 * 
	 * @access private
	 * @param string $method The method from which function is called.
	 * @return object The Model object.
	 */
	protected function getModel( $method = null )
	{
		$name = $this->getModelName( $method );
		return new $name();
	}
	
	/**
	 * The function returns action attribute for file uploading.
	 * 
	 * @access protected
	 * @param object $Object The controller Object.
	 * @return bool TRUE if object must be deleted on failed file upload, otherwise FALSE.
	 */
	protected function dropOnFailedUpload( Object $Object )
	{
		return false;
	}
	
	/**
	 * The function halts controller.
	 * 
	 * @access protected
	 * @param object $Object The object.
	 * @param string $method The method: add | edit.
	 */
	protected function haltForm( Object $Object, $method = 'edit' )
	{
		return $this->halt('', true);
	}
	
	/**
	 * The callback function on object removing action.
	 * 
	 * @access protected
	 * @param object $Object The Object.
	 * @return array The response data.
	 */
	protected function onDelete( Object $Object )
	{
		return array();
	}
	
	/**
	 * The function shows edit form and saves data on submit.
	 * 
	 * @access protected
	 * @param object $Object The object.
	 * @return string The HTML code.
	 */
	protected function initForm( Object $Object, $method = 'edit' )
	{
		$error = array();
		if ( isset( $_POST['submit'] ) )
		{
			$Object->setPost( $_POST );
			$fields = Locale::translate( Error::test( $Object ) );
			if ( count( $fields ) )
			{
				$error[] = _t('Incorrect fields').': '.implode( ', ', $fields );
			} 
			else if ( $Object->save() )
			{
				if ( !empty( $_FILES['file']['tmp_name'] ) )
				{                                    
					if ( File::upload( $Object, $_FILES['file'] ) )
					{                                            
						$Object->save();
					}
					else if ( $this->dropOnFailedUpload( $Object ) )
					{
						$Object->drop();
					}
				}
				if ( !empty( $_POST['detach'] ) )
				{
					if ( File::detach( $Object ) )
					{
						$Object->save();
					}
				}
				return $this->haltForm( $Object, $method );
			}
			else
			{
				$error[] = _t('Database error').': '.$Object->getError();
			}
		}
		$name = $this->getAliasName( $method ) ? $this->getAliasName( $method ) : $this->getModelName( $method );
		$this->getView()->set( $name, $Object );
		$this->getView()->set( 'Error', $error );
		return $this->getView()->render();
	}
	
	/**
	 * The add handler.
	 * 
	 * @access public
	 * @return string The HTML code.
	 */
	public function add()
	{
		$this->addBreadcrumb( new Breadcrumb( _t('Adding '.strtolower( $this->getAliasName('add') )), _L( $this ).'/add' ) );
		return $this->initForm( $this->getModel('add'), 'add' );
	}
	
	/**
	 * The edit handler.
	 * 
	 * @access public
	 * @param int $id The Object id.
	 * @return string The HTML code.
	 */
	public function edit( $id = null )
	{
		$Object = $this->getModel('edit');
		$Object = $Object->findItem( array( 'Id = '.$id ) );
		if ( !$Object->Id )
		{
			$this->halt('', true);
		}
		$this->addBreadcrumb( new Breadcrumb( $Object, _L( $this ).'/edit/'.$Object->Id ) );
		return $this->initForm( $Object, 'edit' );
	}
	
	/**
	 * The delete handler.
	 * 
	 * @access public
	 * @param int $id The Object id.
	 * @return string The JSON response.
	 */
	public function delete( $id = null )
	{
		return $this->rawDelete( 'delete', $id );
	}
	
	/**
	 * The function deletes current Object Item.
	 *
	 * @access protected
	 * @param string $method The method.
	 * @param int $id The Object id.
	 * @return string The JSON response.
	 */
	protected function rawDelete( $method = 'delete', $id = null )
	{
		$response = array( 'result' => 0 );
		$Object = $this->getModel( $method );
		$Object = $Object->findItem( array( 'Id = '.$id ) );
		if ( $Object->Id )
		{
			if ( $Object->drop() )
			{
				$response['result'] = 1;
				$response = array_merge( $response, $this->onDelete( $Object ) );
			}
			else
			{
				$response['msg'] = 'Ошибка базы данных: '.$Object->getError();
			}
		}
		else
		{
			$response['msg'] = 'Элемент не найден';
		}
		return $this->outputJSON( $response );
	}
	
	/**
	 * The position handler.
	 * 
	 * @access public
	 * @return string The JSON response.
	 */
	public function pos()
	{
		return $this->rawPos('pos');
	}
	
	/**
	 * The function changes position for current Object.
	 *
	 * @access protected
	 * @param string $method The method.
	 * @return string The JSON response.
	 */
	protected function rawPos( $method = 'pos', $exit = true )
	{
		$response = array( 'result' => 0 );
		$Object = $this->getModel( $method );
		if ( !property_exists( $Object, 'Position' ) )
		{
			$response['msg'] = 'Поле позиции не определено в классе '.get_class( $Object );
		}
		else if ( isset( $_POST['items'] ) && is_array( $_POST['items'] ) )
		{
			foreach ( $_POST['items'] as $pos => $id )
			{
				$Object = $Object->findItem( array( 'Id = '.$id ) );
				if ( $Object->Id )
				{
					$Object->Position = $pos + 1;
					$Object->save();
				}
			}
			$response['result'] = 1;
		}
		return $this->outputJSON( $response, $exit );
	}
	
}
