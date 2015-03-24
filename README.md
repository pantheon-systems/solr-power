# Solr for WordPress


 * Contributors: mattweber, palepurple, Pantheon
 * Author URI: http://pantheon.io
 * Plugin URI: https://github.com/pantheon-systems/solr-for-wordpress-on-pantheon
 * Tags: solr, search, search results, search integration, custom search, pantheon  
 * Requires at least: 3.0
 * Tested up to: 4.1
 * Stable tag: 0.5


A WordPress plugin that replaces the default WordPress search with Solr.

## Description
This is a fork of Matt Webber's [solr-for-wordpress](https://github.com/mattweber/solr-for-wordpress). 

A WordPress plugin that replaces the default WordPress search with Solr.  Features include:

 * Index pages and posts
 * Enable faceting on fields such as tags, categories, author, and page type.
 * Indexing and faceting on custom fields
 * Multisite support
 * Treat the category facet as a taxonomy
 * Add special template tags so you can create your own custom result pages to match your theme.
 * Completely replaces default WordPress search, just install and configure.
 * Completely integrated into default WordPress theme and search widget.
 * Configuration options allow you to select pages to ignore, features to enable/disable, and what type of result  information you want output.
 * i18n Support
 * Multi server/core support

Note that this plugin requires you to have an instance of Solr using a schema with the following fields: id, permalink, title, content, numcomments, categories, categoriessrch, tags, tagssrch, author, type, and text.  The facet fields (categories, tags, author, and type) should be string fields.  You can make tagssrch and categoriessrch of any type you want as they are used for general searching.  The plugin is distributed with a Solr schema you can use at `solr-for-wordpress/schema.xml`.

## FAQ 

See FAQ.txt


## Installation

 1. Upload the `solr-for-wordpress-on-pantheon` folder to the `/wp-content/plugins/` directory
 1. Activate the plugin through the 'Plugins' menu in WordPress
 1. Visit the Settings page to ensure that the server information has been properly set.
 1. Load all your posts and/or pages via the "Load All Posts" button in the Settings page.

##  Custom Theme Integration 

 1. Create a new theme file called "s4w_search.php".
 2. Insert your markup, use template methods s4w_search_form() and s4w_search_results() to insert the search box and results respectively.
 3. Add result styling to your theme css file, see `solr-for-wordpress/template/search.css` for an example.
 4. You can use the search widget in your sidebar for search, or use a custom search box that submits the query in the parameter "s".


## Screenshots 

### Configuration Page
 
![Configuration Page](screenshot-1.png?raw=true "Configuration Page")

### Example of results page in default WordPress Theme

![Example of results page in default WP theme](screenshot-2.png?raw=true "Example of results page in default WP theme")


## Credits 

 * Dominique Bejean for custom field support and testing.
 * Eric Pugh multi server support.
 * Dustin Rue - fixes for batch import and multisite.
 * Pale Purple / Filip Zajac - update to use composer; remove dependency on PHPSolrClient and use Solarium; update schema.xml to work with newer Solr (v4). update indexing logic etc.
 * Pantheon https://pantheon.io
