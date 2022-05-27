<?php
/**
 * To test the script locally, run:
 *
 *     cd docroot/noc$ php -S localhost:9412
 *
 * Then view <http://localhost:9412/db.php>.
 */

$format = ( $_GET['format'] ?? null ) === 'json' ? 'json' : 'html';

if ( $format === 'json' ) {
	error_reporting( 0 );
} else {
	// Verbose error reporting
	error_reporting( E_ALL );
	ini_set( 'display_errors', 1 );
}

// Default to eqiad but allow limited other DCs to be specified with ?dc=foo.
$dbConfigEtcdPrefix = '/srv/dbconfig';
$dbctlJsonByDC = [
	'codfw' => 'codfw.json',
	'eqiad' => 'eqiad.json',
];
$dbSelectedDC = 'eqiad';

if ( !is_dir( $dbConfigEtcdPrefix ) ) {
	// Local testing and debugging fallback
	$dbConfigEtcdPrefix = __DIR__ . '/../../tests/data/dbconfig';
	$dbctlJsonByDC = [
		'tmsx' => 'tmsx.json',
		'tmsy' => 'tmsy.json',
	];
	$dbSelectedDC = 'tmsx';
}

if ( isset( $_GET['dc'] ) && isset( $dbctlJsonByDC[$_GET['dc']] ) ) {
	$dbSelectedDC = $_GET['dc'];
}

$dbConfigEtcdJsonFilename = $dbctlJsonByDC[$dbSelectedDC];

// Mock vars needed by db-*.php (normally set by CommonSettings.php)
$wgDBname = null;
$wgDBuser = null;
$wgDBpassword = null;
$wgDebugDumpSql = false;
$wgSecretKey = null;
$wmgMasterDatacenter = null;

// Load the actual db vars
require_once __DIR__ . '/../../wmf-config/db-production.php';

// Now load the JSON written to Etcd by dbctl, from the local disk and merge it in.
// This is mimicking what wmfEtcdApplyDBConfig (wmf-config/etcd.php) does in prod.
//
// On mwmaint hosts, these JSON files are produced by a 'fetch_dbconfig' script,
// run via systemd timer, defined in puppet.
$dbconfig = json_decode( file_get_contents( "$dbConfigEtcdPrefix/$dbConfigEtcdJsonFilename" ), true );
global $wgLBFactoryConf;
$wgLBFactoryConf['readOnlyBySection'] = $dbconfig['readOnlyBySection'];
$wgLBFactoryConf['groupLoadsBySection'] = $dbconfig['groupLoadsBySection'];
foreach ( $dbconfig['sectionLoads'] as $section => $sectionLoads ) {
	$wgLBFactoryConf['sectionLoads'][$section] = array_merge( $sectionLoads[0], $sectionLoads[1] );
}
require_once __DIR__ . '/../../multiversion/MWConfigCacheGenerator.php';
require_once __DIR__ . '/../../src/Noc/DbConfig.php';

$dbConf = new Wikimedia\MWConfig\Noc\DbConfig();

if ( $format === 'json' ) {
	$data = [];
	foreach ( $dbConf->getNames() as $name ) {
		$data[$name] = [
			'hosts' => $dbConf->getHosts( $name ),
			'loads' => $dbConf->getLoads( $name ),
			'groupLoads' => $dbConf->getGroupLoads( $name ),
			'dbs' => $dbConf->getDBs( $name ),
			'readOnly' => $dbConf->getReadOnly( $name ),
		];
	}
	header( 'Content-Type: application/json; charset=utf-8' );
	echo json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	exit;
}

$pageTitle = "Database configuration: $dbSelectedDC"

?><!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php echo htmlspecialchars( "$pageTitle – Wikimedia NOC" ); ?></title>
	<link rel="stylesheet" href="css/base.css">
	<style>
	code {
		color: #000;
		background: #f8f9fa;
		border: 1px solid #c8ccd1;
		border-radius: 2px;
		padding: 1px 4px;
	}
	.nocdb-sections {
		display: flex;
		flex-wrap: wrap;
	}
	section {
		flex: 1;
		min-width: 250px;
		border: 1px solid #eaecf0;
		padding: 0 1rem 1rem 1rem;
		margin: 0 1rem 1rem 0;
	}
	section:target { border-color: orange; }
	section:target h2 { background: #fef6e7; }
	</style>
</head>
<body>
	<header><div class="wm-container">
	<a role="banner" href="/" title="Visit the home page"><em>Wikimedia</em> NOC</a>
	</div></header>
	<main role="main">
	<nav class="wm-site-nav"><ul class="wm-nav">
<?php

$sectionNames = $dbConf->getNames();
natsort( $sectionNames ); // natsort for s1 < s2 < s10 rather than s1 < s10 < s2

// Generate navigation links
foreach ( $sectionNames as $name ) {
	$id = urlencode( 'tabs-' . $name );
	print '<li><a href="#' . htmlspecialchars( $id ) . '">Section ' . htmlspecialchars( $name ) . '</a></li>';
}

?>
	</ul></nav>
		<!--
			NOTE: We don't use <div class="wm-container"> here,
			as we want this portal to be full-width
		-->
	<article>
<?php

print '<h1>' . htmlspecialchars( $pageTitle ) . '</h1>';
print '<div class="nocdb-sections">';
// Generate content sections
foreach ( $sectionNames as $name ) {
	$id = urlencode( 'tabs-' . $name );
	print "<section id=\"" . htmlspecialchars( $id ) . "\"><h2>Section <strong>" . htmlspecialchars( $name ) . '</strong></h2>';
	print $dbConf->htmlFor( $name ) . '</section>';
}
print '</div>';
?>
	</article>
	</main>
	<footer role="contentinfo"><div class="wm-container">
<?php
print '<p>Automatically generated based on <a href="./conf/highlight.php?file=db-production.php">';
print 'wmf-config/db-production.php</a> ';
print 'and on <a href="/dbconfig/' . htmlspecialchars( $dbConfigEtcdJsonFilename ) . '">';
print htmlspecialchars( $dbConfigEtcdJsonFilename ) . '</a>.<br/>';
foreach ( $dbctlJsonByDC as $dc => $file ) {
	if ( $file !== $dbConfigEtcdJsonFilename ) {
		print 'View <a href="' . htmlspecialchars( "?dc=$dc" ) . '">' . htmlspecialchars( ucfirst( $dc ) ) . '</a> instead. ';
	}
}
print '</p>';
?>
	</div></footer>
</body>
</html>
