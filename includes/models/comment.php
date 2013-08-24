<?

/*

{INSTALL:SQL{
create table comments(
	Id int not null auto_increment,
	Type tinyint not null,
	Object varchar(50) not null,
	ObjectId int not null,
	ParentId int not null,
	Text text not null,
	Rank smallint not null,
	Author varchar(100) not null,
	Email varchar(100) not null,
	PostedAt int not null,
	IsApproved int not null,

	primary key (Id),
	index (Object, ObjectId),
	index (ParentId),
	index (PostedAt),
	index (IsApproved)
) engine = MyISAM;

}}
*/

/**
 * The Comment model class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Comment extends Object
{
	
	const COMMENT	= 1;
	const REVIEW	= 2;
	const ANSWER	= 3;


	public $Id;
	public $Type;
	public $Object;
	public $ObjectId;
	public $ParentId;
	
	public $Text;
	public $Rank;
	public $Author;
	public $Email;
	public $PostedAt;
	public $IsApproved;
	
	/**
	 * @see parent::getPrimary()
	 */
	protected function getPrimary()
	{
		return array('Id');
	}
	
	public function setPost( array $data = array() )
	{
		parent::setPost( $data );
		if ( isset( $data['PostedAt'] ) )
		{
			$this->PostedAt = Date::strtotime( $data['PostedAt'] );
		}
		if ( isset( $data['Answer'] ) && is_array( $data['Answer'] ) )
		{
			$this->answer = $data['Answer'];
		}
	}
	
	/**
	 * @see parent::getTableName()
	 */
	protected function getTableName()
	{
		return 'comments';
	}
	
	/**
	 * @see parent::getTestRules()
	 */
	public function getTestRules()
	{
		return array(
			'Text'			=> '/\S{2,}/',
		);
	}

	public function saveNew()
	{
		if ( !$this->Type )
		{
			$this->Type = self::COMMENT;
		}
		$this->PostedAt = time();
		return parent::saveNew();
	}

	public function drop()
	{
		if ( parent::drop() )
		{
			if ( $this->Id )
			{
				$Answer = new self();
				$Answer->dropList( array( 'ParentId = '.$this->Id ) );
			}
			return true;
		}
		return false;
	}

	public function approve()
	{
		if ( $this->Id && $this->IsApproved < 2 )
		{
			$this->IsApproved++;
			if ( $this->save() )
			{
				if ( $this->IsApproved == 2 )
				{
					$params = array();
					$params[] = 'Object = '.$this->Object;
					$params[] = 'ObjectId = '.$this->ObjectId;
					$params[] = 'IsApproved = 2';
					$query = 'select sum(Rank) as Rank, count(Id) as Count from comments where 1 '
						.$this->db()->sqlParams( $params );
					$arr = $this->db()->query( $query );
					if ( isset( $arr[0] ) )
					{
						$Object = $this->getObject();	
						$Object->Rank = $arr[0]['Rank'] / ( empty( $arr[0]['Count'] ) ? 1 : $arr[0]['Count'] );
						$Object->RankCount = $arr[0]['Count'];
						$Object->save();
						
						$Tyre = new Car_Tyre;
						$Tyre = $this->getObject();								
						$Tyre->Points = $Object->Rank + $Tyre->Points;													
						$Tyre->save();
					}
				}
				return true;
			}
		}
		return false;
	}

	public function getDate()
	{
		return date( 'd.m.y', $this->PostedAt );
	}

	private function getRawHash()
	{
		return substr( md5( $this->Id.':'.$this->Email.':'.$this->PostedAt.':'.$this->ObjectId ), $this->Id % 20, 10 );
	}

	public function getHash()
	{
		return trim( base64_encode( $this->Id.':'.$this->getRawHash() ), '=' );
	}

	public function getObject()
	{
		if ( $this->Object )
		{
			$name = $this->Object;
			$Object = new $name();
			return $Object->findItem( array( 'Id = '.$this->ObjectId ) );
		}
		return null;
	}

	public function hasAnswers()
	{
		if ( !$this->Id )
		{
			return false;
		}
		return $this->findSize( array( 'ParentId = '.$this->Id ) ) > 0;
	}

	public function getAnswers()
	{
		if ( !$this->Id )
		{
			return array();
		}
		return $this->findList( array( 'ParentId = '.$this->Id ), 'PostedAt asc, Id asc' );
	}

	public function getType()
	{
		$arr = self::getTypes();
		return isset( $arr[ $this->Type ] ) ? $arr[ $this->Type ] : null;
	}

	public static function getTypes()
	{
		return array(
			self::COMMENT 	=> 'Комментарий',
			self::REVIEW 	=> 'Отзыв',
			self::ANSWER 	=> 'Ответ',
		);
	}
	
	/**
	 * The function returns array of Comments attached to Object.
	 * 
	 * @static
	 * @access public
	 * @param object $Object The Object.
	 * @return array The array of Comments.
	 */
	public static function findComments( Object $Object, $approvedOnly = true, $offset = null, $limit = null )
	{
		$Comment = new self();
		$params = array();
		$params[] = 'Object = '.get_class( $Object );
		$params[] = 'ObjectId = '.$Object->Id;
		$params[] = 'ParentId = 0';
		if ( $approvedOnly )
		{
			$params[] = 'IsApproved = 2';
		}
		return $Comment->findList( $params, 'PostedAt desc, Id desc', $offset, $limit );
	}

	public static function post( Object $Object, array $data )
	{
		$Comment = new self();
		$Comment->setPost( $data );
		$Comment->Object = get_class( $Object );
		$Comment->ObjectId = $Object->Id;
		if ( $Comment->save() )
		{
			$Email = new Email_Comment( $Comment );
			$Email->send();
			return true;
		}
		return false;
	}
	
	public static function findHash( $hash )
	{
		$arr = explode( ':', base64_decode( $hash ) );
		$Comment = new self();
		$Comment = $Comment->findItem( array( 'Id = '.$arr[0] ) );
		if ( $arr[1] == $Comment->getRawHash() )
		{
			return $Comment;
		}
		return new self();
	}

}
