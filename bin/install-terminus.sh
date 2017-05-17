#!/bin/bash

if [ -z "$TERMINUS_TOKEN" ]; then
	echo "TERMINUS_TOKEN environment variables missing; assuming unauthenticated build"
	exit 0
fi

# Exit immediately, but don't expose $TERMINUS_TOKEN
set -e
set +x

git clone --branch master https://github.com/pantheon-systems/terminus.git ~/terminus
cd ~/terminus && composer install
terminus auth:login --machine-token=$TERMINUS_TOKEN
