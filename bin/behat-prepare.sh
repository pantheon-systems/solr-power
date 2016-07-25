#!/bin/bash

###
# Prepare a Pantheon site environment for the Behat test suite, by installing
# and configuring the plugin for the environment. This script is architected
# such that it can be run a second time if a step fails.
###

set -ex

if [ -z "$TERMINUS_SITE" ] || [ -z "$TERMINUS_ENV" ]; then
	echo "TERMINUS_SITE and TERMINUS_ENV environment variables must be set"
	exit 1
fi

###
# Create a new environment for this particular test run.
###
terminus site create-env --to-env=$TERMINUS_ENV --from-env=dev

###
# Get all necessary environment details.
###
PANTHEON_GIT_URL=$(terminus site connection-info --field=git_url)
PANTHEON_SITE_URL="$TERMINUS_ENV-$TERMINUS_SITE.pantheonsite.io"
PREPARE_DIR="/tmp/$TERMINUS_ENV-$TERMINUS_SITE"
BASH_DIR="$( cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

###
# Switch to git mode for pushing the files up
###
terminus site set-connection-mode --mode=git
rm -rf $PREPARE_DIR
git clone -b $TERMINUS_ENV $PANTHEON_GIT_URL $PREPARE_DIR

###
# Add the copy of this plugin itself to the environment
###
rm -rf $PREPARE_DIR/wp-content/plugins/solr-power
cd $BASH_DIR/..
rsync -av --exclude='node_modules/' --exclude='tests/' ./* $PREPARE_DIR/wp-content/plugins/solr-power
rm -rf $PREPARE_DIR/wp-content/plugins/solr-power/.git

###
# Push files to the environment
###
cd $PREPARE_DIR
git rm -rf --cached wp-content/plugins/solr-power/vendor
find ./wp-content/plugins/solr-power/vendor -type d -name ".git" -exec rm -rf "{}" \;
git add -A wp-content
git config user.email "solr-power@getpantheon.com"
git config user.name "Pantheon"
git commit -m "Include Solr Power"
git push

# Sometimes Pantheon takes a little time to refresh the filesystem
sleep 10

###
# Set up WordPress, theme, and plugins for the test run
###
terminus wp "user create pantheon solr-power@getpantheon.com --user_pass=pantheon --role=administrator"
terminus wp "plugin activate solr-power"
