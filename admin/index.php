<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2011
 * @license LGPLv3, http://www.arcavias.com/en/license
*/

header( 'Content-type: text/html; charset=UTF-8' );

try
{
	$appdir = dirname( __FILE__ )  . DIRECTORY_SEPARATOR;
	$basedir = dirname( $appdir ) . DIRECTORY_SEPARATOR;

	date_default_timezone_set('UTC');

	require $basedir . 'vendor/autoload.php';

	$configPaths = array( $basedir. 'config', $appdir . 'config' );

	$arcavias = new Arcavias( array( $basedir . 'ext' ), true, dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'arcavias' . DIRECTORY_SEPARATOR . 'arcavias-core' . DIRECTORY_SEPARATOR );
	$init = new Init( $arcavias, $configPaths );

	$html = $init->getHtml( realpath($_SERVER['SCRIPT_FILENAME']), $_SERVER['SCRIPT_NAME'] );
	$site = $init->getJsonSite( ( isset( $_REQUEST['site'] ) ? $_REQUEST['site'] : 'unittest' ) );
	$jsonrpc = $init->getJsonRpcController();

	$itemSchema = $jsonrpc->getJsonItemSchemas();
	$searchSchema = $jsonrpc->getJsonSearchSchemas();
	$smd = $jsonrpc->getJsonSmd( 'jsonrpc.php' );
	$config = $init->getJsonClientConfig();
	$i18n = $init->getJsonClientI18n( 'en' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title>Arcavias ExtJS Admin Interface</title>
	<script type="text/javascript">

		window.MShop = {

			i18n: {
				locale: 'en',
				content: <?php echo $i18n; ?>
			},

			config: {
				data: <?php echo $config; ?>,

				site: <?php echo $site; ?>,

				itemschema: <?php echo $itemSchema ?>,

				searchschema: <?php echo $searchSchema ?>,

				smd: <?php echo $smd ?>,

				urlTemplate: "index.php?&site={site}&tab={tab}",

				activeTab: <?php echo isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 0; ?>,

				baseurl: {
					content: '../images'
				}
			}
		}
	</script>
	<?php echo $html; ?>
</head>
<body>
	<noscript>
		<p>You need to enable javascript!</p>
	</noscript>
<?php
}
catch( Exception $e )
{
	echo '<p>Please make sure you\'ve executed <strong>phing install</strong> on the command line</p>';
	echo '<p>' . $e->getMessage() . '</p>';
	echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
?>
</body>
</html>
