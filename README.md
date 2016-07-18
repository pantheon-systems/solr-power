# Solr Search for WordPress #
**Contributors:** getpantheon, Outlandish Josh, 10up, collinsinternet  
**Tags:** search  
**Requires at least:** 4.2  
**Tested up to:** 4.5.3  
**Stable tag:** 0.4.1
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Improve your user experience with the Apache Solr search engine for your WordPress website.

## Description ##

[![Build Status](https://travis-ci.org/pantheon-systems/solr-power.svg?branch=master)](https://travis-ci.org/pantheon-systems/solr-power)

Search is critical for your site, but the default search for WordPress leaves a lot to be desired. Improve your user experience with the Apache Solr search engine for your WordPress website.

* Fast results, with better accuracy
* Enables faceting on fields such as tags, categories, author, and page type.
* Indexing and faceting on custom fields
* Completely replaces default WordPress search, just install and configure.
* Completely integrated into default WordPress theme and search widget.
* Very developer-friendly: uses the modern [Solarium](http://www.solarium-project.org/) library

## Installation ##

The Solr Power plugin can be installed just like you'd install any other WordPress plugin. Because Solr Power is intended to be a bridge between WordPress and the Apache Solr search engine, you'll need access to a functioning Solr instance for the plugin to work as expected.

If you're using the Solr Power plugin on Pantheon, setting up Apache Solr is as easy as enabling the Apache Solr add-on in your Pantheon dashboard. Once you've done so:

1. Index your existing content by going to the plugin options screen and clicking "Execute" on "Index Searchable Post Types".
2. Search on!
3. See the examples/templates directories for more rich implementation guidelines.

If you're using the Solr Power plugin elsewhere, you'll need to install and configure Apache Solr. On a Linux environment, this involves three steps:

1. Install the Java Runtime Environment.
2. Run `./bin/install-solr.sh` to install and run Apache Solr on port 8983.
3. Configuring Solr Power to use this particular Solr instance by setting the `PANTHEON_INDEX_HOST` and `PANTHEON_INDEX_PORT` environment variables.

In a local development environment, you can point Solr Power to a custom Solr instance by creating a MU plugin with:

```
<?php

putenv( 'PANTHEON_INDEX_HOST=192.168.50.4' ); // Replace with the appropriate IP address
putenv( 'PANTHEON_INDEX_PORT=8983' );

add_filter( 'solr_scheme', function(){ return 'http'; });
```

## Development ##

This plugin is under active development on GitHub:

[https://github.com/pantheon-systems/solr-power](https://github.com/pantheon-systems/solr-power)

Please feel free to file issues there. Pull requests are also welcome!

## Changelog ##

### 0.4.1 ###
* Do not allow plugin activation if the `PANTHEON_INDEX_HOST` or `PANTHEON_INDEX_PORT` environment variables are not set. Instead, show an admin notice to the user advising them to configure the environment variables.

### 0.4 ###
* Auto submission of schema.xml
* Moved legacy functions to a separate file
* PHP version check - warn in the WordPress dashboard and disable Solr Power plugin if the PHP version is less than 5.4

### 0.3 ###
* Bug fixes
* Settings page updates
* Filters for AJAX/Admin integration
* Indexing all publicly queryable post types
* Debug Bar Extension
* Default sort option on settings page
* Initial WP CLI integration

### 0.2 ###
* Works "out of the box" by overriding WP_Query()
* Much improved internal factoring

### 0.1 ###
* Initial alpha release (GitHub only)

### 0.0 ###
* Note this started as a fork of this wonderful project: https://github.com/mattweber/solr-for-wordpress
