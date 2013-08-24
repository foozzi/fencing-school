<?

class Crawler_AutoDiski extends Crawler
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
		return 'http://autodiski.kiev.ua';
	}

	public function crawlTyreBrands( $url )
	{
		$dom = $this->getDom($this->URL() . $url);
		$brand = array();
		foreach ( $dom->find('div.sidebar1 ul.menu li#current ul li a') as $link )
		{
			$brand[trim($link->text())] = $link->href;
		}
		return $this->brands = $brand;
	}

	public function crawlWheelBrands()
	{
		$dom = $this->getDom($this->URL(). '/cars-wheels-kiev/gallery-light-alloy-wheels.html');
		$brand = array();
		foreach ( $dom->find('#phocagallery a.category[href*="\/gallery-light-alloy-wheels\/category\/"]') as $link )
		{
			if ( strpos($link->text(), 'АвтоДиски &raquo;')!==false )
			{
				$name = preg_replace('/^АвтоДиски &raquo;/', '', $link->text());
				$name = preg_replace('/&raquo;.+$/', '', $name);
				$name = trim($name);
				$brand[$name] = $link->href;
			}
		}
		return $this->brands = $brand;
	}

	public function crawlTyreSeasons()
	{
		$dom = $this->getDom($this->URL() . '/autocar-tyres-in-kiev.html');
		$season = array();
		foreach ( $dom->find('div.sidebar1 ul.menu li a[href*="\/autocar-tyres-in-kiev.html"]') as $menu )
		{
			foreach ( $menu->parent()->find('ul li a') as $link )
			{
				$name = trim($link->text());
				$season[$name] = $link->href;
			}
		}
		return $this->seasons = $season;
	}

	public function crawlTyreModels( $url, $brand, $season )
	{
		$dom = $this->getDom($this->URL() . $url);
		$model = array();
		$search_paginator = $dom->find( '#navigation p', 1 );
		
		if ( $search_paginator )
		{
			$num = explode( ' ' , strip_tags( $search_paginator ) );
			
			for ( $i = 0; $i <= $num[3]; $i++ )
			{ 		
				$html = $this->getDom( $this->URL() . $url . '?start=' . $i * 5 );
				foreach ( $html->find( 'div.content td.article_column a.PostHeader' ) as $link )
				{
					$name = preg_replace('/^(.+' . preg_quote($brand) . ')/i', '', $link->text());
					$name = trim($name);
					$model[$name] = $link->href;					
				}							
				
			}						
		}
		else
		{		
			foreach ( $dom->find('div.content td.article_column a.PostHeader') as $link )
			{
				$name = preg_replace('/^(.+' . preg_quote($brand) . ')/i', '', $link->text());
				$name = trim($name);
				$model[$name] = $link->href;
			}
		}
		return $this->models = $model;
	}

	public function crawlWheelModels( $url, $brand )
	{
		$dom = $this->getDom($this->URL() . $url);
		$model = array();
		foreach ( $dom->find('a.jaklightbox') as $link )
		{
			$src = $this->URL() . str_replace( ' ', '%20', $link->href );
			$name = trim( preg_replace( '/'. preg_quote( $brand ).'/', '', $link->title ) );

			$model[$name] = $src;

		}
		return $this->models = $model;
	}

	public function getTyreImage( $url )
	{
		$dom = $this->getDom($this->URL() . $url);
		$model = array();
		foreach ( $dom->find('div.article p img') as $img )
		{
			if ( !$img->onmouseout )
				return $this->URL() . str_replace(' ', '%20', $img->src);
		}
		return '';
	}

	public function getSeason( $season )
	{
		switch ( $season )
		{
			case 'ЛЕТНИЕ ШИНЫ':
				return Car_Tyre::SUMMER;
				break;
			case 'ЗИМНИЕ ШИНЫ':
				return Car_Tyre::WINTER;
				break;
			default :
				return 0;
				break;
		}
	}
	
	public function parseTyreImages( $Tyre, $model, $src )
	{
		$Images = new self();
		
		$tyre = preg_replace( '/[^\w]+/', '', $Tyre->Name );						
		$model = preg_replace( '/[^\w]+/', '', $model );
		$str[] = $tyre;						

		if ( substr( $model, 0, strlen( $tyre ) ) == $tyre || substr( $tyre, 0, strlen( $model ) ) == $model )
		{
			if ( $img = $Images->getTyreImage($src) )
			{
				if ( file_put_contents( 'tmp.jpg', file_get_contents( $img ) ) )
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

							printf("%-40s%s\n", $Tyre->getName(), $img);
							echo 'by brands';
						}
					}
				}
			}			
		}
	}

	public static function runTyreImages()
	{
		$Images = new self();
		$Brand = new Car_Brand();
		$Model = new Car_Model();
		$Tyre = new Car_Tyre();

		$seasons = $Images->crawlTyreSeasons();
		$indexSeason = 0;
		foreach ( $seasons as $season => $link )
		{
			$Season = $Images->getSeason($season);

			$brands = $Images->crawlTyreBrands($link);
			$indexBrand = 0;
			foreach ( $brands as $brand => $link )
			{
				$Brand = $Brand->findItem(array('Type = ' . Car_Brand::TYRE, '* LOWER(Name) = "' . strtolower($brand) . '"'));
				if ( !$Brand->Id )
					continue;


				$models = $Images->crawlTyreModels($link, $brand, $season);				
				
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
				
				
				
				/*foreach ( $models as $model => $link )
				{					
					$Tyre = $Tyre->findItem(array('ParentId = 0', 'ImageId = 0', 'BrandId = ' . $Brand->Id, '* LOWER(Name) = "' . strtolower($model) . '"'));
					
					if ( $Tyre->Id && !$Tyre->ImageId )
					{
						if ( $src = $Images->getTyreImage($link) )
							if ( file_put_contents('tmp.jpg', file_get_contents($src)) )
							{
								$Image = new Car_Image();
								
								$Image->TyreId = $Tyre->Id;
								if ( $Image->save() )
								{
									if ( File::upload($Image, 'tmp.jpg') )
									{
										$Image->save();
										$Tyre->ImageId = $Image->Id;
										$Tyre->save();
									}
									
								}
							}
					}
					$indexModel++;

					/*Console::left($season, 20);
					Console::left(sprintf('%d%%', 100 * $indexSeason / count($seasons)), '5');
					Console::left($brand, 20);
					Console::left(sprintf('%d%%', 100 * $indexBrand / count($brands)), '5');
					Console::left($model, 20);
					Console::right(sprintf('%d%%', 100 * $indexModel / count($models)), '5');
					Console::right('', 20);
					Console::writeln();
					Console::goUp(1);
					echo $model . "\n" . $brand . "\n_\n";
				}*/
				$indexBrand++;
			}
			$indexSeason++;
		}
	}

	public static function runWheelImages()
	{
		$Images = new self();

		$brands = $Images->crawlWheelBrands();
		$indexBrand = 0;
		$indexModel = 0;
		$Brand = new Car_Brand();
		$Model = new Car_Model();
		$Wheel = new Car_Tyre();
		$imagesCount = 0;
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
			
			foreach ( $models as $model => $src )
			{
				$str = array();
				foreach ( $Wheel->findList( array('Type = '.Car_Brand::WHEEL, 'ParentId = 0', 'ImageId = 0', 'BrandId = '.$Brand->Id) ) as $Wheel )
				{
					$wheel = preg_replace( '/[^\w]+/', '', $Wheel->Name );
					$model = preg_replace( '/[^\w]+/', '', $model );
					$str[] = $wheel;

					if ( substr( $model, 0, strlen( $wheel ) ) == $wheel || substr( $wheel, 0, strlen( $model ) ) == $model )
					{
						if ( file_put_contents( 'tmp.jpg', file_get_contents( $src ) ) )
						{
							$Image = new Car_Image();
							$Image->TyreId = $Wheel->Id;
							if ( $Image->save() )
							{
								if ( File::upload( $Image, 'tmp.jpg' ) )
								{
									$Image->save();
									$Wheel->ImageId = $Image->Id;
									$Wheel->save();
									
									$imagesCount++;
									printf("%-40s%s\n", $Wheel->getName(), $src);
								}
							}
						}
					}
				}
				
				$indexModel++;
				
				/*

				Console::left($brand, 20);
				Console::left(sprintf('%d%%', 100 * $indexBrand / count($brands)), '5');
				Console::left($model, 20);
				Console::right(sprintf('%d%%', 100 * $indexModel / count($models)), '5');
				Console::right($imagesCount, 20);
				Console::writeln();
				Console::goUp(1);
				 * *
				 */
			}
			$indexBrand++;
		}

		//Console::writeln();
		//Console::writeln('Done.');	
	}

}

