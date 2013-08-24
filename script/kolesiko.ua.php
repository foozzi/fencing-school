<?

include_once( dirname( dirname( __FILE__ ) ).'/includes/application.php' );

Application::run('config');

for ( $i = 0; $i < 10; $i++ )
{
	if ( !isset( $argv[ $i ] ) )
	{
		$argv[ $i ] = null;
	}
}

$currentBrand = $argv[1];

function printInfo( $brand, $brandIndex, $brandCount, $model, $modelIndex, $modelCount, $year, $yearIndex, $yearCount,
		$engineIndex, $engineCount, $engine, $sizeType, $sizeAxle, $size )
{
	$mult = $brandIndex / $brandCount;
	Console::left( $brand, 30 );
	Console::left( str_repeat('#', 20 * $mult), 20 );
	Console::right( sprintf('%.1f%%', $mult * 100), 5 );
	Console::writeln();

	$mult = $modelIndex / $modelCount;
	Console::left( '  '.$model, 30 );
	Console::left( str_repeat('#', 20 * $mult), 20 );
	Console::right( sprintf('%.1f%%', $mult * 100), 5 );
	Console::writeln();

	$mult = $yearIndex / $yearCount;
	Console::left( '    '.$year, 30 );
	Console::left( str_repeat('#', 20 * $mult), 20 );
	Console::right( sprintf('%.1f%%', $mult * 100), 5 );
	Console::writeln();

	$type = 'f';
	if ( $sizeType == Car_Tyre::REPLACEMENT )
	{
		$type = 'r';
	}
	else if ( $sizeType == Car_Tyre::TUNING )
	{
		$type = 't';
	}
	$mult = $engineIndex / $engineCount;
	Console::left( '      '.$engine, 30 );
	Console::left( str_repeat('#', 20 * $mult), 20 );
	Console::right( sprintf('%.1f%%', $mult * 100), 5 );
	Console::writeln();
	
	Console::left( '      '.$size.' '.$type.' '.$sizeAxle, 55 );
	Console::writeln();
	
	Console::goUp(5);
}

class DebugObject
{
	public function debugLog($level, $msg)
	{
		if ($level == 99)
		{
			file_put_contents( 'debug.log', $msg."\n", FILE_APPEND );
		}
	}
	
	public function debugLogEntry()
	{
		
	}
}
$debugObject = new DebugObject();

//Crawler_Mua::runBrands();
//Crawler_Mua::runTyres();
//Console::writeln();

//Crawler_Rezina::runBrands();
//Console::writeln();
//Crawler_Rezina::runWheels();
//Console::writeln();
//Crawler_Rezina::runTyres();
//Console::writeln();
//Crawler_Rezina::runCars();
Crawler_Kolesiko::runCars();
Console::writeln();

exit;

$Rezina = new Crawler_Rezina();

$brands = $Rezina->crawlCarBrands();

$modelsCount = $enginesCount = 0;
$indexBrand = 0;
foreach ( $brands as $brand => $name )
{
	if ( $currentBrand && $currentBrand != $name )
	{
		continue;
	}
	$models = $Rezina->crawlModels( $brand );
	$indexModel = 0;
	foreach ( $models as $model => $name )
	{
		$Model = new Car_Model();
		$Model = $Model->findItem( array( 'Id = '.$model ) );
		if ( !$Model->Id )
		{
			$Model->Id = $model;
			$Model->BrandId = $brand;
			$Model->Name = $name;
			$Model->saveNew();
		}
		
		$years = $Rezina->crawlYears( $model );
		$indexYear = 0;
		foreach ( $years as $year => $number )
		{
			$engines = $Rezina->crawlEngines( $year );
			$indexEngine = 0;
			foreach ( $engines as $engine => $name )
			{
				$Engine = new Car_Engine();
				$Engine = $Engine->findItem( array( 'Id = '.$engine ) );
				if ( !$Engine->Id )
				{
					$Engine->Id = $engine;
					$Engine->ModelId = $model;
					$Engine->Year = $number;
					$Engine->YearId = $year;
					$Engine->Name = $name;
					$Engine->saveNew();
				}
				$enginesCount++;
				$sizes = $Rezina->crawlTyres( $Engine );
				if ( !count( $sizes ) )
				{
					Console::writeln('Size error: '.$Rezina->getLastUrl(), 80);
					exit;
				}
				$sizeCount = 0;
				foreach ( $sizes as $type => $set )
				{
					foreach ( $set as $axle => $arr )
					{
						$sizeCount += count( $arr );
					}
				}
				$indexSize = 0;
				foreach ( $sizes as $type => $set )
				{
					foreach ( $set as $axle => $arr )
					{
						foreach ( $arr as $size )
						{
							$Engine->attachSize( $type, $axle, $size );
							printInfo($Brand->Name, $indexBrand, count($brands), $Model->Name, $indexModel, 
									count($models), $Engine->Year, $indexYear, count($years), $indexEngine,
									count($engines), $Engine->Name, $type, $axle, $size);
							//var_dump($size[0]);
							/*
							$pages = $Rezina->crawlTyreSize( $size[0], $size[1] );
							$i = 0;
							while ( $i < count( $pages ) )
							{
								if ( !isset( $pages[ $i + 1 ] ) )
								{
									break;
								}
								$tyres = $Rezina->crawlTyrePage( $pages[ $i + 1 ], $pages );
								foreach ( $tyres as $id => $item )
								{
									$Tyre = new Car_Tyre();
									$Tyre = $Tyre->findItem( array( 'RefId = '.$id ) );
									if ( !$Tyre->Id )
									{
										$Tyre->Category = Car_Tyre::TYRE;
										$Tyre->RefId = $id;
										$Tyre->Name = str_replace( 'Шины ', '', $item[0] );
										$Tyre->Link = $item[1];
										$Tyre->saveNew();
									}
									$products = $Rezina->crawlTyreProducts( $Tyre, $item[1] );
									$Tyre->save();
									$Engine->attachTyre( $Tyre, $axle, $type );
									foreach ( $products as $product )
									{
										$id = $product['Button'];
										$Product = new Car_Product();
										$Product = $Product->findItem( array( 'RefId = '.$id ) );
										if ( !$Product->Id )
										{
											$Product->TyreId = $Tyre->Id;
											$Product->RefId = $id;
											$Product->Name = str_replace( 'Шины ', '', $item[0] );
											$Product->set( $product );
											$Product->save();
										}
										$Engine->attachProduct( $Product, $axle, $type );
										printInfo($Brand->Name, $indexBrand, count($brands), $Model->Name, $indexModel, 
												count($models), $Engine->Year, $indexYear, count($years), $indexEngine,
												count($engines), $Engine->Name, $type, $indexSize, $sizeCount, $size[0], 
												$i + 1, count($pages), $Tyre, $Product);
									}
								}
								$i++;
							}
							$indexSize++;
							 * 
							 */
						}
					}
				}
				$indexEngine++;
			}
			$indexYear++;
		}
		$indexModel++;
		$modelsCount++;
	}
	$indexBrand++;
}

Console::left("Total brands: ".count($brands), 80);
Console::writeln();
Console::left("Total models: ".$modelsCount, 80);
Console::writeln();
Console::left("Total engines: ".$enginesCount, 80);
Console::writeln();
Console::left('Done', 80);
Console::writeln();
Console::writeln();
Console::writeln();

