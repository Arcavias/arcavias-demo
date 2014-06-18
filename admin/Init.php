<?php

/**
 * @copyright Copyright (c) Metaways Infosystems GmbH, 2011
 * @license LGPLv3, http://www.arcavias.com/en/license
 */


class Init
{
	private $_arcavias;
	private $_context;


	public function __construct( Arcavias $arcavias, array $confList )
	{
		$configPaths = $arcavias->getConfigPaths( 'mysql' );
		$configPaths = array_merge( $configPaths, $confList );

		$this->_context = $this->_createContext( $configPaths );
		$this->_arcavias = $arcavias;
	}


	public function getJsonClientConfig()
	{
		$config = $this->_context->getConfig()->get( 'client/extjs', array() );
		return json_encode( array( 'client' => array( 'extjs' => $config ) ), JSON_FORCE_OBJECT );
	}


	public function getJsonClientI18n( $locale )
	{
		$i18nPaths = $this->_arcavias->getI18nPaths();
		$i18n = new MW_Translation_Zend( $i18nPaths, 'gettext', $locale, array('disableNotices'=>true) );

		$content = array(
			'client/extjs' => $i18n->getAll( 'client/extjs' ),
			'client/extjs/ext' => $i18n->getAll( 'client/extjs/ext' ),
		);

		return json_encode( $content, JSON_FORCE_OBJECT );
	}


	public function getJsonRpcController()
	{
		$cntlPaths = $this->_arcavias->getCustomPaths( 'controller/extjs' );

		return new Controller_ExtJS_JsonRpc( $this->_context, $cntlPaths );
	}

	/**
	 * Creates a array of all available translations
	 *
	 * @return array List of language IDs with labels
	 */
	public function getAvailableLanguages()
	{
		$languageManager = MShop_Factory::createManager( $this->_context, 'locale/language' );
		$paths = $this->_arcavias->getI18nPaths();
		$langs = $result = array();

		if( isset( $paths['client/extjs'] ) )
		{
			foreach( $paths['client/extjs'] as $path )
			{
				if( ( $scan = scandir( $path ) ) !== false )
				{
					foreach( $scan as $file )
					{
						if( preg_match('/^[a-z]{2,3}(_[A-Z]{2})?$/', $file ) ) {
							$langs[$file] = null;
						}
					}
				}
			}
		}

		$search = $languageManager->createSearch();
		$search->setConditions( $search->compare('==', 'locale.language.id', array_keys( $langs ) ) );
		$search->setSortations( array( $search->sort( '-', 'locale.language.status' ), $search->sort( '+', 'locale.language.label' ) ) );
		$langItems = $languageManager->searchItems( $search );

		foreach( $langItems as $id => $item ) {
			$result[] = array( 'id' => $id, 'label' => $item->getLabel() );
		}

		return $result;
	}

	public function getJsonSite( $site )
	{
		$localeManager = MShop_Locale_Manager_Factory::createManager( $this->_context );
		$manager = $localeManager->getSubManager( 'site' );

		if(  $site === null || $site === '' ) {
			return json_encode( $manager->createItem()->toArray() );
		}

		$criteria = $manager->createSearch();
		$criteria->setConditions( $criteria->compare( '==', 'locale.site.code', $site ) );
		$items = $manager->searchItems( $criteria );

		if( ( $item = reset( $items ) ) === false ) {
			throw new Exception( sprintf( 'No site found for code "%1$s"', $site ) );
		}

		return json_encode( $item->toArray() );
	}


	public function getHtml( $absdir, $relpath )
	{
		while ( basename( $absdir ) === basename( $relpath ) ) {
			$absdir = dirname( $absdir );
			$relpath = dirname( $relpath );
		}

		$relpath = rtrim( $relpath, '/' );
		$abslen = strlen( $absdir );
		$html = '';

		foreach( $this->_arcavias->getCustomPaths( 'client/extjs' ) as $base => $paths )
		{
			$relJsbPath = substr( $base, $abslen );

			foreach( $paths as $path )
			{
				$jsbPath = $relpath . $relJsbPath . '/' . $path;
				$jsbAbsPath = $base . '/' . $path;

				if( !is_file( $jsbAbsPath ) ) {
					throw new Exception( sprintf( 'JSB2 file "%1$s" not found', $jsbAbsPath ) );
				}

				$jsb2 = new MW_Jsb2_Default( $jsbAbsPath, dirname( $jsbPath ) );
				$html .= $jsb2->getHTML( 'css' );
				$html .= $jsb2->getHTML( 'js' );
			}
		}

		return $html;
	}


	protected function _createContext( array $confPaths )
	{
		$context = new MShop_Context_Item_Default();

		$config = new MW_Config_Array( array(), $confPaths );
		if( function_exists( 'apc_store' ) === true ) {
			$config = new MW_Config_Decorator_APC( $config );
		}
		$config = new MW_Config_Decorator_Memory( $config );
		$context->setConfig( $config );

		$dbm = new MW_DB_Manager_PDO( $config );
		$context->setDatabaseManager( $dbm );

		$logger = MAdmin_Log_Manager_Factory::createManager( $context );
		$context->setLogger( $logger );

		$locale = MShop_Locale_Manager_Factory::createManager( $context )->createItem();
		$context->setLocale( $locale );

		$cache = new MAdmin_Cache_Proxy_Default( $context );
		$context->setCache( $cache );

		$context->setEditor( 'tests' );

		return $context;
	}
}
