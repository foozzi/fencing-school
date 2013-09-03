<?
/*

{INSTALL:SQL{
create table carousel(
	Id int not null auto_increment,
	Path varchar(200) not null,
	Url varchar(200) not null,
    Description varchar(800) not null,

	PostedAt int not null,

	primary key (Id),
	index (PostedAt)
) engine = MyISAM;

}}
*/

/**
 * The Carousel image parser model class.
 * 
 * @author foozzi.
 * @version 0.1
 */

class Carousel extends Object
{

	public $Id,
		   $Url,
		   $Path,
		   $Description,
		   $PostedAt;

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
		return 'carousel';
	}

}
