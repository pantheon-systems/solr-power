=== Solr Search for WordPress ===
Contributors: getpantheon, Outlandish Josh, 10up, collinsinternet, andrew.taylor, danielbachhuber, mattleff, mikengarrett, jazzs3quence
Tags: search
Requires at least: 4.6
Requires PHP: 7.1
Tested up to: 6.0
Stable tag: 2.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Improve your user experience with the Apache Solr search engine for your WordPress website.

== Description ==

[![Travis Build Status](https://travis-ci.org/pantheon-systems/solr-power.svg?branch=master)](https://travis-ci.org/pantheon-systems/solr-power)
[![Circle CI Build Status](https://circleci.com/gh/pantheon-systems/solr-power.svg?style=shield&circle-token=3af522a81a29eab25828a6b0d52e2f1afa7f044b)](https://circleci.com/gh/pantheon-systems/solr-power)

Search is critical for your site, but the default search for WordPress leaves a lot to be desired. Improve your user experience with the Apache Solr search engine for your WordPress website.

* Fast results, with better accuracy.
* Enables faceting on fields such as tags, categories, author, and page type.
* Indexing and faceting on custom fields.
* Drop-in support for [WP_Query](https://codex.wordpress.org/Class_Reference/WP_Query) with the "solr-integrate" parameter set to true.
* Completely replaces default WordPress search, just install and configure.
* Completely integrated into default WordPress theme and search widget.
* Very developer-friendly: uses the modern [Solarium](http://www.solarium-project.org/) library

== Installation ==

The Solr Power plugin can be installed just like you'd install any other WordPress plugin.

Because Solr Power is intended to be a bridge between WordPress and the Apache Solr search engine, you'll need access to a functioning Solr 3.6 instance for the plugin to work as expected. This plugin does not support other versions of Solr. The plugin also requires PHP 7.1 or higher.

If you're using the Solr Power plugin on Pantheon, setting up Apache Solr is as easy as enabling the Apache Solr add-on in your Pantheon dashboard. Once you've done so:

1. Configure which post types, taxonomies and custom fields to index by going to the **Indexing** tab of the Solr Power settings page.
2. Index your existing content by going to the plugin options screen and selecting the applicable **Actions**:
   - - **Index Searchable Post Types**
3. Search on!
4. See the examples/templates directories for more rich implementation guidelines.

If you're using the Solr Power plugin elsewhere, you'll need to install and configure Apache Solr. On a Linux environment, this involves four steps:

1. Install the Java Runtime Environment.
2. Run `./bin/install-solr.sh` to install and run Apache Solr on port 8983.
3. Configuring Solr Power to use this particular Solr instance by setting the `PANTHEON_INDEX_HOST` and `PANTHEON_INDEX_PORT` environment variables.
4. Copying `schema.xml` to the Solr configuration directory (a path similar to `solr/conf/schema.xml`).

Alternatively, there are a couple of community-maintained Docker containers you may be able to use: [kalabox/pantheon-solr](https://hub.docker.com/r/kalabox/pantheon-solr/), [kshaner/solr](https://hub.docker.com/r/kshaner/solr/).

In a local development environment, you can point Solr Power to a custom Solr instance by creating a MU plugin with:

    <?php
    /**
     * Define Solr host IP, port, scheme and path
     * Update these as necessary if your configuration differs
     */
    putenv( 'PANTHEON_INDEX_HOST=192.168.50.4' );
    putenv( 'PANTHEON_INDEX_PORT=8983' );
    add_filter( 'solr_scheme', function(){ return 'http'; });
    define( 'SOLR_PATH', '/solr/wordpress/' );

** Note for Lando users **

If you are using lando for development, the MU plugin is not needed. Lando auto configures everything for your local environment to connect to the docker index it maintains and if you overrite the ENV variables it will mess with that configuration.

== Development ==

This plugin is under active development on GitHub:

[https://github.com/pantheon-systems/solr-power](https://github.com/pantheon-systems/solr-power)

Please feel free to file issues there. Pull requests are also welcome!

For further documentation, such as available filters and working with the `SolrPower_Api` class directly, please see the project wiki:

[https://github.com/pantheon-systems/solr-power/wiki](https://github.com/pantheon-systems/solr-power/wiki)

You may notice there are two sets of tests running, on two different services:

* Travis CI runs the [PHPUnit](https://phpunit.de/) test suite against a Solr instance.
* Circle CI runs the [Behat](http://behat.org/) test suite against a Pantheon site, to ensure the plugin's compatibility with the Pantheon platform.

Both of these test suites can be run locally, with a varying amount of setup.

PHPUnit requires the [WordPress PHPUnit test suite](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/), and access to a database with name `wordpress_test`. If you haven't already configured the test suite locally, you can run `bash bin/install-wp-tests.sh wordpress_test root '' localhost`. You'll also need access to a running Solr instance, in order to run the unit tests against Solr.

Behat requires a Pantheon site with Solr enabled. Once you've created the site, you'll need [install Terminus](https://github.com/pantheon-systems/terminus#installation), and set the `TERMINUS_TOKEN`, `TERMINUS_SITE`, and `TERMINUS_ENV` environment variables. Then, you can run `./bin/behat-prepare.sh` to prepare the site for the test suite.

Note that dependencies are installed via Composer and the `vendor` directory is not committed to the repository. You will need to run `composer install` locally for the plugin to function. You can read more about Composer [here](https://getcomposer.org)

== WP-CLI Support ==

This plugin has [WP-CLI](http://wp-cli.org/) support.

All Solr Power related commands are grouped into the `wp solr` command, see an example:

    $ wp solr
    usage: wp solr check-server-settings
       or: wp solr delete [<id>...] [--all]
       or: wp solr index [--batch=<batch>] [--batch_size=<size>] [--post_type=<post-type>]
       or: wp solr info [--field=<field>] [--format=<format>]
       or: wp solr optimize-index
       or: wp solr repost-schema
       or: wp solr stats [--field=<field>] [--format=<format>]

    See 'wp help solr <command>' for more information on a specific command.

You can see more details about the commands using `wp help solr`:

    **NAME**

      wp solr

    **DESCRIPTION**

      Perform a variety of actions against your Solr instance.

    **SYNOPSIS**

      wp solr <command>

    **SUBCOMMANDS**

      check-server-settings      Check server settings.
      delete                     Remove one or more posts from the index.
      index                      Index all posts for a site.
      info                       Report information about Solr Power configuration.
      optimize-index             Optimize the Solr index.
      repost-schema              Repost schema.xml to Solr.
      stats                      Report stats about indexed content.


== WP_Query Integration ==

Use Solr in a custom WP_Query instead of querying a database. Add ```'solr_integrate' => true``` to the query arguments.

**NOTE:** Currently, only basic queries, tax_query, meta_query and date_query are supported. See ```examples/example.custom_WP_Query.php``` for an example.

A meta_query can use the following compare operators:

* ```'='```
* ```'!='```
* ```'>'```
* ```'>='```
* ```'<'```
* ```'<='```
* ```'LIKE'```
* ```'NOT LIKE'```
* ```'IN'```
* ```'NOT IN'```
* ```'BETWEEN'```
* ```'NOT BETWEEN'```
* ```'EXISTS'```
* ```'NOT EXISTS'```

(```'REGEXP'```, ```'NOT REGEXP'```, and ```'RLIKE'``` are not supported.)

== Configuration Tips ==

= Searching by author name =

To support searching by author name (e.g. where "Pantheon" would return posts authored by the "Pantheon" user), add the following to your custom `schema.xml`:

```
<copyField source="post_author" dest="text"/>
```

= Boosting relevancy score by publish date =

The following guidance can be used to extend the Solr index and modify boosts beyond just this example.

To support math functions on dates, you must add a custom `schema.xml` to Solr and **reindex with the new schema**.

Add the following to `schema.xml`:

      <!-- Add to <types> -->
      <!-- See: https://lucene.apache.org/solr/6_2_0/solr-core/org/apache/solr/schema/TrieDateField.html -->
      <fieldType name="tdate" class="solr.TrieDateField" omitNorms="true" precisionStep="6" positionIncrementGap="0"/>

      <!-- Add to <fields> -->
      <field name="post_date_iso" type="tdate" indexed="true" stored="true" required="true" />

Add the following to your `functions.php` file.


      <?php
      /**
       * Hooks into the document build process to add post date field in proper format.
       */
      function my_solr_build_document( $doc, $post_info ) {
            $post_time = strtotime( $post_info->post_date );
            // Matches format required for TrieDateField
            $doc->setField( 'post_date_iso', gmdate( 'c\Z', $post_time ) );
            return $doc;
      }
      add_filter( 'solr_build_document', 'my_solr_build_document', 10, 2 );

      /**
       * Hooks into query processor, Dismax, to add publish date boost.
       * See: https://www.metaltoad.com/blog/date-boosting-solr-drupal-search-results
       */
      function my_solr_dismax_query( $dismax ) {
            $dismax->setQueryParser( 'edismax' );
            $dismax->setBoostQuery( 'recip(abs(ms(NOW/HOUR,post_date_iso),3.16e-11,1,1))' );
            return $dismax;
      }
      add_filter( 'solr_dismax_query', 'my_solr_dismax_query' );


**Common issues**

* Failing to post the schema.xml will result in an error during indexing, "Missing `post_date_iso` field."
* If you have the field and type in the schema, but don't add the `solr_build_document` filter, you will get a similar error.
* If the `post_date_iso` field is missing from the index, Solr will ignore this boost and return regular results.
* Trying to use a regular date field for the boost query will result in an error in the request instead of results.

**Explicit Commit VS Autocommit**

Once solr has sent the data to the solr server, solr must COMMIT the data to the index and adjust the index and
relevancy ratings accordingly before that data can appear in search results. By default, Solr Search for WordPress does this when it sends every post. It may be necessary on occasion to disable this behavior (e.g. when importing a lot of posts via CSV). To do this, you need add the following code to your index.php in the root of your site install:

```
define( 'SOLRPOWER_DISABLE_AUTOCOMMIT', true );
```

When this variable is defined, Solr Search for WordPress will not commit the index until the cron runs. By default, the cron runs on the Pantheon platform every hour.

To force-commit data when this variable is defined outside of a normal cron run, from the command line, you can run the command below or simply force a cron-run.

```
wp solr commit
```
