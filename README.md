# Solr Search for WordPress #
**Contributors:** [getpantheon](https://profiles.wordpress.org/getpantheon), [Outlandish Josh](https://profiles.wordpress.org/outlandish-josh), [10up](https://profiles.wordpress.org/10up), [collinsinternet](https://profiles.wordpress.org/collinsinternet), [andrew.taylor](https://profiles.wordpress.org/andrew.taylor), [danielbachhuber](https://profiles.wordpress.org/danielbachhuber), [mattleff](https://profiles.wordpress.org/mattleff), [mikengarrett](https://profiles.wordpress.org/mikengarrett), [jazzsequence](https://profiles.wordpress.org/jazzs3quence), [jspellman](https://profiles.wordpress.org/jspellman/), [pwtyler](https://profiles.wordpress.org/pwtyler/)  
**Tags:** search  
**Requires at least:** 4.6  
**Requires PHP:** 7.1  
**Tested up to:** 6.5.2  
**Stable tag:** 2.5.4-dev  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

Improve your user experience with the Apache Solr search engine for your WordPress website.

## Description ##

[![Actively Maintained](https://img.shields.io/badge/Pantheon-Actively_Maintained-yellow?logo=pantheon&color=FFDC28)](https://pantheon.io/docs/oss-support-levels#actively-maintained-support)
[![Lint and Test](https://github.com/pantheon-systems/solr-power/actions/workflows/lint-test.yml/badge.svg)](https://github.com/pantheon-systems/solr-power/actions/workflows/lint-test.yml)

Search is critical for your site, but the default search for WordPress leaves a lot to be desired. Improve your user experience with the Apache Solr search engine for your WordPress website.

* Fast results, with better accuracy.
* Enables faceting on fields such as tags, categories, author, and page type.
* Indexing and faceting on custom fields.
* Drop-in support for [WP_Query](https://codex.wordpress.org/Class_Reference/WP_Query) with the `solr_integrate` parameter set to true.
* Completely replaces default WordPress search, just install and configure.
* Completely integrated into default WordPress theme and search widget.
* Very developer-friendly: uses the modern [Solarium](http://www.solarium-project.org/) library

## Installation ##

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

## Development ##

This plugin is under active development on GitHub:

[https://github.com/pantheon-systems/solr-power](https://github.com/pantheon-systems/solr-power)

Please feel free to file issues there. Pull requests are also welcome! See [CONTRIBUTING.md](https://github.com/pantheon-systems/solr-power/blob/main/CONTRIBUTING.md) for information on contributing.

For further documentation, such as available filters and working with the `SolrPower_Api` class directly, please see the project wiki:

[https://github.com/pantheon-systems/solr-power/wiki](https://github.com/pantheon-systems/solr-power/wiki)

## WP-CLI Support ##

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


## WP_Query Integration ##

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

## Configuration Tips ##

### Searching by author name ###

To support searching by author name (e.g. where "Pantheon" would return posts authored by the "Pantheon" user), add the following to your custom `schema.xml`:

```
<copyField source="post_author" dest="text"/>
```

### Boosting relevancy score by publish date ###

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


## Common issues ##

* Failing to post the schema.xml will result in an error during indexing, "Missing `post_date_iso` field."
* If you have the field and type in the schema, but don't add the `solr_build_document` filter, you will get a similar error.
* If the `post_date_iso` field is missing from the index, Solr will ignore this boost and return regular results.
* Trying to use a regular date field for the boost query will result in an error in the request instead of results.

## Explicit Commit vs Autocommit ##

Once solr has sent the data to the solr server, solr must COMMIT the data to the index and adjust the index and relevancy ratings accordingly before that data can appear in search results.

By default, Solr Search for WordPress has auto-commit disabled. The index is committed when the uncommitted item is two minutes old, or the cron runs. By default, the cron runs on the Pantheon platform every hour.

When autocommit is enabled, Solr Search for WordPress commits data when it sends every post. When running on Pantheon, we recommend leaving autocommit disabled to aid overall site performance.

To enable autocommit, add the following to `wp-config.php` or an mu-plugin.

```php
define( 'SOLRPOWER_DISABLE_AUTOCOMMIT', false );
```

To force-commit data outside of a normal cron run, from the command line, you can run the command below or simply force a cron-run.

```bash
wp solr commit
```

## Security Policy
### Reporting Security Bugs
Please report security bugs found in the Solr Power plugin's source code through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/solr-power). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.
