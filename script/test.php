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

switch ( $argv[1] )
{
	case 'adduser':
	
		$Admin = new Admin();
		if ( $argv[2] )
		{
			echo "Adding user ".$argv[2];
			$Admin->Login = $argv[2];
			$Admin->Password = Admin::pwd( $argv[3] );
			$Admin->IsSuper = 1;
			if ( $Admin->save() )
			{
				echo " - OK\n";
			}
			else
			{
				echo " - FAILED\n";
			}
		}
		else
		{
			echo "Login is not set\n";
		}
	
		break;

	case 'console':
		
		$str = "Hello\nMy name is\nYarick";
		$numNewLines = substr_count($str, "\n");
		echo chr(27) . "[0G"; // Set cursor to first column
		echo $str;
		echo chr(27) . "[" . $numNewLines ."A"; // Set cursor up x lines
		sleep(1);
		echo "\rHere         \nit           \ncomes          \n";
    		
		break;

	case 'slugs':

		Console::writeln('Brands');
		$Brand = new Car_Brand();
		foreach ( $Brand->findList() as $Brand )
		{
			$Brand->Slug = preg_replace( '/[^\w\d\._]+/', '_', String::translit( $Brand->Name ) );
			$Brand->save();
			Console::write('.');
		}
		Console::writeln();

		Console::writeln('Models');
		$Model = new Car_Model();
		foreach ( $Model->findList() as $Model )
		{
			$Model->Slug = preg_replace( '/[^\w\d\._]+/', '_', trim( String::translit( $Model->Name ), ' "\'()' ) );
			$Model->save();
			Console::write('.');
		}
		Console::writeln();

		Console::writeln('Engines');
		$Engine = new Car_Engine();
		foreach ( $Engine->findList() as $Engine )
		{
			$Engine->Slug = preg_replace( '/[^\w\d\._]+/', '_', trim( String::translit( $Engine->Name ), ' "\'()' ) );
			$Engine->save();
			Console::write('.');
		}
		Console::writeln();

		break;

	case 'cleanup':
		$Tyre = new Car_Tyre();
		foreach ( $Tyre->findList( array( 'Type <> 1' ) ) as $Tyre )
		{
			Console::write('.');
			$Tyre->drop();
		}
		Console::writeln();
		Console::writeln('Done');
		break;
		
	case 'prices':
	
		$Price = new Pricelist();
		foreach ( $Price->findList( array(), 'Id desc', 0, 1 ) as $Price );
		if ( $argv[2] == 'force' )
		{
			$Price->Status = Pricelist::POSTED;
		}
		if ( $Price->Id )
		{
			$Price->loadPrices(true);
			Console::writeln('Done.');
		}
		else
		{
			Console::writeln('No file to load');
		}
	
		break;

	case 'sitemap':

		Cron::sitemap();
		Console::writeln('Done.');

		break;
		
}


echo "\n";
