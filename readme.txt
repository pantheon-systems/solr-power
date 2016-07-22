=== Solr Search for WordPress ===
Contributors: getpantheon, Outlandish Josh, 10up, collinsinternet
Tags: search
Requires at least: 4.2
Tested up to: 4.5.3
Stable tag: 0.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Improve your user experience with the Apache Solr search engine for your WordPress website.

== Description ==

[![Build Status](https://travis-ci.org/pantheon-systems/solr-power.svg?branch=master)](https://travis-ci.org/pantheon-systems/solr-power)

Search is critical for your site, but the default search for WordPress leaves a lot to be desired. Improve your user experience with the Apache Solr search engine for your WordPress website.

* Fast results, with better accuracy
* Enables faceting on fields such as tags, categories, author, and page type.
* Indexing and faceting on custom fields
* Completely replaces default WordPress search, just install and configure.
* Completely integrated into default WordPress theme and search widget.
* Very developer-friendly: uses the modern [Solarium](http://www.solarium-project.org/) library

== Installation ==

First install the plugin as per normal. If you are installing on Pantheon, you will need to enable the Apache Solr add-on before you can enable the plugin.

1. Index your existing content by going to the plugin options screen and clicking "Execute" on "Index Searchable Post Types".
2. Search on!
3. See the examples/templates directories for more rich implementation guidelines.

== Development ==

This plugin is under active development on GitHub:

[https://github.com/pantheon-systems/solr-power](https://github.com/pantheon-systems/solr-power)

Please feel free to file issues there. Pull requests are also welcome!

You may notice there are two sets of tests running, on two different services:

* Travis CI runs the [PHPUnit](https://phpunit.de/) test suite against a Solr instance.
* Circle CI runs the [Behat](http://behat.org/) test suite against a Pantheon site, to ensure the plugin's compatibility with the Pantheon platform.

Both of these test suites can be run locally, with a varying amount of setup.

PHPUnit requires the [WordPress PHPUnit test suite](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/), and access to a database with name `wordpress_test`. If you haven't already configured the test suite locally, you can run `bash bin/install-wp-tests.sh wordpress_test root '' localhost`. You'll also need access to a running Solr instance, in order to run the unit tests against Solr.

Behat requires a Pantheon site with Solr enabled. Once you've created the site, you'll need install Terminus, and set the `TERMINUS_TOKEN`, `TERMINUS_SITE`, and `TERMINUS_ENV` environment variables. Then, you can run `./bin/behat-prepare.sh` to prepare the site for the test suite.

== Changelog ==

= 0.4.1 =
* Do not allow plugin activation if the `PANTHEON_INDEX_HOST` or `PANTHEON_INDEX_PORT` environment variables are not set. Instead, show an admin notice to the user advising them to configure the environment variables.

= 0.4 =
* Auto submission of schema.xml
* Moved legacy functions to a separate file
* PHP version check - warn in the WordPress dashboard and disable Solr Power plugin if the PHP version is less than 5.4

= 0.3 =
* Bug fixes
* Settings page updates
* Filters for AJAX/Admin integration
* Indexing all publicly queryable post types
* Debug Bar Extension
* Default sort option on settings page
* Initial WP CLI integration

= 0.2 =
* Works "out of the box" by overriding WP_Query()
* Much improved internal factoring

= 0.1 =
* Initial alpha release (GitHub only)

= 0.0 =
* Note this started as a fork of this wonderful project: https://github.com/mattweber/solr-for-wordpress
