<?

/**
 * The Product Brands view class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class View_Backend_Product_Tyres extends View_Backend_Product_Car
{

	protected function getPriceRanges()
	{
		$result = array();
		$Tyre = new Car_Tyre();
		foreach ( $Tyre->findList( array(), 'Price desc', 0, 1 ) as $Tyre );
		$max = $Tyre->Price;
		for ( $i = 0; $i < 5; $i++ )
		{
			$a = round( $i * $max / 5 );
			$b = round( ( $i + 1 ) * $max / 5 );
			$b = $b > $max ? round( $max ) : $b;
			$result[ $a.'-'.$b ] = $a.' - '.$b;
		}
		return $result;
	}

	protected function getFilters()
	{
		$result = array(
			'noimage'		=> 'Без картинки',
			'online'		=> 'В наличии',
			'offline'		=> 'Нет в наличии',
		);
		return $result;
	}
	
	public function htmlPictures( Car_Tyre $Tyre, array $exact, array $rough )
	{
		return $this->includeLayout('view/product/tyres/pictures.html', array('Tyre' => $Tyre, 'exact' => $exact, 'rough' => $rough));
	}
	
	public function htmlTyreSize( Car_Tyre $Tyre )
	{
		return $this->includeLayout('view/product/tyres/size.html', array('Tyre' => $Tyre));
	}

	public function htmlImages( Car_Tyre $Tyre )
	{
		return $this->includeLayout('view/product/tyres/images.html', array('Tyre' => $Tyre));
	}

	public function index()
	{
		return $this->includeLayout('view/product/tyres/index.html');
	}

	public function unique()
	{
		return $this->includeLayout('view/product/tyres/unique.html');
	}

	public function add()
	{
		return $this->includeLayout('view/product/tyres/form.html');
	}

	public function edit()
	{
		return $this->includeLayout('view/product/tyres/form.html');
	}

	public function addt()
	{
		return $this->includeLayout('view/product/tyres/add.html');
	}

	public function addw()
	{
		return $this->includeLayout('view/product/tyres/add.html');
	}

	public function adde()
	{
		return $this->includeLayout('view/product/tyres/add.html');
	}

}
