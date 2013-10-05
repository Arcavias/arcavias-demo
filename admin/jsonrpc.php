<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2011
 * @license LGPLv3, http://www.arcavias.com/en/license
 */


$appdir = dirname( __FILE__ )  . DIRECTORY_SEPARATOR;
$basedir = dirname( $appdir ) . DIRECTORY_SEPARATOR;

require_once $basedir. 'vendor/autoload.php';

$config = array(
	$basedir . 'config',
	$appdir . 'config',
);

$arcavias = new Arcavias( array( $basedir . 'ext' ) );
$init = new Init( $arcavias, $config );

echo $init->getJsonRpcController()->process( $_REQUEST, 'php://input' );
