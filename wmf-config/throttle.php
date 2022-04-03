<?php
# WARNING: This file is publicly viewable on the web. Do not put private data here.

# Initialize the array. Append to that array to add a throttle
$wmgThrottlingExceptions = [];

# $wmgThrottlingExceptions is an array of arrays of parameters:
#  'from'  => date/time to start raising account creation throttle
#  'to'    => date/time to stop
#
# Optional arguments can be added to set the value or restrict by client IP
# or project dbname. Options are:
#  'value'  => new value for $wgAccountCreationThrottle (default: 50)
#  'IP'     => client IP as given by $wgRequest->getIP() or array (default: any IP)
#  'range'  => alternatively, the client IP CIDR ranges or array (default: any range)
#  'dbname' => a $wgDBname or array of dbnames to compare to
#             (eg. enwiki, metawiki, frwikibooks, eswikiversity)
#             Note that the limit is for the total number of account
#             creations on all projects. (default: any project)
# Example:
# $wmgThrottlingExceptions[] = [
# 'from'   => '2016-01-01T00:00 +0:00',
# 'to'     => '2016-02-01T00:00 +0:00',
# 'IP'     => '123.456.78.90',
# 'dbname' => [ 'xxwiki', etc. ],
# 'value'  => xx
# ];
## Add throttling definitions below.
#
## If you are adding a throttle exception with a 'from' time that is less than
## 72 hours in advance, you will also need to manually clear a cache after
## deploying your change to this file!
## https://wikitech.wikimedia.org/wiki/Increasing_account_creation_threshold

$wmgThrottlingExceptions[] = [ // T304016
	'from' => '2022-05-15T00:00 -4:00',
	'to' => '2022-05-16T00:00 -4:00',
	'IP' => '139.60.127.237',
	'dbname' => [ 'enwiki' ],
	'value' => 50,
];

$wmgThrottlingExceptions[] = [ // T304687
	'from' => '2022-03-31T00:00 -4:00',
	'to' => '2022-04-08T00:00 -4:00',
	'range' => [
		'173.46.96.0/19',
		'192.76.239.0/24',
		'192.83.253.0/24',
		'192.246.224.0/21',
		'192.246.233.0/24',
		'192.246.234.0/23'
	],
	'dbname' => [ 'enwiki' ],
	'value' => 35, // 26 estimated participants
];

## Add throttling definitions above.
