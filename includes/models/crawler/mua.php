<?

class Crawler_Mua extends Crawler
{
	
	private $brands = array();

	private $pages = array();
	
	protected function URL()
	{
		return 'http://m.ua/';
	}

	protected function isCacheable()
	{
		return true;
	}

	protected function convertHTML( $html )
	{
		return mb_convert_encoding( $html, 'utf8', 'cp1251' );
	}

	protected function getFullPath( $link )
	{
		if ( preg_match( '/^\/(.*)/', $link, $res ) )
		{
			return $this->URL().$res[1];
		}
		else if ( preg_match( '/^'.preg_quote( $this->URL(), '/' ).'(.*)/', $link, $res ) )
		{
			return $this->URL().$res[1];
		}
		return $this->URL().$link;
	}
	
	public function crawlTyres( Car_Brand $Brand = null )
	{
		$url = $this->URL().'kata/337/';
		$dom = $this->getDom( $url );
		$this->pages = array( 0 => $url );
		$this->checkPages( $dom );
		return $this->pages;
	}

	private function downloadImage( $url, Car_Tyre $Tyre )
	{
		if ( substr( $url, 0, 1 ) == '/' )
		{
			$url = $this->URL().substr( $url, 1 );
		}
		$Image = new Car_Image();
		$Image->TyreId = $Tyre->Id;
		if ( $Image->save() )
		{
			$name = basename( $url );
			$file = $this->getCacheDir().'/img/'.$name;
			File::restore( $file );
			file_put_contents( $file, file_get_contents( $url ) );
			if ( File::upload( $Image, $file ) )
			{
				$Image->save();
				$Tyre->cacheImage();
			}
			return true;
		}
		return false;
	}

	private function getBrands( $type )
	{
		if ( !isset( $this->brands[ $type ] ) )
		{
			$this->brands[ $type ] = array();
			$Brand = new Car_Brand();
			foreach ( $Brand->findList( array( 'Type = '.$type ) ) as $Brand )
			{
				$this->brands[ $type ][ mb_strtoupper( $Brand->Name, 'utf-8' ) ] = $Brand->Id;
			}
		}
		return $this->brands[ $type ];
	}

	private function cutBrand( $string )
	{
		$brand = $this->getBrands( Car_Brand::TYRE );
		$arr = preg_split( '/\s+/', $string );
		for ( $j = count( $arr ) - 1; $j > 0; $j-- )
		{
			$try = array();
			for ( $z = 0; $z < $j; $z++ )
			{
				$try[] = $arr[ $z ];
			}
			$try = mb_strtoupper( implode( ' ', $try ), 'utf-8' );
			if ( isset( $brand[ $try ] ) )
			{
				$name = array();
				for ( $z = $j; $z < count( $arr ); $z++ )
				{
					$name[] = $arr[ $z ];
				}
				$name = implode( ' ', $name );
				return array( $name, $brand[ $try ] );
			}
		}
		return array( $string, 0 ); 
	}

	private function crawlTyreSizes( $html, array &$result )
	{
		if ( is_object( $html ) )
		{
			foreach ( $html->find('tr.conf-tr') as $tr )
			{
				$tds = $tr->find('td.conf-td');
				if ( count( $tds ) < 4 )
				{
					foreach ( $tr->find('td.conf-other a') as $a )
					{
						$this->crawlTyreSizes( $this->getURL( $a->jsource ), $result );
					}
				}
				else
				{
					$item = array();
					foreach ( $tds as $i => $td )
					{
						$key = $this->getProductKey( $i );
						if ( $key )
						{
							$item[ $key ] = $this->getProductValue( $key, $td );
						}
					}
					foreach ( $tr->find('td.conf-price-link-close') as $td )
					{
						if ( $td->id )
						{
							$item['RefId'] = str_replace( 'pr_c_', '', $td->id );
						}
						else if ( preg_match( '/idGood_=(\d+)/', $td->innertext, $res ) )
						{
							$item['RefId'] = $res[1];
						}
					}
					if ( !empty( $item['RefId'] ) )
					{
						$skipped = false;
						foreach ( $result as $a )
						{
							if ( $a['RefId'] == $item['RefId'] )
							{
								$skipped = true;
							}
						}
						if ( !$skipped )
						{
							$result[] = $item;
						}
					}
				}
			}
		}
		else
		{
			if ( mb_substr( $html, 0, 2 ) == '(\'' && mb_substr( $html, -2 ) == '\')' )
			{
				$data = mb_substr( $html, 2, mb_strlen( $html ) - 4 );
				$dom = $this->getDom( str_replace( '\\"', "'", str_replace( "\\'", '"', $data ) ) );
				foreach ( $dom->find('table.conf-table') as $table )
				{
					$this->crawlTyreSizes( $table, $result );
				}
			}
		}
	}
	
