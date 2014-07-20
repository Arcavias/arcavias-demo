<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2014
 * @license LGPLv3, http://www.arcavias.com/en/license
 */

class Email_WatchTest extends MW_Unittest_Testcase
{
	private static $_context;
	private static $_customer;
	private static $_products;
	private $_view;
	private $_mail;
	private $_message;


	public static function setUpBeforeClass()
	{
		if( !class_exists( 'Zend_Mail' ) ) {
			return;
		}

		self::$_context = TestHelper::getContext();
		$config = self::$_context->getConfig();

		$ds = DIRECTORY_SEPARATOR;
		$path = dirname( dirname( __DIR__ ) ) . $ds . 'zfapp' . $ds . 'public' . $ds . 'images' . $ds . 'arcavias.png';
		$config->set( 'client/html/email/logo', $path );

		$zOptions = array();
		if( ( $v = $config->get( 'client/html/email/server/port' ) ) != '' ) { $zOptions['port'] = $v; }
		if( ( $v = $config->get( 'client/html/email/server/ssl' ) ) != '' ) { $zOptions['ssl'] = $v; }
		if( ( $v = $config->get( 'client/html/email/server/auth' ) ) != '' ) { $zOptions['auth'] = $v; }
		if( ( $v = $config->get( 'client/html/email/server/username' ) ) != '' ) { $zOptions['username'] = $v; }
		if( ( $v = $config->get( 'client/html/email/server/password' ) ) != '' ) { $zOptions['password'] = $v; }
		$zServer = $config->get( 'client/html/email/server/host', 'localhost' );

		Zend_Mail::setDefaultTransport( new Zend_Mail_Transport_Smtp( $zServer, $zOptions ) );


		$productManager = MShop_Product_Manager_Factory::createManager( self::$_context );

		$search = $productManager->createSearch();
		$search->setConditions( $search->compare( '==', 'product.code', array( 'CNC', 'CNE' ) ) );

		self::$_products = $productManager->searchItems( $search, array( 'text', 'price', 'media' ) );


		$customerManager = MShop_Customer_Manager_Factory::createManager( self::$_context );

		$search = $customerManager->createSearch();
		$search->setConditions( $search->compare( '==', 'customer.code', 'UTC001' ) );
		$result = $customerManager->searchItems( $search );

		if( ( self::$_customer = reset( $result ) ) === false ) {
			throw new Exception( 'No customer found' );
		}
	}


	protected function setUp()
	{
		if( !class_exists( 'Zend_Mail' ) ) {
			$this->markTestSkipped( 'Zend_Mail not available' );
		}

		$config = self::$_context->getConfig();
		if( ( $recvEmail = $config->get( 'tests/email/destaddr' ) ) == null ) {
			$this->markTestSkipped( 'No receiver e-mail address, use ./config/tests.php with "email/destaddr" key' );
		}

		$this->_mail = new MW_Mail_Zend( new Zend_Mail( 'UTF-8' ) );
		$this->_message = $this->_mail->createMessage( 'UTF-8' );

		$paths = TestHelper::getHtmlTemplatePaths();
		$this->_object = new Client_Html_Email_Watch_Default( self::$_context, $paths );

		$this->_view = TestHelper::getView( 'unittest', $config );
		$this->_view->addHelper( 'mail', new MW_View_Helper_Mail_Default( $this->_view, $this->_message ) );
		$this->_object->setView( $this->_view );


		$addr = self::$_customer->getPaymentAddress();
		$addr->setEmail( $recvEmail );

		$this->_view->extAddressItem = $addr;
		$this->_view->extProducts = self::$_products;
	}


	public function testSend()
	{
		$this->_object->getHeader();
		$this->_object->getBody();

		$this->_mail->send( $this->_message );
	}

}
