<?

/**
 * The Custom order model.
 * 
 * @author Yarick
 * @version 0.5
 */
class Order_Custom
{
	
	public $Assembly;
	public $Brand;
	public $Color;
	public $Comment;
	public $Contact;
	public $Product;
	public $Model;
	public $Name;
	public $Unit;
	public $Goal;
	public $Frame;
	public $Design;
	public $Email;
	
	public function set( array $data = array() )
	{
		foreach ( $data as $key => $value )
		{
			if ( property_exists( $this, $key ) )
			{
				$this->$key = $value;
			}
		}
	}
	
	public function getUnit()
	{
		$Unit = new Product_Unit();
		return $Unit->findItem( array( 'Id = '.$this->Unit ) );
	}
	
	public function getModel()
	{
		$Model = new Product_Model();
		return $Model->findItem( array( 'Id = '.$this->Model ) );
	}
	
	public function getProduct()
	{
		$Product = new Product();
		return $Product->findItem( array( 'Id = '.$this->Product ) );
	}
	
	public function getColor()
	{
		$Image = new Product_Image();
		return $Image->findItem( array( 'Id = '.$this->Color ) );
	}
	
	/**
	 * The function returns current product Brand.
	 * 
	 * @access public
	 * @return object The Brand.
	 */
	public function getBrand()
	{
		$Brand = new Product_Brand();
		return $Brand->findItem( array( 'Id = '.$this->Brand ) );
	}
	
	public function getFrame()
	{
		if ( is_array( $this->Frame ) )
		{
		}
		else if ( $this->Frame )
		{
			$this->Frame = unserialize( $this->Frame );
		}
		else
		{
			$this->Frame = array();
		}
		return $this->Frame;
	}
	
	public function getDesign()
	{
		if ( is_array( $this->Design ) )
		{
		}
		else if ( $this->Design )
		{
			$this->Design = unserialize( $this->Design );
		}
		else
		{
			$this->Design = array();
		}
		return $this->Design;
	}
	
}