	public function crawlTyrePage( $url, array &$pages )
	{
		$dom = $this->getDom( $url );

		$brand = $this->getBrands( Car_Brand::TYRE );

		$this->checkPages( $dom );
		$pages = $this->pages;
		$result = array();
		foreach ( $dom->find('table#list') as $div )
		{
			foreach ( $div->find('tr.list-gr-tr') as $div )
			{
				$tyre = array('RefId' => str_replace( 'mr_', '', $div->id ), 'Name' => '', 'Brand' => '', 'Description' => '', 'Images' => array());

				$image = array();
				foreach ( $div->find('div.pictb') as $img )
				{
					if ( $img->onclick && preg_match( "/open_pg\('([^']+)'/", $img->onclick, $res ) )
					{
						$tyre['Images'][] = $res[1];
					}
				}
				foreach ( $div->find('div.dop-image-div div.i15-item') as $img )
				{
					if ( $img->onclick && preg_match( "/open_pg\('([^']+)'/", $img->onclick, $res ) )
					{
						$tyre['Images'][] = $res[1];
					}
				}
				foreach ( $div->find('div.list-model-title') as $a )
				{
					$tyre['Name'] = trim( $a->text() );
				}
				foreach ( $div->find('div.list-model-desc') as $a )
				{
					$tyre['Description'] = trim( $a->text() );
				}

				$arr = $this->cutBrand( $tyre['Name'] );
				$tyre['Name'] = $arr[0];
				$tyre['Brand'] = $arr[1];

				$items = array();
				foreach ( $div->find('table.conf-table') as $table )
				{
					$this->crawlTyreSizes( $table, $items );
				}
				$tyre['Children'] = $items;

				$result[] = $tyre;
			}
		}
		return $result;
	}
	
	public function clearPages()
	{
		$this->pages = array();
	}
	
	private function checkPages( $html )
	{
		$dom = null;
		$dom = is_object( $html ) ? $html : self::getDom( $html );
		foreach ( $dom->find('div[class="pager-nums"]') as $div )
		{
			foreach ( $div->find('a') as $a )
			{
				if ( !$a->class )
				{
					$page = null;
					if ( preg_match( '/&page_=([\d]+)/', $a->href, $res ) )
					{
						$page = $res[1];
					}
					else if ( preg_match( '/kata\/337\/([\d]+)/', $a->href, $res ) )
					{
						$page = $res[1];
					}
					if ( is_numeric( $page ) )
					{
						$page = intval( $page );
						$this->pages[ $page ] = $this->getFullPath( 'kata/337/'.( $page ? $page : '' ) );
					}
				}
			}
		}
	}
	
	private function getProductKeysMatrix()
	{
		return array(
			2	=> 'Diameter',
 			3	=> 'Width',
			4	=> 'Profile',
			//5	=> '',
			6	=> 'Season',
			7	=> 'Speed',
			8 	=> 'Weight',
			9	=> 'Price',
		);
	}
	
	private function getProductKey( $key )
	{
		$arr = $this->getProductKeysMatrix();
		if ( isset( $arr[ $key ] ) )
		{
			return $arr[ $key ];
		}
		return null;
	}
	
	private function getProductValue( $key, $value )
	{
		if ( in_array( $key, array('Width', 'Profile', 'Diameter', 'Weight') ) )
		{
			if ( is_object( $value ) )
			{
				foreach ( $value->find('> span') as $a )
				{
					return intval( $a->text() );
				}
			}
			return intval( $value );
		}
		if ( $key == 'Speed' )
		{
			if ( is_object( $value ) )
			{
				foreach ( $value->find('span') as $a )
				{
					return trim( preg_replace( '/\/.+/', '', $a->text() ) );
				}
			}
			return trim( preg_replace( '/\/.+/', '', $value ) );
		}
		if ( $key == 'Price' )
		{
			if ( is_object( $value ) )
			{
				foreach ( $value->find('span.price-int span') as $a )
				{
					return intval( preg_replace('/[^\d]+/', '', html_entity_decode( $a->text() )) );
				}
			}
			return intval( preg_replace('/[^\d]+/', '', html_entity_decode( $value ) ) );
		}
		if ( $key == 'Season' )
		{
			if ( is_object( $value ) )
			{
				foreach ( $value->find('> span') as $a )
				{
					$value = preg_replace("/(^\s+)|(\s+$)/us", '', ( html_entity_decode( $a->text() ) ));
				}
			}
			if ( $value == 'зимняя' )
			{
				return Car_Tyre::WINTER;
			}
			else if ( $value == 'летняя' )
			{
				return Car_Tyre::SUMMER;
			}
			return Car_Tyre::ANY;
		}
		return null;
	}
	
