<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2012
 * @license LGPLv3, http://www.arcavias.com/en/license
*/

function usage()
{
	printf( 'Usage: %1$s [--extdir=<dir>]* [--stats] "<job1> [<job2> ...]" ["<site> ..."]' . PHP_EOL, $_SERVER['argv'][0] );
	exit ( 1 );
}


try
{
	if( $_SERVER['argc'] < 2 ) {
		usage();
	}

	$exectimeStart = microtime( true );

	$params = $_SERVER['argv'];
	array_shift( $params );
	$options = array();

	foreach( $params as $key => $option )
	{
		if( $option === '--help' ) {
			usage();
		}

		if( strncmp( $option, '--', 2 ) === 0 )
		{
			if( ( $pos = strpos( $option, '=', 2 ) ) !== false )
			{
				if( ( $name = substr( $option, 2, $pos-2 ) ) !== false )
				{
					if( isset( $options[$name] ) )
					{
						$options[$name] = (array) $options[$name];
						$options[$name][] = substr( $option, $pos+1 );
					}
					else
					{
						$options[$name] = substr( $option, $pos+1 );
					}
				}
				else
				{
					printf( "Invalid option \"%1\$s\"\n", $option );
					usage();
				}
			}
			else
			{
				if( ( $name = substr( $option, 2 ) ) !== false )
				{
					$options[$name] = true;
				}
				else
				{
					printf( "Invalid option \"%1\$s\"\n", $option );
					usage();
				}
			}

			unset( $params[$key] );
		}
	}

	if( count( $params ) > 0 && ( $jobnames = array_shift( $params ) ) === null ) {
		usage();
	}
	$jobnames = explode( ' ', $jobnames );

	$sites = array( 'default' );
	if( count( $params ) > 0 ) {
		$sitenames = explode( ' ', array_shift( $params ) );
	}


	date_default_timezone_set( 'UTC' );

	$appdir = dirname( __FILE__ )  . DIRECTORY_SEPARATOR;
	$basedir = dirname( $appdir ) . DIRECTORY_SEPARATOR;

	$includePaths = array(
		$basedir. 'zendlib',
		get_include_path(),
	);

	if( set_include_path( implode( PATH_SEPARATOR, $includePaths ) ) === false ) {
		throw new Exception( 'Unable to set include path' );
	}


	require_once $basedir . 'vendor/autoload.php';

	$arcavias = new Arcavias( ( isset( $options['extdir'] ) ? (array) $options['extdir'] : array( $basedir . 'ext' ) ) );

	$configPaths = $arcavias->getConfigPaths( 'mysql' );
	$configPaths[] = $basedir . 'config';
	$configPaths[] = $appdir . 'config';

	$jobs = new Jobs( $arcavias, $configPaths );
	$jobs->execute( $jobnames, $sites );

	if( isset( $options['stats'] ) ) {
		printf( "Statistics: \n" );
		printf( "- execution time: %1\$.2f sec\n", ( microtime( true ) - $exectimeStart ) );
		printf( "- peak memory usage: %1\$.2f MiB\n", memory_get_peak_usage( true ) / 1024 / 1024 );
	}

}
catch( Exception $e )
{
	error_log( sprintf( 'Caught exception: "%1$s"', $e->getMessage() ) );
	error_log( $e->getTraceAsString() );
}
