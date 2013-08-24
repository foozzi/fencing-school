<?

if ( isset( $argv[1] ) )
{
	$_SERVER['REQUEST_URI'] = $argv[1];
}

function parseCreateTable( $string )
{
	$table = array(
		'name'		=> '',
		'columns'	=> array(),
		'indexes'	=> array(),
		'engine'	=> '',
		'charset'	=> '',
	);
	$idx = array('primary', 'index', 'unique', 'key');
	if ( preg_match( '/^create table ([\w\$_\s\`]+)\(/i', $string, $res ) )
	{
		$table['name'] = trim( $res[1] );
		if ( preg_match( '/\(([^;]+)/', $string, $res ) )
		{
			foreach ( preg_split( '/(\n|\r\n)/', $res[1] ) as $line )
			{
				$line = rtrim( trim( $line ), ',' );
				$arr = explode( ' ', $line, 2 );
				if ( count( $arr ) == 2 )
				{
					if ( in_array( strtolower( $arr[0] ), $idx ) )
					{
						$index = null;
						$columns = '';
						if ( preg_match( '/^primary key (.+)$/i', $line, $res ) )
						{
							$index = 'primary';
							$columns = $res[1];
						}
						else if ( preg_match( '/^(unique key|unique) .*\((.+)\)$/i', $line, $res ) )
						{
							$index = 'unique';
							$columns = $res[2];
						}
						else if ( preg_match( '/^(index key|index|key) .*\((.+)\)$/i', $line, $res ) )
						{
							$index = 'index';
							$columns = $res[2];
						}
						if ( $index )
						{
							$table['indexes'][] = $index.' '.preg_replace( '/[`\(\) ]/', '', $columns );
						}
					}
					else
					{
						$line = str_replace( '`', '', $line );
						$arr = explode( ' ', $line, 3 );
						if ( count( $arr ) == 3 )
						{
							if ( $arr[0] == ')' )
							{
								if ( preg_match( '/engine(=|= | =| = )(\w+)/i', $line, $res ) )
								{
									$table['engine'] = $res[2];
								}
								if ( preg_match( '/default charset(=|= | =| = )(\w+)/i', $line, $res ) )
								{
									$table['charset'] = $res[2];
								}
							}
							else
							{
								$table['columns'][] = $arr[0].' '
									.preg_replace( '/int\(\d+\)/', 'int', strtolower( $arr[1] ) ).' '
									.str_replace( 'UNSIGNED', 'unsigned', strtoupper( $arr[2] ) );
							}
						}
						else
						{
							
						}
					}
				}
			}
			sort( $table['indexes'] );
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
	return $table;
}

function compareTables( array $old, array $new )
{
	$result = array();
	foreach ( array( 'name', 'columns', 'indexes', 'engine' ) as $key )
	{
		if ( !isset( $old[ $key ] ) || !isset( $new[ $key ] ) )
		{
			return array();
		}
	}
	$prev = null;
	$names = array();
	foreach ( $new['columns'] as $i => $column )
	{
		$arr = explode( ' ', $column, 2 );
		$name = $arr[0];
		$def = $arr[1];
		$names[] = $name;

		$orig = isset( $old['columns'][ $i ] ) ? $old['columns'][ $i ] : null;
		$orig = preg_replace( '/collate (\S+)/i', '', $orig );		// replace COLLATE utf8_bin to empty string
		$orig = preg_replace( '/\s+/', ' ', $orig );				// replace more than one space to one

		if ( $orig == $column )
		{
			// columns are equal
		}
		else if ( in_array( $column, $old['columns'] ) )
		{
			// columns are equal but position is different
			$result[ '^'.$name ] = 'alter table `'.$new['name'].'` change column `'.$name.'` `'.$name.'` '.$def.' '.( $prev ? 'after '.$prev : 'first');
		}
		else
		{
			$gotIt = false;
			foreach ( $old['columns'] as $col )
			{
				$arr = explode( ' ', $col, 2 );
				if ( $arr[0] == $name )
				{
					$gotIt = true;
					break;
				}
			}
			if ( $gotIt )
			{
				// column is different
				$result[ '&'.$name ] = 'alter table `'.$new['name'].'` change column `'.$name.'` `'.$name.'` '.$def.' '.( $prev ? 'after '.$prev : 'first');
			}
			else
			{
				// new column
				$result[ '+'.$name ] = 'alter table `'.$new['name'].'` add column `'.$name.'` '.$def.' '.( $prev ? 'after '.$prev : 'first');
			}
		}
		$prev = $name;
	}
	$drop = array();
	foreach ( $old['columns'] as $col )
	{
		$arr = explode( ' ', $col, 2 );
		if ( !in_array( $arr[0], $names ) )
		{
			$drop[] = $arr[0];
		}
	}
	foreach ( $drop as $col )
	{
		$result[ '-'.$col ] = 'alter table `'.$new['name'].'` drop column `'.$col.'`';
	}
	
	$drop = array();
	foreach ( $old['indexes'] as $index )
	{
		if ( !in_array( $index, $new['indexes'] ) )
		{
			$arr = explode( ' ', $index );
			$cols = explode( ',', $arr[1] );
			$drop[] = $arr[0] == 'primary' ? 'primary key' : 'index `'.$cols[0].'`';
		}
	}
	foreach ( $drop as $index )
	{
		$result[ '*'.$index ] = 'alter table `'.$new['name'].'` drop '.$index;
	}
	foreach ( $new['indexes'] as $index )
	{
		if ( in_array( $index, $old['indexes'] ) )
		{
			// indexes are equal
		}
		else
		{
			// new index
			$arr = explode( ' ', $index, 2 );
			$cols = array();
			foreach ( explode( ',', $arr[1] ) as $col )
			{
				$cols[] = '`'.$col.'`';
			}
			$result[ '*'.trim( $cols[0], '`' ) ] = 'alter table `'.$new['name'].'` add '
				.str_replace( 'primary', 'primary key', $arr[0] ).' ('.implode( ',', $cols ).')';
		}
	}
	return $result;
}

include_once( dirname( dirname( __FILE__ ) ).'/includes/application.php' );

Application::run('config');

$codes = array( '00000', '42S01', '23000', '42S21' );

$DB = Database::getInstance();
$len = strlen( Runtime::get('INCLUDE_DIR').'/models' ) + 1;

$error = array();

Console::writeln('Database objects update');
foreach ( File::readDir( Runtime::get('INCLUDE_DIR').'/models', true, null, '*.php' ) as $file )
{
	$content = file_get_contents( $file );
	if ( preg_match( '/{INSTALL:SQL{(.*)}}/is', $content, $res ) )
	{
		$file = substr( $file, $len );
		//Console::writeln( $file, 30 );
		
		$lines = preg_split( "/;(\n|\r\n){2}/", $res[1] );
		foreach ( $lines as $query )
		{
			$new = parseCreateTable( trim( $query ) );
			if ( $new )
			{
				if ( preg_match( '/class ([\w\_]+) extends/i', $content, $res ) )
				{
					$obj = new $res[1]();
					$missed = $extra = $cols = array();
					foreach( get_object_vars( $obj ) as $field => $value )
					{
						$found = false;
						foreach ( $new['columns'] as $col )
						{
							$arr = explode( ' ', $col, 2 );
							if ( !in_array( $arr[0], $cols ) )
							{
								$cols[] = $arr[0];
							}
							if ( $arr[0] == $field )
							{
								$found = true;
							}
						}
						if ( !$found )
						{
							$missed[] = $field;
						}
					}
					foreach ( $cols as $field )
					{
						if ( !property_exists( $obj, $field ) )
						{
							$extra[] = $field;
						}
					}
					if ( count( $missed ) )
					{
						Console::left( get_class( $obj ).' missed fields', 40 );
						Console::write( $missed );
						Console::writeln();
					}
					if ( count( $extra ) )
					{
						Console::left( get_class( $obj ).' extra fields', 40 );
						Console::write( $extra );
						Console::writeln();
					}
				}
				$old = null;
				$arr = $DB->query('show create table `'.$new['name'].'`');
				if ( isset( $arr[0]['Create Table'] ) )
				{
					$old = parseCreateTable( $arr[0]['Create Table'] );
				}
				if ( $old )
				{
					// existent table
					$arr = compareTables( $old, $new );
					if ( count( $arr ) )
					{
						Console::left( $new['name'], 30 );
						foreach ( $arr as $key => $q )
						{
							$DB->execute( $q );
							if ( in_array( $DB->getError(), $codes ) )
							{
								Console::write( $key.' ' );
							}
							else
							{
								Console::write( $key.' ' );
								$error[] = $q;
							}
						}
						Console::writeln();
					}
					else
					{
						
					}
				}
				else
				{
					Console::left( $new['name'], 30 );
					// new table
					$DB->execute( $query );
					if ( in_array( $DB->getError(), $codes ) )
					{
						Console::writeln('added');
					}
					else
					{
						Console::writeln('FAILED!');
						//$error[] = $query;
						$error[] = $DB->getError(true);
					}
				}
			}
			continue;
		}
	}
}
Console::writeln( $error );
Console::writeln('DONE');
