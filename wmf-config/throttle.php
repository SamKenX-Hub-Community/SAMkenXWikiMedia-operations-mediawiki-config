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

$wmgThrottlingExceptions[] = [ // T215839
	'from' => '2019-03-03T11:00 -4:00',
	'to' => '2019-03-03T17:00 -4:00',
	'IP' => '198.179.69.250',
	'dbname' => [ 'enwiki', 'commonswiki', 'wikidatawiki' ],
	'value' => 80 // 70 expected
];

$wmgThrottlingExceptions[] = [ // T216642
	'from' => '2019-03-07T13:00 -6:00',
	'to' => '2019-03-07T20:00 -6:00',
	'IP' => '24.137.221.250',
	'dbname' => [ 'enwiki', 'frwiki', 'commonswiki', 'wikidatawiki' ],
	'value' => 50
];

$wmgThrottlingExceptions[] = [ // T217270
	'from' => '2019-03-08T13:00 +1:00',
	'to' => '2019-03-08T19:00 +1:00',
	'IP' => '194.228.196.244',
	'dbname' => [ 'cswiki', 'commonswiki', 'wikidatawiki' ],
	'value' => 30
];

$wmgThrottlingExceptions[] = [ // T217155
	'from' => '2019-03-26T08:30 -5:00',
	'to' => '2019-03-26T21:00 -5:00',
	'IP' => '192.154.64.244',
	'dbname' => [ 'enwiki', 'commonswiki', 'wikidatawiki' ],
	'value' => 220 // 200 expected
];

## Add throttling definitions above.
