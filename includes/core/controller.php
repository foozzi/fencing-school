<?

/**
 * The base Controller class.
 * 
 * @abstract
 * @version 0.1
 */
abstract class Controller
{
	
	private $view = null;
	
	protected $defaultAccess = Access::NONE;
	
	/**
	 * No Method controller function.
	 * Passed arguments the first is method.
	 * 
	 * @abstract
	 * @access public
	 */
	abstract public function noMethod();
	
	/**
	 * No Access controller function.
	 * Passed arguments the first is method.
	 * 
	 * @abstract
	 * @access public
	 */
	abstract public function noAccess();
	
	/**
	 * The function returns TRUE if User has access, FALSE if not.
	 * 
	 * @access public
	 * @param string $method The method name.
	 * @return bool TRUE if has access, FALSE if not.
	 */
	abstract public function isAccess( $method = null );
	
	public function __construct()
	{
	}
	
	/**
	 * The function sets View for current controller.
	 * 
	 * @access public
	 * @param object $View The View object.
	 */
	public function setView( View $View = null )
	{
		if ( $View === null )
		{	
			$parent = get_class( $this );
			do
			{
				if ( $parent == 'Controller_Base' )
				{
					break;
				}
				$class = new $parent();
				$name = str_replace( 'Controller_', 'View_', $parent );
				if ( Autoload::exist( $name ) )
				{
					$View = $name;
					break;
				}
				$parent = get_parent_class( $class );
			} while ( $parent );
			if ( $View === null )
			{
				$this->setView( new View_Base() );
			}
			else
			{
				$this->setView( new $View() );
			}
		}
		else
		{
			$this->view = $View;
		}
		$this->view->setController( $this );
	}
	
	/**
	 * The function returns current View object for controller.
	 * 
	 * @access public
	 * @return object The View object.
	 */
	public function getView()
	{
		return $this->view;
	}
	
	/**
	 * The function returns TRUE if controller cannot be parsed from URL string but used only by router, e.g. Frontend
	 * controllers, otherwise returns FALSE.
	 * 
	 * @access public
	 * @return bool TRUE if controller is hidden, otherwise FALSE.
	 */
	public function isHidden()
	{
		return false;
	}
	
	/**
	 * The function prints JSON object out.
	 * 
	 * @access protected
	 * @param mixed $response The response data.
	 * @return string The JSON response.
	 */
	protected function outputJSON( $response, $exit = true )
	{
		$data = String::json_encode( $response );
		if ( $exit )
		{
			echo $data;
			exit;
		}
		return $data;
	}
	
	/**
	 * The function runs current method of controller with passed arguments.
	 * 
	 * @access public
	 * @param string $method The method.
	 * @param array $args The arguments.
	 * @param string $argsText The arguments in text format.
	 * @return mixed The result of current method.
	 */
	public function runMethod( $method, array $args, $argsText = '' )
	{
		foreach ( $args as $key => $value )
		{
			$args[ $key ] = "'".addslashes( $value )."'";
		}
		if ( !$this->isAccess( $method ) )
		{
			array_unshift( $args, "'noAccess'" );
			$method = 'noAccess';
		}
		else if ( !method_exists( $this, $method ) )
		{
			array_unshift( $args, "'".addslashes( $method )."'" );
			$method = 'noMethod';
		}
		$this->getView()->setMethod( $method );
		$string = $method.'('.implode( ', ', $args ).')';
		return eval('return $this->'.$string.';');
	}
	
	/**
	 * The function executes controller built on its name and arguments.
	 * 
	 * @static
	 * @access public
	 * @param string $className The controller class name.
	 * @param array $args The arguments array.
	 * @return string The result of executed controller.
	 */
	public static function executeController( $className, array $args = array() )
	{
		$Controller = null;
		$res = array('method' => null, 'args' => array(), 'argsText' => '');
		for ( $i = 0; $i < count( $args ); $i++ )
		{
			$arr = array_slice( $args, 0, count( $args ) - $i );
			$name = $className.'_'.implode( '_', $arr );
			if ( Autoload::exist( $name ) !== false )
			{
				$Controller = new $name();
				$Controller->setView();
				if ( $Controller->isHidden() )
				{
					continue;
				}
				$res = self::buildParams( $Controller, array_slice( $args, count( $args ) - $i ) );
				break;
			}
		}
		if ( !$Controller )
		{
			$Controller = new $className();
			$Controller->setView();
			$res = self::buildParams( $Controller, $args );
		}
		if ( method_exists( $Controller, 'beforeExecute' ) )
		{
			$Controller->beforeExecute();
		}
		return $Controller->runMethod( $res['method'], $res['args'], $res['argsText'] );
	}
	
	/**
	 * The function returns array of parsed method and arguments.
	 * 
	 * @static
	 * @access private
	 * @param object $Controller The Controller object.
	 * @param array $args The passed arguments.
	 * @return array The data.
	 */
	private static function buildParams( $Controller, $args )
	{
		$result = array('method' => null, 'args' => array(), 'argsText' => '');
		$method = '';
		if ( count( $args ) == 0 || !$args[0] )
		{
			$method = 'index';
		}
		else if ( is_numeric( $args[0] ) )
		{
			$method = 'noMethod';
		}
		else
		{
			$method = array_shift( $args );
		}
		$result['method'] = $method;
		$result['args'] = $args;
		foreach ( $args as $key => $value )
		{
			$args[ $key ] = "'".addslashes( $value )."'"; 
		}
		$result['argsText'] = implode(',', $args);
		return $result;
	}
	
	/**
	 * The function builds string for controller execution method.
	 * 
	 * @deprecated
	 * @static
	 * @access public
	 * @param object $Controller The Controller object.
	 * @param array $args The passed arguments.
	 * @return string The execution string.
	 */
	private static function buildEvalString( Controller $Controller, array $args = array() )
	{
		$method = '';
		if ( count( $args ) == 0 || !$args[0] )
		{
			$method = 'index';
		}
		else if ( is_numeric( $args[0] ) )
		{
			$method = 'noMethod';
		}
		else
		{
			$method = array_shift( $args );
		}
		foreach ( $args as $key => $value )
		{
			$args[ $key ] = "'".addslashes( $value )."'"; 
		}
		$txt = implode(',', $args);
		if ( $txt )
		{
			$txt = ','.$txt;
		}
		if ( !eval("return \$Controller->isAccess('$method'".$txt.");") )
		{
			$method = "'".addslashes( 'noAccess' )."'";
			array_unshift( $args, $method );
			$method = 'noAccess';
		}
		else if ( !method_exists( $Controller, $method ) )
		{
			$method = "'".addslashes( $method )."'";
			array_unshift( $args, $method );
			$method = 'noMethod';
		}
		$Controller->getView()->setMethod( $method );
		$string = $method.'('.implode( ', ', $args ).')';
		return 'return $Controller->'.$string.';';
	}
	
}
