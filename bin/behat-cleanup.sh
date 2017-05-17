#!/bin/bash

###
# Delete the Pantheon site environment after the Behat test suite has run.
###

TERMINUS_USER_ID=$(terminus auth:whoami --field=id 2>&1)
if [[ ! $TERMINUS_USER_ID =~ ^[A-Za-z0-9-]{36}$ ]]; then
	echo "Terminus unauthenticated; assuming unauthenticated build"
	exit 0
fi

if [ -z "$TERMINUS_SITE" ] || [ -z "$TERMINUS_ENV" ]; then
	echo "TERMINUS_SITE and TERMINUS_ENV environment variables must be set"
	exit 1
fi

set -ex

###
# Delete the environment used for this test run.
###
terminus multidev:delete $SITE_ENV --delete-branch --yes
