<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2013
 * @license LGPLv3, http://www.arcavias.com/en/license
 */

/**
 * Account controller
 */
class AccountController extends Application_Controller_Action_Abstract
{
	/**
	 * Integrates the account history.
	 */
	public function historyAction()
	{
		$startaction = microtime( true );

		try
		{
			$arcavias = $this->_getArcavias();
			$context = Zend_Registry::get( 'ctx' );
			$templatePaths = $arcavias->getCustomPaths( 'client/html' );


			$conf = array( 'client' => array( 'html' => array(
				'catalog' => array( 'filter' => array(
					'default' => array( 'subparts' => array( 'search' ) )
				) )
			) ) );

			$localContext = clone $context;
			$localConfig = new MW_Config_Decorator_Memory( $localContext->getConfig(), $conf );
			$localContext->setConfig( $localConfig );

			$this->view->searchfilter = Client_Html_Catalog_Filter_Factory::createClient( $localContext, $templatePaths );
			$this->view->searchfilter->setView( $this->_createView() );


			$this->view->minibasket = Client_Html_Basket_Mini_Factory::createClient( $context, $templatePaths );
			$this->view->minibasket->setView( $this->_createView() );
			$this->view->minibasket->process();


			$this->view->account = Client_Html_Account_History_Factory::createClient( $context, $templatePaths );
			$this->view->account->setView( $this->_createView() );
			$this->view->account->process();

			$this->render( 'index' );
		}
		catch( MW_Exception $e )
		{
			echo 'A database error occured';
		}
		catch( Exception $e )
		{
			echo 'Error: ' . $e->getMessage();
		}


		$msg = 'Account total time: ' . ( ( microtime( true ) - $startaction ) * 1000 ) . 'ms';
		$context->getLogger()->log( $msg, MW_Logger_Abstract::INFO, 'performance' );
	}

}
