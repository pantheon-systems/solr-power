# Solr Search for WordPress #

**Contributors:** getpantheon, Outlandish Josh, mattweber, palepurple, allen23  
**Tags:** search  
**Requires at least:** 4.2  
**Tested up to:** 4.2  
**Stable tag:** trunk  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Improve your user experience with the Apache Solr search engine for your WordPress website.

## Description ##

Search is critical for your site, but the default search for WordPress leaves a lot to be desired. Improve your user experience with the Apache Solr search engine for your WordPress website.

* Fast results, with better accuracy
* Enable faceting on fields such as tags, categories, author, and page type.
* Indexing and faceting on custom fields
* Completely replaces default WordPress search, just install and configure.
* Completely integrated into default WordPress theme and search widget.
* Very developer-friendly: uses the modern [Solarium](http://www.solarium-project.org/] library)

## Installation ##

Using this plugin currently requires that **you are a developer**. You will need to do template work to implement the user-interface for your search. An example is included, but you should expect to spend some time crafting the experience. 

To begin, install the plugin as per normal. If you are installing on Pantheon, you will need to [enable the Apache Solr add-on](https://pantheon.io/docs/articles/sites/apache-solr) before you can enable the plugin.

Also, if you are running on Pantheon, you will need to use the plugin's administrative UI to post in your `schema.xml` file. The default which is included with the plugin is a good place to start, but you are free to experiment with your own modifications. You must post the schema in each environment to finish initializing the index. If you're not seeing content going in, it's likely because your schema hasn't yet been posted. You can ignore this if you are running your own Solr infrastructure.

1. Index your existing content by going to the plugin options screen and clicking "index content".
2. New content will now be indexed automatically by cron.
3. See the examples/templates directories for interface implementation starters.

This plugin is under active development, so please create issues [on GitHub](https://github.com/pantheon-systems/solr-for-wordpress) for any bugs you encounter.

## Roadmap ##

We are working towards a more complete set of functionality. If you have interested in working on these features, get in touch:

* Out-of-the-box search UI implementation: main search plus a widget.
* Improved administrative UI that breaks up configuration from debugging, etc.
* Reporting tools in the admin UI: current state of the index/etc.
* Developer documentation on using Solr as an alternative backend for `WP_Query()` calls in general.

## Screenshots ##

* None Yet

## Changelog ##

### 0.1 ###

* Initial developer release to GitHub.


### 0.0 ###
* Note this started as a fork of this wonderful project: https://github.com/mattweber/solr-for-wordpress
