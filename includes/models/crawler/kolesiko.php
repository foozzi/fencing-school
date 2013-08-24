<?

class Crawler_Kolesiko extends Crawler
{
	
	private $brands = array();
	private $models = array();
	private $years = array();
	private $engines = array();
	
	private $pages = array();
	
	protected function URL()
	{
		return 'http://kolesiko.ua/';
	}
	
	protected function isCacheable()
	{
		return true;
	}
	
	public function crawlCarBrands()
	{
		$dom = $this->getDom( $this->URL() );		
                $brand = array();
                foreach ( $dom->find('div[class="avto_filter"] select[name="vendor"]') as $select )
		{		
                        foreach ( $select->find('option') as $option )
			{				
                                if ( $option->value && $option->value!=='-')
				{
					
                                        $brand[ $option->value ] = $option->text();
				}
			}
		} 
		return $this->brands = $brand;
	}
	
	public function crawlTyreBrands()
	{
		$dom = $this->getDom( $this->URL() );
		$brand = array();
		foreach ( $dom->find('section[id="home_tire"] div.brands') as $section )
		{
			foreach ( $section->find('ul li a') as $a )
			{
				$brand[] = array( trim( $a->text() ), preg_replace( '/.+\?brand=/', '', $a->href ) );
			}
		}
		return $brand;
	}
	
	public function crawlWheelBrands()
	{
		$dom = $this->getDom( $this->URL() );
		$brand = array();
		foreach ( $dom->find('div[id="rim_brands"]') as $div )
		{
			foreach ( $div->find('ul li a') as $a )
			{
				$brand[] = array( trim( $a->text() ), preg_replace( '/.+\?brand=/', '', $a->href ) );
			}
		}
		return $brand;
	}
	
	
        public function crawlModels( $brandName )
	{
            
            $curl = curl_init();            
            curl_setopt($curl, CURLOPT_URL, $this->URL().'assortment/index/car');
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, 'pvendor='.urlencode($brandName));
            $res = json_decode(curl_exec($curl));       
            
