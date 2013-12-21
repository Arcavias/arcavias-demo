<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2013
 * @license LGPLv3, http://www.arcavias.com/en/license
 */

class Email_PaymentTest extends MW_Unittest_Testcase
{
	private static $_context;
	private static $_orderItem;
	private static $_orderBaseItem;
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


		$orderManager = MShop_Order_Manager_Factory::createManager( TestHelper::getContext() );
		$orderBaseManager = $orderManager->getSubManager( 'base' );

		$search = $orderManager->createSearch();
		$search->setConditions( $search->compare( '==', 'order.datepayment', '2008-02-15 12:34:56' ) );
		$result = $orderManager->searchItems( $search );

		if( ( self::$_orderItem = reset( $result ) ) === false ) {
			throw new Exception( 'No order found' );
		}

		self::$_orderBaseItem = $orderBaseManager->load( self::$_orderItem->getBaseId() );
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
		$this->_object = new Client_Html_Email_Payment_Default( self::$_context, $paths );

		$this->_view = TestHelper::getView( 'unittest', $config );
		$this->_view->addHelper( 'mail', new MW_View_Helper_Mail_Default( $this->_view, $this->_message ) );
		$this->_object->setView( $this->_view );


		$this->_view->extOrderItem = clone self::$_orderItem;
		$this->_view->extOrderBaseItem = clone self::$_orderBaseItem;

		$address = $this->_view->extOrderBaseItem->getAddress( MShop_Order_Item_Base_Address_Abstract::TYPE_PAYMENT );
		$address->setEmail( $recvEmail );

	}


	public function testSendReceived()
	{
		$this->_view->extOrderItem->setPaymentStatus( MShop_Order_Item_Abstract::PAY_RECEIVED );

		$this->_object->getHeader();
		$this->_object->getBody();

		$this->_mail->send( $this->_message );
	}


	public function testSendAuthorized()
	{
		$this->_view->extOrderItem->setPaymentStatus( MShop_Order_Item_Abstract::PAY_AUTHORIZED );

		$this->_object->getHeader();
		$this->_object->getBody();

		$this->_mail->send( $this->_message );
	}


	public function testSendPending()
	{
		$this->_view->extOrderItem->setPaymentStatus( MShop_Order_Item_Abstract::PAY_PENDING );

		$this->_object->getHeader();
		$this->_object->getBody();

		$this->_mail->send( $this->_message );
	}


	public function testSendRefund()
	{
		$this->_view->extOrderItem->setPaymentStatus( MShop_Order_Item_Abstract::PAY_REFUND );

		$this->_object->getHeader();
		$this->_object->getBody();

		$this->_mail->send( $this->_message );
	}

}
