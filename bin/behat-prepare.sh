#!/bin/bash

###
# Prepare a Pantheon site environment for the Behat test suite, by installing
# and configuring the plugin for the environment. This script is architected
# such that it can be run a second time if a step fails.
###

echo $WORDPRESS_ADMIN_PASSWORD

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

# Set StrictHostKeyChecking to no
echo "StrictHostKeyChecking no" >> /etc/ssh/ssh_config
cat /etc/ssh/ssh_config

###
# Create a new environment for this particular test run.
###
terminus env:create  $TERMINUS_SITE.dev $TERMINUS_ENV
terminus env:wipe $SITE_ENV --yes

###
# Get all necessary environment details.
###
PANTHEON_GIT_URL=$(terminus connection:info $SITE_ENV --field=git_url)
PANTHEON_SITE_URL="$TERMINUS_ENV-$TERMINUS_SITE.pantheonsite.io"
PREPARE_DIR="/tmp/$TERMINUS_ENV-$TERMINUS_SITE"
BASH_DIR="$( cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

###
# Switch to git mode for pushing the files up
###
terminus connection:set $SITE_ENV git
rm -rf $PREPARE_DIR
git clone -b $TERMINUS_ENV $PANTHEON_GIT_URL $PREPARE_DIR -v

###
# Add the copy of this plugin itself to the environment
###
rm -rf $PREPARE_DIR/wp-content/plugins/solr-power
cd $BASH_DIR/..
rsync -av --exclude='node_modules/' --exclude='tests/' ./* $PREPARE_DIR/wp-content/plugins/solr-power
cd $PREPARE_DIR/wp-content/plugins/solr-power

# Build plugin
npm ci
npm run build
composer install --no-dev -o

# Remove unneeded stuff
rm -rf $PREPARE_DIR/wp-content/plugins/solr-power/.git
rm -rf $PREPARE_DIR/wp-content/plugins/solr-power/node_modules/
rm -rf $PREPARE_DIR/wp-content/plugins/solr-power/tests/
cd $BASH_DIR/..

# Download the latest Classic Editor release from WordPress.org
wget -O $PREPARE_DIR/classic-editor.zip https://downloads.wordpress.org/plugin/classic-editor.zip
unzip $PREPARE_DIR/classic-editor.zip -d $PREPARE_DIR
mv $PREPARE_DIR/classic-editor $PREPARE_DIR/wp-content/plugins/
rm $PREPARE_DIR/classic-editor.zip

###
# Push files to the environment
###
cd $PREPARE_DIR
git add -A wp-content
git config user.email "solr-power@getpantheon.com"
git config user.name "Pantheon"
git commit -m "Include Solr Power"
git push

# Sometimes Pantheon takes a little time to refresh the filesystem
terminus build:workflow:wait $TERMINUS_SITE.$TERMINUS_ENV

###
# Set up WordPress, theme, and plugins for the test run
###
terminus wp $SITE_ENV -- core install --title=$TERMINUS_ENV-$TERMINUS_SITE --url=$PANTHEON_SITE_URL --admin_user=$WORDPRESS_ADMIN_USERNAME --admin_email=$WORDPRESS_ADMIN_EMAIL --admin_password=$WORDPRESS_ADMIN_PASSWORD
terminus wp $SITE_ENV -- plugin activate solr-power classic-editor
terminus wp $SITE_ENV -- theme activate twentyseventeen
terminus wp $SITE_ENV -- rewrite structure '/%year%/%monthnum%/%day%/%postname%/'
