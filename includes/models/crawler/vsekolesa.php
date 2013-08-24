<?

class Crawler_VseKolesa extends Crawler
{

	private $brands = array();
	private $models = array();
	private $seasons = array();

	protected function isCacheable()
	{
		return true;
	}

	protected function URL()
	{
		return 'http://vsekolesa.com.ua';
	}

	public function crawlTyreBrands()
	{
		$dom = $this->getDom($this->URL());
		$brand = array();
		foreach ( $dom->find('a.blue[href*="cPath\/[10]"]') as $link )
		{
			if ( strpos($link->text(), 'Шины:') )
				$brand[preg_replace('/&nbsp;Шины:\s/', '', $link->text())] = $link->href;
		}
		return $this->brands = $brand;
	}

	public function crawlWheelBrands()
	{
		$dom = $this->getDom($this->URL());
		$brand = array();
		foreach ( $dom->find('a.blue[href*="cPath\/[74]"]') as $link )
		{
			if ( ( strpos($link->text(), 'Литые диски:') || strpos($link->text(), 'Стальные диски:') ) && !in_array($link->href, $brand) )
			{
				$del[0] = '/&nbsp;Литые диски:\s/';
				$del[1] = '/&nbsp;Стальные диски:\s/';
				$replacement[0] = '';
				$replacement[1] = '';
				$brand[preg_replace($del, $replacement, $link->text())] = $link->href;
			}
		}
		return $this->brands = $brand;
	}

	public function crawlTyreSeasons( $url )
	{
		$dom = $this->getDom($url);
		$season = array();
		foreach ( $dom->find('table.catalog a.sir') as $link )
		{
			$name = str_replace('&nbsp;', '', $link->text());
			$name = trim($name);
			$season[$name] = $link->href;
		}
		return $this->seasons = $season;
	}

	public function crawlTyreModels( $url, $brand )
	{
		$dom = $this->getDom($url);
		$model = array();
		foreach ( $dom->find('table a.blue') as $link )
		{
			if ( preg_match('/cPath\/.+\/products_id\//', $link->href) )
			{
				$src = preg_replace('/(\/image\/)s_/', '$1', $link->find('img', 0)->src);
				$src = $this->URL() . str_replace(' ', '%20', $src);
				$name = str_replace('Авто шины: ' . $brand . ' ', '', $link->text());
				$name = trim($name);
				$model[$name] = $src;
			}
		}
		return $this->models = $model;
	}

	public function crawlWheelModels( $url, $brand )
	{
		$dom = $this->getDom($url);
		$model = array();
		foreach ( $dom->find('table a.blue[href*="cPath\/.+\/products_id\/"]') as $link )
		{
			$src = preg_replace('/(\/image\/)s_/', '$1', $link->find('img', 0)->src);
			$src = $this->URL() . str_replace(' ', '%20', $src);
			$name = str_replace('Авто диски: ' . $brand . ' ', '', $link->text());
			$name = trim($name);
			$model[$name] = $src;
		}
		return $this->models = $model;
	}

	public function getSeason( $season )
	{
		switch ( $season )
		{
			case 'Летние шины':
				return Car_Tyre::SUMMER;
				break;
			case 'Зимние шины':
				return Car_Tyre::WINTER;
				break;
			default :
				return 0;
				break;
		}
	}
	
	public function parseTyreImages( $Tyre, $model, $src )
	{
		$tyre = preg_replace( '/[^\w]+/', '', $Tyre->Name );						
		$model = preg_replace( '/[^\w]+/', '', $model );
		$str[] = $tyre;						

		if ( substr( $model, 0, strlen( $tyre ) ) == $tyre || substr( $tyre, 0, strlen( $model ) ) == $model )
		{				
			if ( file_put_contents( 'tmp.jpg', file_get_contents( $src ) ) )
			{
				$Image = new Car_Image();
				$Image->TyreId = $Tyre->Id;
				if ( $Image->save() )
				{
					if ( File::upload( $Image, 'tmp.jpg' ) )
					{
						$Image->save();
						$Tyre->ImageId = $Image->Id;
						$Tyre->save();

						printf("%-40s%s\n", $Tyre->getName(), $src);
						echo 'by brands';
					}
				}
			}
		}
	}

	public static function runTyreImages()
	{
		$Images = new self();

		$brands = $Images->crawlTyreBrands();
		
		$indexBrand = 0;
		$Brand = new Car_Brand();
		$Model = new Car_Model();
		$Tyre = new Car_Tyre();
		foreach ( $brands as $brand => $link )
		{
			$Brand = $Brand->findItem(array('Type = ' . Car_Brand::TYRE, '* LOWER(Name) = "' . strtolower($brand) . '"'));
			if ( !$Brand->Id )
				continue;
			$seasons = $Images->crawlTyreSeasons($link);
			$indexSeason = 0;
			foreach ( $seasons as $season => $link )
			{
				$Season = $Images->getSeason($season);
				$models = $Images->crawlTyreModels($link, $brand);
				$indexModel = 0;
				
				
				
				foreach ( $models as $model => $src )
				{
					//parse with brands					
					foreach ( $Tyre->findList( array('Type = '.Car_Brand::TYRE, 'ParentId = 0', 'ImageId = 0', 'BrandId = '.$Brand->Id) ) as $Tyre )
					{
						$Images->parseTyreImages( $Tyre, $model, $src );
					}
					
					// parse with only models
					foreach ( $Tyre->findList( array( 'Type = '. Car_Brand::TYRE, 'ParentId = 0', 'ImageId = 0' ) ) as $Tyre )
					{
						$Images->parseTyreImages( $Tyre, $model, $src );
					}
				}
				
				
				
				$indexSeason++;
			}
			$indexBrand++;
		}
	}

	public static function runWheelImages()
	{
		$Images = new self();

		$brands = $Images->crawlWheelBrands();
		$indexBrand = 0;
		$Brand = new Car_Brand();
		$Model = new Car_Model();
		$Wheel = new Car_Tyre();
		foreach ( $brands as $brand => $link )
		{
			$Brand = $Brand->findItem(array('Type = ' . Car_Brand::WHEEL, '* LOWER(Name) = "' . strtolower($brand) . '"'));
			if ( !$Brand->Id )
			{
				$indexModel++;
				$indexBrand++;
				continue;
			}
			

			$models = $Images->crawlWheelModels($link, $brand);
			$indexModel = 0;
			foreach ( $models as $model => $src )
			{
				$Wheel = $Wheel->findItem(array('ParentId = 0', 'BrandId = ' . $Brand->Id, '* LOWER(Name) = "' . strtolower($model) . '"'));
				
				if ( $Wheel->Id && !$Wheel->ImageId )
					if ( file_put_contents('tmp.jpg', file_get_contents($src)) )
					{
						$Image = new Car_Image();
						$Image->TyreId = $Wheel->Id;
						if ( $Image->save() )
						{
							if ( File::upload($Image, 'tmp.jpg') )
							{
								$Image->save();
								$Wheel->ImageId = $Image->Id;
								$Wheel->save();
							}
						}
					}
				$indexModel++;

				Console::left($brand, 20);
				Console::left(sprintf('%d%%', 100 * $indexBrand / count($brands)), '5');
				Console::left($model, 20);
				Console::right(sprintf('%d%%', 100 * $indexModel / count($models)), '5');
				Console::right('', 20);
				Console::writeln();
				Console::goUp(1);
			}
			$indexBrand++;
		}

		//Console::writeln();
		//Console::writeln('Done.');	
	}

}

