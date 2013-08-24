<?

require_once INCLUDE_HELPERS_DIR.'/hybridauth/Hybrid/Auth.php';

class Auth 
{
	public $provider;	
	
	public function __construct( $provider )
	{		
		$this->provider = $provider;
	}
	
	public function login()
	{		
		$config = INCLUDE_HELPERS_DIR.'/hybridauth/config.php';		
		$hybridauth = new Hybrid_Auth( $config );
		
		$adapter = $hybridauth->authenticate( $this->provider );
		
		return $adapter->getUserProfile();
	}		
	
	public static function callback()
	{		
		require_once INCLUDE_HELPERS_DIR."/hybridauth/Hybrid/Endpoint.php"; 

		Hybrid_Endpoint::process();
	}
	
}


