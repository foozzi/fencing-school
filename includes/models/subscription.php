<?

/*

{INSTALL:SQL{
create table subscriptions(
	Id int not null auto_increment,
	Name varchar(50) not null,
	Email varchar(250) not null,

	primary key (Id),
	unique (Email)
) engine = MyISAM;

}}
*/

/**
 * The Subscription model class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Subscription extends Object
{
	
	public $Id;
	public $Name;
	public $Email;	
	
	
	/**
	 * @see parent::getPrimary()
	 */
	protected function getPrimary()
	{
		return array('Id');
	}
	
	/**
	 * @see parent::getTableName()
	 */
	protected function getTableName()
	{
		return 'subscriptions';
	}
	
	/**
	 * The function returns hash for current Subscription.
	 * 
	 * @access public
	 * @return string The hash.
	 */
	public function getHash()
	{
		return substr( md5( $this->Email.':'.$this->Id ), 0, 10 );
	}
	
	/**
	 * The function returns code for using in links to unsubscribe.
	 * 
	 * @access public
	 * @return string The code.
	 */
	public function getCode()
	{
		return trim( base64_encode( $this->Email.':'.$this->getHash() ), '=' );
	}
	
	/**
	 * The function finds Subscription by code.
	 * 
	 * @access public
	 * @param string $code The code.
	 * @return object The Subscription.
	 */
	public function findCode( $code )
	{
		$code = base64_decode( $code );
		if ( !$code )
		{
			return new self();
		}		
		$arr = explode( ':', $code );		
		if ( count( $arr ) != 2 )
		{
			return new self();
		}		
		$Item = $this->findItem( array( 'Email = '.$arr[0] ) );
		if ( $Item->getHash() == $arr[1] )
		{			
			return $Item;
		}
		return new self();
	}
	
	/**
	 * The function returns array of subscribers emails.
	 * 
	 * @static
	 * @access public
	 * @param int $limit The limit of result.
	 * @returns array The emails.
	 */
	public static function getEmails( $limit = 100 )
	{
		$result = array();
		$Item = new self();
		foreach ( $Item->findList( array(), 'Email asc', 0, $limit ) as $Item )
		{
			$result[] = $Item->Email;
		}
		return $result;
	}
	
	/**
	 * The function total count of all subscribers.
	 * 
	 * @static
	 * @access public
	 * @return int The count.
	 */
	public static function getCount()
	{
		$Item = new self();
		return $Item->findSize();
	}
	
}
