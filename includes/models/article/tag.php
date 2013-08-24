<?

/*

{INSTALL:SQL{
create table articles_tags(
	ArticleId int not null,
	TagId int not null,

	primary key (ArticleId, TagId)
) engine = MyISAM;

}}
*/

/**
 * The Article Tag connection model.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Article_Tag extends Object
{
	
	public $ArticleId;
	public $TagId;
	
	/**
	 * @see parent::getPrimary()
	 */
	protected function getPrimary()
	{
		return array('ArticleId', 'TagId');
	}
	
	/**
	 * @see parent::getTableName()
	 */
	protected function getTableName()
	{
		return 'articles_tags';
	}
	
	/**
	 * The function returns Tag for current connection.
	 * 
	 * @access public
	 * @return object The Tag.
	 */
	public function getTag()
	{
		$Tag = new Tag();
		return $Tag->findItem( array( 'Id = '.$Tag->Id ) );
	}
	
}
