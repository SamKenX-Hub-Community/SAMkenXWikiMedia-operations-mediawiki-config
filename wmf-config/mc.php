<?php
# WARNING: This file is publicly viewable on the web. Do not put private data here.
#
# This is for PRODUCTION.
#
# Effective load order:
# - multiversion
# - mediawiki/DefaultSettings.php
# - wmf-config/*Services.php
# - wmf-config/InitialiseSettings.php
# - wmf-config/mc.php [THIS FILE]
#
# Included from: wmf-config/CommonSettings.php.
#

// Explicitly disable the globals, just in case.
// This is for CACHE_MEMCACHED, and for Memcached-related entries in
// $wgObjectCaches that lack a 'servers' or 'persistent' key.
// Neither of which WMF uses.
$wgMemCachedServers = [];
$wgMemCachedPersistent = false;

$wgObjectCaches['memcached-pecl'] = [
	'class'                => 'MemcachedPeclBagOStuff',
	'serializer'           => 'php',
	'persistent'           => false,
	'servers'              => [ '127.0.0.1:11212' ],
	// Effectively disable the failure limit (0 is invalid)
	'server_failure_limit' => 1e9,
	// Effectively disable the retry timeout
	'retry_timeout'        => -1,
	'loggroup'             => 'memcached',
	// 250ms, in microseconds
	'timeout'              => 0.25 * 1e6,
];

$wgObjectCaches['mcrouter'] = [
	'class'                 => 'MemcachedPeclBagOStuff',
	'serializer'            => 'php',
	'persistent'            => false,
	'servers'               => [ '127.0.0.1:11213' ],
	'server_failure_limit'  => 1e9,
	'retry_timeout'         => -1,
	'loggroup'              => 'memcached',
	// 250ms, in microseconds
	'timeout'               => 0.25 * 1e6,
	'allow_tcp_nagle_delay' => false,
];
$wgObjectCaches['mcrouter-primary-dc'] = array_merge(
	$wgObjectCaches['mcrouter'],
	[ 'routingPrefix' => "/$wmgMasterDatacenter/mw/" ]
);
// Wikifunctions dedicated caching cluster. It's dc-local with no replication.
// See T297815.
$wgObjectCaches['mcrouter-wikifunctions'] = array_merge(
	$wgObjectCaches['mcrouter'],
	[ 'routingPrefix' => '/local/wf/' ]
);

if ( $wgDBname === 'labswiki' || $wgDBname === 'labtestwiki' ) {
	// nutcracker only; no mcrouter present
	$wgMainCacheType = 'memcached-pecl';
} else {
	$wgWANObjectCache = [
		// Specify the route prefix that mcrouter listens for and broadcasts.
		// The route prefix is configured in Puppet (profile::mediawiki::mcrouter_wancache).
		'broadcastRoutingPrefix' => '/*/mw-wan/',
	];
	$wgMainCacheType = 'mcrouter';
}

# vim: set sts=4 sw=4 et :