	private static function checkRequired( array $data, array $required )
	{
		$ok = true;
		foreach ( $required as $id )
		{
			if ( empty( $data[ $id ] ) )
			{
				$ok = false;
				break;
			}
		}
		return $ok;
	}
	
	public static function runTyres( $brands = '' )
	{
		$Rezina = new self();
		
		Console::writeln( 'Downloading tyres ..' );
		
		$brands = explode( ',', $brands );
		if ( !$brands[0] )
		{
			unset( $brands[0] );
		}

		$tyresCount = 0;
		$pages = $Rezina->crawlTyres();
		$arr = array_keys( $pages );
		rsort( $arr );
		$max = array_shift( $arr ) + 1;
		$i = 0;
		while ( $i < $max )
		{
			if ( !isset( $pages[ $i ] ) )
			{
				break;
			}

			$wheels = $Rezina->crawlTyrePage( $pages[ $i ] , $pages );
			foreach ( $wheels as $item )
			{
				$Parent = new Car_Tyre();
				$Parent = $Parent->findItem( array( 'RefId = '.$item['RefId'] ) );
				if ( !$Parent->Id )
				{
					$Parent->set( $item );
					$Parent->Type = Car_Brand::TYRE;
					$Parent->RefId = $item['RefId'];
					$Parent->BrandId = $item['Brand'];
					if ( count( $item['Children'] ) )
					{
						$child = array_shift( $item['Children'] );
						$Parent->set( $child );
					}
					if ( $Parent->save() )
					{
						if ( !$Parent->hasImages() )
						{
							foreach ( $item['Images'] as $image )
							{
								$Rezina->downloadImage( $image, $Parent );
							}
						}
						foreach ( $item['Children'] as $child )
						{
							$Tyre = new Car_Tyre();
							$Tyre->set( $child );
							$Tyre->copyParent( $Parent );
							$Tyre->ParentId = $Parent->Id;
							$Tyre->save();
						}
					}
				}

				//Console::left( $Brand->Name, 30 );
				Console::left( $i.'/'.$max, 8 );
				//Console::right( sprintf('%d%%', 100 * $b / count( $Brands )), 7 );
				Console::right( sprintf('%dMb', memory_get_usage(true) / 1024 / 1024), 10 );
				Console::writeln();
				Console::goUp(1);
			}

			$i++;
		}
		Console::writeln();
		Console::writeln( 'Tyres count: '.$tyresCount );
		Console::writeln( 'Done.' );
	}
	
	public function crawlTyreBrands()
	{
		$dom = $this->getDom( $this->URL().'kata/337/' );
		$brand = array();
		foreach ( $dom->find('#manufacturers_presets li label') as $a )
		{
			$id = $a->find('input', 0)->value;
			$brand[] = array( trim( $a->text() ), $id );
		}
		return $brand;
	}
	
	public static function runBrands()
	{
		$Rezina = new self();
		
		Console::writeln( 'Downloading brands ..' );
		$tyreBrands = 0;
		foreach ( $Rezina->crawlTyreBrands() as $item )
		{
			$name = $item[0];
			$Brand = new Car_Brand();
			$Brand = $Brand->findItem( array( 'Type = '.Car_Brand::TYRE, 'Name = '.$name ) );
			if ( !$Brand->Id )
			{
				$Brand->Type = Car_Brand::TYRE;
				$Brand->Name = $name;
				$Brand->Ref = $item[1];
				$Brand->saveNew();
			}
			$tyreBrands++;
		}
		Console::writeln( 'M.UA Tyre brands: '.$tyreBrands );
		Console::writeln( 'Done.' );
	}
	
}
