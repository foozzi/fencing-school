<?

class Breadcrumb
{

	public $Link;
	public $Name;

	public function __construct( $Object, $link = null )
	{
		if ( $Object instanceof Controller )
		{
			$this->Name = $Object->getTitle();
			$this->Link = $Object->getLink();
		}
		else if ( property_exists( $Object, 'Name' ) )
		{
			$this->Name = $Object->Name;
		}
		else if ( $Object instanceof Comment )
		{
			$this->Name = $Object->getType().' - '.$Object->Author;
		}
		else if ( is_string( $Object ) )
		{
			$this->Name = $Object;
		}
		if ( $link )
		{
			$this->Link = $link;
		}
	}

}
