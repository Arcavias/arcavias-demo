<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2011
 * @license LGPLv3, http://www.arcavias.com/en/license
 */

header( 'Content-type: text/html; charset=UTF-8' );

header( "Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://demo.arcavias.com" );
header( "X-Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://demo.arcavias.com" );
header( "X-Webkit-CSP: default-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://demo.arcavias.com" );


define( 'ZFAPP_ROOT', dirname( dirname( __FILE__ ) ) );
define( 'APPLICATION_PATH', ZFAPP_ROOT . DIRECTORY_SEPARATOR . 'application' );
define( 'APPLICATION_ENV', 'development' ); // development | production

if ( APPLICATION_ENV == 'development' ) {
	error_reporting( -1 );
	ini_set( 'display_errors', true );
}

date_default_timezone_set( 'UTC' );
setlocale( LC_CTYPE, 'en_US.UTF8' );

if( !function_exists( '_' ) ) {
	function _() {};
}


try
{
	require_once dirname( ZFAPP_ROOT ) . '/vendor/autoload.php';

	$includePaths = array(
		ZFAPP_ROOT . DIRECTORY_SEPARATOR . 'library',
		get_include_path(),
	);
	set_include_path( implode( PATH_SEPARATOR, $includePaths ) );

	include_once ZFAPP_ROOT . '/data/cache/pluginLoaderCache.php';

	$application = new Application_Application(
		APPLICATION_ENV,
		include_once realpath( APPLICATION_PATH . '/configs/application.php' )
	);

	$application->bootstrap()->run();

} catch ( Zend_Controller_Exception $e ) {
	include 'errors/404.phtml';
} catch ( Exception $e ) {
	include 'errors/500.phtml';
}