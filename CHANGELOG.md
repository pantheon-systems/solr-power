## Changelog ##

## 2.4.5 (April 9, 2023) ##
* Fixes missing vendor/ directory in previous release [[#580](https://github.com/pantheon-systems/solr-power/pull/580)]

## 2.4.4 (April 7, 2023) ##
* Update Composer dependencies [[#576](https://github.com/pantheon-systems/solr-power/pull/576)] [[#574](https://github.com/pantheon-systems/solr-power/pull/574)] [[#573](https://github.com/pantheon-systems/solr-power/pull/573)]
* Fix failing tests [[#577](https://github.com/pantheon-systems/solr-power/pull/577)]
* Update tested up to version

## 2.4.3 (January 19, 2022) ##
* Include schema.xml in release distribution [[#568](https://github.com/pantheon-systems/solr-power/pull/568)]

## 2.4.2 (December 2, 2022) ##
* Re-add changelog heading to readme.txt [[#564](https://github.com/pantheon-systems/solr-power/pull/564)]

## 2.4.1 (December 1, 2022) ##
* Fixes the WordPress `readme.txt` [[#562](https://github.com/pantheon-systems/solr-power/pull/562/)]

## 2.4.0 (November 30, 2022) ##
* Adds Github Actions for building tag and deploying to wp.org. Add CONTRIBUTING.md. [[#551](https://github.com/pantheon-systems/solr-power/issues/551)]
* Added SOLRPOWER_DISABLE_AUTOCOMMIT to disable autocommitting of posts, moved CHANGELOG to it's own file, added `$post->score` value to parsed search results [[#559](https://github.com/pantheon-systems/solr-power/pull/559)]

## 2.3.3 (September 28, 2022) ##
* Fixes issue where options could not be saved [[#541](https://github.com/pantheon-systems/solr-power/issues/541)]
* Enforces network activation requirement for WordPress multisite [[#538](https://github.com/pantheon-systems/solr-power/issues/538)]

### 2.3.2 (April 1, 2022) ###
* Fixes query filtering for `'fields' => 'id=>parent'` [[#528](https://github.com/pantheon-systems/solr-power/pull/528)].

### 2.3.1 (March 29, 2022) ###
* Adapts `posts_pre_query()` return values based on 'fields' argument [[#522](https://github.com/pantheon-systems/solr-power/pull/522)].

### 2.3.0 (March 29, 2022) ###
* Removes incorrect use of `array_map( 'get_post' )` in `posts_pre_query` [[#521](https://github.com/pantheon-systems/solr-power/pull/521)].

### 2.2.6 (February 22, 2022) ###
* Fixes PHP 8 deprecations in `class-solrpower-options.php` [[#513](https://github.com/pantheon-systems/solr-power/pull/513)].

### 2.2.5 (July 27, 2021) ###
* Switches to `wp_strip_all_tags()` to remove style and script tag content [[#500](https://github.com/pantheon-systems/solr-power/pull/500)].

### 2.2.4 (May 5, 2021) ###
* Introduces `SOLRPOWER_DISABLE_QUERY_ALT` constant for disabling setQueryAlternative behavior [[#495](https://github.com/pantheon-systems/solr-power/pull/495)].

### 2.2.3 (March 8, 2021) ###
* Incorporates the value for `$_ENV['FILEMOUNT']` when indicating path for `schema.xml` [[#492](https://github.com/pantheon-systems/solr-power/pull/492)].

### 2.2.2 (December 1, 2020) ###
* Updates various Composer dependencies [[#477](https://github.com/pantheon-systems/solr-power/pull/477)].
* Updates README to include detail on how to use TrieDateField for publish date [[#466](https://github.com/pantheon-systems/solr-power/pull/466)].

### 2.2.1 (July 13, 2020) ###
* Avoids pinging Solr unless we actually need, to avoid unnecessary requests [[#458](https://github.com/pantheon-systems/solr-power/pull/458)].

### 2.2.0 (May 5, 2020) ###
* Uses `posts_pre_query` hook to support use of 'fields' in `WP_Query` [[#448](https://github.com/pantheon-systems/solr-power/pull/448)].

### 2.1.4 (April 24, 2020) ###
* Ensures highlighting is also applied to the post excerpt [[#446](https://github.com/pantheon-systems/solr-power/pull/446)].

### 2.1.3 (November 16, 2019) ###
* Add `solr_power_ajax_search_query_args` filter to modify AJAX search query arguments [[#432](https://github.com/pantheon-systems/solr-power/pull/432)].

### 2.1.2 (August 28, 2019) ###
* Adds `solr_is_private_blog` filter to allow control over whether a blog is indexed [[#423](https://github.com/pantheon-systems/solr-power/pull/423)].

### 2.1.1 (August 14, 2019) ###
* Uses some fancy `composer` magic to unblock WordPress.org plugin updates [[#418](https://github.com/pantheon-systems/solr-power/pull/418)].

### 2.1.0 (May 22, 2019) ###
* Introduces `solr_index_stat` filter for allowing additional information to be included [[#396](https://github.com/pantheon-systems/solr-power/pull/396)].
* Introduces `solr_facet_operator` filter for allowing facet operator to be overridden [[#388](https://github.com/pantheon-systems/solr-power/pull/388)].
* Ensures warning message appears when activating across the entire network [[#399](https://github.com/pantheon-systems/solr-power/pull/399)].
* Parses `<h1>` tags in Solr error response, in addition to `<title>` [[#407](https://github.com/pantheon-systems/solr-power/pull/407)].
* Fixes incorrect variable name when outputting schema error message [[#404](https://github.com/pantheon-systems/solr-power/pull/404)].

### 2.0.0 ###
* Fix PHP 7 warning caused by bad conditional
* Ensure `$post->post_author` remains user ID when processing WP_Query
* Add a test case asserting that `post_title` and `post_content` are not bolded
* Update Solarium to `4.1.0` and other dependencies updates as needed
* Run automated tests against PHP `7.1`
* Increase the minimum supported PHP version to `7.1`


### 1.5.0 ###
* Adds support for queries using `post__in` and `post__not_in`.
* Clears batch cache when entire index is deleted.
* CLI: Errors early when there are no posts to index.
* Update Composer dependencies

### 1.4.1 ###
* Introduce `batch_size` argument for `wp solr index`
* Ensure custom taxonomies are included in widget facets
* Mention available Docker containers in README
* Properly handle negative integers when indexing and querying
* Increase precision of `test_wp_query_failed_ping` to avoid racy failures
* Catch exception when `$search->getData()` fails
* Remove unused global imports for $current_blog
* Properly escape dismax query strings
* POST actions to `admin.php` in network admin
* Define checked files in PHPCS config so `phpcs` can easily be run
* Remove unused global imports for $current_blog
* Define checked files in PHPCS config so `phpcs` can easily be run
* Rename PHPCS config to correct name

### 1.4.0 ###
* Bumps minimum supported version to WordPress 4.6.
* Updates bundled Solarium library to 3.8.1.
* Fixes Solr queries using `orderby=>meta_value_num` [[#299](https://github.com/pantheon-systems/solr-power/pull/299)].
* Use `$_SERVER['HOME']` as a reliable way of finding the cert on Pantheon [[#314](https://github.com/pantheon-systems/solr-power/pull/314)].


### 1.3.0 ###
* Add `.distignore` file for wp dist-archive solr-power
* Make Solr in the admin opt-in only using the `solr_allow_admin` filter
* Error early when `PANTHEON_ENVIRONMENT` isn't set
* Clarify error message when environment variables aren't set
* Mention copying `schema.xml` and supported Solr version in README
* Include original plugin attribution in the copyright notice
* Boost `post_title` more than `post_content` with sane boost values
* Add missing filter for custom fields
* Boost posts with matching titles to the top of results
* Remove duplicate options when initializing them
* Match author name in search results
* Bug fixes
* Adhere to WordPress coding standards in PHP files

### 1.2.0 ###
* Add multisite support
* Respect all query vars when searching with `WP_Query`
* Display error from schema submit response when Solr includes one

### 1.1.0 ###
* Introduce a new class for managing batch indexes
* Rewrite `wp solr index` to provide more verbosity
* Make batch indexes resumeable by logging `paged` to an option
* Remove old `wp solr index` code
* Fire `solr_power_index_all_finished` action when indexing is complete
* Ensure a completion message displays after indexing is complete
* Fix a bug around Solr taking over searches in wp-admin
* Properly apply the ```solr_allow_admin``` and ```solr_allow_ajax``` filters
* Add ```solr_boost_query``` filter for boosted items
* Add ```solr_dismax_query``` filter for Dismax
* Add ```get_post_types``` and ```get_post_statuses``` helper methods
* Remove unnecessary ```get_env``` call
* Add ```solr_post_status``` filter
* Add missing ```solr_post_types``` filter to ```get_post_types``` call
* Use ```PANTHEON_INDEX_PORT``` instead of hard-coded port in curl

### 1.0.0 ###
* Add Ajax functionality to the facet search widget
* Add date_query support to WP_Query Integration
* Allow ```s``` parameter for WP_Query when Solr is enabled
* Checks for searchable post type before indexing modified post
* Test with WordPress 4.7
* Add ```solr_power_index_all_finished``` action when indexing all posts is complete
* Allow post_title and post_content to score higher
* Make sure that integers and float values are actually of that type. Otherwise, Solr will fail to index the document.

### 0.6.0 ###
* Advanced WP_Query Integration - Meta Queries, Tax Queries
* Translatable strings standardized
* Facet query fixes
* Hide schema submit option if not on the Pantheon platform
* Added a method for API status
* Document available filters
* Fixed single quote/character issues in the facet widget

### 0.5.0 ###
* Add facet search widget
* Update options page internals to utilize WordPress settings API
* Add Behat tests to ensure the plugin's compatibility with the Pantheon platform.
* Defork Solarium and update it to version 3.6.0

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
