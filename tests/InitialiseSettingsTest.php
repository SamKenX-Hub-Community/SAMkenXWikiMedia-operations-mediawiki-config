<?php

class InitialiseSettingsTest extends WgConfTestCase {

	///
	/// wgLogoHD
	///
	public function testLogoHD() {
		$requiredKeys = $this->getRequiredLogoHDKeys();
		$wgConf = $this->loadWgConf( 'unittest' );

		foreach ( $wgConf->settings['wgLogoHD'] as $db => $entry ) {
			// Test if all logos exist
			foreach ( $entry as $size => $logo ) {
				$this->assertFileExists( __DIR__ . '/..' . $logo, "$db has nonexistent $size logo" );
			}

			// Test if only 1.5x and 2x keys are used
			$keys = array_keys( $entry );
			$this->assertEquals( $requiredKeys, $keys, "Unexpected keys for $db", 0.0, 10, true ); // canonicalize
		}
	}

	public function getRequiredLogoHDKeys() {
		return [ '1.5x', '2x' ];
	}

	///
	/// wgLogo
	///
	public function testLogo() {
		$wgConf = $this->loadWgConf( 'unittest' );

		foreach ( $wgConf->settings['wgLogo'] as $db => $logo ) {
			// Test if all logos exist
			$this->assertFileExists( __DIR__ . '/..' . $logo, "$db has nonexistent 1x logo" );
		}
	}

	///
	/// wgExtraNamespaces
	///
	public function testwgExtraNamespaces() {
		$wgConf = $this->loadWgConf( 'unittest' );
		foreach ( $wgConf->settings['wgExtraNamespaces'] as $db => $entry ) {
			foreach ( $entry as $nb => $ns ) {
				$this->assertFalse( strpos( $ns, ' ' ), "Unexpected spaces in '$ns' namespace title for $db, use underscores instead" );
			}
		}
	}

	///
	/// only existing wikis or dblists may be referenced in IS.php
	///
	public function testOnlyExistingWikis() {
		$wgConf = $this->loadWgConf( 'unittest' );
		$dblistNames = array_keys( DBList::getLists() );
		$langs = file( __DIR__ . "/../langlist", FILE_IGNORE_NEW_LINES );
		foreach ( $wgConf->settings as $config ) {
			foreach ( $config as $db => $entry ) {
				$dbNormalized = str_replace( "+", "", $db );
				$this->assertTrue(
					in_array( $dbNormalized, $dblistNames ) ||
					DBList::isInDblist( $dbNormalized, "all" ) ||
					in_array( $dbNormalized,  $langs ) ||
					in_array( $dbNormalized, [ "default", "lzh", "yue", "nan" ] ), // TODO: revert back to $db == "default"
					"$dbNormalized is referenced, but it isn't either a wiki or a dblist" );
			}
		}
	}
}
