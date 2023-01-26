#!/bin/bash

###
# Execute the Behat test suite against a prepared Pantheon site environment.
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

if [ -z "$WORDPRESS_ADMIN_USERNAME" ] || [ -z "$WORDPRESS_ADMIN_PASSWORD" ]; then
	echo "WORDPRESS_ADMIN_USERNAME and WORDPRESS_ADMIN_PASSWORD environment variables must be set"
	exit 1
fi

set -ex

export BEHAT_PARAMS='{"extensions" : {"Behat\\MinkExtension" : {"base_url" : "http://'$TERMINUS_ENV'-'$TERMINUS_SITE'.pantheonsite.io"} }}'

http://$TERMINUS_ENV-$TERMINUS_SITE.pantheonsite.io/wp-content/plugins/solr-power/vendor/bin/behat "$@"
