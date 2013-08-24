<?

/*

{INSTALL:SQL{
create table tags(
	Id int not null auto_increment,
	Name char(50) not null,

	primary key (Id),
	index (Name)
) engine = MyISAM;

}}
*/

/**
 * The Tag model class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Tag extends Object
{
	
	public $Id;
	public $Name;
	
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
		return 'tags';
	}
	
	/**
	 * @see parent::getTestRules()
	 */
	public function getTestRules()
	{
		return array(
			'Name'			=> '/\S{2,}/',
		);
	}
	
	/**
	 * The function returns search param for Tag.
	 * 
	 * @access public
	 * @param string $key The search key.
	 * @param mixed $value The search value.
	 * @return string The param string.
	 */
	public function getParam( $key, $value = null )	
	{
		switch ( $key )
		{
			case 'article':
				$value = intval( $value instanceof Article ? $value->Id : $value );
				return '* Id in (select TagId from articles_tags where ArticleId = '.$value.')';
				
			case 'search':
				return '* Name like '.$this->db()->quote( $value.'%' );
		}
		return null;
	}

	public static function getTags( $assoc = false )
	{
		$Tag = new self();
		$result = $Tag->findList( array(), 'Name asc' );
		return $assoc ? self::convertArray( $result, 'Id', 'Name' ) : $result;
	}

}
