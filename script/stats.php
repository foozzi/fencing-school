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

$m = File::readClasses( INCLUDE_MODELS_DIR );
$c = File::readClasses( INCLUDE_CONTROLLERS_DIR, 'Controller_' );
$v = File::readClasses( INCLUDE_VIEWS_DIR, 'View_' );
$t = array();

$dir = Runtime::get('TEMPLATE_DIR');
foreach ( File::readDir( $dir, true, null, '.html' ) as $file )
{
	if ( is_dir( $file ) )
	{
		continue;
	}
	$text = file_get_contents( $file );
	$arr = explode( "\n", $text );
	$t[] = array( str_replace( $dir.'/', '', $file ), filesize( $file ), count( $arr ) );
}


$stats = array('size' => array('m' => 0, 'c' => 0, 'v' => 0, 't' => 0, 'a' => 0), 'lines' => array('m' => 0, 'c' => 0, 'v' => 0, 't' => 0, 'a' => 0));
foreach ( $stats['size'] as $i => $value )
{
	if ( $i == 'a' )
	{
		break;
	}
	foreach ( $$i as $item )
	{
		$stats['size'][ $i ] += $item[1];
		$stats['lines'][ $i ] += $item[2];
		$stats['size']['a'] += $item[1];
		$stats['lines']['a'] += $item[2];
	}
}

Console::writeln();
Console::write('/');
Console::write(str_repeat('=', 98));
Console::writeln('\\');
Console::write('|');
Console::left('       Models', 18);
Console::write('|');
Console::left('    Controllers', 19);
Console::write('|');
Console::left('       Views', 19);
Console::write('|');
Console::left('      Templates', 19);
Console::write('|');
Console::left('       Total', 19);
Console::writeln('|');
Console::write('| ');

foreach ( $stats['size'] as $i => $value )
{
	if ( $i == 'a' )
	{
		break;
	}
	Console::left(count( $$i ), 4);
	Console::left(File::getFilesize( $stats['size'][ $i ], 'b,K,M,G' ), 5);
	Console::left($stats['lines'][ $i ].'L', 8);
	Console::write('|  ');
}

Console::left(count( $m ) + count( $c ) + count( $v ), 4);
Console::left(File::getFilesize( $stats['size']['a'], 'b,K,M,G' ), 5);
Console::left($stats['lines']['a'].'L', 8);
Console::writeln('|');
Console::write('\\');
Console::write(str_repeat('=', 98));
Console::writeln('/');
Console::writeln();

if ( $argv[1] )
{
	$sign = '$';
	$time = $price = 0;
	$arr = explode( '/', $argv[1] );
	if ( count( $arr ) == 2 )
	{
		$price = $stats['lines']['a'] / $arr[1];
		$sign = $arr[0] ? $arr[0] : $sign;
	}
	else
	{
		$price = $stats['lines']['a'] * $arr[1];
	}
	$time = round( $stats['lines']['a'] / 1500 );
	Console::writeln('Price: '.Price::show( $price ).$sign.', Time: '.$time.' days.');
	Console::writeln();
}

		
//Console::writeln('DONE');