<?

/*

{INSTALL:SQL{
create table pricelists(
	Id int not null auto_increment,
	Type tinyint not null,
	CheckBalance tinyint not null,
	AddNew tinyint not null,
	Status tinyint not null,
	Filename varchar(200) not null,
	Filesize int not null,
	IsFile tinyint not null,

	Added int not null,
	Updated int not null,
	Failed int not null,

	PostedAt int not null,

	primary key (Id),
	index (Status),
	index (PostedAt)
) engine = MyISAM;

}}
*/

/**
 * The Pricelist model class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class Pricelist extends Object
{
	
	const ERR_BRAND		= -1;
	const ERR_ITEM		= -2;
	const ERR_PARENT	= -3;
	const ERR_FILE		= -4;
	const ERR_DB		= -5;
	const ERR_NEW		= 1;

	const POSTED 		= 0;
	const RUNNING 		= 1;
	const COMPLETE 		= 2;
	
	public $Id;
	public $Type;			// Car_Brand type - TYRE or WHEEL
	public $CheckBalance;
	public $AddNew;
	public $Status;
	public $Filename;
	public $Filesize;
	public $IsFile;

	public $Added;
	public $Updated;
	public $Failed;
	
	public $PostedAt;
	
	private $errors = array();
	
	private $currentLine, $currentAuto, $currentSeason, $showLog;
	
	private static $brands, $autos, $spikes, $seasons;
	
	/**
	 * @see parent::getPrimary()
	 */
	protected function getPrimary()
	{
		return array('Id');
	}
	
	/**
	 * @see parent::getTableName()
	 */
	protected function getTableName()
	{
		return 'pricelists';
	}
	
	/**
	 * @see parent::getUploadFileInfo()
	 */
	public function getUploadFileInfo()
	{
		return array(
			'allow'			=> array('csv'),
			'folderFormat'	=> '%05d',
			'folderLimit'	=> 1000,
			'urlFormat'		=> true,
		);
	}
	
	/**
	 * @see parent::getFileUrl()
	 */
	public function getFileUrl( $class, $folder, $index, $ext )
	{
		$info = pathinfo( $this->Filename );
		return '/f/T'.$index.'F'.$folder.'I'.$this->Id.'/'.urlencode( preg_replace( '/\s/', '-', $info['filename'] ) ).'.'.$ext;
	}
	
	public function __construct()
	{
		parent::__construct();
		$this->PostedAt = time();
	}
	
	public function drop()
	{
		if ( parent::drop() )
		{
			File::detach( $this );
			$Log = new Pricelist_Log();
			$Log->dropList( array( 'ListId = '.$this->Id ) );
			return true;
		}
		return false;
	}
	
	public function getDate()
	{
		return date( 'd.m.y', $this->PostedAt );
	}
	
	public function loadPrices( $showLog = false )
	{
		if ( $this->Status <> self::POSTED )
		{
			return false;
		}
		$this->showLog = $showLog;
		$addNew = $this->AddNew;
		$time = time();
		$this->Status = self::RUNNING;
		$this->Added = $this->Updated = $this->Failed = 0;
		$this->save();
		
		$this->initStatic();
		
		$f = @fopen( File::path( $this ), 'r' );
		
		$this->currentSeason = Car_Tyre::ANY;
		$this->currentAuto = Car_Tyre::CAR;
		
		if ( $f )
		{
			$this->currentLine = 0;
			while ( ( $line = fgets( $f, 2048 ) ) !== false )
			{
				$line = mb_convert_encoding( $line, 'utf8', 'cp1251' );
				$arr = str_getcsv( $line, ';', '"' );
				
				$this->currentLine++;

				if ( count( $arr ) < 5 )
				{
					continue;
				}
				if ( is_numeric( $arr[0] ) )
				{
					$this->parseLine( $arr );
				}
				else
				{
					$this->currentSeason = $this->detectTitle( self::$seasons, $arr, Car_Tyre::ANY );
					$this->currentAuto = $this->detectTitle( self::$autos, $arr, Car_Tyre::CAR );
				}
				$this->save();
			}
			fclose( $f );
			$this->Status = self::COMPLETE;
			$this->save();
			if ( $this->CheckBalance )
			{
				$Tyre = new Car_Tyre();
				$params = array();
				$params[] = 'Type = '.$this->Type;
				$params[] = 'PriceAt < '.$time;
				$Tyre->updateList( array( 'Stock' => 0 ), $params );
			}
			return true;
		}
		else
		{
			$this->writeLog( self::ERR_FILE );
		}
		return true;
	}
	
	private function initStatic()
	{
		if ( self::$brands === null )
		{
			$brands = Car_Brand::getBrands( $this->Type, true );
			$brands = array_flip( $brands );
			foreach ( $brands as $name => $id )
			{
				$brands[ mb_strtolower( $name, 'utf8' ) ] = $id;
			}
			self::$brands = $brands;
		}
		if ( self::$autos === null )
		{
			self::$autos = array_flip( self::getAutos() );
		}
		if ( self::$spikes === null )
		{
			self::$spikes = array_flip( self::getSpikes() );
		}
		if ( self::$seasons === null )
		{
			self::$seasons = array_flip( self::getSeasons() );
		}
	}
	
	private function detectTitle( array $data, array $arr, $default = null )
	{
		$title = stristr( $arr[1], 'СКАТИ' );		
		if ( !empty( $title ) )
		{
			// detect season from title
			foreach ( $data as $name => $id )
			{
				$str = str_replace( $name, '', $title );
				if ( $title != $str )
				{
					return $id;
				}
			}
		}
		return $default;
	}

	private function parseLine( array $arr )
	{
		$Tyre = new Car_Tyre();

		$Tyre->Type = $this->Type;				
		$Tyre->PriceRaw = floatval( $arr[3] );
		$Tyre->Stock = intval( $arr[4] );

		if ( !$this->parseTyre( $arr[1], $Tyre ) )
		{
			return false;
		}
		
		if ( !$Tyre->isFilled() )
		{
			$this->writeLog( self::ERR_ITEM , $this->currentLine, 'Некоторые параметры не найдены' );
			if ( $this->showLog )
			{
				printf("Not filled: %-5s%s\n", $this->currentLine, implode(',', $arr));
			}
			return false;
		}
		else
		{
			//printf("%-5s%-5s%-5s%-5s%-5s\n", $this->currentLine, $Tyre->Weight, $Tyre->Speed, $Tyre->Name, $Tyre->Season );
		}

		$Parent = new Car_Tyre();
		$params = array();
		$params[] = 'Type = '.Car_Brand::TYRE;
		$params[] = 'BrandId = '.$Tyre->BrandId;
		$params[] = 'Name = '.$Tyre->Name;

		$clause = $params;
		$clause[] = 'ParentId = 0';

		$Parent = $Parent->findItem( $clause );
		if ( $Parent->Id )
		{
			// parent or exact tyre found
			$clause = $params;
			//$clause[] = 'ParentId = '.$Parent->Id;
			$clause[] = 'Width = '.$Tyre->Width;
			$clause[] = 'WeightExtra = '.$Tyre->WeightExtra;
			$clause[] = 'Profile = '.$Tyre->Profile;
			$clause[] = 'Diameter = '.$Tyre->Diameter;
			$clause[] = 'Season = '.$Tyre->Season;
			$clause[] = 'Spike = '.$Tyre->Spike;
			$clause[] = 'Speed = '.$Tyre->Speed;

			$Item = new Car_Tyre();
			$Item = $Item->findItem( $clause );

			if ( $Item->Id )
			{
				$Item->PriceRaw = $Tyre->PriceRaw;
				$Item->Stock = $Tyre->Stock;
				$Item->PriceAt = time();
				$Item->save();
				$this->Updated++;
			}
			else
			{
				$Tyre->ParentId = $Parent->Id;
				$Tyre->PriceAt = time();
				$Tyre->save();
				$this->Added++;
			}
		}
		else
		{
			// add new tyre
			$Tyre->ParentId = 0;
			$Tyre->PriceAt = time();
			$Tyre->save();
			$this->Added++;
		}
		return true;
	}
	
	private function parseTyre( $line, Car_Tyre $Tyre )
	{
		$line = preg_replace( '/\s+/', ' ', $line );
		$arr = explode( ' ', $line, 3 );
		if ( count( $arr ) < 3 )
		{
			/*$this->writeLog( self::ERR_ITEM, $this->currentLine, 'Wrong name cell' );
			if ( $this->showLog )
			{
				printf("Wrong name cell: %s\n", $line);
			}*/
			return false;
		}
		if ( preg_match( '/(\d+)\/(\d+)/', $arr[0], $res ) )
		{
			$Tyre->Width = $res[1];
			$Tyre->Profile = $res[2];
		}
		
		if( !$Tyre->Profile )
		{
			$Tyre->Profile = 80;
		}
		
		if ( preg_match('/R([\d,]+)([^\d]*)/', $arr[1], $res) )
		{
			$Tyre->Diameter = str_replace( ',', '.', $res[1] );
			$Tyre->Auto = $res[2] ? Car_Tyre::BUS : Car_Tyre::CAR;
		}
		
		$line = $this->detectBrand( $arr[2], $Tyre );
		//die(var_dump($line));
		
		if ( !$Tyre->BrandId )
		{
			$this->writeLog( self::ERR_BRAND, $this->currentLine, $arr[2] );
			if ( $this->showLog )
			{
				printf("Brand not found: %s\n", $line);
			}
			return false;
		}
		
		$line = $this->detectParam( 'Season', $line, $Tyre, self::$seasons, $this->currentSeason );
		$line = $this->detectParam( 'Spike', $line, $Tyre, self::$spikes );

		if ( preg_match('/\b([\d\/]+)([a-z]+)\b/i', $line, $res) )
		{								
			$tmp[] = explode( '/', $res[1]);
			foreach ( $tmp as $weight )
			{			
				$Tyre->Weight = $weight[0];
				if ( isset( $weight[1] ) )
				{
					$Tyre->WeightExtra = $weight[1];
				}				
			}						
				
			$Tyre->Speed = $res[2];
			$line = str_replace( $res[1].$res[2], '', $line );
		}		
		$Tyre->Name = $line;
		return true;
	}
	
	private function detectParam( $field, $string, Car_Tyre $Tyre, array $enum, $default = null )
	{
		foreach ( $enum as $name => $id )
		{
			$str = str_replace( $name, '', $string );
			if ( $string != $str )
			{
				$Tyre->$field = $id;
				return trim( $str );
			}
			else
			{
				$Tyre->$field = $default;
			}
		}
		return $string;
	}
	
	private function detectBrand( $line, Car_Tyre $Tyre )
	{
		$arr = explode( ' ', $line );

		for ( $i = count( $arr ); $i > 0; $i-- )
		{
			$test = mb_strtolower( implode( ' ', array_slice( $arr, 0, $i ) ), 'utf8' );
			if ( isset( self::$brands[ $test ] ) )
			{
				$Tyre->BrandId = self::$brands[ $test ];
				$line = implode( ' ', array_slice( $arr, $i ) );
				break;
			}
		}

		if ( !$Tyre->BrandId )
		{
			$this->writeLog( self::ERR_BRAND, $this->currentLine );
			if ( $this->showLog )
			{				
				printf("Brand not found in: %-5s%s\n", $this->currentLine, $line);
			}
			return false;
		}
		return $line;
	}
	
	private function writeLog( $type, $line = 0, $err = '' )
	{
		if ( !$this->Id )
		{
			return false;
		}
		$Log = new Pricelist_Log();
		$Log->ListId = $this->Id;
		$Log->Type = $type;
		$Log->Line = $line;
		$Log->Error = $err;
		return $Log->save();
	}
	
	public function getErrors()
	{
		$Log = new Pricelist_Log();
		return $Log->findList( array( 'ListId = '.$this->Id ), 'Id asc' );
	}

	public function getType()
	{
		$arr = self::getTypes();
		return isset( $arr[ $this->Type ] ) ? $arr[ $this->Type ] : null;
	}

	public function getStatus()
	{
		$arr = self::getStatuses();
		return isset( $arr[ $this->Status ] ) ? $arr[ $this->Status ] : null;
	}
	
	public static function getErrorType( $code )
	{
		switch ( $code )
		{
			case self::ERR_BRAND:
				return 'Бренд не найден';
				
			case self::ERR_FILE:
				return 'Файл не найден';
				
			case self::ERR_DB:
				return 'Ошибка записи данных';
				
			case self::ERR_ITEM:
				return 'Элемент не найден';
				
			case self::ERR_PARENT:
				return 'Родитель не найден';
				
			case self::ERR_NEW:
				return 'Новый элемент';
		}
		return 'Неизвестная ошибка';
	}
	
	public static function getErrorString( $value )
	{
		if ( $value instanceof Car_Tyre )
		{
			return $value->getName();
		}
		return $value;
	}

	private static function fixValue( $value, $column )
	{
		if ( in_array( $column, array('Width', 'Profile', 'Diameter', 'Price') ) )
		{
			return floatval( strtr( $value, array(',' => '.') ) );
		}
		return $value;
	}
	
	private static function getColumns( $type = Car_Brand::TYRE )
	{
		switch ( $type )
		{
			case Car_Brand::TYRE:
				return array('Brand', 'Width', 'Name', 'Desc', 'Season', 'Auto', 'Profile', 'Diameter', 'Weight', 'Speed', 'Spike', 'Stock', 'Price', 'Supplier', 'City');
				
			case Car_Brand::WHEEL:
				return array('Brand', 'Name', 'Desc', 'Width', 'Diameter', 'PCD1', 'PCD3', 'ET', 'HUB', 'Color', 'Material', 'PCD2', 'Stock', 'Price', 'Supplier', 'City');
		}
		return array();
	}
	
	private static function getMinPrice( array $item )
	{
		if ( isset( $item['Price'] ) )
		{
			return $item['Price'];
		}
		$arr = array();
		$min = null;
		for ( $i = 0; $i < 4; $i++ )
		{
			$price = floatval( strtr( $item['Price'.$i], array(',' => '.') ) );
			$arr[] = $price;
		}
		sort( $arr, SORT_NUMERIC );
		return isset( $arr[2] ) ? $arr[2] : null;
	}

	public static function getTypes()
	{
		return array(
			Car_Brand::TYRE 	=> 'Шины',
			Car_Brand::WHEEL 	=> 'Диски',
		);
	}

	public static function getStatuses()
	{
		return array(
			self::POSTED 	=> 'Новый',
			self::RUNNING	=> 'Обрабатывается',
			self::COMPLETE 	=> 'Загружен',
		);
	}

	public static function getAutos()
	{
		return array(
			Car_Tyre::CAR 			=> 'легковой',
			Car_Tyre::JEEP 			=> 'внедорожник',
			Car_Tyre::MOTO 			=> 'мото',
			Car_Tyre::BUS 			=> 'микроавтобус',
			Car_Tyre::TRUCK			=> 'вантажні',
		);
	}

	public static function getSpikes()
	{
		return array(
			Car_Tyre::SPIKE_NO		=> 'нешип',
			Car_Tyre::SPIKE_WITH	=> 'нип',
			Car_Tyre::SPIKE_FOR		=> 'під шип',
		);
	}

	public static function getSeasons()
	{
		return array(
			Car_Tyre::SUMMER	=> 'літ',
			Car_Tyre::ANY		=> 'всесез',
			Car_Tyre::WINTER	=> 'зим',			
		);
	}

}