            $model = array();
		foreach ( $res as $key=>$value )
		{
			if ( $value )
			{
				$model[ $key ] = $value;
			}
		}
		return $this->models = $model;
	}
	
	public function crawlYears( $modelId )
	{
		$dom = $this->getDom( $this->URL().'index.php?route=module/filter_auto/autocomplete', true, array('car_model' => $modelId) );
		$year = array();
		foreach ( $dom->find('option') as $option )
		{
			if ( $option->value )
			{
				$year[ $option->value ] = $option->text();
			}
		}
		return $this->years = $year;
	}
        
        public function crawlYears2( $brand,$model )
	{
            $curl = curl_init();            
            curl_setopt($curl, CURLOPT_URL, $this->URL().'assortment/index/year');
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, 'pcar='.urlencode($model).'&pvendor='.urlencode($brand));
            $res = json_decode(curl_exec($curl));
            
            $years = array();
		foreach ( $res as $year )
		{
			if ( $year )
			{
				$years[] = $year;
			}
		}
		return $this->years = $years;
        }	
        
        public function crawlEngines( $brand,$model,$year )
        {
            $curl = curl_init();            
            curl_setopt($curl, CURLOPT_URL, $this->URL().'assortment/index/modification');
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, 'pcar='.urlencode($model).'&pvendor='.urlencode($brand).'&pyear='.urlencode($year));
            $res = json_decode(curl_exec($curl));
            
            $engines = array();
		foreach ( $res as $engine )
		{
			if ( $engine )
			{
				$engines[ ] = $engine;
			}
		}
		return $this->engines = $engines;
        }
	
	//public function crawlCarWheels( Car_Engine $Engine )
                
        public function crawlCarWheels( array $Engine )
	{            
            $dom = $this->getDom( $this->URL().'assortment/index', true, array(
			'pyear'         => $Engine['car_year'],
			'pcar'          => $Engine['car_model'],
			'pvendor'	=> $Engine['car_brand'],
			'pmodification'	=> $Engine['car_modif'],
		) );
            
            foreach ($dom->find('table[class="res_avto_wheels"]') as $shiny)
            {                            
                $pcd=$hub=null;
                $category=Car_Tyre::FACTORY;
                $sizes=array();
                foreach ($shiny->find('tr') as $tr)
                {
                    if (count($tr->find('td'))==3 && ( strpos( $tr->find('td',0)->text(),'PCD: ')!==false ) )
                    {
                        $wheelparams=$tr->find('td',0)->text().' '.$tr->find('td',1)->text().' '.$tr->find('td',2)->text();
                        
                        if ( preg_match( '/PCD: ([\dx,]+) DIA: ([\d,]+) мм/i' , trim( $wheelparams ), $res ) )
			{      
                                $pcd = str_replace(',','.',trim( $res[1] ));
				$hub =  str_replace(',','.',trim( $res[2] ));
			}                    
                        
                    }
                    
                    if (count($tr->find('td'))==3 && ( $tr->find('td',1)->find('a') || $tr->find('td',2)->find('a')) )
                    {
                       if ($cat=$tr->find('td',0)->text())
                       {
                           switch ( trim( $cat ) )
				{
					case 'OEM':
						$category = Car_Tyre::FACTORY;
						break;
					case 'Тюнинг':
						$category = Car_Tyre::TUNING;
						break;
					case 'Замена':
					default:
						$category = Car_Tyre::REPLACEMENT;
						break;
				}
                       }
                       $front=$tr->find('td',1)->find('a',0);
                       $rear=$tr->find('td',2)->find('a',0);
                       
                       if ( $rear->text() && $front->text())
                       {
                           $sizes[$category][Car_Axle::FRONT][]=array($front->text(),$pcd,$hub);
                           $sizes[$category][Car_Axle::REAR][]=array($rear->text(),$pcd,$hub);
                       }
                       elseif ($front->text()) 
                       {
                           $sizes[$category][Car_Axle::ANY][]=array($front->text(),$pcd,$hub);
                       }
                        
                    }
                }                
                return $sizes;
            }
            return array();

        }
	
	//public function crawlCarTyres( Car_Engine $Engine )
        
        
        public function crawlCarTyres( array $Engine )
	{            
            $dom = $this->getDom( $this->URL().'assortment/index', true, array(
			'pyear'         => $Engine['car_year'],
			'pcar'          => $Engine['car_model'],
			'pvendor'	=> $Engine['car_brand'],
			'pmodification'	=> $Engine['car_modif'],
		) );
            
            foreach ($dom->find('table[class="res_avto_tyres"]') as $shiny)
            {     
                
                $category=Car_Tyre::FACTORY;
                $sizes=array();
                foreach ($shiny->find('tr') as $tr)
                {
                    if (count($tr->find('td'))==3 && ( $tr->find('td',1)->find('a') || $tr->find('td',2)->find('a')) )
                    {
                       if ($cat=$tr->find('td',0)->text())
                       {
                           switch ( trim( $cat ) )
				{
					case 'OEM':
						$category = Car_Tyre::FACTORY;
						break;
					case 'Тюнинг':
						$category = Car_Tyre::TUNING;
						break;
					case 'Замена':
					default:
						$category = Car_Tyre::REPLACEMENT;
						break;
				}
                       }
                       $front=$tr->find('td',1)->find('a',0);
                       $rear=$tr->find('td',2)->find('a',0);
                       
                       if ( $rear->text() && $front->text())
                       {
                           $sizes[$category][Car_Axle::FRONT][]=$front->text();
                           $sizes[$category][Car_Axle::REAR][]=$rear->text();
                       }
                       elseif ($front->text()) 
                       {
                           $sizes[$category][Car_Axle::ANY][]=$front->text();
                       }
                        
                    }
                }                 
                return $sizes;
            }
            return array();

        }
        
	
	public function crawlWheels( Car_Brand $Brand = null )
	{
		$url = $this->URL().'diski';
		if ( $Brand )
		{
			$url .= '?brand='.$Brand->Ref;
		}
		$dom = $this->getDom( $url );
		$this->pages = array( 1 => $url );
		$this->checkPages( $dom );
		return $this->pages;
	}
	
	public function crawlWheelPage( $url, array &$pages )
	{
		$dom = $this->getDom( $url );
		$this->checkPages( $dom );
		$pages = $this->pages;
		$result = array();
		$required = array('Width', 'Diameter', 'PCD', 'ET');
		$columns = array('Width', 'Diameter', 'PCD', 'ET', 'HUB', 'Stock', 'Price', 'Button');
		foreach ( $dom->find('div[class="product_list"]') as $div )
		{
			foreach ( $div->find('div[class="item"]') as $div )
			{
				$id = str_replace( 'item_', '', $div->id );
				$name = '';
				$image = null;
				foreach ( $div->find('img.small_img') as $img )
				{
					$image = $img->src;
					//$image = str_replace( '100x100', '450x450', $img->src );
				}
				foreach ( $div->find('a.model') as $a )
				{
					$name = str_replace( 'Диски ', '', trim( $a->text() ) );
				}
				foreach ( $div->find('div.name') as $text )
				{
					$data = array();
					if ( preg_match( '/R(\d+) W([\d\.]+) PCD([\d\.x]+) ET(\d+) HUB([\d\.]+)/i', trim( $text->text() ), $res ) )
					{
						$data['Id']			= $id;
						$data['Name']		= $name;
						$data['Image']		= $image;
						$data['Width']		= $res[2];
						$data['Diameter']	= $res[1];
						$data['PCD']		= $res[3];
						$data['ET']			= $res[4];
						$data['HUB']		= $res[5];
						
						foreach ( $div->find('div.buy a.button_buy_middle') as $a )
						{
							if ( preg_match( '/\?product=(\d+)$/i', $a->href, $res ) )
							{
								$data['Button'] = $res[1];
							}
						}
						foreach ( $div->find('div.price') as $a )
						{
							$data['Price'] = preg_replace( '/[^\d]+/', '', $a->text() );
						}
					}
					if ( self::checkRequired( $data, $required ) )
					{
						$result[] = $data;
					}
				}
				foreach ( $div->find('table.model_products') as $table )
				{
					foreach ( $table->find('tr') as $tr )
					{
						$data = array();
						$data['Id']			= $id;
						$data['Name']		= $name;
						$data['Image']		= $image;
						foreach ( $tr->find('td') as $i => $td )
						{
							if ( isset( $columns[ $i ] ) )
							{
								$key = $columns[ $i ];
								$data[ $key ] = $this->getProductValue( $key, trim( $td->innertext() ) );
							}
						}
						if ( self::checkRequired( $data, $required ) )
						{
							$result[] = $data;
						}
					}
				}
			}
		}
		return $result;
	}
	
	public function crawlTyres( Car_Brand $Brand = null )
	{
		$url = $this->URL().'shiny';
		if ( $Brand )
		{
			$url .= '?brand='.$Brand->Ref;
		}
		$dom = $this->getDom( $url );
		$this->pages = array( 1 => $url );
		$this->checkPages( $dom );
		return $this->pages;
	}
	
	public function crawlTyrePage( $url, array &$pages )
	{
		$dom = $this->getDom( $url );
		
		$this->checkPages( $dom );
		$pages = $this->pages;
		$result = array();
		$required = array('Width', 'Profile', 'Diameter', 'Speed', 'Weight');
		$columns = array('Width', 'Profile', 'Diameter', 'Speed', 'Weight', 'Stock', 'Price', 'Button');

		foreach ( $dom->find('div[class="product_list"]') as $div )
		{

			foreach ( $div->find('div[class="item"]') as $div )
			{

				$id = str_replace( 'item_', '', $div->id );

				$name = '';
				$image = null;
				$season = Car_Tyre::ANY;
				foreach ( $div->find('img.small_img') as $img )
				{
					$image = str_replace( '100x100', '450x450', $img->src );
				}
				foreach ( $div->find('a.model') as $a )
				{
					$name = str_replace( 'Шины ', '', trim( $a->text() ) );
				}
				foreach ( $div->find('span.param_season_letnie') as $span )
				{
					$season = Car_Tyre::SUMMER;
				}
				foreach ( $div->find('span.param_season_zimnie') as $span )
				{
					$season = Car_Tyre::WINTER;
				}
				foreach ( $div->find('div.name') as $text )
				{
					$data = array();
					if ( preg_match( '/(\d+)\/(\d+)R(\d+) (\d+)([\w\(\)]+)/i', trim( $text->text() ), $res ) )
					{
						$data['Id']			= $id;
						$data['Name']		= $name;
						$data['Season']		= $season;
						$data['Image']		= $image;
						$data['Width']		= $res[1];
						$data['Profile']	= $res[2];
						$data['Diameter']	= $res[3];
						$data['Weight']		= $res[4];
						$data['Speed']		= $res[5];
						
						foreach ( $div->find('div.buy a.button_buy_middle') as $a )
						{
							if ( preg_match( '/\?product=(\d+)$/i', $a->href, $res ) )
							{
								$data['Button'] = $res[1];
							}
						}
						foreach ( $div->find('div.price') as $a )
						{
							$data['Price'] = preg_replace( '/[^\d]+/', '', $a->text() );
						}
					}
					if ( self::checkRequired( $data, $required ) )
					{
						$result[] = $data;
					}
				}
				foreach ( $div->find('table.model_products') as $table )
				{
					foreach ( $table->find('tr') as $tr )
					{
						$data = array();
						$data['Id']			= $id;
						$data['Name']		= $name;
						$data['Season']		= $season;
						$data['Image']		= $image;
						foreach ( $tr->find('td') as $i => $td )
						{
							if ( isset( $columns[ $i ] ) )
							{
								$key = $columns[ $i ];
								$data[ $key ] = $this->getProductValue( $key, trim( $td->innertext() ) );
							}
						}
						if ( self::checkRequired( $data, $required ) )
						{
							$result[] = $data;
						}
					}
				}
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
		foreach ( $dom->find('div[class="pagination"]') as $div )
		{
			foreach ( $div->find('a') as $a )
			{
				if ( !$a->class )
				{
					$i = trim( $a->text() );
					if ( !isset( $this->pages[ $i ] ) )
					{
						$this->pages[ $i ] = $a->href;
					}
				}
			}
		}
	}
	
	private function getProductKeysMatrix()
	{
		return array(
			'Ширина шины:'	=> 'Width',
			'Профиль:'		=> 'Profile',
			'Диаметр:'		=> 'Diameter',
			'Индекс скорости:' => 'Speed',
			'Индекс нагрузки:' => 'Weight',
			'Сезонность:'	=> 'Season',
		);
	}
	
	private function getProductKey( $key )
	{
		$arr = $this->getProductKeysMatrix();
		if ( isset( $arr[ $key ] ) )
		{
			return $arr[ $key ];
		}
		return $key;
	}
	
	private function getProductValue( $key, $value )
	{
		if ( in_array( $key, array('Speed', 'Weight') ) )
		{
			$arr = explode( '-', $value, 2 );
			return trim( $arr[0] );
		}
		if ( $key == 'Diameter' )
		{
			return str_replace( 'R', '', $value );
		}
		if ( $key == 'Price' )
		{
			return preg_replace( '/[^\d\.\,]/i', '', $value );
		}
		if ( $key == 'Button' )
		{
			if ( preg_match( '/id="but(\d+)"/i', $value, $res ) )
			{
				return $res[1];
			}
			else if ( preg_match( '/\?product=(\d+)$/i', $value, $res ) )
			{
				return $res[1];
			}
		}
		return $value;
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
	
	private static function getSeason( $season )
	{
		if ( $season == 'летние' )
		{
			return Car_Tyre::SUMMER;
		}
		else if ( $season == 'зимние' )
		{
			return Car_Tyre::WINTER;
		}
		return Car_Tyre::ANY;
	}
	
	
	public static function runBrands()
	{
		$Rezina = new self();
		
		Console::writeln( 'Downloading brands ..' );
		$carBrands = $tyreBrands = $wheelBrands = 0;
		foreach ( $Rezina->crawlCarBrands() as $id => $name )
		{
			$Brand = new Car_Brand();
			$Brand = $Brand->findItem( array( 'Id = '.$id ) );
			if ( !$Brand->Id )
			{
				$Brand->Id = $id;
				$Brand->Type = Car_Brand::CAR;
				$Brand->Name = $name;
				$Brand->saveNew();
			}
			$carBrands++;
		}
		Console::writeln( 'Car brands: '.$carBrands );
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
		Console::writeln( 'Tyre brands: '.$tyreBrands );
		foreach ( $Rezina->crawlWheelBrands() as $item )
		{
			$name = $item[0];
			$Brand = new Car_Brand();
			$Brand = $Brand->findItem( array( 'Type = '.Car_Brand::WHEEL, 'Name = '.$name ) );
			if ( !$Brand->Id )
			{
				$Brand->Type = Car_Brand::WHEEL;
				$Brand->Name = $name;
				$Brand->Ref = $item[1];
				$Brand->saveNew();
			}
			$wheelBrands++;
		}
		Console::writeln( 'Wheel brands: '.$wheelBrands );
		Console::writeln( 'Done.' );
	}
	
	public static function runWheels()
	{
		$Rezina = new self();
		
		Console::writeln( 'Downloading wheels ..' );
		
		$wheelsCount = 0;
		$brands = Car_Brand::getBrands( Car_Brand::WHEEL );
		$b = 0;
		foreach ( $brands as $Brand )
		{
			if ( in_array( $Brand->Name, array('MAK') ) )
			{
				continue; // segmentation failed
			}
			$pages = $Rezina->crawlWheels( $Brand );
			$i = 0;
			while ( $i < count( $pages ) )
			{
				if ( !isset( $pages[ $i + 1 ] ) )
				{
					break;
				}
				$wheels = $Rezina->crawlWheelPage( $pages[ $i + 1 ] , $pages );
				foreach ( $wheels as $item )
				{
					$item['Name'] = trim( preg_replace( '/^'.preg_quote( $Brand->Name ).'/i', '', $item['Name'] ), ' \\-' );
					$Tyre = new Car_Tyre();
					$Tyre = $Tyre->findItem( array( 'RefId = '.$item['Id'], 'Type = '.Car_Brand::WHEEL ) );
					if ( !$Tyre->Id )
					{
						$Tyre->Type = Car_Brand::WHEEL;
						$Tyre->RefId = $item['Id'];
						$Tyre->BrandId = $Brand->Id;
						$Tyre->set($item);
						$Tyre->Id = null;
						$Tyre->save();
						$wheelsCount++;
					}
					if ( $item['Button'] )
					{
						$Tyre = $Tyre->findItem( array( 'RefId = '.$item['Button'], 'Type = '.Car_Brand::WHEEL ) );
						if ( !$Tyre->Id )
						{
							$Parent = new Car_Tyre();
							$Parent = $Parent->findItem( array( 'RefId = '.$item['Id'], 'Type = '.Car_Brand::WHEEL ) );
							$Tyre->Type = Car_Brand::WHEEL;
							$Tyre->ParentId = $Parent->Id;
							$Tyre->RefId = $item['Button'];
							$Tyre->BrandId = $Brand->Id;
							$Tyre->set($item);
							$Tyre->Id = null;
							$Tyre->save();
							$wheelsCount++;
						}
					}
					/*
					$Product = new Car_Product();
					$Product = $Product->findItem( array( 'RefId = '.$item['Button'], 'TyreId = '.$Tyre->Id ) );
					if ( !$Product->Id )
					{
						$Product->RefId = $item['Button'];
						$Product->TyreId = $Tyre->Id;
						$Product->set($item);
						$Product->Id = null;
						$Product->save();
						$wheelsCount++;
					}
					*/
					Console::left( $Brand->Name, 30 );
					Console::left( ($i + 1).'/'.count( $pages ), 8 );
					Console::right( sprintf('%d%%', 100 * $b / count( $brands )), 7 );
					Console::writeln();
					Console::goUp(1);
				}
				$i++;
			}
			$b++;
		}
		Console::writeln();
		Console::writeln( 'Wheels count: '.$wheelsCount );
		Console::writeln( 'Done.' );
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
		$Brands = Car_Brand::getBrands( Car_Brand::TYRE );
		$b = 0;
		foreach ( $Brands as $Brand )
		{
			if ( count( $brands ) && !in_array( $Brand->Name, $brands ) )
			{
				continue;
			}
			if ( in_array( $Brand->Name, array('ACCELERA', 'ACHILLES') ) )
			{
				continue; // segmentation failed
			}

			$pages = $Rezina->crawlTyres( $Brand );
			$i = 0;
			while ( $i < count( $pages ) )
			{
				if ( !isset( $pages[ $i + 1 ] ) )
				{
					break;
				}
				$i++;

				$wheels = $Rezina->crawlTyrePage( $pages[$i ] , $pages );
				foreach ( $wheels as $item )
				{
					$item['Name'] = trim( preg_replace( '/^'.preg_quote( $Brand->Name ).'/i', '', $item['Name'] ), ' \\-' );
					$Tyre = new Car_Tyre();
					$Tyre = $Tyre->findItem( array( 'RefId = '.$item['Id'], 'Type = '.Car_Brand::TYRE ) );
					if ( !$Tyre->Id )
					{
						$Tyre->Type = Car_Brand::TYRE;
						$Tyre->RefId = $item['Id'];
						$Tyre->BrandId = $Brand->Id;
						$Tyre->set($item);
						$Tyre->Id = null;
						$Tyre->saveNew();
						$tyresCount++;
					}
					if ( $item['Button'] )
					{
						$Tyre = $Tyre->findItem( array( 'RefId = '.$item['Button'], 'Type = '.Car_Brand::TYRE ) );
						if ( !$Tyre->Id )
						{
							$Parent = new Car_Tyre();
							$Parent = $Parent->findItem( array( 'RefId = '.$item['Id'], 'Type = '.Car_Brand::TYRE ) );
							$Tyre->Type = Car_Brand::TYRE;
							$Tyre->ParentId = $Parent->Id;
							$Tyre->RefId = $item['Button'];
							$Tyre->BrandId = $Brand->Id;
							$Tyre->set($item);
							$Tyre->Id = null;
							$Tyre->save();
							$tyresCount++;
						}
					}
					Console::left( $Brand->Name, 30 );
					Console::left( $i.'/'.count( $pages ), 8 );
					Console::right( sprintf('%d%%', 100 * $b / count( $Brands )), 7 );
					Console::right( sprintf('%dMb', memory_get_usage(true) / 1024 / 1024), 10 );
					Console::writeln();
					Console::goUp(1);
				}
			}
			$b++;
		}
		Console::writeln();
		Console::writeln( 'Tyres count: '.$tyresCount );
		Console::writeln( 'Done.' );
	}

	private static function attachSizes( $sizes, Car_Engine $Engine )
	{
		if ( !is_array( $sizes ) )
		{
			return false;
		}                
		foreach ( $sizes as $tuning => $set )
		{
			foreach ( $set as $axle => $arr )
			{
				foreach ( $arr as $size )
				{					
                                        if ( is_array( $size ) )
					{
						
                                                $Engine->attachSize( $tuning, $axle, $size[0], $size[1], $size[2] );                                                
					}
					else
					{
						$Engine->attachSize( $tuning, $axle, $size );
					}
				}
			}
		}                 
		return true;
	}
	
	public static function runCars( $currentBrand = null )
	{		                
                $Rezina = new self();		
                
		$brands = $Rezina->crawlCarBrands();                
		$modelsCount = $enginesCount = 0;
		$indexBrand = 0;
		foreach ( $brands as $brand => $name )
		{ 	
                        $indexBrand++;          
			if ( $currentBrand )
			{
				                            
                                if ( preg_match( '/^([<>]{1})(.+)/i', $currentBrand, $res ) )
				{
					if ( $res[1] == '>' && $res[2] > $name )
					{
						continue;
					}
					else if ( $res[1] == '<' && $res[2] < $name )
					{
						continue;
					}
				}
				else
				{
					if ( $currentBrand != $name )
					{
						continue;
					}
				}
			}
			$Brand = new Car_Brand();
			//$Brand = $Brand->findItem( array( 'Id = '.$brand, 'Type = '.Car_Brand::CAR ) );			
                        /*new block*/
                        $Brand = $Brand->findItem( array('Type = '.Car_Brand::CAR , 'Name = '.$name ) );
                        if (!$Brand->Id)
                        {
                            $Brand->Name=$name;
                            $Brand->saveNew();                            
                        }                         
                        /*end of new block*/
                        $models = $Rezina->crawlModels( $name );
			$indexModel = 0;                        
			foreach ( $models as $model => $name )
			{
                                $Model = new Car_Model();
				//$Model = $Model->findItem( array( 'Id = '.$model ) );				
                                $Model = $Model->findItem( array( 'BrandId = '.$Brand->Id, 'Name = '.$name ) );
                                if ( !$Model->Id )
				{
					//$Model->Id = $model;
					//$Model->BrandId = $brand;
					$Model->BrandId = $Brand->Id;
                                        $Model->Name = $name;
					$Model->saveNew();
				}
                                $years = $Rezina->crawlYears2( $Brand->Name, $Model->Name );
				$indexYear = 0;
				foreach ( $years as $year => $number )
				{					
                                        $engines = $Rezina->crawlEngines($Brand->Name, $Model->Name, $number );
                                        
                                        $indexEngine = 0;
					foreach ( $engines as $engine => $name )
					{
						$Engine = new Car_Engine();						
                                                $Engine = $Engine->findItem( array( 'ModelId = '.$Model->Id, 'Name = '.$name, 'Year = '.$number ) );                                                
                                                if ( !$Engine->Id )
						{
							//$Engine->Id = $engine;
							$Engine->ModelId = $Model->Id;
							$Engine->Year = $number;
							$Engine->YearId = $year;
							$Engine->Name = $name;
							$Engine->saveNew();                                                        
						}
						$enginesCount++;
                                                						
                                                /*new block*/
                                                $OriginEngine = array(
                                                'car_brand'	=> $Brand->Name,
                                                'car_model'	=> $Model->Name,
                                                'car_year'	=> $number,
                                                'car_modif'	=> $Engine->Name, 
                                                );
                                                /*end new block*/
                                                
                                                //self::attachSizes( $Rezina->crawlCarTyres( $Engine ), $Engine );
						//self::attachSizes( $Rezina->crawlCarWheels( $Engine ), $Engine ); 
                                                
                                                //self::attachSizes( $Rezina->crawlCarTyres( $OriginEngine ), $Engine );
						//self::attachSizes( $Rezina->crawlCarWheels( $OriginEngine ), $Engine );
                                                
                                                self::attachSizes( $Rezina->crawlCarTyres( $OriginEngine ), $Engine );
                                                self::attachSizes( $Rezina->crawlCarWheels( $OriginEngine ), $Engine );                                                
                                                
						Console::left( $Brand->Name, 20 );
						Console::left( $Model->Name, 20 );
						Console::left( sprintf('%d%%', 100 * $indexModel / count($models)), '5' );
						Console::left( $Engine->Year, 5 );
						Console::left( sprintf('%d%%', 100 * $indexYear / count($years)), '5' );
						Console::left( $Engine->Name, 20 );
						Console::right( sprintf('%d%%', 100 * $indexBrand / count($brands)), '5' );
						Console::left('', 20);
						Console::writeln();
						Console::goUp(1);
                                                }                                                
                                                

						$indexEngine++;
					}
					$indexYear++;
				}
				$indexModel++;
				$modelsCount++;
			}
		
		Console::writeln();
		Console::writeln('Done.');
		}	
}
