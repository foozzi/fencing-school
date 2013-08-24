<?

/**
 * The Paginator helper class.
 * 
 * @author Yarick.
 * @version 1.0
 */
class Paginator
{
	
	public $Current = 0;
	public $Limit = 10;
	public $Pages = 0;
	public $Size = 0;
	
	public $Step = 0;
	public $First = null;
	public $Last = null;
	
	private $PagesLimit	= 5;		// Better use odd values.

	/**
	 * The class constructor.
	 * 
	 * @access public
	 * @param mixed $data The array with items or count of items.
	 * @param int $limit The limit.
	 * @param int $page The current page.
	 */
	public function __construct( $data, $limit = null, $page = null )
	{
		if ( is_array( $data ) )
		{
			$this->Size = count( $data );
		}
		else
		{
			$this->Size = abs( intval( $data ) );
		}
		$this->Limit = abs( intval( $limit ) );
		if ( !$this->Limit )
		{
			$this->Limit = 10;
		}
		$this->Pages = ceil( $this->Size / $this->Limit );
		$this->Current = abs( intval( $page ) );
		if ( $this->Current > $this->Pages || !$this->Current )
		{
			$this->Current = 1;
		}
		$this->Step = floor( $this->PagesLimit / 2 );
		if ( $this->Current - $this->Step - 1 > 0 )
		{
			$this->First = 1;
		}
		if ( $this->Current + $this->Step + 1 < $this->Pages )
		{
			$this->Last = $this->Pages;
		}
	}
	
	/**
	 * The function returns array of pages.
	 * 
	 * @access public
	 * @return array The array of pages.
	 */
	public function getPages()
	{
		if ( $this->Pages <= 1 )
		{
			return array();
		}
		if ( $this->Pages > $this->PagesLimit )
		{
			$begin = $this->Current - $this->Step;
			if ( $begin + $this->PagesLimit >= $this->Pages )
			{
				$begin = $this->Pages - $this->PagesLimit;
			}
			if ( $begin < 1 )
			{
				$begin = 1;
			}
			$end = $begin + $this->PagesLimit;
			if ( $end < $this->Pages )
			{
				$end--;
			}
			else if ( $begin != 1 )
			{
				$begin++;
			}
			return range( $begin, $end );
		}
		return range( 1, $this->Pages );
	}
	
	/**
	 * The function returns TRUE if more than one pages exist.
	 * 
	 * @access public
	 * @return bool TRUE if has pages, otherwise FALSE.
	 */
	public function hasPages()
	{
		return $this->Pages > 1;
	}
	
	/**
	 * The function returns previous page. If infinity is TRUE and no previous page returns last page.
	 * 
	 * @access public
	 * @param bool $infinity Defines behaviour.
	 * @return int The previous page.
	 */
	public function getPrev( $infinity = false )
	{
		if ( $this->Pages < 2 )
		{
			return 0;
		}
		$page = $this->Current - 1;
		if ( $page <= 0 )
		{
			$page = $infinity ? $this->Pages : 0;
		}
		return $page;
	}
	
	/**
	 * The function returns next page. If infinity is TRUE and no next page returns first page.
	 * 
	 * @access public
	 * @param bool $infinity Defines behaviour.
	 * @return int The next page.
	 */
	public function getNext( $infinity = false )
	{
		if ( $this->Pages < 2 )
		{
			return 0;
		}
		$page = $this->Current + 1;
		if ( $page > $this->Pages )
		{
			$page = $infinity ? 1 : 0;
		}		
		return $page;
	}

	public function hasSpacePrev()
	{
		return $this->First && $this->First <= $this->Current - $this->Step;
	}

	public function hasSpaceNext()
	{
		return $this->Last && $this->Last >= $this->Current + $this->Step;
	}
	
	/**
	 * The function returns strin represention of object.
	 * 
	 * @access public
	 * @return string The object string.
	 */
	public function __toString()
	{
		return $this->Current.' / '.$this->Pages;
	}
	
}